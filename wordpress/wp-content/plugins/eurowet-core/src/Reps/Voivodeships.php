<?php
/**
 * Fixed list of the 16 Polish voivodeships (ARCHITECTURE §3).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Reps;

defined( 'ABSPATH' ) || exit;

final class Voivodeships {

	/** ASCII slugs in alphabetical order of the Polish names. */
	public const SLUGS = array(
		'dolnoslaskie',
		'kujawsko-pomorskie',
		'lubelskie',
		'lubuskie',
		'lodzkie',
		'malopolskie',
		'mazowieckie',
		'opolskie',
		'podkarpackie',
		'podlaskie',
		'pomorskie',
		'slaskie',
		'swietokrzyskie',
		'warminsko-mazurskie',
		'wielkopolskie',
		'zachodniopomorskie',
	);

	/**
	 * Slug => Polish name (translatable with the 'voivodeship' context).
	 *
	 * @return array<string, string>
	 */
	public static function all(): array {
		return array(
			'dolnoslaskie'        => _x( 'dolnośląskie', 'voivodeship', 'eurowet-core' ),
			'kujawsko-pomorskie'  => _x( 'kujawsko-pomorskie', 'voivodeship', 'eurowet-core' ),
			'lubelskie'           => _x( 'lubelskie', 'voivodeship', 'eurowet-core' ),
			'lubuskie'            => _x( 'lubuskie', 'voivodeship', 'eurowet-core' ),
			'lodzkie'             => _x( 'łódzkie', 'voivodeship', 'eurowet-core' ),
			'malopolskie'         => _x( 'małopolskie', 'voivodeship', 'eurowet-core' ),
			'mazowieckie'         => _x( 'mazowieckie', 'voivodeship', 'eurowet-core' ),
			'opolskie'            => _x( 'opolskie', 'voivodeship', 'eurowet-core' ),
			'podkarpackie'        => _x( 'podkarpackie', 'voivodeship', 'eurowet-core' ),
			'podlaskie'           => _x( 'podlaskie', 'voivodeship', 'eurowet-core' ),
			'pomorskie'           => _x( 'pomorskie', 'voivodeship', 'eurowet-core' ),
			'slaskie'             => _x( 'śląskie', 'voivodeship', 'eurowet-core' ),
			'swietokrzyskie'      => _x( 'świętokrzyskie', 'voivodeship', 'eurowet-core' ),
			'warminsko-mazurskie' => _x( 'warmińsko-mazurskie', 'voivodeship', 'eurowet-core' ),
			'wielkopolskie'       => _x( 'wielkopolskie', 'voivodeship', 'eurowet-core' ),
			'zachodniopomorskie'  => _x( 'zachodniopomorskie', 'voivodeship', 'eurowet-core' ),
		);
	}

	public static function isValid( string $slug ): bool {
		return in_array( $slug, self::SLUGS, true );
	}

	/**
	 * Name for a slug, or null when unknown.
	 */
	public static function name( string $slug ): ?string {
		return self::all()[ $slug ] ?? null;
	}
}
