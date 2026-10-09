<?php
/**
 * Related product groups on a product page (manual relations first). Args: product_id.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$groups = ew_related_products( (int) ( $args['product_id'] ?? get_the_ID() ) );
if ( ! $groups ) {
	return;
}
$labels = array(
	'complementary' => __( 'Produkty uzupełniające', 'eurowet-core' ),
	'similar'       => __( 'Podobne produkty', 'eurowet-core' ),
	'same_need'     => __( 'Dla tej samej potrzeby', 'eurowet-core' ),
	'same_line'     => __( 'Z tej samej linii', 'eurowet-core' ),
	'same_category' => __( 'Z tej samej kategorii', 'eurowet-core' ),
);
foreach ( $labels as $key => $label ) {
	if ( ! empty( $groups[ $key ] ) ) {
		echo ew_render( 'product-grid', array( 'products' => $groups[ $key ], 'heading' => $label, 'id' => 'ew-rel-' . $key, 'context' => 'related' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
