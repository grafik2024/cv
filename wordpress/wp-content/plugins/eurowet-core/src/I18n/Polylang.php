<?php
/**
 * Polylang integration: translatable CPT/taxonomies, ID translation of relation metas.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\I18n;

use Eurowet\Core\Data\Registry;

defined( 'ABSPATH' ) || exit;

final class Polylang {

	public function register(): void {
		add_filter( 'pll_get_post_types', array( self::class, 'postTypes' ), 10, 2 );
		add_filter( 'pll_get_taxonomies', array( self::class, 'taxonomies' ), 10, 2 );
		add_filter( 'pll_copy_post_metas', array( self::class, 'copyMetas' ), 10, 2 );
		add_filter( 'pll_translate_post_meta', array( self::class, 'translateMeta' ), 10, 3 );
	}

	public static function active(): bool {
		return function_exists( 'pll_current_language' );
	}

	public static function currentLang(): string {
		if ( self::active() ) {
			$lang = pll_current_language( 'slug' );
			if ( is_string( $lang ) && '' !== $lang ) {
				return $lang;
			}
			$default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
			if ( is_string( $default ) && '' !== $default ) {
				return $default;
			}
		}
		return 'pl';
	}

	public static function translatePostId( int $post_id, ?string $lang = null ): int {
		if ( $post_id <= 0 || ! self::active() || ! function_exists( 'pll_get_post' ) ) {
			return $post_id;
		}
		$tr = pll_get_post( $post_id, $lang ?? self::currentLang() );
		return $tr ? (int) $tr : $post_id;
	}

	/**
	 * @param int[] $ids
	 * @return int[]
	 */
	public static function translateIds( array $ids, ?string $lang = null ): array {
		return array_values( array_unique( array_filter( array_map( static fn( $id ) => self::translatePostId( (int) $id, $lang ), $ids ) ) ) );
	}

	public static function postTypes( $types, $is_settings ) {
		foreach ( Registry::TRANSLATABLE_POST_TYPES as $t ) {
			$types[ $t ] = $t;
		}
		foreach ( array( 'ew_rep', 'ew_lead' ) as $t ) {
			unset( $types[ $t ] );
		}
		return $types;
	}

	public static function taxonomies( $taxonomies, $is_settings ) {
		foreach ( Registry::TRANSLATABLE_TAXONOMIES as $t ) {
			$taxonomies[ $t ] = $t;
		}
		unset( $taxonomies['ew_family'] );
		return $taxonomies;
	}

	public static function copyMetas( $metas, $sync ) {
		foreach ( Registry::META as $keys ) {
			foreach ( array_keys( $keys ) as $key ) {
				if ( in_array( $key, array( '_ew_source_key', '_ew_need_ids' ), true ) ) {
					continue;
				}
				$metas[] = $key;
			}
		}
		return array_values( array_unique( $metas ) );
	}

	/**
	 * Translates relation IDs inside copied metas (ids arrays, single ids and need→product rows).
	 */
	public static function translateMeta( $value, $key, $lang ) {
		if ( ! is_string( $key ) || 0 !== strpos( $key, '_ew_' ) ) {
			return $value;
		}
		if ( '_ew_products' === $key && is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
			foreach ( $value as &$row ) {
				if ( isset( $row['product_id'] ) ) {
					$row['product_id'] = self::translatePostId( (int) $row['product_id'], $lang );
				}
			}
			return $value;
		}
		foreach ( Registry::RELATIONS as $keys ) {
			if ( isset( $keys[ $key ] ) && 'attachment' !== $keys[ $key ] ) {
				if ( is_array( $value ) ) {
					return self::translateIds( $value, $lang );
				}
				return is_numeric( $value ) ? self::translatePostId( (int) $value, $lang ) : $value;
			}
		}
		return $value;
	}
}
