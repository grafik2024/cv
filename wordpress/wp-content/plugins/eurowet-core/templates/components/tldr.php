<?php
/**
 * "W skrócie" — answer-first summary right after the H1. Args: text (html), heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$text = trim( (string) ( $args['text'] ?? '' ) );
if ( '' === $text ) {
	return;
}
$id = wp_unique_id( 'ew-tldr-' );
?>
<section class="ew-tldr" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<h2 class="ew-tldr__title" id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( (string) ( $args['heading'] ?? __( 'W skrócie', 'eurowet-core' ) ) ); ?></h2>
	<div class="ew-tldr__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
</section>
