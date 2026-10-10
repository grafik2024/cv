<?php
/**
 * Idempotent site configuration used by `wp eurowet setup` (local QA, staging and production).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Cli;

use Eurowet\Core\I18n\Module as I18n;

defined( 'ABSPATH' ) || exit;

final class Setup {

	/** slug => [title, template] */
	public const PAGES = array(
		'strona-glowna'          => array( 'Strona główna', '' ),
		'produkty'               => array( 'Produkty', '' ),
		'wspolpraca-b2b'         => array( 'Współpraca B2B', 'page-b2b.php' ),
		'marka-wlasna'           => array( 'Marka własna', 'page-private-label.php' ),
		'znajdz-przedstawiciela' => array( 'Znajdź przedstawiciela', 'page-reps.php' ),
		'o-firmie'               => array( 'O firmie', 'page-about.php' ),
		'kontakt'                => array( 'Kontakt', 'page-contact.php' ),
		'pobierz'                => array( 'Materiały do pobrania', 'page-downloads.php' ),
	);

	/** @return string[] */
	public static function plan( string $root ): array {
		return array(
			'Permalinks: /%postname%/',
			'WooCommerce: shop page /produkty/, product URLs /produkty/{kategoria}/{produkt}/, categories /produkty/{kategoria}/',
			'Pages: ' . implode( ', ', array_keys( self::PAGES ) ) . ' (created only when missing; templates assigned)',
			'Menus: primary + footer + legal from ' . $root . '/content/menus.json',
			'Polylang: PL default (no prefix), EN, FR, UA (locale uk); browser language detection OFF',
			'Yoast: breadcrumbs, organisation, title templates for Potrzeby/Porady/Składniki',
			'Reading: static front page; comments closed by default; Europe/Warsaw; d.m.Y',
			'Elementor kit: brand colours and Manrope font (if Elementor active)',
		);
	}

	/** @return string[] */
	public static function run( string $root, bool $production ): array {
		$log = array();
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'timezone_string', 'Europe/Warsaw' );
		update_option( 'date_format', 'd.m.Y' );
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );

		$ids = array();
		foreach ( self::PAGES as $slug => [ $title, $template ] ) {
			$page = get_page_by_path( $slug );
			$id   = $page ? (int) $page->ID : (int) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ) );
			if ( $id && $template && ( ! $production || ! get_post_meta( $id, '_wp_page_template', true ) || 'default' === get_post_meta( $id, '_wp_page_template', true ) ) ) {
				update_post_meta( $id, '_wp_page_template', $template );
			}
			if ( $id && function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) && ! pll_get_post_language( $id ) ) {
				pll_set_post_language( $id, 'pl' );
			}
			$ids[ $slug ] = $id;
			$log[]        = ( $page ? 'Page exists: ' : 'Page created: ' ) . $slug;
		}
		if ( ! $production || ! get_option( 'page_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids['strona-glowna'] );
		}
		$news = get_page_by_path( 'aktualnosci' );
		if ( $news ) {
			update_option( 'page_for_posts', (int) $news->ID );
		} elseif ( ! $production ) {
			update_option( 'page_for_posts', (int) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Aktualności', 'post_name' => 'aktualnosci' ) ) );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			update_option( 'woocommerce_shop_page_id', $ids['produkty'] );
			$perma                   = (array) get_option( 'woocommerce_permalinks', array() );
			$perma['product_base']   = '/produkty/%product_cat%/';
			$perma['category_base']  = 'produkty';
			$perma['tag_base']       = 'produkty-tag';
			$perma['attribute_base'] = '';
			$perma['use_verbose_page_rules'] = false;
			update_option( 'woocommerce_permalinks', $perma );
			if ( ! $production ) {
				update_option( 'woocommerce_currency', 'PLN' );
				update_option( 'woocommerce_default_country', 'PL' );
				update_option( 'woocommerce_price_num_decimals', '2' );
				update_option( 'woocommerce_price_decimal_sep', ',' );
				update_option( 'woocommerce_price_thousand_sep', ' ' );
				update_option( 'woocommerce_currency_pos', 'right_space' );
				// WooCommerce ≥ 9.1 starts new stores in "coming soon" mode (replaces every shop page). Staging is
				// protected at server level instead (HTTP auth + noindex), so the store itself must be visible.
				update_option( 'woocommerce_coming_soon', 'no' );
				update_option( 'woocommerce_store_pages_only', 'no' );
			}
			$log[] = 'WooCommerce site visibility: ' . ( 'yes' === get_option( 'woocommerce_coming_soon' ) ? 'COMING SOON (check before launch)' : 'live' );
			$log   = array_merge( $log, self::normalizeProductCategories() );
			$log[] = 'WooCommerce permalinks: /produkty/%product_cat%/';
		}

		foreach ( I18n::setupLanguages() as $l ) {
			$log[] = $l;
		}
		$log = array_merge( $log, self::yoast(), self::menus( $root ), self::elementorKit() );
		flush_rewrite_rules( false );
		return $log;
	}

	/**
	 * Production catalogue uses a parent category "sklep" (/kategoria-produktu/sklep/{kategoria}/) and two slugs that
	 * differ from the old content pages. With product URLs /produkty/%product_cat%/{produkt}/ the parent would leak into
	 * every URL (/produkty/sklep/…), so: re-parent children of "sklep" to the top level, detach "sklep" from products and
	 * align the two slugs with the old /produkty/{kategoria}/ pages. Idempotent; old URLs are covered by the 301 map.
	 *
	 * @return string[]
	 */
	private static function normalizeProductCategories(): array {
		$log  = array();
		$shop = get_term_by( 'slug', 'sklep', 'product_cat' );
		if ( $shop instanceof \WP_Term ) {
			foreach ( (array) get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $shop->term_id, 'hide_empty' => false ) ) as $child ) {
				if ( $child instanceof \WP_Term ) {
					wp_update_term( $child->term_id, 'product_cat', array( 'parent' => 0 ) );
					$log[] = 'Category re-parented to top level: ' . $child->slug;
				}
			}
			$ids = get_objects_in_term( $shop->term_id, 'product_cat' );
			foreach ( is_array( $ids ) ? $ids : array() as $pid ) {
				wp_remove_object_terms( (int) $pid, $shop->term_id, 'product_cat' );
			}
			$log[] = sprintf( 'Category "sklep" detached from %d products (term kept, empty).', is_array( $ids ) ? count( $ids ) : 0 );
		}
		foreach ( Importer::WOO_CAT_MAP as $old => $canonical ) {
			$t = get_term_by( 'slug', $old, 'product_cat' );
			if ( $t instanceof \WP_Term && ! get_term_by( 'slug', $canonical, 'product_cat' ) ) {
				wp_update_term( $t->term_id, 'product_cat', array( 'slug' => $canonical ) );
				$log[] = "Category slug {$old} → {$canonical}";
			}
		}
		return $log;
	}

	/** @return string[] */
	private static function yoast(): array {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return array( 'Yoast not active — skipped.' );
		}
		$t = (array) get_option( 'wpseo_titles', array() );
		$t['breadcrumbs-enable']    = true;
		$t['breadcrumbs-home']      = 'EUROWET';
		$t['breadcrumbs-sep']       = '›';
		$t['separator']             = 'sc-pipe';
		$t['company_or_person']     = 'company';
		$t['company_name']          = 'EUROWET';
		$t['title-ew_guide']        = '%%title%% %%sep%% Porady Eurowet';
		$t['title-ew_need']         = '%%title%% — pielęgnacja i dobór produktu %%sep%% Eurowet';
		$t['title-ew_ingredient']   = '%%title%% — składnik %%sep%% Eurowet';
		$t['title-ptarchive-ew_guide'] = 'Porady i wiedza o pielęgnacji psów i kotów %%sep%% Eurowet';
		$t['title-ptarchive-ew_need']  = 'Dobierz produkt do potrzeby zwierzęcia %%sep%% Eurowet';
		$t['title-tax-ew_hub']      = '%%term_title%% — porady %%sep%% Eurowet';
		// Breadcrumbs: Home › Produkty › {kategoria} › {produkt}; Home › Porady › {dział} › {porada}.
		$t['post_types-product-maintax']  = 'product_cat';
		$t['post_types-ew_guide-maintax'] = 'ew_hub';
		$t['breadcrumbs-display-blog-page'] = true;
		foreach ( array( 'ew_rep', 'ew_material', 'ew_lead' ) as $pt ) {
			$t[ 'noindex-' . $pt ] = true;
		}
		foreach ( array( 'ew_species', 'ew_area', 'ew_family', 'ew_material_type' ) as $tax ) {
			$t[ 'noindex-tax-' . $tax ] = true;
		}
		update_option( 'wpseo_titles', $t );
		$logo = get_stylesheet_directory_uri() . '/assets/brand/eurowet-logo.png';
		$s    = (array) get_option( 'wpseo_social', array() );
		update_option( 'wpseo_social', $s );
		return array( 'Yoast: breadcrumbs on, organisation EUROWET, CPT title templates, noindex for internal types. Logo to set in Yoast UI: ' . $logo );
	}

	/** @return string[] */
	private static function menus( string $root ): array {
		$file = $root . '/content/menus.json';
		if ( ! is_readable( $file ) ) {
			return array( 'No content/menus.json — menus skipped.' );
		}
		$spec = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$log  = array();
		$locs = (array) get_theme_mod( 'nav_menu_locations', array() );
		foreach ( (array) $spec as $location => $menu ) {
			$name = (string) ( $menu['name'] ?? $location );
			$obj  = wp_get_nav_menu_object( $name );
			$mid  = $obj ? (int) $obj->term_id : (int) wp_create_nav_menu( $name );
			if ( ! $mid ) {
				continue;
			}
			foreach ( (array) wp_get_nav_menu_items( $mid ) as $it ) {
				wp_delete_post( $it->ID, true );
			}
			$add = static function ( array $items, int $parent ) use ( &$add, $mid ): void {
				foreach ( $items as $i => $it ) {
					$url = (string) ( $it['url'] ?? '#' );
					$id  = wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => (string) $it['title'], 'menu-item-url' => 0 === strpos( $url, '/' ) ? home_url( $url ) : $url, 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-position' => $i + 1 ) );
					if ( ! is_wp_error( $id ) && ! empty( $it['children'] ) ) {
						$add( (array) $it['children'], (int) $id );
					}
				}
			};
			$add( (array) ( $menu['items'] ?? array() ), 0 );
			$locs[ $location ] = $mid;
			$log[]             = "Menu {$location}: {$name}";
		}
		set_theme_mod( 'nav_menu_locations', $locs );
		// Polylang stores menu locations per language; assign the Polish menus to the default language.
		$pll = get_option( 'polylang' );
		if ( is_array( $pll ) ) {
			$theme = get_stylesheet();
			foreach ( $locs as $location => $mid ) {
				if ( isset( $spec[ $location ] ) ) {
					$pll['nav_menus'][ $theme ][ $location ]['pl'] = (int) $mid;
				}
			}
			update_option( 'polylang', $pll );
			$log[] = 'Polylang: menus assigned to PL; other languages use the theme fallback until translated menus exist.';
		}
		return $log;
	}

	/** @return string[] */
	private static function elementorKit(): array {
		$kit = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit || ! defined( 'ELEMENTOR_VERSION' ) ) {
			return array( 'Elementor kit: skipped.' );
		}
		$s                      = (array) get_post_meta( $kit, '_elementor_page_settings', true );
		$s['system_colors']     = array(
			array( '_id' => 'primary', 'title' => 'Eurowet niebieski', 'color' => '#0074A8' ),
			array( '_id' => 'secondary', 'title' => 'Eurowet zielony', 'color' => '#38B448' ),
			array( '_id' => 'text', 'title' => 'Tekst', 'color' => '#1B2A36' ),
			array( '_id' => 'accent', 'title' => 'Akcent ciemny', 'color' => '#00618C' ),
		);
		$s['system_typography'] = array(
			array( '_id' => 'primary', 'title' => 'Nagłówki', 'typography_typography' => 'custom', 'typography_font_family' => 'Manrope', 'typography_font_weight' => '800' ),
			array( '_id' => 'text', 'title' => 'Tekst', 'typography_typography' => 'custom', 'typography_font_family' => 'Manrope', 'typography_font_weight' => '400' ),
		);
		update_post_meta( $kit, '_elementor_page_settings', $s );
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		return array( 'Elementor kit: brand colours + Manrope.' );
	}
}
