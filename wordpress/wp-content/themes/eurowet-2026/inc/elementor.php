<?php
/**
 * Elementor compatibility: full-width canvas for pages built with Elementor; tokens exposed as globals.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

function ew_theme_built_with_elementor( int $post_id ): bool {
	if ( ! class_exists( '\Elementor\Plugin' ) || $post_id <= 0 ) {
		return false;
	}
	$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $doc && $doc->is_built_with_elementor();
}

// Elementor global colours follow the theme tokens (so Elementor sections switch with dark mode).
add_action(
	'wp_head',
	static function (): void {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			echo '<style id="ew-elementor-tokens">:root{--e-global-color-primary:var(--ew-color-brand);--e-global-color-secondary:var(--ew-color-accent);--e-global-color-text:var(--ew-color-text);--e-global-color-accent:var(--ew-color-brand-strong);--e-global-typography-primary-font-family:var(--ew-font-display);--e-global-typography-text-font-family:var(--ew-font-sans)}</style>' . "\n";
		}
	},
	30
);
