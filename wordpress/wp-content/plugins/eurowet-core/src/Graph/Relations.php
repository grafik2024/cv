<?php
/**
 * Need ↔ product relations (verified relations stored on the need in _ew_products).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Relations {

	private const ROLE_ORDER = array( 'primary' => 0, 'similar' => 1, 'complementary' => 2 );

	/**
	 * @return array<int, array{product: \WC_Product, role: string, reason: string, evidence: string}>
	 */
	public static function needProducts( int $need_id, ?string $role = null ): array {
		$need_id = Polylang::translatePostId( $need_id );
		if ( ! Util::isActiveNeed( $need_id ) ) {
			return array();
		}
		$out  = array();
		$seen = array();
		foreach ( Meta::json( $need_id, '_ew_products' ) as $i => $row ) {
			if ( ! is_array( $row ) || empty( $row['product_id'] ) ) {
				continue;
			}
			$r = isset( $row['role'] ) && isset( self::ROLE_ORDER[ $row['role'] ] ) ? (string) $row['role'] : 'similar';
			if ( null !== $role && $r !== $role ) {
				continue;
			}
			$pid = Polylang::translatePostId( (int) $row['product_id'] );
			if ( isset( $seen[ $pid ] ) ) {
				continue;
			}
			$product = Util::product( $pid );
			if ( null === $product ) {
				continue;
			}
			$seen[ $pid ] = true;
			$out[]        = array(
				'product'  => $product,
				'role'     => $r,
				'reason'   => (string) ( $row['reason'] ?? '' ),
				'evidence' => (string) ( $row['evidence'] ?? '' ),
				'_order'   => self::ROLE_ORDER[ $r ] * 1000 + (int) $i,
			);
		}
		usort( $out, static fn( $a, $b ) => $a['_order'] <=> $b['_order'] );
		return array_map(
			static function ( array $r ): array {
				unset( $r['_order'] );
				return $r;
			},
			$out
		);
	}

	/** @return array{product: \WC_Product, role: string, reason: string, evidence: string}|null */
	public static function primaryProduct( int $need_id ): ?array {
		$rows = self::needProducts( $need_id, 'primary' );
		return $rows[0] ?? null;
	}

	/** @return \WP_Post[] active needs (current language) that reference the product */
	public static function needsForProduct( int $product_id ): array {
		$product_id = Polylang::translatePostId( $product_id );
		$ids        = array();
		foreach ( Meta::ids( $product_id, '_ew_need_ids' ) as $nid ) {
			$nid = Polylang::translatePostId( (int) $nid );
			if ( Util::isActiveNeed( $nid ) ) {
				$ids[ $nid ] = (int) Meta::int( $nid, '_ew_priority' );
			}
		}
		arsort( $ids );
		return Util::posts( array_keys( $ids ) );
	}

	/**
	 * Roles the product plays across needs: need_id => role.
	 *
	 * @return array<int, string>
	 */
	public static function rolesOfProduct( int $product_id ): array {
		$roles = array();
		foreach ( self::needsForProduct( $product_id ) as $need ) {
			foreach ( Meta::json( $need->ID, '_ew_products' ) as $row ) {
				if ( is_array( $row ) && Polylang::translatePostId( (int) ( $row['product_id'] ?? 0 ) ) === $product_id ) {
					$roles[ $need->ID ] = (string) ( $row['role'] ?? 'similar' );
				}
			}
		}
		return $roles;
	}
}
