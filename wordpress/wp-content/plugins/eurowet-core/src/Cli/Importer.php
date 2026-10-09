<?php
/**
 * Idempotent content importer (keys: _ew_source_key). Reads the repository's data files:
 *  - content/data/products/*.json  (verbatim product fact base: families[] → variants[])
 *  - content/build/{taxonomy,needs,guides,ingredients,pages,reps,materials,company}.json
 *    (built from Markdown/YAML by tools/build_content.py)
 * Production mode (--production) only adds missing data and never overwrites prices, stock or content.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Cli;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Data\Registry;
use Eurowet\Core\Data\ReverseIndex;
use Eurowet\Core\Graph\Cache;
use Eurowet\Core\Graph\Families;

defined( 'ABSPATH' ) || exit;

final class Importer {

	public const SPECIES_MAP = array(
		'pies' => 'pies', 'szczenię' => 'pies', 'szczenieta' => 'pies', 'kot' => 'kot', 'kocię' => 'kot', 'gryzonie/małe ssaki' => 'male-ssaki', 'małe ssaki' => 'male-ssaki', 'gryzonie' => 'male-ssaki', 'male-ssaki' => 'male-ssaki',
		'fretka' => 'fretka', 'ptaki ozdobne' => 'ptaki-ozdobne', 'ptaki-ozdobne' => 'ptaki-ozdobne', 'gołębie' => 'golebie', 'golebie' => 'golebie', 'koń' => 'kon', 'kon' => 'kon', 'konie' => 'kon',
		'zwierzęta gospodarskie' => 'zwierzeta-gospodarskie', 'zwierzeta-gospodarskie' => 'zwierzeta-gospodarskie',
	);

	public const AREA_MAP = array(
		'skóra' => 'skora', 'skora' => 'skora', 'sierść' => 'siersc', 'siersc' => 'siersc', 'uszy' => 'uszy', 'oczy' => 'oczy', 'jama ustna i zęby' => 'jama-ustna-i-zeby', 'jama-ustna-i-zeby' => 'jama-ustna-i-zeby',
		'łapy/opuszki' => 'lapy-i-pazury', 'pazury' => 'lapy-i-pazury', 'lapy-i-pazury' => 'lapy-i-pazury', 'stawy' => 'stawy', 'układ pokarmowy' => 'uklad-pokarmowy', 'uklad-pokarmowy' => 'uklad-pokarmowy',
		'wątroba' => 'watroba', 'watroba' => 'watroba', 'układ moczowy' => 'uklad-moczowy', 'uklad-moczowy' => 'uklad-moczowy', 'serce' => 'serce', 'odporność' => 'odpornosc', 'odpornosc' => 'odpornosc',
		'ogólna kondycja/witalność' => 'kondycja', 'kondycja' => 'kondycja', 'stres/zachowanie' => 'stres-i-zachowanie', 'stres-i-zachowanie' => 'stres-i-zachowanie', 'rozród/ciąża' => 'rozrod', 'rozrod' => 'rozrod',
	);

	/** Canonical product category slugs (old /produkty/{cat}/ page paths) and names. */
	public const CATEGORIES = array(
		'dermokosmetyki-weterynaryjne'            => 'Dermokosmetyki weterynaryjne',
		'kolekcja-kolorpielegnacja'               => 'Kolekcja Kolor & Pielęgnacja',
		'karmy-uzupelniajace'                     => 'Karmy uzupełniające',
		'produkty-do-pielegnacji-oczu-i-uszu'     => 'Pielęgnacja oczu i uszu',
		'pielegnacja-jamy-ustnej-i-zebow'         => 'Pielęgnacja jamy ustnej i zębów',
		'higiena'                                 => 'Higiena',
		'preparaty-wspomagajace-zdrowie'          => 'Preparaty wspomagające zdrowie',
		'preparaty-dla-ptakow-ozdobnych'          => 'Preparaty dla ptaków ozdobnych',
		'preparaty-odstraszajace-pchly-i-kleszcze' => 'Preparaty ochronne przeciw pchłom i kleszczom',
		'akcesoria-dla-zwierzat'                  => 'Akcesoria dla zwierząt',
		'produkty-dla-zwierzat-gospodarskich'     => 'Produkty dla zwierząt gospodarskich',
		'preparaty-dla-golebi'                    => 'Preparaty dla gołębi',
	);

	/** Woo category slug → canonical slug. */
	public const WOO_CAT_MAP = array(
		'pielegnacja-oczu-i-uszu'    => 'produkty-do-pielegnacji-oczu-i-uszu',
		'kolekcja-kolor-pielegnacja' => 'kolekcja-kolorpielegnacja',
	);

	private bool $production;
	private bool $dryRun;
	private bool $images;
	private string $root;
	/** @var array<string, int[]> family_slug → product IDs */
	private array $familyProducts = array();
	/** @var string[] */
	public array $log = array();

	public function __construct( string $root, bool $production = false, bool $dry_run = false, bool $images = true ) {
		$this->root       = rtrim( $root, '/' );
		$this->production = $production;
		$this->dryRun     = $dry_run;
		$this->images     = $images;
	}

	private function say( string $m ): void {
		$this->log[] = $m;
		if ( class_exists( '\WP_CLI' ) ) {
			\WP_CLI::log( $m );
		}
	}

	/** @return array<int|string, mixed> */
	private function json( string $rel ): array {
		$file = $this->root . '/' . ltrim( $rel, '/' );
		if ( ! is_readable( $file ) ) {
			return array();
		}
		$d = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		return is_array( $d ) ? $d : array();
	}

	private function findBySourceKey( string $post_type, string $key ): int {
		$ids = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ew_source_key', 'meta_value' => $key, 'lang' => '', 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		return $ids ? (int) $ids[0] : 0;
	}

	private function termId( string $taxonomy, string $slug, string $name = '' ): int {
		$t = get_term_by( 'slug', $slug, $taxonomy );
		if ( $t ) {
			return (int) $t->term_id;
		}
		if ( $this->dryRun ) {
			return 0;
		}
		$r = wp_insert_term( '' !== $name ? $name : $slug, $taxonomy, array( 'slug' => $slug ) );
		return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
	}

	// ---------------------------------------------------------------- taxonomy

	public function taxonomy(): void {
		$data = $this->json( 'content/build/taxonomy.json' );
		$map  = array( 'species' => 'ew_species', 'areas' => 'ew_area', 'hubs' => 'ew_hub', 'lines' => 'ew_line', 'material_types' => 'ew_material_type' );
		$n    = 0;
		foreach ( $map as $key => $tax ) {
			foreach ( (array) ( $data[ $key ] ?? array() ) as $i => $t ) {
				$slug = sanitize_title( (string) ( $t['slug'] ?? '' ) );
				$name = (string) ( $t['name']['pl'] ?? $t['name'] ?? $slug );
				if ( '' === $slug ) {
					continue;
				}
				$id = $this->termId( $tax, $slug, $name );
				if ( $id && ! $this->dryRun ) {
					wp_update_term( $id, $tax, array( 'name' => $name, 'description' => (string) ( $t['description']['pl'] ?? '' ) ) );
					foreach ( (array) ( $t['meta'] ?? array() ) as $mk => $mv ) {
						Meta::setTerm( $id, (string) $mk, $mv );
					}
					if ( ! isset( $t['meta']['order'] ) ) {
						Meta::setTerm( $id, 'order', (int) $i );
					}
					if ( function_exists( 'pll_set_term_language' ) ) {
						pll_set_term_language( $id, 'pl' );
					}
				}
				++$n;
			}
		}
		foreach ( self::CATEGORIES as $slug => $name ) {
			$id = $this->termId( 'product_cat', $slug, $name );
			if ( $id && function_exists( 'pll_set_term_language' ) && ! $this->dryRun ) {
				pll_set_term_language( $id, 'pl' );
			}
		}
		$this->say( "Taxonomy terms processed: {$n} (+ product categories)." );
	}

	// ---------------------------------------------------------------- products

	public function products(): void {
		$files = glob( $this->root . '/content/data/products/*.json' ) ?: array();
		$made  = 0;
		$upd   = 0;
		ReverseIndex::suspend();
		foreach ( $files as $file ) {
			if ( str_ends_with( $file, 'catalog-summary.json' ) ) {
				continue;
			}
			$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			foreach ( (array) ( $data['families'] ?? array() ) as $fam ) {
				foreach ( (array) ( $fam['variants'] ?? array() ) as $v ) {
					$r = $this->importVariant( (array) $fam, (array) $v );
					'created' === $r ? ++$made : ( 'updated' === $r ? ++$upd : null );
				}
			}
		}
		ReverseIndex::resume();
		Cache::bump();
		$this->say( "Products: {$made} created, {$upd} updated." );
	}

	private static function slugFromPath( ?string $path ): string {
		$path = trim( (string) $path, '/' );
		return '' === $path ? '' : sanitize_title( basename( $path ) );
	}

	private function categorySlug( array $fam, array $v ): string {
		$parts = explode( '/', trim( (string) ( $v['page_path'] ?? '' ), '/' ) );
		if ( count( $parts ) >= 3 && isset( self::CATEGORIES[ $parts[1] ] ) ) {
			return $parts[1];
		}
		foreach ( (array) ( $fam['category_slugs'] ?? array() ) as $c ) {
			$c = self::WOO_CAT_MAP[ $c ] ?? $c;
			if ( isset( self::CATEGORIES[ $c ] ) ) {
				return $c;
			}
		}
		return 'dermokosmetyki-weterynaryjne' === ( $fam['category_slugs'][0] ?? '' ) ? 'dermokosmetyki-weterynaryjne' : ( self::WOO_CAT_MAP[ $fam['category_slugs'][0] ?? '' ] ?? (string) ( $fam['category_slugs'][0] ?? 'higiena' ) );
	}

	private static function html( ?string $text ): string {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return '';
		}
		$text = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', esc_html( $text ) ) ?? $text;
		return '<p>' . implode( '</p><p>', array_filter( array_map( 'trim', preg_split( '/\n+/', $text ) ?: array() ) ) ) . '</p>';
	}

	private function importVariant( array $fam, array $v ): string {
		$key      = 'product:' . ( $v['page_path'] ?? '' ) . '|' . ( $v['woo_id'] ?? '' );
		$slug     = self::slugFromPath( $v['page_path'] ?? '' ) ?: self::slugFromPath( $v['woo_path'] ?? '' );
		$existing = $this->findBySourceKey( 'product', $key );
		if ( ! $existing && ! empty( $v['woo_id'] ) && 'product' === get_post_type( (int) $v['woo_id'] ) ) {
			$existing = (int) $v['woo_id']; // production: same database, keep the real product (orders, stock, SKU)
		}
		if ( ! $existing && $this->production && $slug ) {
			$p        = get_page_by_path( self::slugFromPath( $v['woo_path'] ?? '' ) ?: $slug, OBJECT, 'product' );
			$existing = $p ? (int) $p->ID : 0;
		}
		if ( $this->dryRun ) {
			$this->say( ( $existing ? 'update ' : 'create ' ) . ( $v['title'] ?? $slug ) );
			return $existing ? 'updated' : 'created';
		}
		if ( ! function_exists( 'wc_get_product' ) ) {
			return 'skipped';
		}
		$product = $existing ? wc_get_product( $existing ) : new \WC_Product_Simple();
		if ( ! $product ) {
			return 'skipped';
		}
		$is_new = ! $existing;
		if ( $is_new || ! $this->production ) {
			$product->set_name( html_entity_decode( (string) ( $v['title'] ?? $fam['name'] ?? $slug ), ENT_QUOTES, 'UTF-8' ) );
			$product->set_slug( $slug );
			$product->set_status( 'publish' );
			$product->set_description( self::html( $fam['properties_verbatim'] ?? '' ) );
			$product->set_short_description( esc_html( (string) ( $fam['subtitle_verbatim'] ?? '' ) ) );
			if ( isset( $v['regular_price_pln'] ) && null !== $v['regular_price_pln'] ) {
				$product->set_regular_price( (string) $v['regular_price_pln'] );
				if ( isset( $v['price_pln'] ) && null !== $v['price_pln'] && (float) $v['price_pln'] < (float) $v['regular_price_pln'] ) {
					$product->set_sale_price( (string) $v['price_pln'] );
				}
			} elseif ( isset( $v['price_pln'] ) && null !== $v['price_pln'] ) {
				$product->set_regular_price( (string) $v['price_pln'] );
			}
			$product->set_stock_status( false === ( $v['in_stock'] ?? true ) ? 'outofstock' : 'instock' );
			$product->set_catalog_visibility( 'visible' );
		} elseif ( $this->production && '' !== $slug && $product->get_slug() !== $slug ) {
			$product->set_slug( $slug ); // align URL with the old /produkty/{cat}/{slug}/ page (the old /produkt/ URL gets a 301)
		}
		$cat = $this->categorySlug( $fam, $v );
		$cid = $this->termId( 'product_cat', $cat, self::CATEGORIES[ $cat ] ?? $cat );
		if ( $cid ) {
			$product->set_category_ids( array_values( array_unique( array_merge( $is_new ? array() : $product->get_category_ids(), array( $cid ) ) ) ) );
		}
		$id = $product->save();
		if ( ! $id ) {
			return 'skipped';
		}
		if ( function_exists( 'pll_set_post_language' ) && ( $is_new || ! pll_get_post_language( $id ) ) ) {
			pll_set_post_language( $id, 'pl' );
		}
		$catalog_only = empty( $v['woo_id'] ) || ( isset( $v['price_pln'] ) && null === $v['price_pln'] && ! isset( $v['regular_price_pln'] ) );
		$metas        = array(
			'_ew_subtitle'     => (string) ( $fam['subtitle_verbatim'] ?? '' ),
			'_ew_badges'       => array_values( array_filter( array_map( 'strval', (array) ( $fam['badges_verbatim'] ?? array() ) ) ) ),
			'_ew_properties'   => self::html( $fam['properties_verbatim'] ?? '' ),
			'_ew_usage'        => self::html( $fam['usage_verbatim'] ?? '' ),
			'_ew_indications'  => self::html( $fam['indications_verbatim'] ?? '' ),
			'_ew_intended_for' => self::html( $fam['intended_for_verbatim'] ?? '' ),
			'_ew_precautions'  => self::html( $fam['precautions_verbatim'] ?? '' ),
			'_ew_composition'  => self::html( $fam['composition_verbatim'] ?? '' ),
			'_ew_analytical'   => self::html( $fam['analytical_verbatim'] ?? '' ),
			'_ew_notes'        => self::html( $fam['notes_verbatim'] ?? '' ),
			'_ew_capacity'     => (string) ( $v['capacity'] ?? '' ),
			'_ew_product_type' => (string) ( $fam['product_type'] ?? '' ),
			'_ew_catalog_only' => $catalog_only,
			'_ew_source_path'  => (string) ( $v['page_path'] ?? $v['woo_path'] ?? '' ),
			'_ew_source_key'   => $key,
			'_ew_evidence'     => array_map(
				static fn( $e ) => array( 'need' => 0, 'quote' => (string) ( $e['evidence_verbatim'] ?? '' ), 'field' => (string) ( $e['source_field'] ?? '' ) . ': ' . (string) ( $e['need'] ?? '' ) ),
				array_values( array_filter( (array) ( $fam['needs_evidence'] ?? array() ), static fn( $e ) => ! empty( $e['evidence_verbatim'] ) ) )
			),
		);
		foreach ( $metas as $k => $val ) {
			if ( $this->production && ! $is_new && metadata_exists( 'post', $id, $k ) && '_ew_source_key' !== $k ) {
				continue;
			}
			Meta::set( $id, $k, $val );
		}
		$fslug = sanitize_title( (string) ( $fam['family_slug'] ?? $slug ) );
		wp_set_object_terms( $id, array( $this->termId( 'ew_family', $fslug, (string) ( $fam['name'] ?? $fslug ) ) ), 'ew_family' );
		$species = array_values( array_unique( array_filter( array_map( static fn( $s ) => self::SPECIES_MAP[ mb_strtolower( (string) $s ) ] ?? null, (array) ( $fam['species'] ?? array() ) ) ) ) );
		wp_set_object_terms( $id, array_values( array_filter( array_map( fn( $s ) => $this->termId( 'ew_species', $s ), $species ) ) ), 'ew_species' );
		$areas = array_values( array_unique( array_map( static fn( $a ) => self::AREA_MAP[ mb_strtolower( (string) $a ) ] ?? 'inne', (array) ( $fam['body_areas'] ?? array() ) ) ) );
		wp_set_object_terms( $id, array_values( array_filter( array_map( fn( $a ) => $this->termId( 'ew_area', $a ), $areas ) ) ), 'ew_area' );
		if ( ! empty( $fam['line'] ) ) {
			wp_set_object_terms( $id, array( $this->termId( 'ew_line', sanitize_title( (string) $fam['line'] ), (string) $fam['line'] ) ), 'ew_line' );
		}
		if ( $this->images && ! empty( $v['packshot_url'] ) && ( $is_new || ! $product->get_image_id() ) ) {
			$aid = $this->sideload( (string) $v['packshot_url'], $id, trim( ( $fam['name'] ?? '' ) . ' ' . ( $v['capacity'] ?? '' ) ) );
			if ( $aid ) {
				set_post_thumbnail( $id, $aid );
			}
		}
		$this->familyProducts[ $fslug ][] = $id;
		return $is_new ? 'created' : 'updated';
	}

	/** Downloads once into a local cache, then sideloads into the media library. */
	public function sideload( string $url, int $parent, string $alt ): int {
		$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ew_source_url', 'meta_value' => $url, 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $found ) {
			return (int) $found[0];
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$cache = WP_CONTENT_DIR . '/uploads/ew-import-cache';
		wp_mkdir_p( $cache );
		$local = $cache . '/' . md5( $url ) . '-' . sanitize_file_name( basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		if ( ! is_readable( $local ) ) {
			$tmp = download_url( $url, 120 );
			if ( is_wp_error( $tmp ) ) {
				$this->say( 'Image download failed: ' . $url . ' — ' . $tmp->get_error_message() );
				return 0;
			}
			rename( $tmp, $local ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		$tmp_copy = wp_tempnam( basename( $local ) );
		copy( $local, $tmp_copy );
		$aid = media_handle_sideload( array( 'name' => basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ), 'tmp_name' => $tmp_copy ), $parent );
		if ( is_wp_error( $aid ) ) {
			$this->say( 'Sideload failed: ' . $aid->get_error_message() );
			return 0;
		}
		update_post_meta( (int) $aid, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
		update_post_meta( (int) $aid, '_ew_source_url', esc_url_raw( $url ) );
		return (int) $aid;
	}

	/** Product IDs for a family slug (all capacities), preferring purchasable ~200 ml variant first. */
	public function familyIds( string $family_slug ): array {
		if ( ! isset( $this->familyProducts[ $family_slug ] ) ) {
			$term = get_term_by( 'slug', $family_slug, 'ew_family' );
			$ids  = $term ? get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 50, 'fields' => 'ids', 'lang' => 'pl', 'tax_query' => array( array( 'taxonomy' => 'ew_family', 'terms' => $term->term_id ) ) ) ) : array(); // phpcs:ignore WordPress.DB.SlowDBQuery
			$this->familyProducts[ $family_slug ] = array_map( 'intval', $ids );
		}
		$ids = array_values( array_unique( $this->familyProducts[ $family_slug ] ) );
		usort(
			$ids,
			static function ( $a, $b ) {
				$pa = Meta::bool( $a, '_ew_catalog_only' ) ? 1 : 0;
				$pb = Meta::bool( $b, '_ew_catalog_only' ) ? 1 : 0;
				if ( $pa !== $pb ) {
					return $pa <=> $pb;
				}
				$ca = abs( Families::capacityValue( (string) Meta::get( $a, '_ew_capacity', '' ) ) - 200 );
				$cb = abs( Families::capacityValue( (string) Meta::get( $b, '_ew_capacity', '' ) ) - 200 );
				return $ca <=> $cb;
			}
		);
		return $ids;
	}

	// ---------------------------------------------------------------- generic content (needs, guides, ingredients, pages)

	/**
	 * @param array<string, mixed> $item  {slug, title, content, excerpt, meta{}, terms{tax: [slugs]}, translations{lang: {...}}}
	 */
	private function upsertPost( string $post_type, array $item, string $lang = 'pl', int $translation_of = 0 ): int {
		$key = $post_type . ':' . $lang . ':' . (string) $item['slug'];
		$id  = $this->findBySourceKey( $post_type, $key );
		if ( $this->dryRun ) {
			$this->say( ( $id ? 'update ' : 'create ' ) . $key );
			return $id;
		}
		if ( $this->production && $id && ! empty( $item['protect'] ) ) {
			return $id;
		}
		$arr = array(
			'post_type'    => $post_type,
			'post_status'  => (string) ( $item['status'] ?? 'publish' ),
			'post_title'   => (string) $item['title'],
			'post_name'    => sanitize_title( (string) ( $item['post_name'] ?? $item['slug'] ) ),
			'post_content' => (string) ( $item['content'] ?? '' ),
			'post_excerpt' => (string) ( $item['excerpt'] ?? '' ),
			'menu_order'   => (int) ( $item['menu_order'] ?? 0 ),
		);
		if ( ! empty( $item['date'] ) ) {
			$arr['post_date']     = (string) $item['date'];
			$arr['post_date_gmt'] = get_gmt_from_date( (string) $item['date'] );
		}
		if ( ! empty( $item['template'] ) ) {
			$arr['page_template'] = (string) $item['template'];
		}
		if ( ! empty( $item['parent_slug'] ) ) {
			$parent = get_page_by_path( (string) $item['parent_slug'], OBJECT, $post_type );
			$arr['post_parent'] = $parent ? $parent->ID : 0;
		}
		if ( $id ) {
			$arr['ID'] = $id;
			$id        = (int) wp_update_post( $arr, true );
		} else {
			$id = (int) wp_insert_post( $arr, true );
		}
		if ( ! $id ) {
			return 0;
		}
		Meta::set( $id, '_ew_source_key', $key );
		foreach ( (array) ( $item['meta'] ?? array() ) as $k => $v ) {
			if ( isset( Registry::META[ $post_type ][ $k ] ) && ! Registry::relation( $post_type, $k ) && '_ew_products' !== $k ) {
				Meta::set( $id, (string) $k, $v );
			}
		}
		foreach ( (array) ( $item['terms'] ?? array() ) as $tax => $slugs ) {
			$ids = array_values( array_filter( array_map( fn( $s ) => $this->termId( (string) $tax, sanitize_title( (string) $s ) ), (array) $slugs ) ) );
			wp_set_object_terms( $id, $ids, (string) $tax );
		}
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, $lang );
			if ( $translation_of && function_exists( 'pll_get_post_translations' ) && function_exists( 'pll_save_post_translations' ) ) {
				$tr          = pll_get_post_translations( $translation_of );
				$tr['pl']    = $translation_of;
				$tr[ $lang ] = $id;
				pll_save_post_translations( $tr );
			}
		}
		if ( ! empty( $item['image'] ) ) {
			$file = $this->root . '/content/' . ltrim( (string) $item['image'], '/' );
			if ( is_readable( $file ) && ! get_post_thumbnail_id( $id ) ) {
				$aid = $this->sideloadLocal( $file, $id, (string) ( $item['image_alt'] ?? '' ) );
				if ( $aid ) {
					set_post_thumbnail( $id, $aid );
				}
			}
		}
		return $id;
	}

	private function sideloadLocal( string $file, int $parent, string $alt ): int {
		$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ew_source_url', 'meta_value' => 'repo:' . basename( $file ), 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $found ) {
			return (int) $found[0];
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$aid = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), $parent );
		if ( is_wp_error( $aid ) ) {
			return 0;
		}
		update_post_meta( (int) $aid, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
		update_post_meta( (int) $aid, '_ew_source_url', 'repo:' . basename( $file ) );
		// Images from content/assets/higgsfield/ or composites/ are AI-assisted: flagged for the visible disclosure.
		if ( preg_match( '#/assets/(higgsfield|composites)/#', $file ) ) {
			update_post_meta( (int) $aid, '_ew_ai_generated', '1' );
		}
		return (int) $aid;
	}

	/** Imports needs/guides/ingredients/pages from content/build/{type}.json (two passes: posts, then relations). */
	public function content( string $type ): void {
		$pt   = array( 'needs' => 'ew_need', 'guides' => 'ew_guide', 'ingredients' => 'ew_ingredient', 'pages' => 'page' )[ $type ] ?? '';
		$data = $this->json( 'content/build/' . $type . '.json' );
		if ( '' === $pt || ! $data ) {
			$this->say( "No data for {$type} (run tools/build_content.py)." );
			return;
		}
		$ids = array();
		ReverseIndex::suspend();
		foreach ( $data as $item ) {
			$pl = $this->upsertPost( $pt, (array) $item, 'pl' );
			if ( ! $pl ) {
				continue;
			}
			$ids[ (string) $item['slug'] ] = array( 'pl' => $pl );
			foreach ( (array) ( $item['translations'] ?? array() ) as $lang => $tr ) {
				if ( ! is_array( $tr ) || empty( $tr['title'] ) ) {
					continue;
				}
				$tr['slug'] = (string) ( $tr['slug'] ?? $item['slug'] );
				$tr['meta'] = array_merge( (array) ( $item['meta_shared'] ?? array() ), (array) ( $tr['meta'] ?? array() ) );
				$tr['terms'] = (array) ( $item['terms'] ?? array() );
				$tr['image'] = $item['image'] ?? '';
				$tid        = $this->upsertPost( $pt, $tr, (string) $lang, $pl );
				if ( $tid ) {
					$ids[ (string) $item['slug'] ][ (string) $lang ] = $tid;
				}
			}
		}
		if ( ! $this->dryRun ) {
			foreach ( $data as $item ) {
				foreach ( $ids[ (string) $item['slug'] ] ?? array() as $lang => $pid ) {
					$this->relations( $pt, (int) $pid, (array) $item, (string) $lang );
				}
			}
		}
		ReverseIndex::resume();
		Cache::bump();
		$this->say( sprintf( '%s: %d items imported.', $type, count( $ids ) ) );
	}

	private function postIdBySlug( string $post_type, string $slug, string $lang ): int {
		$q = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ew_source_key', 'meta_value' => $post_type . ':' . $lang . ':' . $slug, 'lang' => '', 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! $q && 'pl' !== $lang ) {
			return $this->postIdBySlug( $post_type, $slug, 'pl' );
		}
		return $q ? (int) $q[0] : 0;
	}

	private function productFor( string $family_slug, string $lang ): int {
		$ids = $this->familyIds( sanitize_title( $family_slug ) );
		$pid = $ids[0] ?? 0;
		return ( $pid && 'pl' !== $lang && function_exists( 'pll_get_post' ) ) ? ( (int) pll_get_post( $pid, $lang ) ?: $pid ) : $pid;
	}

	private function relations( string $pt, int $id, array $item, string $lang ): void {
		$rel = (array) ( $item['relations'] ?? array() );
		$map = array( 'guides' => array( '_ew_guides', 'ew_guide' ), 'needs' => array( '_ew_needs', 'ew_need' ), 'related' => array( '_ew_related', 'ew_guide' ) );
		foreach ( $map as $k => [ $meta, $type ] ) {
			if ( isset( $rel[ $k ] ) && isset( Registry::META[ $pt ][ $meta ] ) ) {
				Meta::set( $id, $meta, array_values( array_filter( array_map( fn( $s ) => $this->postIdBySlug( $type, (string) $s, $lang ), (array) $rel[ $k ] ) ) ) );
			}
		}
		if ( ! empty( $rel['next'] ) && isset( Registry::META[ $pt ]['_ew_next'] ) ) {
			Meta::set( $id, '_ew_next', $this->postIdBySlug( 'ew_guide', (string) $rel['next'], $lang ) );
		}
		if ( isset( $rel['products'] ) && 'ew_guide' === $pt ) {
			Meta::set( $id, '_ew_products', array_values( array_filter( array_map( fn( $f ) => $this->productFor( (string) $f, $lang ), (array) $rel['products'] ) ) ) );
		}
		if ( isset( $rel['need_products'] ) && 'ew_need' === $pt ) {
			$rows = array();
			foreach ( (array) $rel['need_products'] as $r ) {
				$pid = $this->productFor( (string) ( $r['family_slug'] ?? '' ), $lang );
				if ( $pid ) {
					$rows[] = array( 'product_id' => $pid, 'role' => (string) ( $r['role'] ?? 'similar' ), 'reason' => (string) ( $r['reason'][ $lang ] ?? $r['reason']['pl'] ?? $r['reason'] ?? '' ), 'evidence' => (string) ( $r['evidence'] ?? '' ) );
				}
			}
			Meta::set( $id, '_ew_products', $rows );
		}
		if ( isset( $rel['ingredient_families'] ) && 'ew_ingredient' === $pt ) {
			foreach ( (array) $rel['ingredient_families'] as $fslug ) {
				foreach ( $this->familyIds( sanitize_title( (string) $fslug ) ) as $pid ) {
					$pid = 'pl' !== $lang && function_exists( 'pll_get_post' ) ? ( (int) pll_get_post( $pid, $lang ) ?: 0 ) : $pid;
					if ( $pid ) {
						Meta::set( $pid, '_ew_key_ingredients', array_values( array_unique( array_merge( Meta::ids( $pid, '_ew_key_ingredients' ), array( $id ) ) ) ) );
					}
				}
			}
			$quotes = array();
			foreach ( (array) ( $rel['function_quotes'] ?? array() ) as $q ) {
				$pid = $this->productFor( (string) ( $q['family_slug'] ?? '' ), $lang );
				if ( $pid ) {
					$quotes[] = array( 'product_id' => $pid, 'quote' => (string) ( $q['quote'] ?? '' ) );
				}
			}
			if ( $quotes ) {
				Meta::set( $id, '_ew_function_quotes', $quotes );
			}
		}
	}

	// ---------------------------------------------------------------- reps, materials, company

	public function reps(): void {
		$n = 0;
		foreach ( (array) $this->json( 'content/build/reps.json' ) as $i => $r ) {
			$item = array(
				'slug'  => sanitize_title( (string) ( $r['slug'] ?? $r['name'] ?? 'ph-' . $i ) ),
				'title' => (string) ( $r['name'] ?? '' ),
				'status' => 'publish',
				'meta'  => array(
					'_ew_first_name'   => (string) ( $r['first_name'] ?? '' ),
					'_ew_last_name'    => (string) ( $r['last_name'] ?? '' ),
					'_ew_position'     => (string) ( $r['position'] ?? '' ),
					'_ew_phone'        => (string) ( $r['phone'] ?? '' ),
					'_ew_email'        => (string) ( $r['email'] ?? '' ),
					'_ew_voivodeships' => array_values( (array) ( $r['voivodeships'] ?? array() ) ),
					'_ew_segments'     => array_values( (array) ( $r['segments'] ?? array() ) ),
					'_ew_active'       => true,
					'_ew_order'        => (int) $i,
				),
			);
			$id = $this->upsertPost( 'ew_rep', $item, 'pl' );
			if ( $id && ! empty( $r['photo_url'] ) && ! $this->dryRun && ! get_post_thumbnail_id( $id ) ) {
				$aid = $this->sideload( (string) $r['photo_url'], $id, (string) ( $r['name'] ?? '' ) );
				$aid && set_post_thumbnail( $id, $aid );
			}
			$n += $id ? 1 : 0;
		}
		$this->say( "Reps: {$n}." );
	}

	public function materials(): void {
		$n = 0;
		foreach ( (array) $this->json( 'content/build/materials.json' ) as $i => $m ) {
			$item = array(
				'slug'       => sanitize_title( (string) ( $m['slug'] ?? $m['title'] ) ),
				'title'      => (string) $m['title'],
				'menu_order' => (int) $i,
				'terms'      => array( 'ew_material_type' => array( (string) ( $m['type'] ?? 'katalog' ) ) ),
				'meta'       => array( '_ew_lang' => (string) ( $m['lang'] ?? 'pl' ), '_ew_source_url' => (string) $m['url'] ),
			);
			$id = $this->upsertPost( 'ew_material', $item, 'pl' );
			if ( $id && $this->images && ! $this->dryRun && ! Meta::int( $id, '_ew_file' ) ) {
				$aid = $this->sideload( (string) $m['url'], $id, (string) $m['title'] );
				$aid && Meta::set( $id, '_ew_file', $aid );
			}
			$n += $id ? 1 : 0;
		}
		$this->say( "Materials: {$n}." );
	}

	public function company(): void {
		$c = $this->json( 'content/build/company.json' );
		if ( ! $c || $this->dryRun ) {
			$this->say( $c ? 'Company (dry run).' : 'No company data.' );
			return;
		}
		$opts = (array) get_option( 'ew_settings', array() );
		foreach ( array( 'contact_phone', 'contact_phone_sales', 'contact_email', 'company_name', 'company_address', 'company_ids', 'company_social', 'company_summary' ) as $k ) {
			if ( isset( $c[ $k ] ) && ( ! $this->production || empty( $opts[ $k ] ) ) ) {
				$opts[ $k ] = is_array( $c[ $k ] ) ? implode( "\n", $c[ $k ] ) : (string) $c[ $k ];
			}
		}
		update_option( 'ew_settings', $opts );
		$this->say( 'Company settings imported.' );
	}

	public function redirects(): void {
		$file = $this->root . '/content/redirects.csv';
		if ( ! is_readable( $file ) ) {
			$this->say( 'No content/redirects.csv.' );
			return;
		}
		$s = \Eurowet\Core\Seo\Redirects::import( $file );
		$this->say( sprintf( 'Redirects: %d added, %d updated, %d skipped. %s', $s['added'], $s['updated'], $s['skipped'], implode( '; ', $s['errors'] ) ) );
	}
}
