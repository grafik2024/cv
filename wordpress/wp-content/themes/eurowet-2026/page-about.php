<?php
/**
 * Template Name: O firmie
 *
 * About Eurowet: the client's own text (page content), product lines, knowledge, contact.
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
			'eyebrow' => 'EUROWET',
			'after'   => static function (): void {
				$cats = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
				if ( is_array( $cats ) && $cats ) :
					?>
					<section class="ew-section ew-section--alt" aria-labelledby="ew-about-lines">
						<div class="ew-container">
							<h2 id="ew-about-lines"><?php esc_html_e( 'Nasze produkty', 'eurowet-2026' ); ?></h2>
							<ul class="ew-chips ew-list-reset" role="list">
								<?php foreach ( $cats as $cat ) : ?>
									<li><a class="ew-chip" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>
					<?php
				endif;
				?>
				<section class="ew-section" aria-label="<?php esc_attr_e( 'Kontakt', 'eurowet-2026' ); ?>">
					<div class="ew-container ew-split">
						<div><?php get_template_part( 'template-parts/company/card' ); ?></div>
						<div class="ew-stack">
							<h2 class="ew-h3"><?php esc_html_e( 'Współpraca', 'eurowet-2026' ); ?></h2>
							<p class="ew-cluster">
								<a class="ew-btn" href="<?php echo esc_url( ew_theme_url( 'b2b' ) ); ?>"><?php esc_html_e( 'Współpraca B2B', 'eurowet-2026' ); ?></a>
								<a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( ew_theme_url( 'private_label' ) ); ?>"><?php esc_html_e( 'Marka własna', 'eurowet-2026' ); ?></a>
								<a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'reps' ) ); ?>"><?php esc_html_e( 'Znajdź przedstawiciela', 'eurowet-2026' ); ?></a>
							</p>
						</div>
					</div>
				</section>
				<?php
			},
		)
	);
endwhile;

get_footer();
