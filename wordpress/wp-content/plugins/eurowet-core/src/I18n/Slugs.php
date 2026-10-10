<?php
/**
 * Localized URL bases for the secondary languages (Polylang free cannot translate post-type/taxonomy bases):
 *   PL  /produkty/{kategoria}/{produkt}/   /potrzeby/…   /porady/…   /skladniki/…
 *   EN  /en/products/{category}/{product}/ /en/needs/…   /en/guides/… /en/ingredients/…   (= the old EN product URLs)
 *   FR  /fr/produits/…                     /fr/besoins/… /fr/conseils/… /fr/ingredients/…
 *   UA  /ua/produkty/…                     /ua/potreby/… /ua/porady/…   /ua/inhredienty/…
 * Rewrite rules are added for every base; generated links (posts, archives, terms) are rewritten for posts/terms in
 * those languages. The default language keeps its existing URLs untouched.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\I18n;

defined( 'ABSPATH' ) || exit;

final class Slugs {

	/** Polish bases (as registered). */
	public const PL = array( 'product' => 'produkty', 'ew_need' => 'potrzeby', 'ew_guide' => 'porady', 'ew_ingredient' => 'skladniki' );

	/** lang => post type => base. ew_hub terms live under the guides base, product_cat under the product base. */
	public const BASES = array(
		'en' => array( 'product' => 'products', 'ew_need' => 'needs', 'ew_guide' => 'guides', 'ew_ingredient' => 'ingredients' ),
		'fr' => array( 'product' => 'produits', 'ew_need' => 'besoins', 'ew_guide' => 'conseils', 'ew_ingredient' => 'ingredients' ),
		'ua' => array( 'product' => 'produkty', 'ew_need' => 'potreby', 'ew_guide' => 'porady', 'ew_ingredient' => 'inhredienty' ),
	);

	public static function register(): void {
		add_filter( 'rewrite_rules_array', array( self::class, 'rules' ), 100 );
		add_filter( 'post_type_link', array( self::class, 'postLink' ), 999, 2 );
		add_filter( 'post_type_archive_link', array( self::class, 'archiveLink' ), 999, 2 );
		add_filter( 'term_link', array( self::class, 'termLink' ), 999, 3 );
	}

	/**
	 * @param array<string, string> $rules
	 * @return array<string, string>
	 */
	public static function rules( $rules ) {
		if ( ! is_array( $rules ) ) {
			return $rules;
		}
		$add = array();
		foreach ( self::BASES as $lang => $bases ) {
			$l = preg_quote( $lang, '#' );
			foreach ( $bases as $type => $base ) {
				$b = preg_quote( $base, '#' );
				if ( 'product' === $type ) {
					$add[ "{$l}/{$b}/?$" ]                                    = "index.php?post_type=product&lang={$lang}";
					$add[ "{$l}/{$b}/page/?([0-9]{1,})/?$" ]                  = "index.php?post_type=product&paged=\$matches[1]&lang={$lang}";
					$add[ "{$l}/{$b}/([^/]+)/page/?([0-9]{1,})/?$" ]          = "index.php?product_cat=\$matches[1]&paged=\$matches[2]&lang={$lang}";
					$add[ "{$l}/{$b}/([^/]+)/([^/]+)/?$" ]                    = "index.php?product_cat=\$matches[1]&product=\$matches[2]&lang={$lang}";
					$add[ "{$l}/{$b}/([^/]+)/?$" ]                            = "index.php?product_cat=\$matches[1]&lang={$lang}";
					continue;
				}
				$add[ "{$l}/{$b}/?$" ]                   = "index.php?post_type={$type}&lang={$lang}";
				$add[ "{$l}/{$b}/page/?([0-9]{1,})/?$" ] = "index.php?post_type={$type}&paged=\$matches[1]&lang={$lang}";
				// Guides base also serves hubs (/en/guides/{hub}/) — Graph\Routing::resolve() decides guide vs hub.
				$add[ "{$l}/{$b}/([^/]+)/page/?([0-9]{1,})/?$" ] = 'ew_guide' === $type ? "index.php?ew_hub=\$matches[1]&paged=\$matches[2]&lang={$lang}" : "index.php?{$type}=\$matches[1]&post_type={$type}&name=\$matches[1]&paged=\$matches[2]&lang={$lang}";
				$add[ "{$l}/{$b}/([^/]+)/?$" ]           = "index.php?{$type}=\$matches[1]&post_type={$type}&name=\$matches[1]&lang={$lang}";
			}
		}
		return $add + $rules;
	}

	private static function swap( string $url, string $lang, string $type ): string {
		$base = self::BASES[ $lang ][ $type ] ?? '';
		$pl   = self::PL[ $type ] ?? '';
		if ( '' === $base || '' === $pl || $base === $pl ) {
			return $url;
		}
		return (string) preg_replace( '#/' . preg_quote( $lang, '#' ) . '/' . preg_quote( $pl, '#' ) . '(/|$)#', '/' . $lang . '/' . $base . '$1', $url, 1 );
	}

	private static function postLang( int $post_id ): string {
		return function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $post_id, 'slug' ) : '';
	}

	public static function postLink( $url, $post ) {
		if ( ! $post instanceof \WP_Post || ! isset( self::PL[ $post->post_type ] ) ) {
			return $url;
		}
		$lang = self::postLang( (int) $post->ID );
		return isset( self::BASES[ $lang ] ) ? self::swap( (string) $url, $lang, $post->post_type ) : $url;
	}

	public static function archiveLink( $url, $post_type ) {
		$lang = Polylang::currentLang();
		return isset( self::BASES[ $lang ], self::PL[ (string) $post_type ] ) ? self::swap( (string) $url, $lang, (string) $post_type ) : $url;
	}

	public static function termLink( $url, $term, $taxonomy ) {
		$map = array( 'product_cat' => 'product', 'ew_hub' => 'ew_guide' );
		if ( ! isset( $map[ $taxonomy ] ) || ! $term instanceof \WP_Term || ! function_exists( 'pll_get_term_language' ) ) {
			return $url;
		}
		$lang = (string) pll_get_term_language( $term->term_id, 'slug' );
		return isset( self::BASES[ $lang ] ) ? self::swap( (string) $url, $lang, $map[ $taxonomy ] ) : $url;
	}

	/** Archive URL of a post type (or the shop) in a language. */
	public static function archiveUrl( string $type, ?string $lang = null ): string {
		$lang = $lang ?? Polylang::currentLang();
		if ( isset( self::BASES[ $lang ][ $type ] ) ) {
			return home_url( '/' . $lang . '/' . self::BASES[ $lang ][ $type ] . '/' );
		}
		return home_url( '/' . ( self::PL[ $type ] ?? '' ) . '/' );
	}
}
