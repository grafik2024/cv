<?php
/**
 * Guide grid. Args: posts (WP_Post[]) OR hub (slug) + limit; heading; heading_level.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$posts = (array) ( $args['posts'] ?? array() );
if ( ! $posts ) {
	$q = array( 'post_type' => 'ew_guide', 'post_status' => 'publish', 'posts_per_page' => max( 1, (int) ( $args['limit'] ?? 6 ) ), 'no_found_rows' => true );
	if ( ! empty( $args['hub'] ) ) {
		$q['tax_query'] = array( array( 'taxonomy' => 'ew_hub', 'field' => 'slug', 'terms' => sanitize_title( (string) $args['hub'] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$posts = get_posts( $q );
}
if ( ! $posts ) {
	return;
}
$hid   = $args['id'] ?? 'ew-gg-' . wp_unique_id();
$level = (int) ( $args['heading_level'] ?? 2 );
?>
<section class="ew-ggrid" aria-labelledby="<?php echo esc_attr( $hid ); ?>">
	<?php echo ! empty( $args['heading'] ) ? ew_heading( (string) $args['heading'], $hid, $level ) : '<h2 class="ew-visually-hidden" id="' . esc_attr( $hid ) . '">' . esc_html__( 'Porady', 'eurowet-core' ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<ul class="ew-grid ew-list-reset" role="list">
		<?php foreach ( $posts as $p ) : ?>
			<li><?php echo ew_render( 'guide-card', array( 'post' => $p, 'heading_level' => $level + 1 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endforeach; ?>
	</ul>
</section>
