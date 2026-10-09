<?php
/**
 * Template Name: Marka własna
 *
 * Private label page: the client's own description of the service (page content) + enquiry form.
 * The template adds no capabilities, certificates or numbers of its own.
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
			'eyebrow' => __( 'Dla partnerów', 'eurowet-2026' ),
			'after'   => static function (): void {
				?>
				<section class="ew-section ew-section--alt" aria-label="<?php esc_attr_e( 'Zapytanie o markę własną', 'eurowet-2026' ); ?>">
					<div class="ew-container ew-container--narrow">
						<?php echo ew_theme_render( 'lead-form', array( 'form' => 'private_label', 'heading' => __( 'Zapytaj o produkcję pod marką własną', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</section>
				<?php
			},
		)
	);
endwhile;

get_footer();
