<?php
/**
 * WooCommerce integration: structured product page (template override), archive filters (species, area,
 * line — noindex via plugin), wrappers, catalog-only products without add-to-cart.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

if ( ! ew_theme_has_woo() ) {
	return;
}

// Theme wrappers instead of WooCommerce defaults.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action( 'woocommerce_before_main_content', static fn() => print( '<div class="ew-container ew-shop">' ), 10 );
add_action( 'woocommerce_after_main_content', static fn() => print( '</div>' ), 10 );

// Single product: the theme renders sections itself (no tabs; real headings, no hidden content).
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

// Catalog-only products: no price/add to cart (CTA to reps instead).
add_filter(
	'woocommerce_is_purchasable',
	static function ( $purchasable, $product ) {
		return ( $product instanceof WC_Product && ew_theme_meta( $product->get_id(), '_ew_catalog_only', false ) ) ? false : $purchasable;
	},
	10,
	2
);

/** Whitelisted archive filters. */
function ew_theme_shop_filters(): array {
	return array(
		'ew_gatunek' => 'ew_species',
		'ew_obszar'  => 'ew_area',
		'ew_linia'   => 'ew_line',
	);
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) ) ) {
			return;
		}
		$tax = (array) $q->get( 'tax_query' );
		foreach ( ew_theme_shop_filters() as $param => $taxonomy ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$val = isset( $_GET[ $param ] ) ? sanitize_title( wp_unslash( (string) $_GET[ $param ] ) ) : '';
			if ( '' !== $val && taxonomy_exists( $taxonomy ) ) {
				$tax[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $val );
			}
		}
		if ( count( $tax ) > 0 ) {
			$q->set( 'tax_query', $tax );
		}
		$q->set( 'posts_per_page', 24 );
	}
);

add_filter( 'loop_shop_columns', static fn() => 4 );

// Shop loop: use the shared product card component (same markup as everywhere else).
add_action(
	'init',
	static function (): void {
		if ( function_exists( 'ew_render' ) ) {
			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			add_action( 'woocommerce_before_shop_loop_item', static function (): void {
				global $product;
				echo ew_render( 'product-card', array( 'product' => $product, 'heading_level' => 2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			} );
		}
	}
);

/** Filter bar for product archives (GET form; filtered views are noindex,follow). */
function ew_theme_shop_filter_bar(): void {
	$action = is_tax() ? get_term_link( get_queried_object() ) : ew_theme_url( 'products' );
	echo '<form class="ew-filters" method="get" action="' . esc_url( is_wp_error( $action ) ? ew_theme_url( 'products' ) : $action ) . '" aria-label="' . esc_attr__( 'Filtruj produkty', 'eurowet-2026' ) . '">';
	$labels = array( 'ew_gatunek' => __( 'Gatunek', 'eurowet-2026' ), 'ew_obszar' => __( 'Obszar', 'eurowet-2026' ), 'ew_linia' => __( 'Linia', 'eurowet-2026' ) );
	foreach ( ew_theme_shop_filters() as $param => $taxonomy ) {
		$terms = taxonomy_exists( $taxonomy ) ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ) : array();
		if ( ! is_array( $terms ) || ! $terms ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cur = isset( $_GET[ $param ] ) ? sanitize_title( wp_unslash( (string) $_GET[ $param ] ) ) : '';
		printf( '<div class="ew-filters__field"><label for="ew-f-%1$s">%2$s</label><select id="ew-f-%1$s" name="%1$s"><option value="">%3$s</option>', esc_attr( $param ), esc_html( $labels[ $param ] ), esc_html__( 'wszystkie', 'eurowet-2026' ) );
		foreach ( $terms as $t ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $t->slug ), selected( $cur, $t->slug, false ), esc_html( $t->name ) );
		}
		echo '</select></div>';
	}
	echo '<button class="ew-btn ew-btn--secondary" type="submit">' . esc_html__( 'Filtruj', 'eurowet-2026' ) . '</button></form>';
}
