<?php
/**
 * Fixed-window rate limiter keyed by a salted hash of the client IP (the IP itself is never stored).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Security;

defined( 'ABSPATH' ) || exit;

final class RateLimiter {

	public static function hit( string $bucket, int $max, int $window ): bool {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
		$key = 'ew_rl_' . substr( hash_hmac( 'sha256', $bucket . '|' . $ip . '|' . (int) floor( time() / max( 1, $window ) ), wp_salt( 'nonce' ) ), 0, 32 );
		$n   = (int) get_transient( $key );
		if ( $n >= $max ) {
			return false;
		}
		set_transient( $key, $n + 1, $window );
		return true;
	}
}
