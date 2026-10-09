<?php
/**
 * Sales representatives directory — one central list (CPT ew_rep), never hard-coded in Elementor pages.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Reps;

use Eurowet\Core\Data\Meta;

defined( 'ABSPATH' ) || exit;

final class Directory {

	/** @return array<int, array<string, mixed>> */
	public static function forVoivodeship( string $slug ): array {
		if ( ! Voivodeships::isValid( $slug ) ) {
			return array();
		}
		$out = array();
		foreach ( self::all() as $rep ) {
			if ( in_array( $slug, $rep['voivodeship_slugs'], true ) ) {
				$out[] = $rep;
			}
		}
		return $out;
	}

	/** @return array<int, array<string, mixed>> all active reps */
	public static function all(): array {
		$posts = get_posts( array( 'post_type' => 'ew_rep', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) );
		$out   = array();
		foreach ( $posts as $p ) {
			if ( metadata_exists( 'post', $p->ID, '_ew_active' ) && ! Meta::bool( $p->ID, '_ew_active' ) ) {
				continue;
			}
			$out[] = self::card( $p );
		}
		usort( $out, static fn( $a, $b ) => $a['order'] <=> $b['order'] ?: strcmp( $a['name'], $b['name'] ) );
		return $out;
	}

	/** @return array<string, mixed> */
	public static function card( \WP_Post $p ): array {
		$first  = (string) Meta::get( $p->ID, '_ew_first_name', '' );
		$last   = (string) Meta::get( $p->ID, '_ew_last_name', '' );
		$name   = trim( $first . ' ' . $last ) ?: get_the_title( $p );
		$phone  = (string) Meta::get( $p->ID, '_ew_phone', '' );
		$slugs  = array_values( array_filter( (array) Meta::json( $p->ID, '_ew_voivodeships' ), array( Voivodeships::class, 'isValid' ) ) );
		$thumb  = get_post_thumbnail_id( $p );
		return array(
			'id'                => $p->ID,
			'name'              => $name,
			'position'          => (string) Meta::get( $p->ID, '_ew_position', '' ),
			'phone'             => $phone,
			'phone_href'        => self::tel( $phone ),
			'email'             => (string) Meta::get( $p->ID, '_ew_email', '' ),
			'photo_id'          => $thumb ? (int) $thumb : 0,
			'voivodeship_slugs' => $slugs,
			'voivodeships'      => array_map( array( Voivodeships::class, 'name' ), $slugs ),
			'segments'          => array_map( 'strval', (array) Meta::json( $p->ID, '_ew_segments' ) ),
			'order'             => Meta::int( $p->ID, '_ew_order' ),
		);
	}

	/** E.164 tel: link for Polish numbers. */
	public static function tel( string $phone ): string {
		$digits = preg_replace( '/[^\d+]/', '', $phone ) ?? '';
		if ( '' === $digits ) {
			return '';
		}
		if ( 0 !== strpos( $digits, '+' ) ) {
			$digits = ( 9 === strlen( $digits ) ? '+48' : '+' ) . ltrim( $digits, '0' );
		}
		return 'tel:' . $digits;
	}

	/** @return array{phone:string, phone_href:string, email:string, label:string} */
	public static function fallback(): array {
		$phone = (string) ew_get_option( 'contact_phone', '' );
		return array( 'phone' => $phone, 'phone_href' => self::tel( $phone ), 'email' => (string) ew_get_option( 'contact_email', '' ), 'label' => __( 'Biuro Eurowet', 'eurowet-core' ) );
	}
}
