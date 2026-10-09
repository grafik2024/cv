<?php
/**
 * Product grid. Args: products (WC_Product[]|int[]) OR category/featured/limit; heading; id; context.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$products = (array) ( $args['products'] ?? array() );
if ( ! $products && function_exists( 'wc_get_products' ) ) {
	$q = array( 'status' => 'publish', 'limit' => max( 1, (int) ( $args['limit'] ?? 8 ) ), 'orderby' => 'menu_order', 'order' => 'ASC', 'visibility' => 'catalog' );
	if ( ! empty( $args['category'] ) ) {
		$q['category'] = array( sanitize_title( (string) $args['category'] ) );
	}
	if ( ! empty( $args['featured'] ) && 'no' !== $args['featured'] ) {
		$q['featured'] = true;
	}
	if ( function_exists( 'pll_current_language' ) ) {
		$q['lang'] = pll_current_language();
	}
	$products = wc_get_products( $q );
}
if ( ! $products ) {
	return;
}
$hid = $args['id'] ?? 'ew-pg-' . wp_unique_id();
?>
<section class="ew-pgrid" aria-labelledby="<?php echo esc_attr( $hid ); ?>">
	<?php if ( ! empty( $args['heading'] ) ) : ?>
		<?php echo ew_heading( (string) $args['heading'], $hid, (int) ( $args['heading_level'] ?? 2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php else : ?>
		<h2 class="ew-visually-hidden" id="<?php echo esc_attr( $hid ); ?>"><?php esc_html_e( 'Produkty', 'eurowet-core' ); ?></h2>
	<?php endif; ?>
	<ul class="ew-grid ew-pgrid__list ew-list-reset" role="list">
		<?php foreach ( $products as $p ) : ?>
			<li><?php echo ew_render( 'product-card', array( 'product' => $p, 'heading_level' => (int) ( $args['heading_level'] ?? 2 ) + 1, 'context' => (string) ( $args['context'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endforeach; ?>
	</ul>
</section>
