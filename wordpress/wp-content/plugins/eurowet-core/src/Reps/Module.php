<?php
/**
 * Reps module: REST GET /eurowet/v1/reps?voivodeship=slug.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Reps;

use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		add_action(
			'rest_api_init',
			static function (): void {
				register_rest_route(
					'eurowet/v1',
					'/reps',
					array(
						'methods'             => 'GET',
						'permission_callback' => '__return_true',
						'args'                => array( 'voivodeship' => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_key' ) ),
						'callback'            => static function ( \WP_REST_Request $r ) {
							$slug = (string) $r['voivodeship'];
							if ( ! Voivodeships::isValid( $slug ) ) {
								return new \WP_Error( 'invalid_voivodeship', __( 'Nieznane województwo.', 'eurowet-core' ), array( 'status' => 400 ) );
							}
							$reps = array_map(
								static function ( $rep ) {
									$rep['photo'] = $rep['photo_id'] ? (string) wp_get_attachment_image_url( $rep['photo_id'], 'thumbnail' ) : '';
									unset( $rep['photo_id'], $rep['order'] );
									return $rep;
								},
								Directory::forVoivodeship( $slug )
							);
							$resp = new \WP_REST_Response( array( 'voivodeship' => array( 'slug' => $slug, 'name' => Voivodeships::name( $slug ) ), 'reps' => $reps, 'fallback' => $reps ? null : Directory::fallback() ), 200 );
							$resp->header( 'Cache-Control', 'public, max-age=300' );
							return $resp;
						},
					)
				);
			}
		);
	}
}
