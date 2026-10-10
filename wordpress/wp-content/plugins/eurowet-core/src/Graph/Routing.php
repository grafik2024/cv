<?php
/**
 * Resolves shared URL bases: /porady/{slug}/ (guide or hub) and /produkty/{cat}/{product}/ vs categories.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

defined( 'ABSPATH' ) || exit;

final class Routing {

	public function register(): void {
		add_filter( 'request', array( self::class, 'resolve' ), 20 );
		add_action( 'init', array( self::class, 'addRewriteRules' ), 20 );
		add_filter( 'query_vars', static fn( $v ) => array_merge( (array) $v, array( 'ew_llms' ) ) );
		add_filter( 'rewrite_rules_array', array( self::class, 'categoryRules' ), 99 );
	}

	/**
	 * WooCommerce drops product-category rules whose base equals the product base (/produkty/%product_cat%/), so
	 * /produkty/{kategoria}/ would 404. Categories here are flat, products always have two segments, so a single
	 * segment after /produkty/ is unambiguous: restore it (plus pagination) for the default language.
	 *
	 * @param array<string, string> $rules
	 * @return array<string, string>
	 */
	public static function categoryRules( $rules ) {
		if ( ! is_array( $rules ) || ! taxonomy_exists( 'product_cat' ) ) {
			return $rules;
		}
		$perma = (array) get_option( 'woocommerce_permalinks', array() );
		$base  = trim( (string) ( $perma['category_base'] ?? '' ), '/' );
		$pbase = trim( (string) ( $perma['product_base'] ?? '' ), '/' );
		if ( '' === $base || 0 !== strpos( $pbase, $base . '/%product_cat%' ) ) {
			return $rules;
		}
		$lang = function_exists( 'pll_default_language' ) ? '&lang=' . pll_default_language() : '';
		$q    = preg_quote( $base, '#' );
		$add  = array(
			$q . '/(?!page/|feed/)([^/]+)/page/?([0-9]{1,})/?$' => 'index.php?product_cat=$matches[1]&paged=$matches[2]' . $lang,
			$q . '/(?!page/|feed/|feed$|page$)([^/]+)/?$'       => 'index.php?product_cat=$matches[1]' . $lang,
		);
		return $add + $rules;
	}

	/**
	 * Plugin-owned rewrite rules (also called on activation before the flush).
	 */
	public static function addRewriteRules(): void {
		add_rewrite_rule( '^llms\.txt$', 'index.php?ew_llms=1', 'top' );
		do_action( 'ew_add_rewrite_rules' );
	}

	/**
	 * @param array<string, mixed> $vars
	 * @return array<string, mixed>
	 */
	public static function resolve( $vars ) {
		if ( ! is_array( $vars ) || is_admin() ) {
			return $vars;
		}
		$keep = array_intersect_key( $vars, array_flip( array( 'lang', 'paged', 'page', 'feed', 'preview' ) ) );

		// /porady/{slug}/ — guide first, then hub.
		if ( ! empty( $vars['ew_guide'] ) && empty( $vars['ew_hub'] ) ) {
			$slug = (string) $vars['ew_guide'];
			if ( ! self::postExists( $slug, 'ew_guide' ) && term_exists( $slug, 'ew_hub' ) ) {
				return array( 'ew_hub' => $slug ) + $keep;
			}
		} elseif ( ! empty( $vars['ew_hub'] ) && empty( $vars['ew_guide'] ) ) {
			$slug = (string) $vars['ew_hub'];
			if ( ! term_exists( $slug, 'ew_hub' ) && self::postExists( $slug, 'ew_guide' ) ) {
				return array( 'ew_guide' => $slug, 'post_type' => 'ew_guide', 'name' => $slug ) + $keep;
			}
		}

		// /produkty/{cat}/{product}/ parsed as a nested category path.
		if ( ! empty( $vars['product_cat'] ) && empty( $vars['product'] ) && false !== strpos( (string) $vars['product_cat'], '/' ) ) {
			$path = trim( (string) $vars['product_cat'], '/' );
			$last = basename( $path );
			if ( ! get_term_by( 'slug', $last, 'product_cat' ) && self::postExists( $last, 'product' ) ) {
				return array( 'product' => $last, 'post_type' => 'product', 'name' => $last ) + $keep;
			}
		}
		// /produkty/{cat}/{product}/ where {product} is actually a sub-category.
		if ( ! empty( $vars['product'] ) && ! empty( $vars['product_cat'] ) && ! self::postExists( (string) $vars['product'], 'product' ) ) {
			$sub = get_term_by( 'slug', (string) $vars['product'], 'product_cat' );
			if ( $sub ) {
				return array( 'product_cat' => $sub->slug ) + $keep;
			}
		}
		return $vars;
	}

	private static function postExists( string $slug, string $type ): bool {
		$q = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'private' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'lang'           => '',
			)
		);
		return ! empty( $q );
	}
}
