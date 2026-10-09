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
