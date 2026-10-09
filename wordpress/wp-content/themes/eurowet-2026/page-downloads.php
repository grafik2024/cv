<?php
/**
 * Template Name: Materiały do pobrania
 *
 * Downloadable materials (catalogues, leaflets, documents) grouped by type.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/page/standard',
		null,
		array(
			'after' => static function (): void {
				$types = get_terms( array( 'taxonomy' => 'ew_material_type', 'hide_empty' => true ) );
				echo '<section class="ew-section ew-section--tight"><div class="ew-container ew-container--narrow ew-stack">';
				if ( is_array( $types ) && $types ) {
					foreach ( $types as $t ) {
						echo ew_theme_render( 'material-list', array( 'type' => $t->slug, 'heading' => $t->name ) ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
				} else {
					$all = ew_theme_render( 'material-list', array() );
					echo $all ? $all : '<p>' . esc_html__( 'Materiały są w przygotowaniu.', 'eurowet-2026' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				echo '</div></section>';
			},
		)
	);
endwhile;

get_footer();
