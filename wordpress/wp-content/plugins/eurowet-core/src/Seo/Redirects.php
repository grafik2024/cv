<?php
/**
 * 301 map (table {prefix}ew_redirects): handled before WordPress decides on a 404, with hit counters,
 * admin list, CSV import/export and loop/chain protection.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Seo;

use Eurowet\Core\Data\Schema;

defined( 'ABSPATH' ) || exit;

final class Redirects {

	public static function register(): void {
		add_action( 'template_redirect', array( self::class, 'handle' ), 1 );
		add_action( 'ew_admin_menu', array( self::class, 'menu' ) );
		add_action( 'admin_post_ew_redirects_import', array( self::class, 'importUpload' ) );
		add_action( 'admin_post_ew_redirects_export', array( self::class, 'export' ) );
		add_action( 'admin_post_ew_redirects_delete', array( self::class, 'deleteOne' ) );
	}

	public static function normalize( string $path ): string {
		$path = (string) wp_parse_url( $path, PHP_URL_PATH );
		$path = '/' . trim( rawurldecode( $path ), '/' );
		return '/' === $path ? '/' : mb_strtolower( $path ) . '/';
	}

	/** @return array{target:string, code:int, id:int}|null */
	public static function find( string $path ): ?array {
		global $wpdb;
		$key = self::normalize( $path );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT id, target, code FROM ' . Schema::table( 'ew_redirects' ) . ' WHERE source_path = %s LIMIT 1', $key ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ? array( 'id' => (int) $row['id'], 'target' => (string) $row['target'], 'code' => (int) $row['code'] ) : null;
	}

	public static function handle(): void {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$hit = self::find( $uri );
		if ( ! $hit ) {
			return;
		}
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . Schema::table( 'ew_redirects' ) . ' SET hits = hits + 1, last_hit = %s WHERE id = %d', gmdate( 'Y-m-d H:i:s' ), $hit['id'] ) ); // phpcs:ignore WordPress.DB
		if ( 410 === $hit['code'] ) {
			status_header( 410 );
			nocache_headers();
			exit;
		}
		$target = $hit['target'];
		$query  = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		$target = 0 === strpos( $target, '/' ) ? home_url( $target ) : $target;
		if ( '' !== $query && false === strpos( $target, '?' ) ) {
			$target .= '?' . $query;
		}
		if ( self::normalize( $target ) === self::normalize( $uri ) ) {
			return; // never redirect to itself
		}
		wp_safe_redirect( $target, in_array( $hit['code'], array( 301, 302, 307, 308 ), true ) ? $hit['code'] : 301, 'Eurowet' );
		exit;
	}

	/**
	 * Imports rows (old_path,new_path,status,reason). Status "keep" rows are skipped.
	 *
	 * @return array{added:int, updated:int, skipped:int, errors:string[]}
	 */
	public static function import( string $file ): array {
		global $wpdb;
		$stats = array( 'added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array() );
		$fh    = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $fh ) {
			$stats['errors'][] = 'Cannot open ' . $file;
			return $stats;
		}
		$header = fgetcsv( $fh );
		$map    = array_flip( array_map( static fn( $h ) => strtolower( trim( (string) $h ) ), (array) $header ) );
		$table  = Schema::table( 'ew_redirects' );
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			$old    = trim( (string) ( $row[ $map['old_path'] ?? 0 ] ?? '' ) );
			$new    = trim( (string) ( $row[ $map['new_path'] ?? 1 ] ?? '' ) );
			$status = strtolower( trim( (string) ( $row[ $map['status'] ?? 2 ] ?? '301' ) ) );
			if ( '' === $old || 'keep' === $status || ( '' === $new && '410' !== $status ) ) {
				++$stats['skipped'];
				continue;
			}
			$code = in_array( $status, array( '301', '302', '410' ), true ) ? (int) $status : 301;
			$src  = self::normalize( $old );
			if ( 410 !== $code && self::normalize( $new ) === $src ) {
				++$stats['skipped'];
				continue;
			}
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE source_path = %s", $src ) ); // phpcs:ignore WordPress.DB
			if ( $exists ) {
				$wpdb->update( $table, array( 'target' => $new, 'code' => $code ), array( 'id' => (int) $exists ) ); // phpcs:ignore WordPress.DB
				++$stats['updated'];
			} else {
				$wpdb->insert( $table, array( 'source_path' => $src, 'target' => $new, 'code' => $code, 'hits' => 0 ) ); // phpcs:ignore WordPress.DB
				++$stats['added'];
			}
		}
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		foreach ( self::chains() as $c ) {
			$stats['errors'][] = 'Chain/loop: ' . $c;
		}
		return $stats;
	}

	/** @return string[] descriptions of chains (A→B→C) and loops */
	public static function chains(): array {
		global $wpdb;
		$rows = (array) $wpdb->get_results( 'SELECT source_path, target, code FROM ' . Schema::table( 'ew_redirects' ), ARRAY_A ); // phpcs:ignore WordPress.DB
		$map  = array();
		foreach ( $rows as $r ) {
			if ( 410 !== (int) $r['code'] ) {
				$map[ $r['source_path'] ] = self::normalize( (string) $r['target'] );
			}
		}
		$out = array();
		foreach ( $map as $src => $dst ) {
			if ( isset( $map[ $dst ] ) ) {
				$out[] = $src . ' → ' . $dst . ' → ' . $map[ $dst ];
			}
		}
		return $out;
	}

	public static function menu( string $parent ): void {
		add_submenu_page( $parent, __( 'Przekierowania 301', 'eurowet-core' ), __( 'Przekierowania', 'eurowet-core' ), 'manage_options', 'eurowet-redirects', array( self::class, 'render' ) );
	}

	public static function render(): void {
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$s     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ) : '';
		$table = Schema::table( 'ew_redirects' );
		$rows  = $s
			? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE source_path LIKE %s OR target LIKE %s ORDER BY hits DESC LIMIT 500", '%' . $wpdb->esc_like( $s ) . '%', '%' . $wpdb->esc_like( $s ) . '%' ), ARRAY_A ) // phpcs:ignore WordPress.DB
			: $wpdb->get_results( "SELECT * FROM {$table} ORDER BY hits DESC, source_path ASC LIMIT 500", ARRAY_A ); // phpcs:ignore WordPress.DB
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB
		echo '<div class="wrap"><h1>' . esc_html__( 'Przekierowania 301', 'eurowet-core' ) . '</h1>';
		printf( '<p>%s</p>', esc_html( sprintf( /* translators: %d: count */ __( 'Liczba przekierowań: %d. Obsługiwane przed wyświetleniem strony 404.', 'eurowet-core' ), $total ) ) );
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'ew_redirects_import' );
		echo '<input type="hidden" name="action" value="ew_redirects_import"><label>' . esc_html__( 'Import CSV (old_path,new_path,status,reason)', 'eurowet-core' ) . ' <input type="file" name="csv" accept=".csv,text/csv" required></label> ';
		submit_button( __( 'Importuj', 'eurowet-core' ), 'secondary', 'submit', false );
		printf( ' <a class="button" href="%s">%s</a></form>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ew_redirects_export' ), 'ew_redirects_export' ) ), esc_html__( 'Eksport CSV', 'eurowet-core' ) );
		printf( '<form method="get"><input type="hidden" name="page" value="eurowet-redirects"><input type="search" name="s" value="%s" aria-label="%s"> %s</form>', esc_attr( $s ), esc_attr__( 'Szukaj przekierowań', 'eurowet-core' ), get_submit_button( __( 'Szukaj', 'eurowet-core' ), 'secondary', '', false ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		$chains = self::chains();
		if ( $chains ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Wykryto łańcuchy przekierowań:', 'eurowet-core' ) . '</p><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $chains ) ) . '</li></ul></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Stary adres', 'eurowet-core' ) . '</th><th>' . esc_html__( 'Cel', 'eurowet-core' ) . '</th><th>' . esc_html__( 'Kod', 'eurowet-core' ) . '</th><th>' . esc_html__( 'Wejścia', 'eurowet-core' ) . '</th><th>' . esc_html__( 'Ostatnio', 'eurowet-core' ) . '</th><th></th></tr></thead><tbody>';
		foreach ( (array) $rows as $r ) {
			printf(
				'<tr><td>%s</td><td><a href="%s">%s</a></td><td>%d</td><td>%d</td><td>%s</td><td><a href="%s" onclick="return confirm(\'%s\')">%s</a></td></tr>',
				esc_html( (string) $r['source_path'] ),
				esc_url( 0 === strpos( (string) $r['target'], '/' ) ? home_url( (string) $r['target'] ) : (string) $r['target'] ),
				esc_html( (string) $r['target'] ),
				(int) $r['code'],
				(int) $r['hits'],
				esc_html( (string) $r['last_hit'] ),
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ew_redirects_delete&id=' . (int) $r['id'] ), 'ew_redirects_delete' ) ),
				esc_js( __( 'Usunąć przekierowanie?', 'eurowet-core' ) ),
				esc_html__( 'Usuń', 'eurowet-core' )
			);
		}
		echo '</tbody></table></div>';
	}

	public static function importUpload(): void {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ew_redirects_import' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		$tmp   = isset( $_FILES['csv']['tmp_name'] ) ? (string) $_FILES['csv']['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$stats = '' !== $tmp && is_uploaded_file( $tmp ) ? self::import( $tmp ) : array( 'added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array( 'upload' ) );
		wp_safe_redirect( add_query_arg( array( 'page' => 'eurowet-redirects', 'imported' => $stats['added'] + $stats['updated'] ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function export(): void {
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ew_redirects_export' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=eurowet-redirects.csv' );
		$fh = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $fh, array( 'old_path', 'new_path', 'status', 'hits', 'last_hit' ) );
		foreach ( (array) $wpdb->get_results( 'SELECT * FROM ' . Schema::table( 'ew_redirects' ) . ' ORDER BY source_path', ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			fputcsv( $fh, array( $r['source_path'], $r['target'], $r['code'], $r['hits'], $r['last_hit'] ) );
		}
		exit;
	}

	public static function deleteOne(): void {
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ew_redirects_delete' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$wpdb->delete( Schema::table( 'ew_redirects' ), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
		wp_safe_redirect( admin_url( 'admin.php?page=eurowet-redirects' ) );
		exit;
	}
}
