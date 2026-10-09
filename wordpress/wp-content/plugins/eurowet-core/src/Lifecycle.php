<?php
/**
 * Activation, deactivation and version upgrades.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core;

use Eurowet\Core\Data\Registry;
use Eurowet\Core\Data\Schema;
use Eurowet\Core\Graph\Routing;

defined( 'ABSPATH' ) || exit;

final class Lifecycle {

	/** Set when rewrite rules must be regenerated on the next fully loaded request. */
	public const FLUSH_OPTION = 'ew_core_flush_rewrite';

	/** Last plugin version that ran the upgrade routine. */
	public const VERSION_OPTION = 'ew_core_version';

	/**
	 * Activation: tables, content model, rewrite rules.
	 *
	 * @param bool $network_wide Network activation (unused; the plugin is activated per site).
	 */
	public static function activate( $network_wide = false ): void {
		Schema::install();
		// The plugin was not loaded during this request's `init`, so register what rewrite rules depend on.
		Registry::register();
		Routing::addRewriteRules();
		flush_rewrite_rules( false );
		// Flush again once Polylang and WooCommerce added their rules (they hook into wp_loaded/init).
		self::scheduleFlush();
		update_option( self::VERSION_OPTION, EW_CORE_VERSION, true );

		Plugin::instance()->boot();
		/**
		 * Fires when the plugin is activated (modules may schedule cron events here).
		 */
		do_action( 'ew_core_activate' );
	}

	/**
	 * Deactivation: drop generated rewrite rules so WordPress rebuilds them without our types.
	 */
	public static function deactivate(): void {
		delete_option( 'rewrite_rules' );
		/**
		 * Fires when the plugin is deactivated (modules should unschedule cron events here).
		 */
		do_action( 'ew_core_deactivate' );
	}

	public static function scheduleFlush(): void {
		update_option( self::FLUSH_OPTION, 1, true );
	}

	/**
	 * Flushes rewrite rules when scheduled. Hooked late on wp_loaded so every plugin's rules exist.
	 */
	public static function maybeFlush(): void {
		if ( get_option( self::FLUSH_OPTION ) ) {
			delete_option( self::FLUSH_OPTION );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Runs upgrades after a plugin update (schema + rewrite rules).
	 */
	public static function checkVersion(): void {
		Schema::maybeUpgrade();
		if ( get_option( self::VERSION_OPTION ) !== EW_CORE_VERSION ) {
			update_option( self::VERSION_OPTION, EW_CORE_VERSION, true );
			self::scheduleFlush();
			/**
			 * Fires once after the plugin version changed.
			 *
			 * @param string $version New version.
			 */
			do_action( 'ew_core_upgraded', EW_CORE_VERSION );
		}
	}
}
