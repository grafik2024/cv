<?php
/**
 * Anonymous finder analytics: no IP, no cookies, no user ID; personal data patterns masked; hour precision.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Data\Schema;

defined( 'ABSPATH' ) || exit;

final class Log {

	public const EVENT_TYPES = array( 'product_click', 'buy_click', 'guide_click', 'need_click' );

	public static function enabled(): bool {
		return (bool) ew_get_option( 'finder_logging', true );
	}

	public static function scrub( string $q ): string {
		$q = preg_replace( '/[\w.+-]+@[\w-]+\.[\w.-]+/u', '[e-mail]', $q ) ?? $q;
		$q = preg_replace( '/(\+?\d[\d\s-]{5,}\d)/u', '[nr]', $q ) ?? $q;
		return mb_substr( trim( $q ), 0, 200 );
	}

	/** @param array<string, mixed> $row */
	public static function write( array $row ): int {
		global $wpdb;
		if ( ! self::enabled() ) {
			return 0;
		}
		$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::table( 'ew_finder_log' ),
			array(
				'created_at'         => gmdate( 'Y-m-d H:00:00' ),
				'lang'               => substr( (string) ( $row['lang'] ?? '' ), 0, 10 ),
				'query'              => self::scrub( (string) ( $row['query'] ?? '' ) ),
				'need_id'            => (int) ( $row['need_id'] ?? 0 ),
				'confidence'         => (float) ( $row['confidence'] ?? 0 ),
				'matched'            => empty( $row['matched'] ) ? 0 : 1,
				'primary_product_id' => (int) ( $row['primary_product_id'] ?? 0 ),
				'red_flag_level'     => substr( (string) ( $row['red_flag_level'] ?? 'none' ), 0, 10 ),
				'source'             => substr( (string) ( $row['source'] ?? 'finder' ), 0, 10 ),
				'interpreter'        => substr( (string) ( $row['interpreter'] ?? 'local' ), 0, 10 ),
			),
			array( '%s', '%s', '%s', '%d', '%f', '%d', '%d', '%s', '%s', '%s' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	public static function event( int $log_id, string $type, int $target_id ): bool {
		global $wpdb;
		if ( ! self::enabled() || ! in_array( $type, self::EVENT_TYPES, true ) ) {
			return false;
		}
		return (bool) $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::table( 'ew_finder_event' ),
			array( 'log_id' => max( 0, $log_id ), 'created_at' => gmdate( 'Y-m-d H:00:00' ), 'type' => $type, 'target_id' => max( 0, $target_id ) ),
			array( '%d', '%s', '%s', '%d' )
		);
	}

	public static function purge(): void {
		global $wpdb;
		$months = max( 1, (int) ew_get_option( 'finder_retention_months', 13 ) );
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) );
		foreach ( array( 'ew_finder_log', 'ew_finder_event' ) as $t ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Schema::table( $t ) . ' WHERE created_at < %s', $cutoff ) ); // phpcs:ignore WordPress.DB
		}
	}
}
