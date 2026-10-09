<?php
/**
 * REST: GET /eurowet/v1/finder, GET /finder/facets, POST /finder/event (all public, sanitised, rate limited).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Security\RateLimiter;

defined( 'ABSPATH' ) || exit;

final class RestController {

	public static function register(): void {
		register_rest_route(
			'eurowet/v1',
			'/finder',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'finder' ),
				'args'                => array(
					'q'       => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => static fn( $v ) => mb_substr( sanitize_text_field( (string) $v ), 0, 200 ) ),
					'species' => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_key' ),
					'area'    => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_key' ),
					'need'    => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_title' ),
					'lang'    => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_key' ),
					'format'  => array( 'type' => 'string', 'default' => 'json', 'enum' => array( 'json', 'html' ) ),
					'log'     => array( 'type' => 'boolean', 'default' => true ),
				),
			)
		);
		register_rest_route(
			'eurowet/v1',
			'/finder/facets',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'facets' ),
			)
		);
		register_rest_route(
			'eurowet/v1',
			'/finder/event',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'event' ),
				'args'                => array(
					'log_id'    => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
					'type'      => array( 'type' => 'string', 'required' => true, 'enum' => Log::EVENT_TYPES ),
					'target_id' => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
				),
			)
		);
	}

	public static function finder( \WP_REST_Request $r ): \WP_REST_Response {
		if ( class_exists( RateLimiter::class ) && ! RateLimiter::hit( 'finder', 120, HOUR_IN_SECONDS ) ) {
			return new \WP_REST_Response( array( 'code' => 'rate_limited', 'message' => __( 'Zbyt wiele zapytań. Spróbuj za chwilę.', 'eurowet-core' ) ), 429 );
		}
		if ( $r['lang'] && function_exists( 'PLL' ) && PLL()->model->get_language( (string) $r['lang'] ) ) {
			PLL()->curlang = PLL()->model->get_language( (string) $r['lang'] );
		}
		$res = Service::instance()->query(
			array(
				'q'       => (string) $r['q'],
				'species' => (string) $r['species'],
				'area'    => (string) $r['area'],
				'need'    => (string) $r['need'],
				'source'  => 'finder',
				'log'     => (bool) $r['log'],
			)
		);
		if ( 'html' === $r['format'] ) {
			// Same server-rendered markup as the no-JS page: one source of truth for the results UI.
			$res = array( 'html' => ew_render( 'finder-results', array( 'result' => $res ) ), 'log_id' => $res['log_id'], 'match' => $res['match'] );
		}
		$resp = new \WP_REST_Response( $res, 200 );
		$resp->header( 'Cache-Control', 'no-store' );
		return $resp;
	}

	public static function facets(): \WP_REST_Response {
		$needs = array();
		foreach ( Index::needs() as $n ) {
			$needs[] = array( 'slug' => $n['slug'], 'title' => $n['title'], 'species' => $n['species'], 'areas' => $n['areas'], 'url' => get_permalink( $n['id'] ) );
		}
		$terms = static function ( string $tax ): array {
			$t = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
			return is_array( $t ) ? array_map( static fn( $x ) => array( 'slug' => $x->slug, 'name' => $x->name ), $t ) : array();
		};
		$resp = new \WP_REST_Response( array( 'species' => $terms( 'ew_species' ), 'areas' => $terms( 'ew_area' ), 'needs' => $needs ), 200 );
		$resp->header( 'Cache-Control', 'public, max-age=600' );
		return $resp;
	}

	public static function event( \WP_REST_Request $r ): \WP_REST_Response {
		if ( class_exists( RateLimiter::class ) && ! RateLimiter::hit( 'finder_event', 300, HOUR_IN_SECONDS ) ) {
			return new \WP_REST_Response( array( 'ok' => false ), 429 );
		}
		$ok = Log::event( (int) $r['log_id'], (string) $r['type'], (int) $r['target_id'] );
		return new \WP_REST_Response( array( 'ok' => $ok ), $ok ? 201 : 200 );
	}
}
