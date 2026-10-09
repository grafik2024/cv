<?php
/**
 * Structured data that matches visible content only (brief §28): enriches Yoast's graph (Organization,
 * WebSite SearchAction, Article/WebPage for guides, needs, ingredients, FAQPage when an FAQ is rendered,
 * VideoObject with transcript) and WooCommerce's Product JSON-LD (brand, no offers for catalog-only).
 * Without Yoast a minimal equivalent graph is printed.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Seo;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\I18n\Polylang;

defined( 'ABSPATH' ) || exit;

final class Schema {

	public static function register(): void {
		add_filter( 'wpseo_schema_organization', array( self::class, 'organization' ) );
		add_filter( 'wpseo_schema_website', array( self::class, 'website' ) );
		add_filter( 'wpseo_schema_webpage', array( self::class, 'webpage' ) );
		add_filter( 'wpseo_schema_article', array( self::class, 'article' ) );
		add_filter( 'wpseo_schema_graph', array( self::class, 'graph' ), 20, 2 );
		add_filter( 'woocommerce_structured_data_product', array( self::class, 'product' ), 20, 2 );
		add_action( 'wp_head', array( self::class, 'fallback' ), 99 );
	}

	private static function yoast(): bool {
		return defined( 'WPSEO_VERSION' );
	}

	public static function organization( $data ) {
		$data = is_array( $data ) ? $data : array();
		$same = array_values( array_filter( array_map( 'trim', preg_split( '/\s+/', (string) ew_get_option( 'company_social', '' ) ) ?: array() ) ) );
		if ( $same ) {
			$data['sameAs'] = array_values( array_unique( array_merge( (array) ( $data['sameAs'] ?? array() ), $same ) ) );
		}
		$points = array();
		foreach ( array( 'sales' => 'contact_phone_sales', 'customer service' => 'contact_phone' ) as $type => $opt ) {
			$phone = (string) ew_get_option( $opt, '' );
			if ( '' !== $phone ) {
				$points[] = array_filter( array( '@type' => 'ContactPoint', 'contactType' => $type, 'telephone' => $phone, 'email' => (string) ew_get_option( 'contact_email', '' ), 'areaServed' => 'PL', 'availableLanguage' => array( 'pl', 'en' ) ) );
			}
		}
		if ( $points ) {
			$data['contactPoint'] = $points;
		}
		$addr = (string) ew_get_option( 'company_address', '' );
		if ( '' !== $addr ) {
			$data['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => $addr, 'addressCountry' => 'PL' );
		}
		return $data;
	}

	public static function website( $data ) {
		$data                    = is_array( $data ) ? $data : array();
		$data['potentialAction'] = array(
			array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/potrzeby/?q={search_term_string}' ) ),
				'query-input' => 'required name=search_term_string',
			),
		);
		return $data;
	}

	public static function webpage( $data ) {
		if ( ! is_singular( array( 'ew_guide', 'ew_need', 'ew_ingredient' ) ) ) {
			return $data;
		}
		$id = get_queried_object_id();
		if ( is_singular( 'ew_guide' ) ) {
			$reviewed = (string) Meta::get( $id, '_ew_reviewed', '' );
			if ( '' !== $reviewed ) {
				$data['lastReviewed'] = $reviewed;
			}
			$about = self::needRefs( Polylang::translateIds( Meta::ids( $id, '_ew_needs' ) ) );
			if ( $about ) {
				$data['about'] = $about;
			}
		} elseif ( is_singular( 'ew_need' ) ) {
			$data['about'] = array( '@type' => 'Thing', 'name' => get_the_title( $id ) );
			$items         = array();
			foreach ( ew_need_products( $id ) as $i => $row ) {
				$items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'url' => get_permalink( $row['product']->get_id() ), 'name' => $row['product']->get_name() );
			}
			if ( $items ) {
				$data['mainEntity'] = array( '@type' => 'ItemList', 'name' => __( 'Produkty dopasowane do potrzeby', 'eurowet-core' ), 'itemListElement' => $items );
			}
		} else {
			$data['about'] = array_filter( array( '@type' => 'DefinedTerm', 'name' => get_the_title( $id ), 'alternateName' => (string) Meta::get( $id, '_ew_inci', '' ), 'description' => wp_strip_all_tags( (string) Meta::get( $id, '_ew_summary', '' ) ) ) );
		}
		return $data;
	}

	public static function article( $data ) {
		if ( ! is_singular( 'ew_guide' ) ) {
			return $data;
		}
		$id       = get_queried_object_id();
		$reviewer = trim( (string) Meta::get( $id, '_ew_reviewer', '' ) );
		// Author = organisation's editorial team unless a real, company-approved person is set.
		$data['author'] = array( '@type' => 'Organization', 'name' => (string) Meta::get( $id, '_ew_author_label', 'Zespół Eurowet' ) ?: 'Zespół Eurowet', 'url' => home_url( '/' ) );
		if ( '' !== $reviewer ) {
			$data['reviewedBy'] = array( '@type' => 'Person', 'name' => $reviewer );
		}
		$mentions = array();
		foreach ( Polylang::translateIds( Meta::ids( $id, '_ew_products' ) ) as $pid ) {
			if ( 'publish' === get_post_status( $pid ) ) {
				$mentions[] = array( '@type' => 'Product', 'name' => get_the_title( $pid ), 'url' => get_permalink( $pid ) );
			}
		}
		if ( $mentions ) {
			$data['mentions'] = $mentions;
		}
		$data['inLanguage'] = Polylang::currentLang() === 'ua' ? 'uk' : Polylang::currentLang();
		return $data;
	}

	/**
	 * Adds FAQPage and VideoObject pieces when (and only when) the page renders them.
	 */
	public static function graph( $graph, $context ) {
		if ( ! is_array( $graph ) ) {
			return $graph;
		}
		foreach ( self::extraPieces() as $piece ) {
			$graph[] = $piece;
		}
		return $graph;
	}

	/** @return array<int, array<string, mixed>> */
	private static function extraPieces(): array {
		if ( ! is_singular( array( 'ew_guide', 'ew_need', 'product' ) ) ) {
			return array();
		}
		$id     = get_queried_object_id();
		$url    = get_permalink( $id );
		$pieces = array();
		$faq    = 'product' === get_post_type( $id ) ? array() : Meta::json( $id, '_ew_faq' );
		$faq    = array_values( array_filter( $faq, static fn( $r ) => is_array( $r ) && ! empty( $r['q'] ) && ! empty( $r['a'] ) ) );
		if ( $faq ) {
			$pieces[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'isPartOf'   => array( '@id' => $url ),
				'mainEntity' => array_map(
					static fn( $r ) => array( '@type' => 'Question', 'name' => wp_strip_all_tags( (string) $r['q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( (string) $r['a'] ) ) ),
					$faq
				),
			);
		}
		$video = Meta::int( $id, '_ew_video' );
		if ( $video && wp_attachment_is( 'video', $video ) ) {
			$meta     = wp_get_attachment_metadata( $video );
			$thumb    = get_the_post_thumbnail_url( $video, 'large' ) ?: get_the_post_thumbnail_url( $id, 'large' );
			$pieces[] = array_filter(
				array(
					'@type'        => 'VideoObject',
					'@id'          => $url . '#video',
					'name'         => get_the_title( $video ) ?: get_the_title( $id ),
					'description'  => wp_strip_all_tags( (string) get_post_field( 'post_content', $video ) ) ?: wp_strip_all_tags( (string) Meta::get( $id, 'product' === get_post_type( $id ) ? '_ew_subtitle' : '_ew_tldr', '' ) ),
					'thumbnailUrl' => $thumb ?: null,
					'uploadDate'   => get_post_time( 'c', true, $video ),
					'contentUrl'   => wp_get_attachment_url( $video ),
					'duration'     => isset( $meta['length'] ) ? 'PT' . (int) $meta['length'] . 'S' : null,
					'transcript'   => wp_strip_all_tags( (string) Meta::get( $id, '_ew_video_transcript', '' ) ) ?: null,
				)
			);
		}
		return $pieces;
	}

	/** @return array<int, array<string, string>> */
	private static function needRefs( array $ids ): array {
		$out = array();
		foreach ( $ids as $nid ) {
			if ( 'publish' === get_post_status( $nid ) ) {
				$out[] = array( '@type' => 'Thing', 'name' => get_the_title( $nid ), 'url' => get_permalink( $nid ) );
			}
		}
		return $out;
	}

	public static function product( $markup, $product ) {
		if ( ! is_array( $markup ) || ! $product instanceof \WC_Product ) {
			return $markup;
		}
		$id              = $product->get_id();
		$markup['brand'] = array( '@type' => 'Brand', 'name' => 'Eurowet' );
		$desc            = wp_strip_all_tags( (string) Meta::get( $id, '_ew_properties', '' ) );
		if ( '' !== $desc ) {
			$markup['description'] = wp_trim_words( $desc, 60 );
		}
		if ( empty( $markup['sku'] ) || (string) $markup['sku'] === (string) $id ) {
			unset( $markup['sku'] ); // WooCommerce falls back to the post ID — not a real SKU.
		}
		if ( Meta::bool( $id, '_ew_catalog_only' ) || '' === (string) $product->get_price() ) {
			unset( $markup['offers'] );
		}
		$cap = (string) Meta::get( $id, '_ew_capacity', '' );
		if ( '' !== $cap ) {
			$markup['size'] = $cap;
		}
		return $markup;
	}

	/** Minimal graph when Yoast is not active (local development, emergencies). */
	public static function fallback(): void {
		if ( self::yoast() || is_admin() ) {
			return;
		}
		$graph = array(
			self::organization( array( '@type' => 'Organization', '@id' => home_url( '/#organization' ), 'name' => 'EUROWET', 'url' => home_url( '/' ) ) ),
			self::website( array( '@type' => 'WebSite', '@id' => home_url( '/#website' ), 'url' => home_url( '/' ), 'name' => get_bloginfo( 'name' ), 'publisher' => array( '@id' => home_url( '/#organization' ) ) ) ),
		);
		if ( is_singular( array( 'ew_guide', 'ew_need', 'ew_ingredient' ) ) ) {
			$id      = get_queried_object_id();
			$graph[] = self::webpage( array( '@type' => 'WebPage', '@id' => get_permalink( $id ), 'url' => get_permalink( $id ), 'name' => get_the_title( $id ), 'datePublished' => get_post_time( 'c', true, $id ), 'dateModified' => get_post_modified_time( 'c', true, $id ) ) );
			if ( is_singular( 'ew_guide' ) ) {
				$graph[] = self::article( array( '@type' => 'Article', '@id' => get_permalink( $id ) . '#article', 'headline' => get_the_title( $id ), 'datePublished' => get_post_time( 'c', true, $id ), 'dateModified' => get_post_modified_time( 'c', true, $id ), 'mainEntityOfPage' => array( '@id' => get_permalink( $id ) ), 'publisher' => array( '@id' => home_url( '/#organization' ) ) ) );
			}
		}
		$graph = array_merge( $graph, self::extraPieces() );
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
