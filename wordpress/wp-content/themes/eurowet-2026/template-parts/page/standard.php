<?php
/**
 * Standard page body: header (breadcrumbs, H1, excerpt as intro) + content in prose width.
 * Args: eyebrow, after (callable printing extra sections after the content), wide (bool).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$eyebrow = (string) ( $args['eyebrow'] ?? '' );
$wide    = ! empty( $args['wide'] );
?>
<article <?php post_class( 'ew-page' ); ?>>
	<?php ew_theme_page_header( get_the_title(), has_excerpt() ? get_the_excerpt() : '', $eyebrow ); ?>
	<?php if ( '' !== trim( wp_strip_all_tags( (string) get_the_content() ) ) ) : ?>
		<div class="ew-section ew-section--tight">
			<div class="ew-container<?php echo $wide ? '' : ' ew-container--narrow'; ?>">
				<div class="ew-prose<?php echo $wide ? ' ew-prose--wide' : ''; ?>"><?php the_content(); ?></div>
			</div>
		</div>
	<?php endif; ?>
	<?php
	if ( isset( $args['after'] ) && is_callable( $args['after'] ) ) {
		call_user_func( $args['after'] );
	}
	?>
</article>
