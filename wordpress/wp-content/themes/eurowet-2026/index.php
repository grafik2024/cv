<?php
/**
 * Fallback template: list of posts.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/content/list', null, array( 'title' => is_home() ? single_post_title( '', false ) : get_the_archive_title() ) );
get_footer();
