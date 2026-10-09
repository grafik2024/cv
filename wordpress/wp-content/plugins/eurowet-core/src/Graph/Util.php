<?php
/**
 * Small query helpers shared by graph services (language-aware).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Util {

	/**
	 * IDs of published posts of a type in the current language.
	 *
	 * @return int[]
	 */
	public static function publishedIds( string $post_type ): array {
		$lang = Polylang::currentLang();
		return Cache::remember(
			"ids|{$post_type}|{$lang}",
			static function () use ( $post_type ): array {
				$args = array(
					'post_type'              => $post_type,
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
				);
				if ( Polylang::active() ) {
					$args['lang'] = Polylang::currentLang();
				}
				return array_map( 'intval', get_posts( $args ) );
			}
		);
	}

	public static function isPublished( int $id ): bool {
		return $id > 0 && 'publish' === get_post_status( $id );
	}

	public static function isActiveNeed( int $need_id ): bool {
		if ( ! self::isPublished( $need_id ) ) {
			return false;
		}
		return ! metadata_exists( 'post', $need_id, '_ew_active' ) || Meta::bool( $need_id, '_ew_active' );
	}

	/** @return string[] term slugs */
	public static function slugs( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		return is_array( $terms ) ? array_map( static fn( $t ) => $t->slug, $terms ) : array();
	}

	/** Product object usable for display (published, visible). */
	public static function product( int $id ): ?\WC_Product {
		if ( ! function_exists( 'wc_get_product' ) || ! self::isPublished( $id ) ) {
			return null;
		}
		$p = wc_get_product( $id );
		return ( $p instanceof \WC_Product && 'hidden' !== $p->get_catalog_visibility() ) ? $p : null;
	}

	/**
	 * @param int[] $ids
	 * @return \WP_Post[]
	 */
	public static function posts( array $ids ): array {
		$out = array();
		foreach ( $ids as $id ) {
			$p = get_post( (int) $id );
			if ( $p instanceof \WP_Post && 'publish' === $p->post_status ) {
				$out[] = $p;
			}
		}
		return $out;
	}
}
