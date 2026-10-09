<?php
/**
 * Ingredient list (for a product, or all ingredients A–Z). Args: product_id (optional), heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$pid   = (int) ( $args['product_id'] ?? 0 );
$items = $pid ? ew_product_ingredients( $pid ) : get_posts( array( 'post_type' => 'ew_ingredient', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) );
if ( ! $items ) {
	return;
}
$id = wp_unique_id( 'ew-ing-' );
?>
<section class="ew-ingredients" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<h2 id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( (string) ( $args['heading'] ?? ( $pid ? __( 'Kluczowe składniki', 'eurowet-core' ) : __( 'Składniki i technologie', 'eurowet-core' ) ) ) ); ?></h2>
	<ul class="ew-ingredients__list ew-list-reset">
		<?php foreach ( $items as $ing ) : ?>
			<?php $inci = (string) ew_meta( $ing->ID, '_ew_inci', '' ); ?>
			<li class="ew-ingredients__item">
				<a href="<?php echo esc_url( get_permalink( $ing ) ); ?>"><strong><?php echo esc_html( get_the_title( $ing ) ); ?></strong><?php echo $inci ? '<span class="ew-ingredients__inci">' . esc_html( $inci ) . '</span>' : ''; ?></a>
				<?php $sum = wp_strip_all_tags( (string) ew_meta( $ing->ID, '_ew_summary', '' ) ); ?>
				<?php if ( $sum && ! $pid ) : ?><p class="ew-text-muted"><?php echo esc_html( wp_trim_words( $sum, 22 ) ); ?></p><?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
