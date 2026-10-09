<?php
/**
 * Small template helpers shared by components (loaded once by the first component that needs them).
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ew_icon' ) ) {
	/** Theme icon if available (decorative unless a label is given). */
	function ew_icon( string $name, string $label = '' ): string {
		return function_exists( 'ew_theme_icon' ) ? (string) ew_theme_icon( $name, $label ) : '';
	}
}

if ( ! function_exists( 'ew_species_label' ) ) {
	function ew_species_label( string $slug ): string {
		$t = get_term_by( 'slug', $slug, 'ew_species' );
		return $t ? $t->name : $slug;
	}
}

if ( ! function_exists( 'ew_heading' ) ) {
	/** Section heading with configurable level (h2 default). */
	function ew_heading( string $text, string $id = '', int $level = 2, string $class = 'ew-section__title' ): string {
		$level = max( 2, min( 4, $level ) );
		return sprintf( '<h%1$d class="%2$s"%3$s>%4$s</h%1$d>', $level, esc_attr( $class ), $id ? ' id="' . esc_attr( $id ) . '"' : '', esc_html( $text ) );
	}
}
