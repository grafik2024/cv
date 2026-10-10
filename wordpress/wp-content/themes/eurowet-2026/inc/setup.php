<?php
/**
 * Theme setup: supports, menus, image sizes, text domain, Hello Elementor defaults off.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'eurowet-2026', EW_THEME_DIR . '/languages' );
		load_child_theme_textdomain( 'eurowet-2026', EW_THEME_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 480, 'single_image_width' => 960 ) );
		add_theme_support( 'wc-product-gallery-lightbox' );
		register_nav_menus(
			array(
				'primary' => __( 'Menu główne', 'eurowet-2026' ),
				'footer'  => __( 'Menu w stopce', 'eurowet-2026' ),
				'legal'   => __( 'Linki prawne', 'eurowet-2026' ),
			)
		);
		add_image_size( 'ew-card', 480, 480, false );
		add_image_size( 'ew-stage', 960, 960, false );
		add_image_size( 'ew-hero', 1600, 900, true );
	},
	20
);

// Hello Elementor ships its own reset/header CSS — the Eurowet design system replaces it.
add_filter( 'hello_elementor_enqueue_style', '__return_false' );
add_filter( 'hello_elementor_enqueue_theme_style', '__return_false' );
add_filter( 'hello_elementor_header_footer', '__return_false' );
add_filter( 'hello_elementor_description_meta_tag', '__return_false' );
add_filter( 'hello_elementor_page_title', '__return_false' );

add_filter( 'body_class', static function ( array $classes ): array {
	$classes[] = 'ew-site';
	return $classes;
} );

// Needs archive lists every need (grouped by area in the template), not 10 per page.
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'ew_need' ) ) {
			$q->set( 'posts_per_page', 200 );
			$q->set( 'no_found_rows', true );
		}
		if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'ew_guide' ) ) {
			$q->set( 'posts_per_page', 18 );
		}
	}
);

// Ingredient A–Z lists all ingredients on one page, alphabetically.
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'ew_ingredient' ) ) {
			$q->set( 'posts_per_page', 500 );
			$q->set( 'orderby', 'title' );
			$q->set( 'order', 'ASC' );
			$q->set( 'no_found_rows', true );
		}
	}
);
