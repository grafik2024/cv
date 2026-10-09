<?php
/**
 * Finder orchestration: ZAPYTANIE → INTENCJA (engine / optional LLM, closed set) → KONTROLOWANA TAKSONOMIA (need)
 * → ZWERYFIKOWANE ZASTOSOWANIA (need→product relations) → PRODUKT. Never returns a random product.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Graph\Relations;
use Eurowet\Core\Graph\Util;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Service {

	private static ?Service $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public static function threshold(): float {
		return (float) ew_get_option( 'finder_threshold', 0.45 );
	}

	/**
	 * @param array{q?:string, species?:string, area?:string, need?:string, source?:string, lang?:string, log?:bool} $p
	 * @return array<string, mixed>
	 */
	public function query( array $p ): array {
		$lang    = (string) ( $p['lang'] ?? Polylang::currentLang() );
		$q       = trim( mb_substr( (string) ( $p['q'] ?? '' ), 0, 200 ) );
		$needs   = Index::needs( $lang );
		$by_slug = array();
		foreach ( $needs as $n ) {
			$by_slug[ $n['slug'] ] = $n;
		}
		$species     = sanitize_key( (string) ( $p['species'] ?? '' ) ) ?: null;
		$area        = sanitize_key( (string) ( $p['area'] ?? '' ) ) ?: null;
		$analysis    = array( 'normalized' => '', 'corrected' => null, 'species' => null, 'area' => null, 'red_flag' => array( 'level' => 'none', 'terms' => array() ), 'ranked' => array() );
		$need        = null;
		$confidence  = 0.0;
		$interpreter = 'local';
		$matched     = array();

		if ( ! empty( $p['need'] ) && isset( $by_slug[ (string) $p['need'] ] ) ) {
			$need       = $by_slug[ (string) $p['need'] ];
			$confidence = 1.0;
			$interpreter = 'facet';
		} elseif ( '' !== $q ) {
			$analysis = Index::engine( $lang )->analyze( $q );
			$species  = $species ?? $analysis['species'];
			$area     = $area ?? $analysis['area'];
			$top      = $analysis['ranked'][0] ?? null;
			if ( $top && $top['confidence'] >= self::threshold() ) {
				$need       = $this->needById( $needs, (int) $top['need_id'] );
				$confidence = (float) $top['confidence'];
				$matched    = $top['matched'];
			} elseif ( 'urgent' !== $analysis['red_flag']['level'] && LlmInterpreter::enabled() ) {
				$llm = LlmInterpreter::interpret( $q, $needs, $lang );
				if ( $llm && $llm['slug'] && isset( $by_slug[ $llm['slug'] ] ) ) {
					$need        = $by_slug[ $llm['slug'] ];
					$confidence  = max( self::threshold(), 0.6 );
					$interpreter = 'llm';
					$species     = $species ?? $llm['species'];
				}
			}
		}

		$red   = $analysis['red_flag'];
		$out   = array(
			'query'        => $q,
			'normalized'   => $analysis['normalized'],
			'corrected'    => $analysis['corrected'],
			'intent'       => array(
				'need_id'     => $need ? (int) $need['id'] : null,
				'need_slug'   => $need ? (string) $need['slug'] : null,
				'confidence'  => round( $confidence, 3 ),
				'species'     => $species,
				'area'        => $area,
				'matched'     => $matched,
				'interpreter' => $interpreter,
			),
			'match'        => null !== $need,
			'red_flag'     => array(
				'level'   => $red['level'],
				'terms'   => $red['terms'],
				'message' => self::redFlagMessage( $red['level'] ),
			),
			'need'         => null,
			'primary'      => null,
			'complementary' => array(),
			'guides'       => array(),
			'alternatives' => array(),
			'log_id'       => 0,
		);

		if ( $need ) {
			$nid         = (int) $need['id'];
			$out['need'] = array(
				'id'           => $nid,
				'title'        => html_entity_decode( get_the_title( $nid ), ENT_QUOTES, 'UTF-8' ),
				'url'          => get_permalink( $nid ),
				'short_answer' => wp_strip_all_tags( (string) Meta::get( $nid, '_ew_short_answer', '' ) ),
				'red_flag_level' => (string) Meta::get( $nid, '_ew_red_flag_level', 'none' ),
			);
			if ( 'urgent' !== $red['level'] ) {
				$rows      = Relations::needProducts( $nid );
				$fits      = static fn( array $r ) => null === $species || ! ( $sp = Util::slugs( $r['product']->get_id(), 'ew_species' ) ) || in_array( $species, $sp, true );
				$primaries = array_values( array_filter( $rows, static fn( $r ) => 'primary' === $r['role'] && $fits( $r ) ) );
				if ( ! $primaries ) {
					$primaries = array_values( array_filter( $rows, static fn( $r ) => 'similar' === $r['role'] && $fits( $r ) ) );
				}
				if ( $primaries ) {
					$out['primary'] = self::productPayload( $primaries[0] );
				}
				$taken = $out['primary'] ? array( $out['primary']['id'] ) : array();
				foreach ( $rows as $r ) {
					if ( count( $out['complementary'] ) >= 4 ) {
						break;
					}
					if ( 'complementary' === $r['role'] && $fits( $r ) && ! in_array( $r['product']->get_id(), $taken, true ) ) {
						$out['complementary'][] = self::productPayload( $r );
						$taken[]                = $r['product']->get_id();
					}
				}
			}
			foreach ( ew_guides_for_need( $nid, 3 ) as $g ) {
				$out['guides'][] = array(
					'id'    => $g->ID,
					'title' => html_entity_decode( get_the_title( $g ), ENT_QUOTES, 'UTF-8' ),
					'url'   => get_permalink( $g ),
					'tldr'  => wp_trim_words( wp_strip_all_tags( (string) Meta::get( $g->ID, '_ew_tldr', '' ) ), 32 ),
				);
			}
		}

		// Alternatives: other plausible needs (never products).
		$alt_ids = array();
		foreach ( $analysis['ranked'] as $r ) {
			if ( ( ! $need || (int) $r['need_id'] !== (int) $need['id'] ) && $r['confidence'] >= 0.2 ) {
				$alt_ids[] = (int) $r['need_id'];
			}
		}
		if ( ! $need && ! $alt_ids && ( $species || $area ) ) {
			foreach ( $needs as $n ) {
				if ( ( ! $species || in_array( $species, $n['species'], true ) ) && ( ! $area || in_array( $area, $n['areas'], true ) ) ) {
					$alt_ids[] = (int) $n['id'];
				}
			}
		}
		foreach ( array_slice( array_unique( $alt_ids ), 0, $need ? 3 : 6 ) as $aid ) {
			$out['alternatives'][] = array( 'need_id' => $aid, 'title' => html_entity_decode( get_the_title( $aid ), ENT_QUOTES, 'UTF-8' ), 'url' => get_permalink( $aid ) );
		}

		if ( '' !== $q && ( $p['log'] ?? true ) ) {
			$out['log_id'] = Log::write(
				array(
					'lang'               => $lang,
					'query'              => $q,
					'need_id'            => $need ? (int) $need['id'] : 0,
					'confidence'         => $confidence,
					'matched'            => null !== $need,
					'primary_product_id' => $out['primary']['id'] ?? 0,
					'red_flag_level'     => $red['level'],
					'source'             => (string) ( $p['source'] ?? 'finder' ),
					'interpreter'        => $interpreter,
				)
			);
		}
		return (array) apply_filters( 'ew_finder_result', $out, $p );
	}

	/** @param array<int, array<string, mixed>> $needs */
	private function needById( array $needs, int $id ): ?array {
		foreach ( $needs as $n ) {
			if ( (int) $n['id'] === $id ) {
				return $n;
			}
		}
		return null;
	}

	/** @param array{product: \WC_Product, role: string, reason: string, evidence: string} $row */
	public static function productPayload( array $row ): array {
		$s     = ew_product_summary( $row['product'] );
		$image = $s['image_id'] ? wp_get_attachment_image_src( $s['image_id'], 'woocommerce_thumbnail' ) : false;
		return array(
			'id'           => $s['id'],
			'name'         => $s['name'],
			'url'          => $s['url'],
			'image'        => $image ? $image[0] : '',
			'image_alt'    => $s['image_id'] ? (string) get_post_meta( $s['image_id'], '_wp_attachment_image_alt', true ) : '',
			'capacity'     => $s['capacity'],
			'subtitle'     => $s['subtitle'],
			'intended_for' => $s['intended_for'],
			'reason'       => (string) $row['reason'],
			'evidence'     => (string) $row['evidence'],
			'role'         => (string) $row['role'],
			'buy_url'      => $s['buy_url'],
			'catalog_only' => $s['catalog_only'],
			'price'        => trim( html_entity_decode( wp_strip_all_tags( (string) $s['price_html'] ), ENT_QUOTES, 'UTF-8' ) ),
		);
	}

	public static function redFlagMessage( string $level ): string {
		if ( 'urgent' === $level ) {
			return __( 'Opisane objawy mogą oznaczać stan wymagający pilnej pomocy. Skontaktuj się niezwłocznie z lekarzem weterynarii — w takiej sytuacji nie polecamy samodzielnej pielęgnacji zamiast wizyty.', 'eurowet-core' );
		}
		if ( 'caution' === $level ) {
			return __( 'Jeśli objawy są nasilone, utrzymują się lub stan zwierzęcia budzi niepokój, skonsultuj się z lekarzem weterynarii.', 'eurowet-core' );
		}
		return '';
	}
}
