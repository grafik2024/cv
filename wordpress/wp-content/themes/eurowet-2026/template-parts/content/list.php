<?php
/**
 * Post list (news, archives). Args: title, intro.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

ew_theme_page_header( (string) ( $args['title'] ?? '' ), (string) ( $args['intro'] ?? '' ) );
?>
<div class="ew-section ew-section--tight">
	<div class="ew-container">
		<?php if ( have_posts() ) : ?>
			<ul class="ew-grid ew-grid--fill ew-list-reset" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/card' );
				endwhile;
				?>
			</ul>
			<?php ew_theme_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Brak wpisów.', 'eurowet-2026' ); ?></p>
		<?php endif; ?>
	</div>
</div>
