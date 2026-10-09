<?php
/**
 * Knowledge-hub queries: topic hubs (ew_hub term) and species hubs (also include guides tagged by species).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;

defined( 'ABSPATH' ) || exit;

final class Hubs {

	/** @return array<string, mixed> WP_Query args */
	public static function queryArgs( \WP_Term $hub, array $extra = array() ): array {
		$tax = array( array( 'taxonomy' => 'ew_hub', 'terms' => $hub->term_id ) );
		if ( 'species' === (string) Meta::term( $hub->term_id, 'hub_type', 'topic' ) ) {
			$species = (string) Meta::term( $hub->term_id, 'species_slug', '' );
			if ( '' !== $species ) {
				$tax = array(
					'relation' => 'OR',
					$tax[0],
					array( 'taxonomy' => 'ew_species', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $species ) ) ),
				);
			}
		}
		return array_merge(
			array(
				'post_type'      => 'ew_guide',
				'post_status'    => 'publish',
				'posts_per_page' => 24,
				'tax_query'      => $tax, // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'        => array( 'menu_order' => 'ASC', 'modified' => 'DESC' ),
			),
			$extra
		);
	}

	/** Main query of a hub archive (/porady/{hub}/): species hubs also list guides tagged with the species. */
	public static function mainQuery( \WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_tax( 'ew_hub' ) ) {
			return;
		}
		$term = get_term_by( 'slug', (string) $q->get( 'ew_hub' ), 'ew_hub' );
		if ( ! $term instanceof \WP_Term ) {
			return;
		}
		$args = self::queryArgs( $term );
		$q->set( 'post_type', 'ew_guide' );
		$q->set( 'tax_query', $args['tax_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery
		$q->set( 'orderby', $args['orderby'] );
		$q->set( 'posts_per_page', 18 );
	}

	/** @return \WP_Term[] topic hubs ordered by term meta 'order' */
	public static function topicHubs(): array {
		return self::byType( 'topic' );
	}

	/** @return \WP_Term[] */
	public static function speciesHubs(): array {
		return self::byType( 'species' );
	}

	/** @return \WP_Term[] */
	private static function byType( string $type ): array {
		$terms = get_terms( array( 'taxonomy' => 'ew_hub', 'hide_empty' => false ) );
		if ( ! is_array( $terms ) ) {
			return array();
		}
		$terms = array_values( array_filter( $terms, static fn( $t ) => (string) Meta::term( $t->term_id, 'hub_type', 'topic' ) === $type ) );
		usort( $terms, static fn( $a, $b ) => (int) Meta::term( $a->term_id, 'order', 0 ) <=> (int) Meta::term( $b->term_id, 'order', 0 ) );
		return $terms;
	}
}
