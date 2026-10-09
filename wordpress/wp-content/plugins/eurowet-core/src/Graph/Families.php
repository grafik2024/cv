<?php
/**
 * Capacity variants of one product family (ew_family taxonomy). Products stay `simple`
 * so IDs, URLs and order history are untouched.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;

defined( 'ABSPATH' ) || exit;

final class Families {

	/** @return array<int, array<string, mixed>> */
	public static function variants( int $product_id ): array {
		$terms = get_the_terms( $product_id, 'ew_family' );
		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return array();
		}
		$term = $terms[0];
		$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $product_id ) : null;
		$ids  = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'ew_family', 'terms' => $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => $lang ? $lang : '',
			)
		);
		$out = array();
		foreach ( $ids as $id ) {
			$p = Util::product( (int) $id );
			if ( null === $p ) {
				continue;
			}
			$cap   = (string) Meta::get( (int) $id, '_ew_capacity', '' );
			$cap   = '' !== $cap ? $cap : self::capacityFromTitle( $p->get_name() );
			$out[] = array(
				'product_id' => (int) $id,
				'capacity'   => $cap,
				'url'        => get_permalink( (int) $id ),
				'price_html' => $p->get_price_html(),
				'in_stock'   => $p->is_in_stock(),
				'current'    => (int) $id === $product_id,
				'_sort'      => self::capacityValue( $cap ),
			);
		}
		if ( count( $out ) < 2 ) {
			return array();
		}
		usort( $out, static fn( $a, $b ) => $a['_sort'] <=> $b['_sort'] );
		return array_map(
			static function ( $v ) {
				unset( $v['_sort'] );
				return $v;
			},
			$out
		);
	}

	public static function capacityFromTitle( string $title ): string {
		return preg_match( '/(\d+(?:[.,]\d+)?\s?(?:x\s?\d+\s?)?(?:ml|l|g|kg|szt\.?|tab\.?|kaps\.?))/iu', $title, $m ) ? trim( $m[1] ) : '';
	}

	/** Comparable size in ml/g (packs multiply), unknown sizes last. */
	public static function capacityValue( string $cap ): float {
		$c = str_replace( ',', '.', mb_strtolower( $cap ) );
		if ( preg_match( '/(\d+(?:\.\d+)?)\s?x\s?(\d+(?:\.\d+)?)/', $c, $m ) ) {
			return (float) $m[1] * (float) $m[2];
		}
		if ( ! preg_match( '/(\d+(?:\.\d+)?)\s?(ml|l|g|kg|szt|tab|kaps)?/', $c, $m ) ) {
			return PHP_FLOAT_MAX;
		}
		$v = (float) $m[1];
		$u = $m[2] ?? '';
		return in_array( $u, array( 'l', 'kg' ), true ) ? $v * 1000 : $v;
	}
}
