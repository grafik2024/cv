<?php
/**
 * Related products for a product page. Manual relations (_ew_rel_*) always win; algorithmic groups
 * are derived only from verified need relations, product line and category + species overlap.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class RelatedProducts {

	public const MAX = 4;

	/** @return array<string, \WC_Product[]> complementary|similar|same_need|same_line|same_category */
	public static function forProduct( int $product_id ): array {
		$product_id = Polylang::translatePostId( $product_id );
		$lang       = Polylang::currentLang();
		$ids        = Cache::remember( "related|{$product_id}|{$lang}", static fn() => self::compute( $product_id ) );
		$out        = array();
		foreach ( $ids as $group => $list ) {
			$products = array_values( array_filter( array_map( static fn( $id ) => Util::product( (int) $id ), $list ) ) );
			if ( $products ) {
				$out[ $group ] = $products;
			}
		}
		return $out;
	}

	/** @return array<string, int[]> */
	private static function compute( int $pid ): array {
		$exclude = array( $pid => true );
		foreach ( Families::variants( $pid ) as $v ) {
			$exclude[ (int) $v['product_id'] ] = true; // capacity variants are shown by the switcher, not here
		}
		$groups = array( 'complementary' => array(), 'similar' => array(), 'same_need' => array(), 'same_line' => array(), 'same_category' => array() );
		$take   = static function ( string $group, array $candidates ) use ( &$groups, &$exclude ): void {
			foreach ( $candidates as $id ) {
				$id = (int) $id;
				if ( count( $groups[ $group ] ) >= self::MAX || isset( $exclude[ $id ] ) || null === Util::product( $id ) ) {
					continue;
				}
				$groups[ $group ][] = $id;
				$exclude[ $id ]     = true;
			}
		};

		// 1. Manual (editor) relations first.
		$take( 'complementary', Polylang::translateIds( Meta::ids( $pid, '_ew_rel_complementary' ) ) );
		$take( 'similar', Polylang::translateIds( Meta::ids( $pid, '_ew_rel_similar' ) ) );

		// 2. Verified need relations.
		$comp = array();
		$same = array();
		foreach ( Relations::rolesOfProduct( $pid ) as $need_id => $role ) {
			foreach ( Relations::needProducts( $need_id ) as $row ) {
				$other = $row['product']->get_id();
				if ( 'complementary' === $row['role'] && 'complementary' !== $role ) {
					$comp[ $other ] = ( $comp[ $other ] ?? 0 ) + 1;
				} elseif ( 'complementary' !== $row['role'] ) {
					$same[ $other ] = ( $same[ $other ] ?? 0 ) + 1;
				}
			}
		}
		arsort( $comp );
		arsort( $same );
		$take( 'complementary', array_keys( $comp ) );
		$take( 'same_need', array_keys( $same ) );

		// 3. Same product line.
		$lines = wp_get_post_terms( $pid, 'ew_line', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $lines ) && $lines ) {
			$take( 'same_line', self::byTerms( 'ew_line', $lines ) );
		}

		// 4. Same category, only with overlapping species.
		$cats    = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'ids' ) );
		$species = Util::slugs( $pid, 'ew_species' );
		if ( ! is_wp_error( $cats ) && $cats ) {
			$cands = array_filter(
				self::byTerms( 'product_cat', $cats ),
				static fn( $id ) => ! $species || array_intersect( $species, Util::slugs( (int) $id, 'ew_species' ) )
			);
			$take( 'same_category', $cands );
		}
		return $groups;
	}

	/** @return int[] */
	private static function byTerms( string $taxonomy, array $term_ids ): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 24,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => $taxonomy, 'terms' => array_map( 'intval', $term_ids ) ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		);
		if ( Polylang::active() ) {
			$args['lang'] = Polylang::currentLang();
		}
		return array_map( 'intval', get_posts( $args ) );
	}
}
