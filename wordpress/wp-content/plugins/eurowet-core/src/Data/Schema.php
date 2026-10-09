<?php
/**
 * Custom database tables (ARCHITECTURE §7 and §8).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Creates/upgrades the plugin tables with dbDelta.
 *
 * Tables are owned here; other modules must not run their own dbDelta on them.
 * To change a table: edit self::definitions() and bump self::VERSION (the upgrade runs automatically).
 */
final class Schema {

	public const VERSION = '1.0.0';

	public const OPTION = 'ew_core_db_version';

	/** Logical table names (without prefix). */
	public const TABLES = array( 'ew_finder_log', 'ew_finder_event', 'ew_redirects' );

	/**
	 * Full table name with the site prefix.
	 */
	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . $name;
	}

	/**
	 * Runs install() when the stored schema version differs (cheap autoloaded option check).
	 */
	public static function maybeUpgrade(): void {
		if ( get_option( self::OPTION ) !== self::VERSION ) {
			self::install();
		}
	}

	/**
	 * Creates or updates all tables.
	 */
	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		foreach ( self::definitions() as $name => $columns ) {
			$table = self::table( $name );
			// dbDelta requires: two spaces after PRIMARY KEY, one field per line, KEY instead of INDEX.
			dbDelta( "CREATE TABLE {$table} (\n{$columns}\n) {$charset};" );
		}
		update_option( self::OPTION, self::VERSION, true );

		/**
		 * Fires after the plugin tables were created or upgraded.
		 *
		 * @param string $version Schema version.
		 */
		do_action( 'ew_schema_installed', self::VERSION );
	}

	/**
	 * Column/index definitions per table (dbDelta syntax).
	 *
	 * ew_finder_log: anonymous Product Finder queries. created_at is rounded to the hour by the writer,
	 *   query is max 200 chars with personal data masked by the writer; no IP/cookies/user IDs.
	 * ew_finder_event: clicks on finder results (product_click|buy_click|guide_click|need_click).
	 * ew_redirects: 301/302/410 redirects; source_path is matched exactly (non-unique prefix index: long
	 *   percent-encoded paths may share 191 chars, so uniqueness is enforced by the writer).
	 *
	 * @return array<string, string>
	 */
	public static function definitions(): array {
		$tables = array(
			'ew_finder_log'   => "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
created_at datetime NOT NULL,
lang varchar(10) NOT NULL DEFAULT '',
query varchar(200) NOT NULL DEFAULT '',
need_id bigint(20) unsigned NOT NULL DEFAULT 0,
confidence decimal(5,4) NOT NULL DEFAULT 0.0000,
matched tinyint(1) unsigned NOT NULL DEFAULT 0,
primary_product_id bigint(20) unsigned NOT NULL DEFAULT 0,
red_flag_level varchar(10) NOT NULL DEFAULT 'none',
source varchar(10) NOT NULL DEFAULT 'finder',
interpreter varchar(10) NOT NULL DEFAULT 'local',
PRIMARY KEY  (id),
KEY created_at (created_at),
KEY need_id (need_id),
KEY matched_created (matched,created_at),
KEY lang_created (lang,created_at)",
			'ew_finder_event' => "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
log_id bigint(20) unsigned NOT NULL DEFAULT 0,
created_at datetime NOT NULL,
type varchar(20) NOT NULL DEFAULT '',
target_id bigint(20) unsigned NOT NULL DEFAULT 0,
PRIMARY KEY  (id),
KEY log_id (log_id),
KEY created_at (created_at),
KEY type_created (type,created_at)",
			'ew_redirects'    => "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
source_path varchar(255) NOT NULL DEFAULT '',
target varchar(2048) NOT NULL DEFAULT '',
code smallint(3) unsigned NOT NULL DEFAULT 301,
hits bigint(20) unsigned NOT NULL DEFAULT 0,
last_hit datetime NULL DEFAULT NULL,
PRIMARY KEY  (id),
KEY source_path (source_path(191))",
		);

		/**
		 * Filters table definitions before dbDelta. Bump Schema::VERSION when changing them.
		 *
		 * @param array<string, string> $tables Table name (without prefix) => dbDelta column list.
		 */
		return (array) apply_filters( 'ew_schema_tables', $tables );
	}

	/**
	 * Existence of each table.
	 *
	 * @return array<string, bool> table (with prefix) => exists
	 */
	public static function status(): array {
		global $wpdb;
		$out = array();
		foreach ( array_keys( self::definitions() ) as $name ) {
			$table         = self::table( $name );
			$found         = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
			$out[ $table ] = ( $found === $table );
		}
		return $out;
	}
}
