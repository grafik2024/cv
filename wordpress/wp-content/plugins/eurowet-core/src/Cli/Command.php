<?php
/**
 * Eurowet site tooling.
 *
 * ## EXAMPLES
 *
 *     wp eurowet setup --dry-run
 *     wp eurowet import all --root=/path/to/repo
 *     wp eurowet import products --production --no-images
 *     wp eurowet redirects test --root=/path/to/repo
 *     wp eurowet audit
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Cli;

use Eurowet\Core\Admin\ContentAudit;
use Eurowet\Core\Data\ReverseIndex;
use Eurowet\Core\Data\Schema;
use Eurowet\Core\Graph\Cache;
use Eurowet\Core\I18n\Module as I18n;
use Eurowet\Core\Plugin;
use WP_CLI;

defined( 'ABSPATH' ) || exit;

final class Command {

	/**
	 * Module status, content counts and DB tables.
	 */
	public function status(): void {
		foreach ( Plugin::instance()->status() as $id => $s ) {
			WP_CLI::log( sprintf( '%-12s %-8s %s', $id, $s['state'], $s['error'] ) );
		}
		foreach ( array( 'product', 'ew_need', 'ew_guide', 'ew_ingredient', 'ew_rep', 'ew_material', 'ew_lead' ) as $pt ) {
			$c = wp_count_posts( $pt );
			WP_CLI::log( sprintf( '%-14s %d', $pt, (int) ( $c->publish ?? 0 ) + (int) ( $c->private ?? 0 ) ) );
		}
		foreach ( Schema::status() as $t => $ok ) {
			WP_CLI::log( sprintf( '%-30s %s', $t, $ok ? 'ok' : 'MISSING' ) );
		}
	}

	/**
	 * Configures the site (idempotent): permalinks, WooCommerce URL structure, pages, menus, languages, Yoast.
	 *
	 * ## OPTIONS
	 *
	 * [--production]
	 * : Never delete/overwrite existing content or tax settings.
	 *
	 * [--dry-run]
	 * : Print the plan only.
	 *
	 * [--root=<path>]
	 * : Repository root (for content/menus.json). Default: plugin dir ../../../..
	 */
	public function setup( $args, $assoc ): void {
		$dry  = isset( $assoc['dry-run'] );
		$root = $this->root( $assoc );
		$plan = Setup::plan( $root );
		foreach ( $plan as $line ) {
			WP_CLI::log( ( $dry ? '[plan] ' : '' ) . $line );
		}
		if ( $dry ) {
			return;
		}
		foreach ( Setup::run( $root, isset( $assoc['production'] ) ) as $line ) {
			WP_CLI::log( $line );
		}
		WP_CLI::success( 'Setup done.' );
	}

	/**
	 * Imports content from the repository.
	 *
	 * ## OPTIONS
	 *
	 * <what>
	 * : taxonomy | products | needs | guides | ingredients | pages | reps | materials | company | redirects | all
	 *
	 * [--root=<path>]
	 * : Repository root.
	 *
	 * [--production]
	 * : Only add missing data; never overwrite prices, stock or existing content.
	 *
	 * [--dry-run]
	 * : Print changes only.
	 *
	 * [--no-images]
	 * : Skip downloading packshots/PDFs.
	 */
	public function import( $args, $assoc ): void {
		$what = $args[0] ?? 'all';
		$imp  = new Importer( $this->root( $assoc ), isset( $assoc['production'] ), isset( $assoc['dry-run'] ), ! isset( $assoc['no-images'] ) && ! ( isset( $assoc['images'] ) && 'false' === $assoc['images'] ) );
		$all  = array( 'taxonomy', 'company', 'products', 'ingredients', 'guides', 'needs', 'pages', 'reps', 'materials', 'redirects' );
		$list = 'all' === $what ? $all : array( $what );
		foreach ( $list as $w ) {
			switch ( $w ) {
				case 'taxonomy':
					$imp->taxonomy();
					break;
				case 'products':
					$imp->products();
					break;
				case 'needs':
				case 'guides':
				case 'ingredients':
				case 'pages':
					$imp->content( $w );
					break;
				case 'reps':
					$imp->reps();
					break;
				case 'materials':
					$imp->materials();
					break;
				case 'company':
					$imp->company();
					break;
				case 'redirects':
					$imp->redirects();
					break;
				default:
					WP_CLI::error( "Unknown import type: {$w}" );
			}
		}
		if ( ! isset( $assoc['dry-run'] ) ) {
			ReverseIndex::rebuildAll();
			Cache::bump();
			flush_rewrite_rules( false );
		}
		WP_CLI::success( 'Import finished.' );
	}

	/**
	 * Rebuilds the product → needs reverse index and clears graph caches.
	 *
	 * @subcommand graph
	 */
	public function graph( $args ): void {
		$n = ReverseIndex::rebuildAll();
		Cache::bump();
		WP_CLI::success( "Reverse index rebuilt for {$n} products." );
	}

	/**
	 * Redirect utilities.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : import | test | chains
	 *
	 * [--root=<path>]
	 * : Repository root.
	 *
	 * [--base=<url>]
	 * : Base URL for the HTTP test (default: home_url()).
	 */
	public function redirects( $args, $assoc ): void {
		$action = $args[0] ?? 'test';
		$root   = $this->root( $assoc );
		if ( 'import' === $action ) {
			$s = \Eurowet\Core\Seo\Redirects::import( $root . '/content/redirects.csv' );
			WP_CLI::success( sprintf( '%d added, %d updated, %d skipped. %s', $s['added'], $s['updated'], $s['skipped'], implode( '; ', $s['errors'] ) ) );
			return;
		}
		if ( 'chains' === $action ) {
			$c = \Eurowet\Core\Seo\Redirects::chains();
			$c ? WP_CLI::warning( implode( "\n", $c ) ) : WP_CLI::success( 'No chains or loops.' );
			return;
		}
		$base = rtrim( (string) ( $assoc['base'] ?? home_url() ), '/' );
		$fh   = fopen( $root . '/content/redirects.csv', 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $fh ) {
			WP_CLI::error( 'Missing content/redirects.csv' );
		}
		$head  = array_flip( (array) fgetcsv( $fh ) );
		$fails = 0;
		$total = 0;
		while ( ( $r = fgetcsv( $fh ) ) !== false ) {
			$old    = (string) ( $r[ $head['old_path'] ] ?? '' );
			$status = (string) ( $r[ $head['status'] ] ?? '' );
			if ( '' === $old || 'keep' === $status ) {
				continue;
			}
			++$total;
			$res  = wp_remote_get( $base . $old, array( 'redirection' => 5, 'timeout' => 20, 'sslverify' => false ) );
			$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
			$ok   = '410' === $status ? 410 === $code : 200 === $code;
			if ( ! $ok ) {
				++$fails;
				WP_CLI::log( "FAIL {$old} → {$code}" );
			}
		}
		$fails ? WP_CLI::warning( "{$fails}/{$total} redirects failed." ) : WP_CLI::success( "All {$total} redirects resolve." );
	}

	/**
	 * Content audit (stale guides, broken links, products without needs, missing translations…).
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : table | json
	 */
	public function audit( $args, $assoc ): void {
		$r = ContentAudit::run( false );
		if ( 'json' === ( $assoc['format'] ?? 'table' ) ) {
			WP_CLI::log( (string) wp_json_encode( $r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			return;
		}
		foreach ( $r as $section => $rows ) {
			WP_CLI::log( sprintf( '%s: %d', $section, count( $rows ) ) );
			foreach ( array_slice( $rows, 0, 15 ) as $row ) {
				WP_CLI::log( '  - ' . ( is_array( $row ) ? implode( ' | ', array_map( 'strval', $row ) ) : (string) $row ) );
			}
		}
	}

	/**
	 * Polylang languages: PL (default, no prefix), EN, FR, UA (locale uk).
	 *
	 * @subcommand languages
	 */
	public function languages( $args ): void {
		foreach ( I18n::setupLanguages() as $l ) {
			WP_CLI::log( $l );
		}
		WP_CLI::success( 'Languages configured.' );
	}

	/**
	 * Translates a post into an extra language (stored as machine translation, noindex until reviewed).
	 *
	 * ## OPTIONS
	 *
	 * <post_id>
	 * <lang>
	 */
	public function translate( $args ): void {
		$r = \Eurowet\Core\I18n\Extended::translate( (int) $args[0], (string) $args[1] );
		$r['ok'] ? WP_CLI::success( $r['message'] ) : WP_CLI::error( $r['message'] );
	}

	/** @param array<string, string> $assoc */
	private function root( array $assoc ): string {
		return rtrim( (string) ( $assoc['root'] ?? dirname( EW_CORE_DIR, 4 ) ), '/' );
	}
}
