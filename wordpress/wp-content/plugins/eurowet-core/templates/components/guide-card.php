<?php
/**
 * Guide card. Args: post (WP_Post|int), heading_level.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$post = get_post( $args['post'] ?? 0 );
if ( ! $post ) {
	return;
}
$level   = (int) ( $args['heading_level'] ?? 3 );
$tldr    = wp_strip_all_tags( (string) ew_meta( $post->ID, '_ew_tldr', '' ) );
$excerpt = '' !== $tldr ? $tldr : wp_strip_all_tags( get_the_excerpt( $post ) );
$words   = str_word_count( wp_strip_all_tags( (string) $post->post_content ) );
$minutes = max( 1, (int) round( $words / 200 ) );
$hubs    = wp_get_post_terms( $post->ID, 'ew_hub', array( 'fields' => 'names' ) );
?>
<article class="ew-card ew-card--interactive ew-gcard">
	<?php if ( has_post_thumbnail( $post ) ) : ?>
		<div class="ew-card__media ew-card__media--cover"><?php echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(min-width: 1024px) 380px, 90vw' ) ); ?></div>
	<?php endif; ?>
	<div class="ew-card__body">
		<?php if ( is_array( $hubs ) && $hubs ) : ?>
			<p class="ew-card__eyebrow"><?php echo esc_html( $hubs[0] ); ?></p>
		<?php endif; ?>
		<h<?php echo (int) $level; ?> class="ew-card__title"><a class="ew-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>" data-ew-track="guide_click" data-ew-target="<?php echo (int) $post->ID; ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h<?php echo (int) $level; ?>>
		<?php if ( $excerpt ) : ?>
			<p class="ew-card__text"><?php echo esc_html( wp_trim_words( $excerpt, 26 ) ); ?></p>
		<?php endif; ?>
		<p class="ew-card__footer ew-text-muted">
			<?php
			/* translators: %d: minutes */
			echo esc_html( sprintf( _n( '%d min czytania', '%d min czytania', $minutes, 'eurowet-core' ), $minutes ) );
			?>
			· <time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_modified_date( '', $post ) ); ?></time>
		</p>
	</div>
</article>
