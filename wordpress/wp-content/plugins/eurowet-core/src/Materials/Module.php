<?php
/**
 * Downloadable materials (catalogues, leaflets) as ew_material posts; rendered by the material-list component.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Materials;

use Eurowet\Core\Contracts\ModuleInterface;
use Eurowet\Core\Data\Meta;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
	}

	/** @return array<int, array<string, mixed>> */
	public static function items( string $type = '' ): array {
		$args = array( 'post_type' => 'ew_material', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true );
		if ( '' !== $type ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'ew_material_type', 'field' => 'slug', 'terms' => $type ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$out = array();
		foreach ( get_posts( $args ) as $p ) {
			$file = Meta::int( $p->ID, '_ew_file' );
			$url  = $file ? (string) wp_get_attachment_url( $file ) : (string) Meta::get( $p->ID, '_ew_source_url', '' );
			if ( '' === $url ) {
				continue;
			}
			$path  = $file ? get_attached_file( $file ) : '';
			$types = wp_get_post_terms( $p->ID, 'ew_material_type', array( 'fields' => 'names' ) );
			$out[] = array(
				'title' => get_the_title( $p ),
				'url'   => $url,
				'size'  => $path && is_readable( $path ) ? size_format( (int) filesize( $path ) ) : '',
				'ext'   => strtoupper( (string) pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ),
				'lang'  => (string) Meta::get( $p->ID, '_ew_lang', 'pl' ),
				'type'  => is_array( $types ) && $types ? (string) $types[0] : '',
			);
		}
		return $out;
	}
}
