<?php
/**
 * Need tile (link to the need page). Args: need (WP_Post|int), heading_level.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$need = get_post( $args['need'] ?? 0 );
if ( ! $need ) {
	return;
}
$areas = wp_get_post_terms( $need->ID, 'ew_area', array( 'fields' => 'slugs' ) );
$icon  = array( 'uszy' => 'ear', 'oczy' => 'eye', 'jama-ustna-i-zeby' => 'tooth', 'skora' => 'skin', 'siersc' => 'coat' )[ is_array( $areas ) && $areas ? $areas[0] : '' ] ?? 'paw';
$level = (int) ( $args['heading_level'] ?? 3 );
?>
<a class="ew-need-tile" href="<?php echo esc_url( get_permalink( $need ) ); ?>" data-ew-track="need_click" data-ew-target="<?php echo (int) $need->ID; ?>">
	<span class="ew-need-tile__icon"><?php echo ew_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	<span class="ew-need-tile__title" role="heading" aria-level="<?php echo (int) $level; ?>"><?php echo esc_html( get_the_title( $need ) ); ?></span>
	<span class="ew-need-tile__arrow" aria-hidden="true"><?php echo ew_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
</a>
