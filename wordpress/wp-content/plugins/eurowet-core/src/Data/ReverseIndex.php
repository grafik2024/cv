<?php
/**
 * Maintains the product → needs reverse index (`_ew_need_ids` on products).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Data;

defined( 'ABSPATH' ) || exit;

/**
 * `_ew_products` on ew_need is the source of truth. Whenever it changes (admin, importer, REST, WP-CLI),
 * the affected products get their `_ew_need_ids` recalculated. The index lists every non-trashed need
 * that references the product (any status); consumers filter by status/`_ew_active` themselves.
 *
 * Bulk imports can call suspend() … resume() (resume() rebuilds the whole index once).
 */
final class ReverseIndex {

	private const SOURCE_KEY = '_ew_products';
	private const INDEX_KEY  = '_ew_need_ids';

	private static bool $suspended = false;

	/**
	 * Product IDs referenced by a need before a pending change, keyed by need ID.
	 *
	 * @var array<int, list<int>>
	 */
	private static array $before = array();

	public static function register(): void {
		add_action( 'update_post_meta', array( self::class, 'beforeMetaChange' ), 10, 3 );
		add_action( 'delete_post_meta', array( self::class, 'beforeMetaDelete' ), 10, 3 );
		add_action( 'added_post_meta', array( self::class, 'afterMetaChange' ), 10, 3 );
		add_action( 'updated_post_meta', array( self::class, 'afterMetaChange' ), 10, 3 );
		add_action( 'deleted_post_meta', array( self::class, 'afterMetaChange' ), 10, 3 );
		add_action( 'transition_post_status', array( self::class, 'onStatusChange' ), 10, 3 );
	}

	/**
	 * Stops index maintenance (for bulk imports).
	 */
	public static function suspend(): void {
		self::$suspended = true;
	}

	/**
	 * Resumes maintenance and rebuilds the full index.
	 */
	public static function resume(): int {
		self::$suspended = false;
		return self::rebuildAll();
	}

	/**
	 * @param int|int[] $meta_id   Meta ID(s).
	 * @param int       $object_id Post ID.
	 * @param string    $meta_key  Meta key.
	 */
	public static function beforeMetaChange( $meta_id, $object_id, $meta_key ): void {
		if ( self::SOURCE_KEY === $meta_key && 'ew_need' === get_post_type( (int) $object_id ) ) {
			self::$before[ (int) $object_id ] = self::productIdsOfNeed( (int) $object_id );
		}
	}

	/**
	 * @param int[]  $meta_ids  Meta IDs.
	 * @param int    $object_id Post ID.
	 * @param string $meta_key  Meta key.
	 */
	public static function beforeMetaDelete( $meta_ids, $object_id, $meta_key ): void {
		self::beforeMetaChange( $meta_ids, $object_id, $meta_key );
	}

	/**
	 * @param int|int[] $meta_id   Meta ID(s).
	 * @param int       $object_id Post ID.
	 * @param string    $meta_key  Meta key.
	 */
	public static function afterMetaChange( $meta_id, $object_id, $meta_key ): void {
		if ( self::SOURCE_KEY !== $meta_key || 'ew_need' !== get_post_type( (int) $object_id ) ) {
			return;
		}
		$need_id  = (int) $object_id;
		$affected = array_merge( self::$before[ $need_id ] ?? array(), self::productIdsOfNeed( $need_id ) );
		unset( self::$before[ $need_id ] );
		if ( ! self::$suspended ) {
			self::rebuildProducts( $affected );
		}
	}

	/**
	 * Trashing/untrashing a need changes which needs reference a product.
	 *
	 * @param string   $new_status New status.
	 * @param string   $old_status Old status.
	 * @param \WP_Post $post       Post.
	 */
	public static function onStatusChange( $new_status, $old_status, $post ): void {
		if ( self::$suspended || ! $post instanceof \WP_Post || 'ew_need' !== $post->post_type || $new_status === $old_status ) {
			return;
		}
		if ( 'trash' === $new_status || 'trash' === $old_status ) {
			self::rebuildProducts( self::productIdsOfNeed( (int) $post->ID ) );
		}
	}

	/**
	 * Product IDs referenced by a need's `_ew_products`.
	 *
	 * @return list<int>
	 */
	public static function productIdsOfNeed( int $need_id ): array {
		$rows = get_post_meta( $need_id, self::SOURCE_KEY, true );
		return self::extractProductIds( $rows );
	}

	/**
	 * @param mixed $rows Stored `_ew_products` value.
	 * @return list<int>
	 */
	private static function extractProductIds( $rows ): array {
		if ( ! is_array( $rows ) ) {
			return array();
		}
		$ids = array();
		foreach ( $rows as $row ) {
			if ( is_array( $row ) && ! empty( $row['product_id'] ) && (int) $row['product_id'] > 0 ) {
				$ids[ (int) $row['product_id'] ] = true;
			}
		}
		return array_keys( $ids );
	}

	/**
	 * Map product ID => list of need IDs, computed from all non-trashed needs.
	 *
	 * @return array<int, list<int>>
	 */
	private static function map(): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one aggregated read; values are unserialized below.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status NOT IN ('trash', 'auto-draft', 'inherit')
				ORDER BY pm.post_id ASC",
				self::SOURCE_KEY,
				'ew_need'
			)
		);
		$map = array();
		foreach ( (array) $rows as $row ) {
			foreach ( self::extractProductIds( maybe_unserialize( $row->meta_value ) ) as $product_id ) {
				$map[ $product_id ][] = (int) $row->post_id;
			}
		}
		return $map;
	}

	/**
	 * Recalculates `_ew_need_ids` for the given products.
	 *
	 * @param int[] $product_ids Product IDs.
	 */
	public static function rebuildProducts( array $product_ids ): void {
		$product_ids = array_unique( array_filter( array_map( 'intval', $product_ids ) ) );
		if ( ! $product_ids ) {
			return;
		}
		$map = self::map();
		foreach ( $product_ids as $product_id ) {
			if ( 'product' !== get_post_type( $product_id ) ) {
				continue;
			}
			Meta::set( $product_id, self::INDEX_KEY, array_values( array_unique( $map[ $product_id ] ?? array() ) ) );
		}
	}

	/**
	 * Rebuilds the index for every product (WP-CLI / after imports).
	 *
	 * @return int Number of products that reference at least one need.
	 */
	public static function rebuildAll(): int {
		global $wpdb;
		$map = self::map();
		// Products that currently carry an index but may no longer be referenced.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$indexed = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s", self::INDEX_KEY ) );
		foreach ( array_unique( array_merge( array_map( 'intval', (array) $indexed ), array_keys( $map ) ) ) as $product_id ) {
			if ( 'product' === get_post_type( $product_id ) ) {
				Meta::set( $product_id, self::INDEX_KEY, $map[ $product_id ] ?? array() );
			}
		}
		return count( $map );
	}
}
