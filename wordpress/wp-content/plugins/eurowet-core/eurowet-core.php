<?php
/**
 * Plugin Name:       Eurowet Core
 * Plugin URI:        https://eurowet.pl/
 * Description:       Rdzeń serwisu Eurowet: typy treści (potrzeby, porady, składniki, przedstawiciele, materiały, leady B2B), relacje produktów, wyszukiwarka potrzeb, SEO, ustawienia i narzędzia redakcyjne.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Eurowet
 * Author URI:        https://eurowet.pl/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eurowet-core
 * Domain Path:       /languages
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'EW_CORE_VERSION', '1.0.0' );
define( 'EW_CORE_FILE', __FILE__ );
define( 'EW_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'EW_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'EW_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'EW_CORE_MIN_PHP', '8.1' );

if ( version_compare( PHP_VERSION, EW_CORE_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version. */
						__( 'Wtyczka Eurowet Core wymaga PHP w wersji %1$s lub nowszej (obecnie: %2$s). Wtyczka nie została uruchomiona.', 'eurowet-core' ),
						EW_CORE_MIN_PHP,
						PHP_VERSION
					)
				)
			);
		}
	);
	return;
}

/*
 * PSR-4 autoloader: Eurowet\Core\Foo\Bar => src/Foo/Bar.php.
 * No Composer at runtime (dev dependencies for tests only).
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'Eurowet\\Core\\';
		if ( 0 !== strncmp( $class, $prefix, strlen( $prefix ) ) ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		// Reject anything that is not a plain namespaced identifier (defence against odd class strings).
		if ( ! preg_match( '/^[A-Za-z0-9_\\\\]+$/', $relative ) ) {
			return;
		}
		$file = EW_CORE_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

require_once EW_CORE_DIR . 'src/functions.php';

register_activation_hook( __FILE__, array( \Eurowet\Core\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \Eurowet\Core\Lifecycle::class, 'deactivate' ) );

add_action(
	'init',
	static function (): void {
		load_plugin_textdomain( 'eurowet-core', false, dirname( EW_CORE_BASENAME ) . '/languages' );
	},
	0
);

// Boot after all plugins are loaded so integrations (WooCommerce, Polylang, Yoast, Elementor) can be detected.
add_action(
	'plugins_loaded',
	static function (): void {
		\Eurowet\Core\Plugin::instance()->boot();
	},
	20
);
