<?php
/**
 * News/post card (list item). Guides use the eurowet-core guide-card component.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

if ( 'ew_guide' === get_post_type() ) {
	echo '<li>' . ew_theme_render( 'guide-card', array( 'post' => get_post() ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
?>
<li>
	<article class="ew-card">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ew-card__media ew-card__media--cover"><?php echo ew_theme_image( (int) get_post_thumbnail_id(), 'ew-card', 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
		<div class="ew-card__body">
			<p class="ew-card__eyebrow"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			<h2 class="ew-card__title ew-h4"><a class="ew-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php if ( has_excerpt() || get_the_content() ) : ?>
				<p class="ew-card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
			<?php endif; ?>
		</div>
	</article>
</li>
