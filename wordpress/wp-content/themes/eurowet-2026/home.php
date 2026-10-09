<?php
/**
 * News (Aktualności) — posts page.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/content/list',
	null,
	array(
		'title' => (string) ( get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Aktualności', 'eurowet-2026' ) ),
	)
);
get_footer();
