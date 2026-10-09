<?php
/**
 * Front-end hardening that does not depend on server config: security headers, no version leaks,
 * no ?author=N user enumeration for visitors.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Security;

use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		add_action( 'send_headers', array( self::class, 'headers' ) );
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		add_action( 'template_redirect', array( self::class, 'blockAuthorEnumeration' ), 0 );
		add_filter(
			'rest_endpoints',
			static function ( $endpoints ) {
				if ( ! is_user_logged_in() ) {
					unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
				}
				return $endpoints;
			}
		);
	}

	public static function headers(): void {
		if ( is_admin() || headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
		header( 'X-Frame-Options: SAMEORIGIN' );
	}

	public static function blockAuthorEnumeration(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_user_logged_in() && ( isset( $_GET['author'] ) || is_author() ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}
}
