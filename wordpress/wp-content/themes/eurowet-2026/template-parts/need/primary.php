<?php
/**
 * Recommended product for a need: packshot, confirmed purpose (verbatim product field), why it fits
 * (curated reason) and the evidence quote from the product description. Args: rel {product, reason, evidence}.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$rel = (array) ( $args['rel'] ?? array() );
$p   = $rel['product'] ?? null;
if ( ! $p instanceof WC_Product ) {
	return;
}
$s   = function_exists( 'ew_product_summary' ) ? ew_product_summary( $p ) : array( 'id' => $p->get_id(), 'name' => $p->get_name(), 'url' => get_permalink( $p->get_id() ), 'image_id' => (int) $p->get_image_id(), 'capacity' => '', 'intended_for' => '', 'catalog_only' => false, 'price_html' => $p->get_price_html(), 'purchasable' => $p->is_purchasable(), 'buy_url' => '' );
$hid = 'ew-primary-' . (int) $s['id'];
?>
<article class="ew-primary" aria-labelledby="<?php echo esc_attr( $hid ); ?>">
	<div class="ew-primary__stage">
		<?php echo ew_theme_image( (int) $s['image_id'], 'ew-stage', 'stage' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<div class="ew-primary__body">
		<p class="ew-eyebrow"><?php esc_html_e( 'Najlepiej dopasowany produkt', 'eurowet-2026' ); ?></p>
		<h3 class="ew-primary__name" id="<?php echo esc_attr( $hid ); ?>"><a href="<?php echo esc_url( $s['url'] ); ?>" data-ew-track="product_click" data-ew-target="<?php echo (int) $s['id']; ?>"><?php echo esc_html( $s['name'] ); ?></a></h3>
		<?php if ( $s['capacity'] ) : ?><p class="ew-primary__cap"><?php echo esc_html( $s['capacity'] ); ?></p><?php endif; ?>
		<?php if ( $s['intended_for'] ) : ?>
			<p><strong><?php esc_html_e( 'Potwierdzone przeznaczenie:', 'eurowet-2026' ); ?></strong> <?php echo esc_html( wp_trim_words( $s['intended_for'], 40 ) ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $rel['reason'] ) ) : ?>
			<p><strong><?php esc_html_e( 'Dlaczego pasuje:', 'eurowet-2026' ); ?></strong> <?php echo esc_html( (string) $rel['reason'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $rel['evidence'] ) ) : ?>
			<blockquote class="ew-primary__quote"><p>„<?php echo esc_html( (string) $rel['evidence'] ); ?>”</p><footer><?php esc_html_e( 'z opisu produktu', 'eurowet-2026' ); ?></footer></blockquote>
		<?php endif; ?>
		<?php if ( ! $s['catalog_only'] && $s['price_html'] ) : ?>
			<p class="ew-primary__price"><?php echo wp_kses_post( $s['price_html'] ); ?></p>
		<?php endif; ?>
		<p class="ew-cluster">
			<a class="ew-btn" href="<?php echo esc_url( $s['url'] ); ?>" data-ew-track="product_click" data-ew-target="<?php echo (int) $s['id']; ?>"><?php esc_html_e( 'Zobacz produkt', 'eurowet-2026' ); ?></a>
			<?php if ( $s['buy_url'] ) : ?>
				<a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( $s['buy_url'] ); ?>" rel="nofollow" data-ew-track="buy_click" data-ew-target="<?php echo (int) $s['id']; ?>"><?php esc_html_e( 'Kup', 'eurowet-2026' ); ?><span class="ew-visually-hidden"> <?php echo esc_html( $s['name'] ); ?></span></a>
			<?php elseif ( $s['catalog_only'] ) : ?>
				<a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( ew_theme_url( 'reps' ) ); ?>"><?php esc_html_e( 'Zapytaj o dostępność', 'eurowet-2026' ); ?></a>
			<?php endif; ?>
		</p>
	</div>
</article>
