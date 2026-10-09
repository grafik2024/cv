<?php
/**
 * Template Name: Współpraca B2B
 *
 * B2B page: page content (who Eurowet works with — from the client's texts), audiences, reps, B2B form.
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
				$audiences = ew_theme_home_list( 'b2b_audiences' );
				if ( $audiences ) :
					?>
					<section class="ew-section ew-section--alt" aria-labelledby="ew-b2b-who">
						<div class="ew-container">
							<h2 id="ew-b2b-who"><?php esc_html_e( 'Z kim współpracujemy', 'eurowet-2026' ); ?></h2>
							<ul class="ew-feature-list" role="list">
								<?php foreach ( $audiences as $a ) : ?>
									<li><?php echo ew_theme_ui_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $a ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>
					<?php
				endif;
				?>
				<section class="ew-section" aria-label="<?php esc_attr_e( 'Kontakt B2B', 'eurowet-2026' ); ?>">
					<div class="ew-container ew-split ew-split--form">
						<div><?php echo ew_theme_render( 'lead-form', array( 'form' => 'b2b', 'heading' => __( 'Zapytanie o współpracę', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<div><?php echo ew_theme_render( 'rep-finder', array( 'variant' => 'compact', 'heading' => __( 'Twój przedstawiciel handlowy', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					</div>
				</section>
				<?php
				$materials = ew_theme_render( 'material-list', array( 'heading' => __( 'Materiały dla partnerów', 'eurowet-2026' ) ) );
				if ( $materials ) {
					echo '<section class="ew-section ew-section--tight"><div class="ew-container">' . $materials . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			},
		)
	);
endwhile;

get_footer();
