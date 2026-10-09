<?php
/**
 * Generic archive (categories, tags, dates of news posts).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/content/list', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ), 'intro' => (string) get_the_archive_description() ) );
get_footer();
