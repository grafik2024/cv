<?php
/**
 * Builds the need records the engine scores (active ew_need posts in the current language) and the
 * per-language lexicon. Cached per language; the graph version invalidates it on any content change.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Graph\Cache;
use Eurowet\Core\Graph\Util;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Index {

	/** Lexicon file per Polylang language slug (UA uses the Ukrainian 'uk' lexicon). */
	public static function lexiconLang( string $lang ): string {
		return array( 'ua' => 'uk', 'uk' => 'uk', 'en' => 'en', 'fr' => 'fr' )[ $lang ] ?? 'pl';
	}

	/** @return array<string, mixed> */
	public static function lexicon( string $lang ): array {
		static $cache = array();
		$code = self::lexiconLang( $lang );
		if ( ! isset( $cache[ $code ] ) ) {
			$file           = EW_CORE_DIR . 'data/lexicon-' . $code . '.json';
			$data           = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$cache[ $code ] = (array) apply_filters( 'ew_finder_lexicon', is_array( $data ) ? $data : array(), $code );
		}
		return $cache[ $code ];
	}

	/** @return array<int, array<string, mixed>> */
	public static function needs( ?string $lang = null ): array {
		$lang = $lang ?? Polylang::currentLang();
		return Cache::remember(
			'finder-needs|' . $lang,
			static function (): array {
				$out = array();
				foreach ( Util::publishedIds( 'ew_need' ) as $id ) {
					if ( ! Util::isActiveNeed( $id ) ) {
						continue;
					}
					$out[] = array(
						'id'        => $id,
						'slug'      => (string) get_post_field( 'post_name', $id ),
						'title'     => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
						'synonyms'  => array_map( 'strval', Meta::json( $id, '_ew_synonyms' ) ),
						'questions' => array_map( 'strval', Meta::json( $id, '_ew_questions' ) ),
						'species'   => Util::slugs( $id, 'ew_species' ),
						'areas'     => Util::slugs( $id, 'ew_area' ),
						'priority'  => Meta::int( $id, '_ew_priority' ),
					);
				}
				return $out;
			},
			WEEK_IN_SECONDS
		);
	}

	public static function engine( ?string $lang = null ): Engine {
		$lang = $lang ?? Polylang::currentLang();
		return new Engine( self::needs( $lang ), self::lexicon( $lang ), self::lexiconLang( $lang ) );
	}
}
