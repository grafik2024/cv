<?php
/**
 * Periodic content audit (brief §36–37): stale guides, needs without products, products referenced
 * but unavailable, products/guides without relations, broken internal links, missing translations,
 * images without alt. Reports only — never publishes or changes content.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Admin;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Seo\Redirects;

defined( 'ABSPATH' ) || exit;

final class ContentAudit {

	private const CRON   = 'ew_content_audit';
	private const OPTION = 'ew_content_audit_last';

	public static function register(): void {
		add_action( 'ew_admin_menu', static fn( $parent ) => add_submenu_page( $parent, __( 'Audyt treści', 'eurowet-core' ), __( 'Audyt treści', 'eurowet-core' ), 'edit_others_posts', 'eurowet-audit', array( self::class, 'render' ) ) );
		add_action( self::CRON, static fn() => self::run( true ) );
		add_action( 'init', static fn() => wp_next_scheduled( self::CRON ) || wp_schedule_event( time() + DAY_IN_SECONDS, 'weekly', self::CRON ) );
		add_action( 'admin_post_ew_audit_run', array( self::class, 'runNow' ) );
	}

	/** @return array<string, array<int, array<string, string>>> */
	public static function run( bool $store = true ): array {
		$months = max( 1, (int) ew_get_option( 'guide_review_months', 12 ) );
		$limit  = strtotime( "-{$months} months" );
		$r      = array(
			'stale_guides'          => array(),
			'needs_without_primary' => array(),
			'unavailable_products'  => array(),
			'products_without_need' => array(),
			'orphan_guides'         => array(),
			'broken_links'          => array(),
			'missing_translations'  => array(),
			'images_without_alt'    => array(),
		);
		$row = static fn( int $id, string $note = '' ) => array( 'id' => (string) $id, 'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ), 'note' => $note );

		$guides   = get_posts( array( 'post_type' => 'ew_guide', 'post_status' => 'publish', 'posts_per_page' => -1, 'lang' => '', 'no_found_rows' => true ) );
		$linked   = array();
		foreach ( $guides as $g ) {
			$rev = (string) Meta::get( $g->ID, '_ew_reviewed', '' );
			$ts  = $rev ? strtotime( $rev ) : strtotime( $g->post_modified_gmt );
			if ( Meta::bool( $g->ID, '_ew_needs_review' ) || $ts < $limit ) {
				$r['stale_guides'][] = $row( $g->ID, Meta::bool( $g->ID, '_ew_needs_review' ) ? __( 'oznaczona do sprawdzenia', 'eurowet-core' ) : sprintf( /* translators: %s date */ __( 'ostatnia weryfikacja: %s', 'eurowet-core' ), $rev ?: __( 'brak', 'eurowet-core' ) ) );
			}
			foreach ( array_merge( Meta::ids( $g->ID, '_ew_related' ), array( Meta::int( $g->ID, '_ew_next' ) ) ) as $l ) {
				$linked[ (int) $l ] = true;
			}
			foreach ( self::internalLinks( (string) $g->post_content ) as $url ) {
				if ( ! self::resolves( $url ) ) {
					$r['broken_links'][] = $row( $g->ID, $url );
				}
			}
		}
		$needs = get_posts( array( 'post_type' => 'ew_need', 'post_status' => 'publish', 'posts_per_page' => -1, 'lang' => '', 'no_found_rows' => true ) );
		foreach ( $needs as $n ) {
			foreach ( Meta::ids( $n->ID, '_ew_guides' ) as $l ) {
				$linked[ (int) $l ] = true;
			}
			$has_primary = false;
			foreach ( Meta::json( $n->ID, '_ew_products' ) as $p ) {
				$pid = (int) ( $p['product_id'] ?? 0 );
				$st  = get_post_status( $pid );
				if ( 'publish' !== $st ) {
					$r['unavailable_products'][] = $row( $n->ID, sprintf( 'product #%d: %s', $pid, $st ?: 'deleted' ) );
				} elseif ( 'primary' === ( $p['role'] ?? '' ) ) {
					$has_primary = true;
					$prod        = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
					if ( $prod && ! $prod->is_in_stock() ) {
						$r['unavailable_products'][] = $row( $n->ID, sprintf( '%s: %s', $prod->get_name(), __( 'brak w magazynie', 'eurowet-core' ) ) );
					}
				}
			}
			if ( ! $has_primary ) {
				$r['needs_without_primary'][] = $row( $n->ID );
			}
		}
		foreach ( $guides as $g ) {
			if ( ! isset( $linked[ $g->ID ] ) && ! Meta::ids( $g->ID, '_ew_needs' ) ) {
				$r['orphan_guides'][] = $row( $g->ID, __( 'brak powiązań z potrzebami i innymi poradami', 'eurowet-core' ) );
			}
		}
		$products = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'lang' => 'pl', 'no_found_rows' => true ) );
		foreach ( $products as $pid ) {
			if ( ! Meta::ids( (int) $pid, '_ew_need_ids' ) ) {
				$r['products_without_need'][] = $row( (int) $pid );
			}
			$img = get_post_thumbnail_id( (int) $pid );
			if ( $img && '' === trim( (string) get_post_meta( $img, '_wp_attachment_image_alt', true ) ) ) {
				$r['images_without_alt'][] = $row( (int) $pid, 'attachment #' . $img );
			}
		}
		if ( function_exists( 'pll_languages_list' ) && function_exists( 'pll_get_post' ) ) {
			$langs = array_diff( (array) pll_languages_list(), array( 'pl' ) );
			foreach ( array_merge( $guides, $needs ) as $p ) {
				if ( function_exists( 'pll_get_post_language' ) && 'pl' !== pll_get_post_language( $p->ID ) ) {
					continue;
				}
				$missing = array_filter( $langs, static fn( $l ) => ! pll_get_post( $p->ID, $l ) );
				if ( $missing ) {
					$r['missing_translations'][] = $row( $p->ID, implode( ', ', $missing ) );
				}
			}
		}
		if ( $store ) {
			update_option( self::OPTION, array( 'at' => gmdate( 'c' ), 'counts' => array_map( 'count', $r ) ), false );
		}
		return $r;
	}

	/** @return string[] */
	private static function internalLinks( string $html ): array {
		preg_match_all( '/href=["\']([^"\']+)["\']/i', $html, $m );
		$home = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$out  = array();
		foreach ( (array) $m[1] as $u ) {
			if ( 0 === strpos( $u, '/' ) && 0 !== strpos( $u, '//' ) ) {
				$out[] = $u;
			} elseif ( $home && (string) wp_parse_url( $u, PHP_URL_HOST ) === $home ) {
				$out[] = (string) wp_parse_url( $u, PHP_URL_PATH );
			}
		}
		return array_unique( $out );
	}

	private static function resolves( string $path ): bool {
		$path = strtok( $path, '#?' ) ?: '/';
		if ( '/' === $path || preg_match( '#^/(wp-content|wp-json)/#', $path ) || url_to_postid( home_url( $path ) ) ) {
			return true;
		}
		if ( Redirects::find( $path ) ) {
			return true;
		}
		foreach ( array( '#^/porady/([^/]+)/?$#' => 'ew_hub', '#^/produkty/([^/]+)/?$#' => 'product_cat' ) as $re => $tax ) {
			if ( preg_match( $re, $path, $mm ) && term_exists( $mm[1], $tax ) ) {
				return true;
			}
		}
		foreach ( array( '/porady/', '/potrzeby/', '/skladniki/', '/produkty/' ) as $archive ) {
			if ( $path === $archive ) {
				return true;
			}
		}
		return false;
	}

	public static function render(): void {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		$labels = array(
			'stale_guides'          => __( 'Porady wymagające weryfikacji', 'eurowet-core' ),
			'needs_without_primary' => __( 'Potrzeby bez produktu głównego', 'eurowet-core' ),
			'unavailable_products'  => __( 'Produkty w relacjach — niedostępne/wycofane', 'eurowet-core' ),
			'products_without_need' => __( 'Produkty bez przypisanej potrzeby', 'eurowet-core' ),
			'orphan_guides'         => __( 'Porady bez powiązań (sieroty)', 'eurowet-core' ),
			'broken_links'          => __( 'Niedziałające linki wewnętrzne', 'eurowet-core' ),
			'missing_translations'  => __( 'Brakujące tłumaczenia', 'eurowet-core' ),
			'images_without_alt'    => __( 'Zdjęcia produktów bez tekstu alternatywnego', 'eurowet-core' ),
		);
		$r = self::run( true );
		echo '<div class="wrap"><h1>' . esc_html__( 'Audyt treści', 'eurowet-core' ) . '</h1><p>' . esc_html__( 'Raport generowany co tydzień (i przy każdym otwarciu tej strony). Nic nie jest zmieniane automatycznie.', 'eurowet-core' ) . '</p>';
		foreach ( $r as $k => $rows ) {
			printf( '<h2>%s <span class="count">(%d)</span></h2>', esc_html( $labels[ $k ] ?? $k ), count( $rows ) );
			if ( ! $rows ) {
				echo '<p>✔ ' . esc_html__( 'Brak problemów.', 'eurowet-core' ) . '</p>';
				continue;
			}
			echo '<table class="widefat striped"><tbody>';
			foreach ( array_slice( $rows, 0, 200 ) as $row ) {
				printf( '<tr><td><a href="%s">%s</a></td><td>%s</td></tr>', esc_url( (string) get_edit_post_link( (int) $row['id'] ) ), esc_html( $row['title'] ), esc_html( $row['note'] ) );
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}

	public static function runNow(): void {
		if ( ! current_user_can( 'edit_others_posts' ) || ! check_admin_referer( 'ew_audit_run' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		self::run( true );
		wp_safe_redirect( admin_url( 'admin.php?page=eurowet-audit' ) );
		exit;
	}
}
