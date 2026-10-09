<?php
/**
 * Text normalisation for the finder (pure PHP, no WordPress): lowercase, diacritics folding (PL/FR),
 * apostrophe/Cyrillic cleanup (UK), punctuation removal, tokenisation and light stemming.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

final class Normalizer {

	private const FOLD = array(
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
		'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i',
		'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae', 'ÿ' => 'y', 'ñ' => 'n',
		'ґ' => 'г', 'ё' => 'е', '’' => '', "'" => '', 'ʼ' => '', '`' => '',
	);

	private const SUFFIXES = array(
		'pl' => array( 'owaniami', 'owaniach', 'owania', 'owanie', 'owaniu', 'aniem', 'eniem', 'aniu', 'eniu', 'ania', 'enia', 'anie', 'enie', 'ymi', 'ami', 'ach', 'ego', 'emu', 'owi', 'ich', 'imi', 'ych', 'uje', 'uja', 'esz', 'owa', 'owe', 'owy', 'ow', 'om', 'em', 'ej', 'ie', 'ia', 'iu', 'ym', 'ac', 'ic', 'yc', 'a', 'e', 'i', 'o', 'u', 'y' ),
		'en' => array( 'ations', 'ation', 'ness', 'ings', 'ing', 'ies', 'ied', 'ed', 'es', 'ly', 's' ),
		'fr' => array( 'issements', 'issement', 'ations', 'ation', 'ements', 'ement', 'euses', 'euse', 'ables', 'able', 'eux', 'ees', 'ee', 'es', 'er', 'e', 's', 'x' ),
		'uk' => array( 'ями', 'ами', 'ові', 'еві', 'ого', 'ому', 'ими', 'ини', 'ння', 'их', 'ах', 'ях', 'ом', 'ем', 'ою', 'ею', 'ів', 'ей', 'ям', 'ам', 'ий', 'ій', 'а', 'я', 'у', 'ю', 'і', 'и', 'о', 'е', 'ь', 'й' ),
	);

	public static function fold( string $text ): string {
		$t = mb_strtolower( $text, 'UTF-8' );
		if ( class_exists( \Normalizer::class ) ) {
			$n = \Normalizer::normalize( $t, \Normalizer::FORM_KC );
			$t = false === $n ? $t : $n;
		}
		$t = strtr( $t, self::FOLD );
		$t = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $t ) ?? $t;
		return trim( preg_replace( '/\s+/u', ' ', $t ) ?? $t );
	}

	/** @return string[] */
	public static function tokens( string $text ): array {
		$f = self::fold( $text );
		return '' === $f ? array() : explode( ' ', $f );
	}

	public static function stem( string $token, string $lang ): string {
		$lang = isset( self::SUFFIXES[ $lang ] ) ? $lang : 'pl';
		$len  = mb_strlen( $token );
		if ( $len <= 3 || preg_match( '/^\d+$/', $token ) ) {
			return $token;
		}
		$min = 'uk' === $lang ? 3 : 3;
		foreach ( self::SUFFIXES[ $lang ] as $suffix ) {
			$sl = mb_strlen( $suffix );
			if ( $len - $sl >= $min && mb_substr( $token, -$sl ) === $suffix ) {
				return mb_substr( $token, 0, $len - $sl );
			}
		}
		return $token;
	}

	/**
	 * Damerau–Levenshtein (optimal string alignment) on characters, with early exit above $max.
	 */
	public static function distance( string $a, string $b, int $max = 2 ): int {
		if ( $a === $b ) {
			return 0;
		}
		$s = mb_str_split( $a );
		$t = mb_str_split( $b );
		$n = count( $s );
		$m = count( $t );
		if ( abs( $n - $m ) > $max ) {
			return $max + 1;
		}
		$d = array();
		for ( $i = 0; $i <= $n; $i++ ) {
			$d[ $i ][0] = $i;
		}
		for ( $j = 0; $j <= $m; $j++ ) {
			$d[0][ $j ] = $j;
		}
		for ( $i = 1; $i <= $n; $i++ ) {
			$row_min = PHP_INT_MAX;
			for ( $j = 1; $j <= $m; $j++ ) {
				$cost       = $s[ $i - 1 ] === $t[ $j - 1 ] ? 0 : 1;
				$d[ $i ][ $j ] = min( $d[ $i - 1 ][ $j ] + 1, $d[ $i ][ $j - 1 ] + 1, $d[ $i - 1 ][ $j - 1 ] + $cost );
				if ( $i > 1 && $j > 1 && $s[ $i - 1 ] === $t[ $j - 2 ] && $s[ $i - 2 ] === $t[ $j - 1 ] ) {
					$d[ $i ][ $j ] = min( $d[ $i ][ $j ], $d[ $i - 2 ][ $j - 2 ] + 1 );
				}
				$row_min = min( $row_min, $d[ $i ][ $j ] );
			}
			if ( $row_min > $max ) {
				return $max + 1;
			}
		}
		return $d[ $n ][ $m ];
	}
}
