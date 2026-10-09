<?php
/**
 * Template Name: Znajdź przedstawiciela
 *
 * Sales representatives: interactive map of 16 voivodeships + select (mobile / assistive tech) + contact form.
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
			'eyebrow' => __( 'Kontakt', 'eurowet-2026' ),
			'after'   => static function (): void {
				?>
				<section class="ew-section ew-section--tight" aria-label="<?php esc_attr_e( 'Mapa przedstawicieli', 'eurowet-2026' ); ?>">
					<div class="ew-container">
						<?php echo ew_theme_render( 'rep-finder', array( 'variant' => 'full' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</section>
				<section class="ew-section ew-section--alt" aria-label="<?php esc_attr_e( 'Formularz kontaktowy', 'eurowet-2026' ); ?>">
					<div class="ew-container ew-container--narrow">
						<?php echo ew_theme_render( 'lead-form', array( 'form' => 'rep_contact', 'heading' => __( 'Poproś o kontakt przedstawiciela', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</section>
				<?php
			},
		)
	);
endwhile;

get_footer();
