<?php
/**
 * Product Finder engine (pure PHP, deterministic, no WordPress calls).
 *
 * QUERY → normalise → tokens → typo correction → species/area/red-flag detection → score every NEED
 * (phrase matches of synonyms/questions > IDF-weighted token overlap, species/area agreement, priority)
 * → ranked intents with confidence. Products are NOT chosen here — only needs (controlled taxonomy).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

final class Engine {

	/** @var array<int, array<string, mixed>> need_id => need with prepared phrases */
	private array $needs = array();

	/** @var array<string, int> stem => number of needs containing it */
	private array $df = array();

	/** @var array<string, int> folded vocabulary word => frequency (for typo correction) */
	private array $vocab = array();

	/** @var array<string, bool> */
	private array $stop = array();

	/** @var array<string, string> folded word => species slug */
	private array $speciesWords = array();

	/** @var array<string, string> folded word => area slug */
	private array $areaWords = array();

	/** @var array<string, array<int, string>> level => patterns */
	private array $redFlags = array();

	private string $lang;

	/**
	 * @param array<int, array{id:int, slug:string, title:string, synonyms?:string[], questions?:string[], species?:string[], areas?:string[], priority?:int}> $needs
	 * @param array<string, mixed>                                                                                                                         $lexicon
	 */
	public function __construct( array $needs, array $lexicon, string $lang = 'pl' ) {
		$this->lang = $lang;
		foreach ( array_merge( (array) ( $lexicon['stopwords'] ?? array() ), (array) ( $lexicon['intent_words'] ?? array() ) ) as $w ) {
			$this->stop[ Normalizer::fold( (string) $w ) ] = true;
		}
		foreach ( (array) ( $lexicon['species'] ?? array() ) as $slug => $words ) {
			foreach ( (array) $words as $w ) {
				$this->speciesWords[ Normalizer::fold( (string) $w ) ] = (string) $slug;
			}
		}
		foreach ( (array) ( $lexicon['areas'] ?? array() ) as $slug => $words ) {
			foreach ( (array) $words as $w ) {
				$this->areaWords[ Normalizer::fold( (string) $w ) ] = (string) $slug;
			}
		}
		foreach ( (array) ( $lexicon['red_flags'] ?? array() ) as $level => $patterns ) {
			$this->redFlags[ (string) $level ] = array_map( static fn( $p ) => Normalizer::fold( rtrim( (string) $p, '*' ) ) . ( str_ends_with( (string) $p, '*' ) ? '*' : '' ), (array) $patterns );
		}
		foreach ( array_keys( $this->speciesWords + $this->areaWords ) as $w ) {
			$this->vocab[ $w ] = ( $this->vocab[ $w ] ?? 0 ) + 1;
		}

		foreach ( $needs as $need ) {
			$phrases = array();
			$stems   = array();
			$texts   = array_merge( array( (string) $need['title'] ), (array) ( $need['synonyms'] ?? array() ), (array) ( $need['questions'] ?? array() ) );
			foreach ( $texts as $i => $text ) {
				$tokens = $this->contentTokens( Normalizer::tokens( (string) $text ) );
				foreach ( Normalizer::tokens( (string) $text ) as $t ) {
					$this->vocab[ $t ] = ( $this->vocab[ $t ] ?? 0 ) + 1;
				}
				$ps = array_values( array_unique( array_map( fn( $t ) => Normalizer::stem( $t, $this->lang ), $tokens ) ) );
				if ( $ps ) {
					$phrases[] = array( 'stems' => $ps, 'weight' => 0 === $i ? 1.2 : 1.0 );
				}
				foreach ( $ps as $s ) {
					$stems[ $s ] = true;
				}
			}
			foreach ( array_keys( $stems ) as $s ) {
				$this->df[ $s ] = ( $this->df[ $s ] ?? 0 ) + 1;
			}
			$need['phrases']         = $phrases;
			$need['stems']           = $stems;
			$this->needs[ (int) $need['id'] ] = $need;
		}
	}

	/**
	 * @return array{normalized:string, corrected:?string, tokens:string[], species:?string, area:?string,
	 *               red_flag:array{level:string, terms:string[]}, ranked:array<int, array{need_id:int, slug:string, score:float, confidence:float, matched:string[]}>}
	 */
	public function analyze( string $query ): array {
		$normalized = Normalizer::fold( mb_substr( $query, 0, 200 ) );
		$raw        = '' === $normalized ? array() : explode( ' ', $normalized );

		// Typo correction against the vocabulary (never into stopwords).
		$corrected_tokens = array();
		$changed          = false;
		foreach ( $raw as $t ) {
			$c = $this->correct( $t );
			if ( $c !== $t ) {
				$changed = true;
			}
			$corrected_tokens[] = $c;
		}
		$text = implode( ' ', $corrected_tokens );

		$species = null;
		$area    = null;
		foreach ( $corrected_tokens as $t ) {
			$species ??= $this->speciesWords[ $t ] ?? null;
			$area    ??= $this->areaWords[ $t ] ?? null;
		}
		$red = $this->redFlags( ' ' . $text . ' ', $corrected_tokens );

		$content = $this->contentTokens( $corrected_tokens );
		$qstems  = array_values( array_unique( array_map( fn( $t ) => Normalizer::stem( $t, $this->lang ), $content ) ) );
		$n       = max( 1, count( $this->needs ) );
		$ranked  = array();
		foreach ( $this->needs as $id => $need ) {
			$score   = 0.0;
			$matched = array();
			// Phrase matches: every stem of a multi-word synonym/question present in the query.
			$best_phrase = 0.0;
			foreach ( $need['phrases'] as $ph ) {
				if ( count( $ph['stems'] ) >= 2 && ! array_diff( $ph['stems'], $qstems ) ) {
					$best_phrase = max( $best_phrase, ( 2.5 + 0.5 * count( $ph['stems'] ) ) * $ph['weight'] );
				}
			}
			if ( $best_phrase > 0 ) {
				$score    += $best_phrase;
				$matched[] = 'phrase';
			}
			// IDF-weighted token overlap.
			foreach ( $qstems as $s ) {
				if ( isset( $need['stems'][ $s ] ) ) {
					$score    += log( 1 + $n / max( 1, $this->df[ $s ] ?? 1 ) );
					$matched[] = $s;
				}
			}
			if ( $score <= 0 ) {
				continue;
			}
			$nspecies = (array) ( $need['species'] ?? array() );
			if ( null !== $species && $nspecies ) {
				$score += in_array( $species, $nspecies, true ) ? 0.5 : -2.5;
			}
			$nareas = (array) ( $need['areas'] ?? array() );
			if ( null !== $area && $nareas ) {
				$score += in_array( $area, $nareas, true ) ? 1.0 : -0.5;
			}
			$score += ( (int) ( $need['priority'] ?? 0 ) ) / 1000;
			if ( $score <= 0 ) {
				continue;
			}
			$ranked[] = array(
				'need_id'    => (int) $id,
				'slug'       => (string) $need['slug'],
				'score'      => round( $score, 4 ),
				'confidence' => round( $score / ( $score + 3.0 ), 4 ),
				'matched'    => array_values( array_unique( $matched ) ),
			);
		}
		usort( $ranked, static fn( $a, $b ) => $b['score'] <=> $a['score'] ?: $a['need_id'] <=> $b['need_id'] );

		return array(
			'normalized' => $normalized,
			'corrected'  => $changed ? $text : null,
			'tokens'     => $content,
			'species'    => $species,
			'area'       => $area,
			'red_flag'   => $red,
			'ranked'     => $ranked,
		);
	}

	/** Content tokens: no stopwords, no species words, no 1-char tokens. */
	private function contentTokens( array $tokens ): array {
		return array_values(
			array_filter(
				$tokens,
				fn( $t ) => mb_strlen( (string) $t ) > 1 && ! isset( $this->stop[ $t ] ) && ! isset( $this->speciesWords[ $t ] )
			)
		);
	}

	private function correct( string $t ): string {
		$len = mb_strlen( $t );
		if ( $len < 4 || isset( $this->vocab[ $t ] ) || isset( $this->stop[ $t ] ) || preg_match( '/\d/', $t ) ) {
			return $t;
		}
		$max  = $len >= 8 ? 2 : 1;
		$best = $t;
		$bd   = $max + 1;
		$bf   = 0;
		foreach ( $this->vocab as $w => $freq ) {
			$w = (string) $w;
			if ( abs( mb_strlen( $w ) - $len ) > $max || isset( $this->stop[ $w ] ) ) {
				continue;
			}
			$d = Normalizer::distance( $t, $w, $max );
			if ( $d < $bd || ( $d === $bd && $freq > $bf ) ) {
				$best = $w;
				$bd   = $d;
				$bf   = $freq;
			}
		}
		return $bd <= $max ? $best : $t;
	}

	/**
	 * @param string[] $tokens
	 * @return array{level:string, terms:string[]}
	 */
	private function redFlags( string $padded, array $tokens ): array {
		foreach ( array( 'urgent', 'caution' ) as $level ) {
			$hits = array();
			foreach ( $this->redFlags[ $level ] ?? array() as $p ) {
				$prefix = str_ends_with( $p, '*' );
				$p      = rtrim( $p, '*' );
				if ( '' === $p ) {
					continue;
				}
				if ( str_contains( $p, ' ' ) ) {
					if ( str_contains( $padded, ' ' . $p . ( $prefix ? '' : ' ' ) ) ) {
						$hits[] = $p;
					}
					continue;
				}
				foreach ( $tokens as $t ) {
					if ( $prefix ? str_starts_with( $t, $p ) : $t === $p ) {
						$hits[] = $p;
						break;
					}
				}
			}
			if ( $hits ) {
				return array( 'level' => $level, 'terms' => array_values( array_unique( $hits ) ) );
			}
		}
		return array( 'level' => 'none', 'terms' => array() );
	}
}
