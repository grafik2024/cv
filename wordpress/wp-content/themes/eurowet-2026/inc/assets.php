<?php
/**
 * Styles and scripts: small shell CSS everywhere, template CSS only where used, ES modules deferred.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

/** Template CSS key for the current request. */
function ew_theme_template_css(): array {
	$keys = array();
	if ( is_front_page() ) {
		$keys[] = 'home';
		$keys[] = 'pages'; // company card, form split
		$keys[] = 'content'; // hub cards
	}
	if ( is_singular( array( 'ew_guide', 'ew_need', 'ew_ingredient', 'post' ) ) || is_post_type_archive( array( 'ew_guide', 'ew_need', 'ew_ingredient' ) ) || is_tax( 'ew_hub' ) || is_home() || is_archive() || is_search() || is_404() ) {
		$keys[] = 'content';
	}
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$keys[] = 'shop';
	}
	if ( is_page() && ! is_front_page() ) {
		$keys[] = 'pages';
	}
	return array_unique( $keys );
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$css = array( 'fonts', 'tokens', 'base', 'layout', 'a11y' );
		$dep = array();
		foreach ( $css as $name ) {
			wp_enqueue_style( 'ew-' . $name, ew_theme_asset_uri( 'assets/css/' . $name . '.css' ), $dep, ew_theme_asset_version( 'assets/css/' . $name . '.css' ) );
			$dep = array( 'ew-' . $name );
		}
		foreach ( ew_theme_template_css() as $key ) {
			$rel = 'assets/css/templates/' . $key . '.css';
			if ( is_readable( EW_THEME_DIR . '/' . $rel ) ) {
				wp_enqueue_style( 'ew-t-' . $key, ew_theme_asset_uri( $rel ), array( 'ew-base' ), ew_theme_asset_version( $rel ) );
			}
		}
		ew_theme_enqueue_module( 'ew-shell', 'assets/js/shell.js' );
		ew_theme_enqueue_module( 'ew-a11y', 'assets/js/a11y-panel.js' );
		if ( is_front_page() ) {
			ew_theme_enqueue_module( 'ew-hero-3d', 'assets/js/hero-3d.js' );
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			ew_theme_enqueue_module( 'ew-product', 'assets/js/product.js' );
		}
	},
	20
);

/** ES module enqueue with WP 6.5+ script modules and a classic fallback. */
function ew_theme_enqueue_module( string $handle, string $rel ): void {
	if ( ! is_readable( EW_THEME_DIR . '/' . $rel ) ) {
		return;
	}
	$src = ew_theme_asset_uri( $rel );
	$ver = ew_theme_asset_version( $rel );
	if ( function_exists( 'wp_enqueue_script_module' ) ) {
		wp_enqueue_script_module( $handle, $src, array(), $ver );
		return;
	}
	wp_enqueue_script( $handle, $src, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	add_filter( 'script_loader_tag', static fn( $tag, $h ) => $h === $handle ? str_replace( '<script ', '<script type="module" ', $tag ) : $tag, 10, 2 );
}
