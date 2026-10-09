<?php
/**
 * Versioned cache for graph computations. Any save of a product/need/guide/ingredient bumps the version,
 * so cached rankings never go stale and no per-key invalidation is needed.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

defined( 'ABSPATH' ) || exit;

final class Cache {

	private const OPTION = 'ew_graph_version';
	private const TYPES  = array( 'product', 'ew_need', 'ew_guide', 'ew_ingredient' );

	/** @var array<string, mixed> request-level memo */
	private static array $memo = array();

	public static function register(): void {
		add_action( 'save_post', array( self::class, 'onSave' ), 10, 2 );
		add_action( 'deleted_post', array( self::class, 'bump' ) );
		add_action( 'set_object_terms', array( self::class, 'bump' ) );
	}

	public static function onSave( $post_id, $post ): void {
		if ( $post instanceof \WP_Post && in_array( $post->post_type, self::TYPES, true ) && ! wp_is_post_revision( $post_id ) ) {
			self::bump();
		}
	}

	public static function bump(): void {
		update_option( self::OPTION, (string) microtime( true ), true );
		self::$memo = array();
	}

	public static function version(): string {
		return (string) get_option( self::OPTION, '1' );
	}

	/**
	 * @param callable(): mixed $compute
	 * @return mixed
	 */
	public static function remember( string $key, callable $compute, int $ttl = DAY_IN_SECONDS ) {
		$full = 'ewg_' . md5( self::version() . '|' . $key );
		if ( array_key_exists( $full, self::$memo ) ) {
			return self::$memo[ $full ];
		}
		$hit = get_transient( $full );
		if ( false !== $hit ) {
			return self::$memo[ $full ] = $hit;
		}
		$value = $compute();
		set_transient( $full, $value, $ttl );
		return self::$memo[ $full ] = $value;
	}
}
