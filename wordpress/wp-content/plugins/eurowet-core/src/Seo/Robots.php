<?php
/**
 * robots.txt: never blocks search engines or AI search/answer crawlers by default (brief §30).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Seo;

defined( 'ABSPATH' ) || exit;

final class Robots {

	public const TRAINING_BOTS = array( 'GPTBot', 'ClaudeBot', 'Google-Extended', 'Applebot-Extended', 'CCBot', 'Meta-ExternalAgent' );

	public static function register(): void {
		add_filter( 'robots_txt', array( self::class, 'filter' ), 100000, 2 );
		add_action( 'admin_notices', array( self::class, 'visibilityNotice' ) );
	}

	/**
	 * Runs after Yoast (99999): rebuilds robots.txt as ONE "User-agent: *" group (WordPress, WooCommerce, Yoast and
	 * our rules merged; empty "Disallow:" dropped), keeps other plugins' groups for specific bots, adds the optional
	 * AI-training block and the sitemap.
	 */
	public static function filter( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		$sitemaps = array();
		$star     = array();
		$others   = array(); // UA => lines
		$current  = array();
		$in_group = false;
		foreach ( preg_split( '/\r\n|\n/', (string) $output ) ?: array() as $line ) {
			$line = trim( $line );
			if ( '' === $line || 0 === strpos( $line, '#' ) ) {
				continue;
			}
			if ( 0 === stripos( $line, 'sitemap:' ) ) {
				$sitemaps[] = 'Sitemap: ' . trim( substr( $line, 8 ) );
				continue;
			}
			if ( 0 === stripos( $line, 'user-agent:' ) ) {
				if ( $in_group ) { // a User-agent line after rules starts a new group
					$current  = array();
					$in_group = false;
				}
				$current[] = trim( substr( $line, 11 ) );
				continue;
			}
			$in_group = true;
			if ( preg_match( '/^disallow:\s*$/i', $line ) ) {
				continue; // "Disallow:" (allow all) adds nothing to a merged group.
			}
			foreach ( $current ?: array( '*' ) as $ua ) {
				if ( '*' === $ua ) {
					$star[] = $line;
				} else {
					$others[ $ua ][] = $line;
				}
			}
		}
		$lines = array( 'User-agent: *' );
		foreach ( array_unique( array_merge( $star, array( 'Disallow: /koszyk/', 'Disallow: /zamowienie/', 'Disallow: /moje-konto/', 'Disallow: /*?add-to-cart=', 'Disallow: /*?ew_gatunek=', 'Disallow: /*?ew_obszar=', 'Disallow: /*?ew_linia=' ) ) ) as $l ) {
			$lines[] = $l;
		}
		foreach ( $others as $ua => $rules ) {
			$lines[] = '';
			$lines[] = 'User-agent: ' . $ua;
			foreach ( array_unique( $rules ) as $l ) {
				$lines[] = $l;
			}
		}
		if ( 'block' === ew_get_option( 'ai_training_bots', 'allow' ) ) {
			foreach ( self::TRAINING_BOTS as $bot ) {
				$lines[] = '';
				$lines[] = 'User-agent: ' . $bot;
				$lines[] = 'Disallow: /';
			}
		}
		if ( ! $sitemaps ) {
			$sitemaps[] = 'Sitemap: ' . home_url( defined( 'WPSEO_VERSION' ) ? '/sitemap_index.xml' : '/wp-sitemap.xml' );
		}
		return implode( "\n", array_merge( $lines, array( '' ), array_values( array_unique( $sitemaps ) ) ) ) . "\n";
	}

	public static function visibilityNotice(): void {
		if ( 'production' === wp_get_environment_type() && ! get_option( 'blog_public' ) && current_user_can( 'manage_options' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Uwaga: witryna produkcyjna ma włączone „Proś wyszukiwarki o nieindeksowanie” (Ustawienia → Czytanie).', 'eurowet-core' ) . '</p></div>';
		}
	}
}
