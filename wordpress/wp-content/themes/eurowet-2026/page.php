<?php
/**
 * Default page. Pages built with Elementor render full width (Elementor controls the layout);
 * other pages use the readable prose layout.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	if ( ew_theme_built_with_elementor( get_the_ID() ) ) :
		the_content();
	else :
		get_template_part( 'template-parts/page/standard' );
	endif;
endwhile;

get_footer();
