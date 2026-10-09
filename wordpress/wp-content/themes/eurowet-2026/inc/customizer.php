<?php
/**
 * Customizer panel "Strona główna Eurowet": editable homepage texts (defaults from
 * content-defaults/home.{locale}.json written by the content team — the theme never invents copy)
 * and the 3D hero product.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

/** Homepage text keys → label. */
function ew_theme_home_fields(): array {
	return array(
		'hero_eyebrow'        => __( 'Hero — nadtytuł', 'eurowet-2026' ),
		'hero_title'          => __( 'Hero — nagłówek (H1)', 'eurowet-2026' ),
		'hero_lead'           => __( 'Hero — wprowadzenie', 'eurowet-2026' ),
		'finder_title'        => __( 'Finder — nagłówek', 'eurowet-2026' ),
		'finder_lead'         => __( 'Finder — opis', 'eurowet-2026' ),
		'needs_title'         => __( 'Najczęstsze potrzeby — nagłówek', 'eurowet-2026' ),
		'categories_title'    => __( 'Kategorie — nagłówek', 'eurowet-2026' ),
		'flagship_title'      => __( 'Produkty flagowe — nagłówek', 'eurowet-2026' ),
		'flagship_lead'       => __( 'Produkty flagowe — opis', 'eurowet-2026' ),
		'knowledge_title'     => __( 'Centrum wiedzy — nagłówek', 'eurowet-2026' ),
		'knowledge_lead'      => __( 'Centrum wiedzy — opis', 'eurowet-2026' ),
		'ingredients_title'   => __( 'Składniki — nagłówek', 'eurowet-2026' ),
		'ingredients_lead'    => __( 'Składniki — opis', 'eurowet-2026' ),
		'about_title'         => __( 'O Eurowet — nagłówek', 'eurowet-2026' ),
		'about_text'          => __( 'O Eurowet — tekst', 'eurowet-2026' ),
		'b2b_title'           => __( 'Współpraca B2B — nagłówek', 'eurowet-2026' ),
		'b2b_text'            => __( 'Współpraca B2B — tekst', 'eurowet-2026' ),
		'private_label_title' => __( 'Marka własna — nagłówek', 'eurowet-2026' ),
		'private_label_text'  => __( 'Marka własna — tekst', 'eurowet-2026' ),
		'reps_title'          => __( 'Przedstawiciele — nagłówek', 'eurowet-2026' ),
		'reps_text'           => __( 'Przedstawiciele — tekst', 'eurowet-2026' ),
		'contact_title'       => __( 'Kontakt — nagłówek', 'eurowet-2026' ),
	);
}

/** Text for a homepage key: Customizer value, else locale defaults file, else ''. */
function ew_theme_home_text( string $key ): string {
	// Customizer overrides apply to the default language; other languages read content-defaults/home.{locale}.json.
	$mod = 'pl' === ( function_exists( 'pll_current_language' ) ? ( pll_current_language( 'slug' ) ?: 'pl' ) : 'pl' ) ? get_theme_mod( 'ew_home_' . $key, '' ) : '';
	if ( is_string( $mod ) && '' !== trim( $mod ) ) {
		return $mod;
	}
	static $defaults = null;
	if ( null === $defaults ) {
		$locale   = determine_locale();
		$file     = EW_THEME_DIR . '/content-defaults/home.' . $locale . '.json';
		$file     = is_readable( $file ) ? $file : EW_THEME_DIR . '/content-defaults/home.pl_PL.json';
		$defaults = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	$v = $defaults[ $key ] ?? '';
	return is_string( $v ) ? $v : '';
}

/** @return string[] list values from the defaults file (e.g. b2b_audiences). */
function ew_theme_home_list( string $key ): array {
	ew_theme_home_text( 'hero_title' ); // load defaults
	$locale = determine_locale();
	$file   = EW_THEME_DIR . '/content-defaults/home.' . $locale . '.json';
	$file   = is_readable( $file ) ? $file : EW_THEME_DIR . '/content-defaults/home.pl_PL.json';
	$data   = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return array_values( array_filter( array_map( 'strval', (array) ( $data[ $key ] ?? array() ) ) ) );
}

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $c ): void {
		$c->add_section( 'ew_home', array( 'title' => __( 'Strona główna Eurowet', 'eurowet-2026' ), 'priority' => 30 ) );
		foreach ( ew_theme_home_fields() as $key => $label ) {
			$c->add_setting( 'ew_home_' . $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
			$c->add_control( 'ew_home_' . $key, array( 'label' => $label, 'section' => 'ew_home', 'type' => str_contains( $key, 'title' ) ? 'text' : 'textarea', 'description' => wp_trim_words( ew_theme_home_text( $key ), 14 ) ) );
		}
		$c->add_setting( 'ew_hero_product', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$c->add_control( 'ew_hero_product', array( 'label' => __( 'Produkt w hero 3D (ID)', 'eurowet-2026' ), 'section' => 'ew_home', 'type' => 'number' ) );
		$c->add_setting( 'ew_hero_profile', array( 'default' => 'triaderm-excellence-200ml', 'sanitize_callback' => 'sanitize_key' ) );
		$c->add_control( 'ew_hero_profile', array( 'label' => __( 'Profil bryły 3D (assets/3d/*.profile.json)', 'eurowet-2026' ), 'section' => 'ew_home', 'type' => 'text' ) );
	}
);
