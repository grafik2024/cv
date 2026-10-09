<?php
/**
 * "Wiedza związana z produktem" — top guides ranked by verified relations. Args: product_id, limit.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$posts = ew_guides_for_product( (int) ( $args['product_id'] ?? get_the_ID() ), (int) ( $args['limit'] ?? 3 ) );
if ( ! $posts ) {
	return;
}
echo ew_render( 'guide-grid', array( 'posts' => $posts, 'heading' => (string) ( $args['heading'] ?? __( 'Wiedza związana z produktem', 'eurowet-core' ) ), 'id' => 'ew-knowledge' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
