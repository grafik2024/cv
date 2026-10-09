<?php
/**
 * Capacity switcher (links to sibling simple products). Args: product_id.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$variants = ew_family_variants( (int) ( $args['product_id'] ?? get_the_ID() ) );
if ( ! $variants ) {
	return;
}
$id = wp_unique_id( 'ew-fam-' );
?>
<div class="ew-family">
	<p class="ew-family__label" id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Pojemność / opakowanie', 'eurowet-core' ); ?></p>
	<ul class="ew-family__list ew-list-reset" aria-labelledby="<?php echo esc_attr( $id ); ?>">
		<?php foreach ( $variants as $v ) : ?>
			<li>
				<?php if ( $v['current'] ) : ?>
					<span class="ew-family__pill is-current" aria-current="true"><?php echo esc_html( $v['capacity'] ?: __( 'ten wariant', 'eurowet-core' ) ); ?></span>
				<?php else : ?>
					<a class="ew-family__pill<?php echo $v['in_stock'] ? '' : ' is-oos'; ?>" href="<?php echo esc_url( $v['url'] ); ?>"><?php echo esc_html( $v['capacity'] ?: __( 'inny wariant', 'eurowet-core' ) ); ?><?php echo $v['in_stock'] ? '' : '<span class="ew-visually-hidden"> — ' . esc_html__( 'chwilowo niedostępny', 'eurowet-core' ) . '</span>'; ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
