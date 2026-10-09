<?php
/**
 * Template Name: Kontakt
 *
 * Contact: company data, contact form, sales representatives.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/page/standard',
		null,
		array(
			'after' => static function (): void {
				?>
				<section class="ew-section ew-section--tight" aria-label="<?php esc_attr_e( 'Kontakt', 'eurowet-2026' ); ?>">
					<div class="ew-container ew-split ew-split--form">
						<div><?php get_template_part( 'template-parts/company/card' ); ?></div>
						<div><?php echo ew_theme_render( 'lead-form', array( 'form' => 'contact', 'heading' => __( 'Napisz do nas', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					</div>
				</section>
				<section class="ew-section ew-section--alt" aria-label="<?php esc_attr_e( 'Przedstawiciele handlowi', 'eurowet-2026' ); ?>">
					<div class="ew-container">
						<?php echo ew_theme_render( 'rep-finder', array( 'variant' => 'compact', 'heading' => __( 'Przedstawiciel w Twoim województwie', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</section>
				<?php
			},
		)
	);
endwhile;

get_footer();
