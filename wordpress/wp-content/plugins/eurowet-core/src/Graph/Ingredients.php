<?php
/**
 * Ingredient ↔ product relations (product meta _ew_key_ingredients).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Ingredients {

	/** @return \WP_Post[] */
	public static function forProduct( int $product_id ): array {
		return Util::posts( Polylang::translateIds( Meta::ids( Polylang::translatePostId( $product_id ), '_ew_key_ingredients' ) ) );
	}

	/** @return \WC_Product[] */
	public static function products( int $ingredient_id ): array {
		$ingredient_id = Polylang::translatePostId( $ingredient_id );
		$lang          = Polylang::currentLang();
		$ids           = Cache::remember(
			"i2p|{$ingredient_id}|{$lang}",
			static function () use ( $ingredient_id ): array {
				$out = array();
				foreach ( Util::publishedIds( 'product' ) as $pid ) {
					if ( in_array( $ingredient_id, Polylang::translateIds( Meta::ids( $pid, '_ew_key_ingredients' ) ), true ) ) {
						$out[] = $pid;
					}
				}
				return $out;
			}
		);
		return array_values( array_filter( array_map( static fn( $id ) => Util::product( (int) $id ), $ids ) ) );
	}
}
