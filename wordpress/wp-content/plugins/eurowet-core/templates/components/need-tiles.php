<?php
/**
 * Need tiles (most common needs). Args: needs (WP_Post[]) OR species/area filters + limit; heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$needs = (array) ( $args['needs'] ?? array() );
if ( ! $needs ) {
	$q   = array( 'post_type' => 'ew_need', 'post_status' => 'publish', 'posts_per_page' => max( 1, (int) ( $args['limit'] ?? 8 ) ), 'no_found_rows' => true, 'meta_key' => '_ew_priority', 'orderby' => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	$tax = array();
	foreach ( array( 'species' => 'ew_species', 'area' => 'ew_area' ) as $k => $t ) {
		if ( ! empty( $args[ $k ] ) ) {
			$tax[] = array( 'taxonomy' => $t, 'field' => 'slug', 'terms' => array_map( 'sanitize_title', (array) $args[ $k ] ) );
		}
	}
	if ( $tax ) {
		$q['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$needs = get_posts( $q );
}
if ( ! $needs ) {
	return;
}
$hid = $args['id'] ?? 'ew-nt-' . wp_unique_id();
?>
<section class="ew-need-tiles" aria-labelledby="<?php echo esc_attr( $hid ); ?>">
	<?php echo ! empty( $args['heading'] ) ? ew_heading( (string) $args['heading'], $hid ) : '<h2 class="ew-visually-hidden" id="' . esc_attr( $hid ) . '">' . esc_html__( 'Potrzeby', 'eurowet-core' ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<ul class="ew-need-tiles__list ew-list-reset" role="list">
		<?php foreach ( $needs as $n ) : ?>
			<li><?php echo ew_render( 'need-tile', array( 'need' => $n ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endforeach; ?>
	</ul>
</section>
