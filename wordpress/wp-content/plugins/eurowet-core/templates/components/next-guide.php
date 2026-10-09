<?php
/**
 * "Czytaj dalej" — next best article or the matching product. Args: guide_id.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$next = ew_next_guide( (int) ( $args['guide_id'] ?? get_the_ID() ) );
if ( ! $next ) {
	return;
}
$id = wp_unique_id( 'ew-next-' );
?>
<aside class="ew-next" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<p class="ew-eyebrow" id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Czytaj dalej', 'eurowet-core' ); ?> · <span class="ew-next__reason"><?php echo esc_html( (string) $next['reason'] ); ?></span></p>
	<?php if ( $next['post'] instanceof WP_Post ) : ?>
		<a class="ew-next__link" href="<?php echo esc_url( get_permalink( $next['post'] ) ); ?>" data-ew-track="guide_click" data-ew-target="<?php echo (int) $next['post']->ID; ?>">
			<span class="ew-next__title"><?php echo esc_html( get_the_title( $next['post'] ) ); ?></span>
			<span class="ew-next__arrow" aria-hidden="true"><?php echo ew_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</a>
	<?php endif; ?>
	<?php if ( ! empty( $next['product_cta'] ) && $next['product_cta']['product'] instanceof WC_Product ) : ?>
		<?php $p = $next['product_cta']['product']; ?>
		<p class="ew-next__product"><?php esc_html_e( 'Produkt dopasowany do tej potrzeby:', 'eurowet-core' ); ?> <a href="<?php echo esc_url( get_permalink( $p->get_id() ) ); ?>" data-ew-track="product_click" data-ew-target="<?php echo (int) $p->get_id(); ?>"><?php echo esc_html( $p->get_name() ); ?></a></p>
	<?php endif; ?>
</aside>
