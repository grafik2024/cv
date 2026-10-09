<?php
/**
 * /llms.txt — a concise, link-rich map of the site for external AI systems. Not an SEO trick:
 * the crawlable HTML remains the source of truth (brief §31). Cached 12 h, invalidated on content changes.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Seo;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Graph\Cache;

defined( 'ABSPATH' ) || exit;

final class LlmsTxt {

	public static function register(): void {
		add_action( 'template_redirect', array( self::class, 'serve' ), 0 );
	}

	public static function serve(): void {
		if ( ! get_query_var( 'ew_llms' ) ) {
			return;
		}
		if ( ! ew_get_option( 'llms_enabled', 1 ) ) {
			status_header( 404 );
			exit;
		}
		$body = Cache::remember( 'llms-txt', array( self::class, 'build' ), 12 * HOUR_IN_SECONDS );
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- plain text document.
		exit;
	}

	public static function build(): string {
		$lines   = array( '# EUROWET', '' );
		$summary = (string) ew_get_option( 'company_summary', '' );
		$lines[] = '> ' . ( '' !== $summary ? $summary : wp_strip_all_tags( get_bloginfo( 'description' ) ) );
		$lines[] = '';
		$lines[] = 'Treści na tej stronie mają charakter informacyjny i pielęgnacyjny; nie zastępują porady lekarza weterynarii.';
		$lines[] = '';
		$section = static function ( string $title, string $post_type, string $desc_key ) use ( &$lines ): void {
			$posts = get_posts( array( 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'lang' => 'pl', 'no_found_rows' => true ) );
			if ( ! $posts ) {
				return;
			}
			$lines[] = '## ' . $title;
			$lines[] = '';
			foreach ( $posts as $p ) {
				$desc    = wp_trim_words( wp_strip_all_tags( (string) Meta::get( $p->ID, $desc_key, '' ) ), 30, '…' );
				$lines[] = sprintf( '- [%s](%s)%s', html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ), get_permalink( $p ), '' !== $desc ? ': ' . $desc : '' );
			}
			$lines[] = '';
		};
		$hubs = get_terms( array( 'taxonomy' => 'ew_hub', 'hide_empty' => false ) );
		if ( is_array( $hubs ) && $hubs ) {
			$lines[] = '## Centrum wiedzy — działy';
			$lines[] = '';
			foreach ( $hubs as $h ) {
				$lines[] = sprintf( '- [%s](%s)', $h->name, get_term_link( $h ) );
			}
			$lines[] = '';
		}
		$section( 'Potrzeby i problemy (dobór produktu)', 'ew_need', '_ew_short_answer' );
		$section( 'Porady', 'ew_guide', '_ew_tldr' );
		$section( 'Składniki', 'ew_ingredient', '_ew_summary' );
		$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
		if ( is_array( $cats ) && $cats ) {
			$lines[] = '## Kategorie produktów';
			$lines[] = '';
			foreach ( $cats as $c ) {
				$lines[] = sprintf( '- [%s](%s)', html_entity_decode( $c->name, ENT_QUOTES, 'UTF-8' ), get_term_link( $c ) );
			}
			$lines[] = '';
		}
		$lines[] = '## Kontakt i współpraca';
		$lines[] = '';
		foreach ( array( '/kontakt/' => 'Kontakt', '/wspolpraca-b2b/' => 'Współpraca B2B', '/marka-wlasna/' => 'Marka własna', '/znajdz-przedstawiciela/' => 'Przedstawiciele handlowi' ) as $path => $label ) {
			$lines[] = sprintf( '- [%s](%s)', $label, home_url( $path ) );
		}
		return implode( "\n", $lines ) . "\n";
	}
}
