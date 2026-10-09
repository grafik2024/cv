<?php
/**
 * Product card. Args: product (WC_Product|int), context (string), reason (string), heading_level (int).
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$product = $args['product'] ?? null;
$product = is_numeric( $product ) && function_exists( 'wc_get_product' ) ? wc_get_product( (int) $product ) : $product;
if ( ! $product instanceof WC_Product ) {
	return;
}
$s       = ew_product_summary( $product );
$level   = (int) ( $args['heading_level'] ?? 3 );
$reason  = (string) ( $args['reason'] ?? '' );
$species = wp_get_post_terms( $s['id'], 'ew_species', array( 'fields' => 'names' ) );
$track   = (string) ( $args['context'] ?? '' );
?>
<article class="ew-card ew-card--interactive ew-pcard" data-product-id="<?php echo (int) $s['id']; ?>">
	<div class="ew-pcard__stage">
		<?php
		if ( $s['image_id'] ) {
			echo wp_get_attachment_image( $s['image_id'], 'woocommerce_thumbnail', false, array( 'class' => 'ew-pcard__img', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(min-width: 1024px) 280px, (min-width: 640px) 45vw, 90vw' ) );
		}
		?>
	</div>
	<div class="ew-card__body ew-pcard__body">
		<?php if ( $s['capacity'] ) : ?>
			<p class="ew-card__eyebrow ew-pcard__capacity"><?php echo esc_html( $s['capacity'] ); ?></p>
		<?php endif; ?>
		<h<?php echo (int) $level; ?> class="ew-card__title ew-pcard__title">
			<a class="ew-card__link" href="<?php echo esc_url( $s['url'] ); ?>" <?php echo $track ? 'data-ew-track="product_click" data-ew-target="' . (int) $s['id'] . '"' : ''; ?>><?php echo esc_html( $s['name'] ); ?></a>
		</h<?php echo (int) $level; ?>>
		<?php if ( $s['subtitle'] ) : ?>
			<p class="ew-card__text ew-pcard__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
		<?php endif; ?>
		<?php if ( $reason ) : ?>
			<p class="ew-pcard__reason"><?php echo esc_html( $reason ); ?></p>
		<?php endif; ?>
		<?php if ( is_array( $species ) && $species ) : ?>
			<p class="ew-pcard__species"><span class="ew-visually-hidden"><?php esc_html_e( 'Dla:', 'eurowet-core' ); ?> </span><?php echo esc_html( implode( ' · ', $species ) ); ?></p>
		<?php endif; ?>
		<p class="ew-pcard__price">
			<?php
			if ( $s['catalog_only'] || '' === trim( wp_strip_all_tags( (string) $s['price_html'] ) ) ) {
				esc_html_e( 'Dostępny u partnerów handlowych', 'eurowet-core' );
			} else {
				echo wp_kses_post( $s['price_html'] );
			}
			?>
		</p>
	</div>
</article>
