<?php
/**
 * Ranking of guides (porady) for products, needs and other guides, plus "next best article".
 * Deterministic — never random.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class GuideRanker {

	private const STAGES = array( 'informational' => 0, 'practical' => 1, 'product' => 2 );

	/** @return \WP_Post[] */
	public static function forProduct( int $product_id, int $limit = 3 ): array {
		$product_id = Polylang::translatePostId( $product_id );
		$lang       = Polylang::currentLang();
		$ids        = Cache::remember(
			"g4p|{$product_id}|{$lang}",
			static function () use ( $product_id ): array {
				$roles   = Relations::rolesOfProduct( $product_id );
				$areas   = Util::slugs( $product_id, 'ew_area' );
				$species = Util::slugs( $product_id, 'ew_species' );
				$scores  = array();
				foreach ( Util::publishedIds( 'ew_guide' ) as $gid ) {
					$score = 0.0;
					if ( in_array( $product_id, Polylang::translateIds( Meta::ids( $gid, '_ew_products' ) ), true ) ) {
						$score += 3;
					}
					foreach ( Polylang::translateIds( Meta::ids( $gid, '_ew_needs' ) ) as $nid ) {
						if ( isset( $roles[ $nid ] ) ) {
							$score += 'primary' === $roles[ $nid ] ? 2 : 1;
						}
					}
					if ( $score <= 0 ) {
						continue; // area/species overlap alone is not enough to call a guide "related knowledge"
					}
					if ( array_intersect( $areas, Util::slugs( $gid, 'ew_area' ) ) ) {
						$score += 0.5;
					}
					if ( array_intersect( $species, Util::slugs( $gid, 'ew_species' ) ) ) {
						$score += 0.5;
					}
					$scores[ $gid ] = $score + self::recency( $gid );
				}
				arsort( $scores );
				return array_keys( $scores );
			}
		);
		return Util::posts( array_slice( $ids, 0, $limit ) );
	}

	/** @return \WP_Post[] */
	public static function forNeed( int $need_id, int $limit = 6 ): array {
		$need_id = Polylang::translatePostId( $need_id );
		$manual  = Polylang::translateIds( Meta::ids( $need_id, '_ew_guides' ) );
		$lang    = Polylang::currentLang();
		$ids     = Cache::remember(
			"g4n|{$need_id}|{$lang}",
			static function () use ( $need_id, $manual ): array {
				$linked = array();
				foreach ( Util::publishedIds( 'ew_guide' ) as $gid ) {
					if ( ! in_array( $gid, $manual, true ) && in_array( $need_id, Polylang::translateIds( Meta::ids( $gid, '_ew_needs' ) ), true ) ) {
						$linked[ $gid ] = self::stage( $gid );
					}
				}
				asort( $linked );
				return array_merge( $manual, array_keys( $linked ) );
			}
		);
		return array_slice( Util::posts( $ids ), 0, $limit );
	}

	/** @return \WP_Post[] */
	public static function related( int $guide_id, int $limit = 3 ): array {
		$guide_id = Polylang::translatePostId( $guide_id );
		$lang     = Polylang::currentLang();
		$ids      = Cache::remember(
			"g4g|{$guide_id}|{$lang}",
			static function () use ( $guide_id ): array {
				$manual  = Polylang::translateIds( Meta::ids( $guide_id, '_ew_related' ) );
				$needs   = Polylang::translateIds( Meta::ids( $guide_id, '_ew_needs' ) );
				$hubs    = Util::slugs( $guide_id, 'ew_hub' );
				$species = Util::slugs( $guide_id, 'ew_species' );
				$scores  = array();
				foreach ( Util::publishedIds( 'ew_guide' ) as $gid ) {
					if ( $gid === $guide_id || in_array( $gid, $manual, true ) ) {
						continue;
					}
					$score = 2 * count( array_intersect( $needs, Polylang::translateIds( Meta::ids( $gid, '_ew_needs' ) ) ) )
						+ count( array_intersect( $hubs, Util::slugs( $gid, 'ew_hub' ) ) )
						+ 0.5 * count( array_intersect( $species, Util::slugs( $gid, 'ew_species' ) ) );
					if ( $score > 0 ) {
						$scores[ $gid ] = $score + self::recency( $gid );
					}
				}
				arsort( $scores );
				return array_merge( $manual, array_keys( $scores ) );
			}
		);
		return array_slice( Util::posts( $ids ), 0, $limit );
	}

	/**
	 * Next best article: manual > next stage in the same need journey > best related > product CTA.
	 *
	 * @return array{post: ?\WP_Post, product_cta: ?array, reason: string}|null
	 */
	public static function next( int $guide_id ): ?array {
		$guide_id = Polylang::translatePostId( $guide_id );
		$manual   = Polylang::translatePostId( Meta::int( $guide_id, '_ew_next' ) );
		if ( $manual && Util::isPublished( $manual ) ) {
			return array( 'post' => get_post( $manual ), 'product_cta' => null, 'reason' => __( 'Kolejny krok w tym temacie', 'eurowet-core' ) );
		}
		$stage = self::stage( $guide_id );
		$needs = Polylang::translateIds( Meta::ids( $guide_id, '_ew_needs' ) );
		foreach ( $needs as $nid ) {
			foreach ( self::forNeed( $nid, 20 ) as $cand ) {
				if ( $cand->ID !== $guide_id && self::stage( $cand->ID ) > $stage ) {
					return array( 'post' => $cand, 'product_cta' => null, 'reason' => __( 'Następny etap: od zrozumienia problemu do właściwej pielęgnacji', 'eurowet-core' ) );
				}
			}
		}
		$cta = null;
		foreach ( $needs as $nid ) {
			$cta = Relations::primaryProduct( $nid );
			if ( $cta ) {
				$cta['need_id'] = $nid;
				break;
			}
		}
		if ( $stage >= self::STAGES['product'] && $cta ) {
			return array( 'post' => null, 'product_cta' => $cta, 'reason' => __( 'Produkt dopasowany do tej potrzeby', 'eurowet-core' ) );
		}
		$related = self::related( $guide_id, 1 );
		if ( $related ) {
			return array( 'post' => $related[0], 'product_cta' => $cta, 'reason' => __( 'Powiązany temat', 'eurowet-core' ) );
		}
		return $cta ? array( 'post' => null, 'product_cta' => $cta, 'reason' => __( 'Produkt dopasowany do tej potrzeby', 'eurowet-core' ) ) : null;
	}

	private static function stage( int $gid ): int {
		$s = (string) Meta::get( $gid, '_ew_stage', 'informational' );
		return self::STAGES[ $s ] ?? 0;
	}

	/** Tiny tie-breaker favouring recently reviewed/updated guides (< 0.1). */
	private static function recency( int $gid ): float {
		$date = (string) Meta::get( $gid, '_ew_reviewed', '' );
		$ts   = $date ? strtotime( $date ) : (int) get_post_modified_time( 'U', true, $gid );
		return $ts ? min( 0.099, $ts / 1e11 ) : 0.0;
	}
}
