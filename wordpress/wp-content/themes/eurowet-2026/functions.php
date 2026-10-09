<?php
/**
 * Eurowet 2026 — theme bootstrap (child of Hello Elementor).
 *
 * Every integration with eurowet-core (ew_* functions, ew_render() components), WooCommerce,
 * Polylang, Yoast SEO and Elementor is guarded, so the theme never fatals when one is missing.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

define( 'EW_THEME_VERSION', '1.0.0' );
define( 'EW_THEME_DIR', get_stylesheet_directory() );
define( 'EW_THEME_URI', get_stylesheet_directory_uri() );

/*
 * Load order matters: helpers first, then modules. Any other inc/*.php file (e.g. icons.php,
 * three.php added by other workstreams) is loaded afterwards, alphabetically.
 */
$ew_theme_core_files = array(
	'helpers.php',
	'setup.php',
	'head.php',
	'assets.php',
	'performance.php',
	'nav.php',
	'template-tags.php',
	'elementor.php',
	'woocommerce.php',
);

foreach ( $ew_theme_core_files as $ew_theme_file ) {
	require_once EW_THEME_DIR . '/inc/' . $ew_theme_file;
}

foreach ( (array) glob( EW_THEME_DIR . '/inc/*.php' ) as $ew_theme_file ) {
	if ( ! in_array( basename( $ew_theme_file ), $ew_theme_core_files, true ) ) {
		require_once $ew_theme_file;
	}
}

unset( $ew_theme_core_files, $ew_theme_file );
