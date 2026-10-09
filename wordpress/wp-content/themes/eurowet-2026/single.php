<?php
/**
 * Single news post (Aktualności) and any other single post type without its own template.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'ew-article' ); ?> aria-labelledby="ew-article-title">
		<header class="ew-article__header">
			<div class="ew-container ew-container--narrow">
				<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<p class="ew-eyebrow"><?php esc_html_e( 'Aktualności', 'eurowet-2026' ); ?></p>
				<?php endif; ?>
				<h1 class="ew-article__title" id="ew-article-title"><?php the_title(); ?></h1>
				<p class="ew-meta-line"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			</div>
		</header>
		<div class="ew-container ew-container--narrow">
			<?php echo ew_theme_featured_figure( get_the_ID(), 'hero', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="ew-prose"><?php the_content(); ?></div>
			<?php
			wp_link_pages();
			the_post_navigation(
				array(
					'prev_text' => '<span class="ew-eyebrow">' . esc_html__( 'Poprzedni wpis', 'eurowet-2026' ) . '</span> %title',
					'next_text' => '<span class="ew-eyebrow">' . esc_html__( 'Następny wpis', 'eurowet-2026' ) . '</span> %title',
				)
			);
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
