<?php
/**
 * 04 Product categories (top-level product_cat with products; description = client's category text).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}
$cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'order', // phpcs:ignore WordPress.DB.SlowDBQuery -- WooCommerce category order.
	)
);
if ( ! is_array( $cats ) || ! $cats ) {
	return;
}
?>
<section class="ew-section ew-home-cats" aria-labelledby="ew-home-cats-title">
	<div class="ew-container">
		<div class="ew-section__header ew-section__header--split">
			<h2 id="ew-home-cats-title"><?php echo esc_html( ew_theme_home_text( 'categories_title' ) ?: __( 'Kategorie produktów', 'eurowet-2026' ) ); ?></h2>
			<a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'products' ) ); ?>"><?php esc_html_e( 'Wszystkie produkty', 'eurowet-2026' ); ?></a>
		</div>
		<ul class="ew-cat-tiles ew-list-reset" role="list">
			<?php foreach ( $cats as $cat ) : ?>
				<?php $thumb = (int) get_term_meta( $cat->term_id, 'thumbnail_id', true ); ?>
				<li class="ew-cat-tile">
					<div class="ew-cat-tile__media" aria-hidden="true">
						<?php echo $thumb ? ew_theme_image( $thumb, 'ew-card', 'tile', array( 'alt' => '' ) ) : ew_theme_ui_icon( 'paw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<div class="ew-cat-tile__body">
						<h3 class="ew-cat-tile__title"><a class="ew-card__link" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></h3>
						<?php if ( $cat->description ) : ?><p class="ew-cat-tile__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $cat->description ), 18 ) ); ?></p><?php endif; ?>
						<p class="ew-cat-tile__count"><?php echo esc_html( sprintf( /* translators: %d products */ _n( '%d produkt', '%d produktów', (int) $cat->count, 'eurowet-2026' ), (int) $cat->count ) ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
