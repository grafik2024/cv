<?php
/**
 * SEO / GEO module: schema, llms.txt, robots, redirects, Yoast defaults, meta description fallbacks.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Seo;

use Eurowet\Core\Admin\Settings;
use Eurowet\Core\Contracts\ModuleInterface;
use Eurowet\Core\Data\Meta;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		Schema::register();
		LlmsTxt::register();
		Robots::register();
		Redirects::register();
		add_filter( 'wpseo_metadesc', array( self::class, 'metaDescription' ) );
		add_filter( 'wpseo_sitemap_exclude_post_type', static fn( $exclude, $pt ) => in_array( $pt, array( 'ew_rep', 'ew_material', 'ew_lead' ), true ) ? true : $exclude, 10, 2 );
		add_filter( 'wpseo_sitemap_exclude_taxonomy', static fn( $exclude, $tax ) => in_array( $tax, array( 'ew_family', 'ew_species', 'ew_area', 'ew_material_type' ), true ) ? true : $exclude, 10, 2 );
		add_filter( 'wp_robots', array( self::class, 'robotsMeta' ) );
		// Yoast prints its own robots tag: same rule through its filter.
		add_filter(
			'wpseo_robots_array',
			static function ( $robots ) {
				if ( is_array( $robots ) && self::isFilteredListing() ) {
					$robots['index']  = 'noindex';
					$robots['follow'] = 'follow';
				}
				return $robots;
			}
		);
		add_action(
			'ew_settings_sections',
			static function (): void {
				Settings::addSection(
					'seo',
					__( 'SEO / GEO', 'eurowet-core' ),
					array(
						array( 'id' => 'llms_enabled', 'type' => 'checkbox', 'label' => __( 'Udostępniaj /llms.txt', 'eurowet-core' ), 'default' => 1 ),
						array( 'id' => 'ai_training_bots', 'type' => 'select', 'label' => __( 'Boty trenujące modele AI', 'eurowet-core' ), 'options' => array( 'allow' => __( 'zezwalaj', 'eurowet-core' ), 'block' => __( 'blokuj (GPTBot, ClaudeBot, Google-Extended…)', 'eurowet-core' ) ), 'default' => 'allow', 'help' => __( 'Boty wyszukiwarek i wyszukiwania AI (Googlebot, Bingbot, OAI-SearchBot, PerplexityBot) nigdy nie są blokowane.', 'eurowet-core' ) ),
						array( 'id' => 'company_summary', 'type' => 'textarea', 'label' => __( 'Opis firmy (llms.txt, schema)', 'eurowet-core' ) ),
						array( 'id' => 'guide_review_months', 'type' => 'number', 'label' => __( 'Po ilu miesiącach porada wymaga weryfikacji', 'eurowet-core' ), 'default' => 12, 'min' => 1, 'max' => 60 ),
					)
				);
			}
		);
	}

	public static function metaDescription( $desc ) {
		if ( '' !== trim( (string) $desc ) || ! is_singular() ) {
			return $desc;
		}
		$id  = get_queried_object_id();
		$key = array( 'ew_need' => '_ew_short_answer', 'ew_guide' => '_ew_tldr', 'ew_ingredient' => '_ew_summary', 'product' => '_ew_subtitle' )[ get_post_type( $id ) ] ?? '';
		if ( '' === $key ) {
			return $desc;
		}
		$text = wp_strip_all_tags( (string) Meta::get( $id, $key, '' ) );
		return '' !== $text ? mb_substr( preg_replace( '/\s+/', ' ', $text ) ?? $text, 0, 155 ) : $desc;
	}

	/** Filtered/faceted listings: noindex,follow (canonical stays the unfiltered URL). */
	public static function isFilteredListing(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['ew_gatunek'] ) || isset( $_GET['ew_obszar'] ) || isset( $_GET['ew_linia'] ) || ( is_post_type_archive( 'ew_need' ) && ( isset( $_GET['q'] ) || isset( $_GET['gatunek'] ) || isset( $_GET['obszar'] ) ) );
	}

	public static function robotsMeta( array $robots ): array {
		if ( self::isFilteredListing() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
		return $robots;
	}
}
