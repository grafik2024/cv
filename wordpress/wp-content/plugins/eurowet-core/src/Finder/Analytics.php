<?php
/**
 * Admin page "Analityka wyszukiwarki": what people searched, what found no answer, popular needs,
 * click-through to products, guide→product transitions. CSV export.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Data\Schema;

defined( 'ABSPATH' ) || exit;

final class Analytics {

	public static function menu( string $parent ): void {
		add_submenu_page( $parent, __( 'Analityka wyszukiwarki', 'eurowet-core' ), __( 'Analityka wyszukiwarki', 'eurowet-core' ), 'edit_others_posts', 'eurowet-finder-analytics', array( self::class, 'render' ) );
		add_action( 'admin_post_ew_finder_csv', array( self::class, 'csv' ) );
	}

	/** @return array{0:string,1:string} */
	private static function range(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['from'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$to   = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['to'] ) ) : gmdate( 'Y-m-d' );
		// phpcs:enable
		$from = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ? $from : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$to   = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ? $to : gmdate( 'Y-m-d' );
		return array( $from . ' 00:00:00', $to . ' 23:59:59' );
	}

	/** @return array<string, array<int, array<string, mixed>>> */
	public static function data( string $from, string $to ): array {
		global $wpdb;
		$log = Schema::table( 'ew_finder_log' );
		$ev  = Schema::table( 'ew_finder_event' );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array(
			'top'      => (array) $wpdb->get_results( $wpdb->prepare( "SELECT query, COUNT(*) n, ROUND(AVG(matched)*100) match_pct FROM {$log} WHERE created_at BETWEEN %s AND %s GROUP BY query ORDER BY n DESC LIMIT 50", $from, $to ), ARRAY_A ),
			'nomatch'  => (array) $wpdb->get_results( $wpdb->prepare( "SELECT query, COUNT(*) n, MAX(red_flag_level) red_flag FROM {$log} WHERE matched = 0 AND created_at BETWEEN %s AND %s GROUP BY query ORDER BY n DESC LIMIT 100", $from, $to ), ARRAY_A ),
			'needs'    => (array) $wpdb->get_results( $wpdb->prepare( "SELECT l.need_id, COUNT(*) n, SUM(e.type IN ('product_click','buy_click')) clicks FROM {$log} l LEFT JOIN {$ev} e ON e.log_id = l.id WHERE l.need_id > 0 AND l.created_at BETWEEN %s AND %s GROUP BY l.need_id ORDER BY n DESC LIMIT 50", $from, $to ), ARRAY_A ),
			'guide2product' => (array) $wpdb->get_results( $wpdb->prepare( "SELECT target_id, COUNT(*) n FROM {$ev} WHERE type IN ('product_click','buy_click') AND log_id = 0 AND created_at BETWEEN %s AND %s GROUP BY target_id ORDER BY n DESC LIMIT 50", $from, $to ), ARRAY_A ),
		);
		// phpcs:enable
	}

	public static function render(): void {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		[ $from, $to ] = self::range();
		$d             = self::data( $from, $to );
		echo '<div class="wrap"><h1>' . esc_html__( 'Analityka wyszukiwarki (Product Finder)', 'eurowet-core' ) . '</h1>';
		echo '<p>' . esc_html__( 'Dane są anonimowe: bez adresów IP, cookies i identyfikatorów użytkowników. Zapytania z danymi kontaktowymi są maskowane.', 'eurowet-core' ) . '</p>';
		printf(
			'<form method="get"><input type="hidden" name="page" value="eurowet-finder-analytics"><label>%s <input type="date" name="from" value="%s"></label> <label>%s <input type="date" name="to" value="%s"></label> %s <a class="button" href="%s">%s</a></form>',
			esc_html__( 'Od', 'eurowet-core' ),
			esc_attr( substr( $from, 0, 10 ) ),
			esc_html__( 'Do', 'eurowet-core' ),
			esc_attr( substr( $to, 0, 10 ) ),
			get_submit_button( __( 'Pokaż', 'eurowet-core' ), 'secondary', '', false ),
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ew_finder_csv&from=' . rawurlencode( substr( $from, 0, 10 ) ) . '&to=' . rawurlencode( substr( $to, 0, 10 ) ) ), 'ew_finder_csv' ) ),
			esc_html__( 'Eksport CSV (zapytania bez wyniku)', 'eurowet-core' )
		);
		self::table( __( 'Zapytania bez dopasowania — kandydaci na nowe treści', 'eurowet-core' ), $d['nomatch'], array( 'query' => __( 'Zapytanie', 'eurowet-core' ), 'n' => __( 'Liczba', 'eurowet-core' ), 'red_flag' => __( 'Czerwona flaga', 'eurowet-core' ) ), true );
		self::table( __( 'Najczęstsze zapytania', 'eurowet-core' ), $d['top'], array( 'query' => __( 'Zapytanie', 'eurowet-core' ), 'n' => __( 'Liczba', 'eurowet-core' ), 'match_pct' => __( '% dopasowanych', 'eurowet-core' ) ) );
		$needs = array_map(
			static function ( $r ) {
				$r['need'] = get_the_title( (int) $r['need_id'] );
				$r['ctr']  = $r['n'] ? round( 100 * (int) $r['clicks'] / (int) $r['n'] ) . '%' : '—';
				return $r;
			},
			$d['needs']
		);
		self::table( __( 'Najpopularniejsze potrzeby i przejścia do produktu', 'eurowet-core' ), $needs, array( 'need' => __( 'Potrzeba', 'eurowet-core' ), 'n' => __( 'Wyszukania', 'eurowet-core' ), 'clicks' => __( 'Kliknięcia produktu', 'eurowet-core' ), 'ctr' => 'CTR' ) );
		$g2p = array_map(
			static function ( $r ) {
				$r['product'] = get_the_title( (int) $r['target_id'] );
				return $r;
			},
			$d['guide2product']
		);
		self::table( __( 'Przejścia z porad do produktów', 'eurowet-core' ), $g2p, array( 'product' => __( 'Produkt', 'eurowet-core' ), 'n' => __( 'Kliknięcia', 'eurowet-core' ) ) );
		echo '</div>';
	}

	private static function table( string $title, array $rows, array $cols, bool $create_links = false ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';
		if ( ! $rows ) {
			echo '<p>' . esc_html__( 'Brak danych w wybranym okresie.', 'eurowet-core' ) . '</p>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr>';
		foreach ( $cols as $label ) {
			echo '<th scope="col">' . esc_html( $label ) . '</th>';
		}
		echo $create_links ? '<th scope="col">' . esc_html__( 'Akcja', 'eurowet-core' ) . '</th>' : '';
		echo '</tr></thead><tbody>';
		foreach ( $rows as $r ) {
			echo '<tr>';
			foreach ( array_keys( $cols ) as $k ) {
				echo '<td>' . esc_html( (string) ( $r[ $k ] ?? '' ) ) . '</td>';
			}
			if ( $create_links ) {
				printf(
					'<td><a href="%s">%s</a> · <a href="%s">%s</a></td>',
					esc_url( admin_url( 'post-new.php?post_type=ew_need&post_title=' . rawurlencode( (string) $r['query'] ) ) ),
					esc_html__( 'Nowa potrzeba', 'eurowet-core' ),
					esc_url( admin_url( 'post-new.php?post_type=ew_guide&post_title=' . rawurlencode( (string) $r['query'] ) ) ),
					esc_html__( 'Nowa porada (szkic)', 'eurowet-core' )
				);
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	public static function csv(): void {
		if ( ! current_user_can( 'edit_others_posts' ) || ! check_admin_referer( 'ew_finder_csv' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		[ $from, $to ] = self::range();
		$d             = self::data( $from, $to );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=eurowet-finder-bez-wyniku.csv' );
		$fh = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $fh, array( 'query', 'count', 'red_flag' ) );
		foreach ( $d['nomatch'] as $r ) {
			fputcsv( $fh, array( $r['query'], $r['n'], $r['red_flag'] ) );
		}
		exit;
	}
}
