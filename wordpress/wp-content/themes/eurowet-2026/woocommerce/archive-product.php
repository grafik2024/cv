<?php
/**
 * Product archive (/produkty/, /produkty/{kategoria}/): header with the category text, category chips,
 * filters (species, area, line — filtered views are noindex,follow), product cards, pagination.
 *
 * Overrides woocommerce/templates/archive-product.php (version 8.6.0 contract).
 *
 * @package Eurowet2026
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$term  = is_product_category() ? get_queried_object() : null;
$title = $term instanceof WP_Term ? $term->name : woocommerce_page_title( false );
$intro = $term instanceof WP_Term ? (string) $term->description : '';
if ( ! $term && is_shop() ) {
	$shop = get_post( wc_get_page_id( 'shop' ) );
	$intro = $shop ? (string) $shop->post_excerpt : '';
}

ew_theme_page_header( (string) $title, $intro, $term ? __( 'Produkty', 'eurowet-2026' ) : '' );
?>
<div class="ew-container ew-shop">
	<?php
	$cats = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ), 'orderby' => 'meta_value_num', 'meta_key' => 'order' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( is_array( $cats ) && $cats ) :
		?>
		<nav class="ew-shop__cats" aria-label="<?php esc_attr_e( 'Kategorie produktów', 'eurowet-2026' ); ?>">
			<ul class="ew-chips ew-list-reset">
				<li><a class="ew-chip" href="<?php echo esc_url( ew_theme_url( 'products' ) ); ?>" <?php echo is_shop() ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Wszystkie', 'eurowet-2026' ); ?></a></li>
				<?php foreach ( $cats as $c ) : ?>
					<li><a class="ew-chip" href="<?php echo esc_url( get_term_link( $c ) ); ?>" <?php echo ( $term && $term->term_id === $c->term_id ) ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php ew_theme_shop_filter_bar(); ?>

	<?php
	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );
		woocommerce_product_loop_start();
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
	?>

	<aside class="ew-shop__help">
		<p><?php esc_html_e( 'Nie wiesz, który produkt wybrać?', 'eurowet-2026' ); ?> <a href="<?php echo esc_url( ew_theme_url( 'needs' ) ); ?>"><?php esc_html_e( 'Opisz potrzebę zwierzęcia — pomożemy dobrać produkt.', 'eurowet-2026' ); ?></a></p>
	</aside>
</div>
<?php
get_footer( 'shop' );
