<?php
/**
 * Downloadable materials. Args: type (ew_material_type slug), heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$items = class_exists( \Eurowet\Core\Materials\Module::class ) ? \Eurowet\Core\Materials\Module::items( sanitize_title( (string) ( $args['type'] ?? '' ) ) ) : array();
if ( ! $items ) {
	return;
}
$id = wp_unique_id( 'ew-mat-' );
?>
<section class="ew-materials" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<h2 id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( (string) ( $args['heading'] ?? __( 'Materiały do pobrania', 'eurowet-core' ) ) ); ?></h2>
	<ul class="ew-materials__list ew-list-reset">
		<?php foreach ( $items as $m ) : ?>
			<li class="ew-materials__item">
				<a class="ew-materials__link" href="<?php echo esc_url( $m['url'] ); ?>" <?php echo 'pl' !== $m['lang'] ? 'hreflang="' . esc_attr( $m['lang'] ) . '"' : ''; ?>>
					<span aria-hidden="true"><?php echo ew_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="ew-materials__title"><?php echo esc_html( $m['title'] ); ?></span>
					<span class="ew-materials__meta"><?php echo esc_html( trim( $m['ext'] . ( $m['size'] ? ', ' . $m['size'] : '' ) . ( 'pl' !== $m['lang'] ? ', ' . strtoupper( $m['lang'] ) : '' ) ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
