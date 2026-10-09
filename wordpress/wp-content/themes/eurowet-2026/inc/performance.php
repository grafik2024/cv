<?php
/**
 * Performance: drop unused WordPress/WooCommerce/Elementor assets per page (brief §63).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_filter( 'emoji_svg_url', '__return_false' );

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$woo_page = function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() );
		if ( ! $woo_page ) {
			// WooCommerce front assets only where the shop is used.
			foreach ( array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general', 'wc-blocks-style', 'wc-blocks-vendors-style' ) as $h ) {
				wp_dequeue_style( $h );
			}
			foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'sourcebuster-js', 'wc-order-attribution' ) as $h ) {
				wp_dequeue_script( $h );
			}
		}
		if ( ! is_singular() || ! has_blocks( get_queried_object_id() ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
		}
		if ( function_exists( 'is_product' ) && ! is_product() && ! is_cart() ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	},
	100
);

// WooCommerce default CSS is replaced by the theme's shop.css on shop pages.
add_filter( 'woocommerce_enqueue_styles', static fn( $styles ) => array() );

// Explicit dimensions and async decoding for every attachment image.
add_filter(
	'wp_get_attachment_image_attributes',
	static function ( array $attr ): array {
		if ( empty( $attr['decoding'] ) && empty( $attr['fetchpriority'] ) ) {
			$attr['decoding'] = 'async';
		}
		return $attr;
	}
);
