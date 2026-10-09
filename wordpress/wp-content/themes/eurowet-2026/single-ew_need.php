<?php
/**
 * Need / problem page (/potrzeby/{slug}/): answer first, red flags, recommended product from verified
 * relations, complementary and similar products, care steps, what to avoid, guides, FAQ.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$nid      = get_the_ID();
	$answer   = (string) ew_theme_meta( $nid, '_ew_short_answer', '' );
	$level    = (string) ew_theme_meta( $nid, '_ew_red_flag_level', 'none' );
	$flags    = (array) ew_theme_meta( $nid, '_ew_red_flags', array() );
	$care     = (string) ew_theme_meta( $nid, '_ew_care_steps', '' );
	$avoid    = (string) ew_theme_meta( $nid, '_ew_avoid', '' );
	$faq      = (array) ew_theme_meta( $nid, '_ew_faq', array() );
	$rels     = function_exists( 'ew_need_products' ) ? ew_need_products( $nid ) : array();
	$primary  = function_exists( 'ew_primary_product' ) ? ew_primary_product( $nid ) : null;
	$by_role  = array( 'complementary' => array(), 'similar' => array() );
	foreach ( $rels as $rel ) {
		if ( $primary && $rel['product']->get_id() === $primary['product']->get_id() ) {
			continue;
		}
		if ( isset( $by_role[ $rel['role'] ] ) ) {
			$by_role[ $rel['role'] ][] = $rel;
		} elseif ( 'primary' === $rel['role'] ) {
			$by_role['similar'][] = $rel;
		}
	}
	$guides   = function_exists( 'ew_guides_for_need' ) ? ew_guides_for_need( $nid, 6 ) : array();
	$species  = get_the_terms( $nid, 'ew_species' );
	$has_body = '' !== trim( wp_strip_all_tags( (string) get_the_content() ) );
	?>
	<article <?php post_class( 'ew-need' ); ?> aria-labelledby="ew-need-title">
		<header class="ew-page-header">
			<div class="ew-container ew-container--narrow">
				<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="ew-eyebrow"><?php esc_html_e( 'Potrzeba', 'eurowet-2026' ); ?></p>
				<h1 class="ew-page-header__title" id="ew-need-title"><?php the_title(); ?></h1>
				<?php if ( is_array( $species ) && $species ) : ?>
					<ul class="ew-chips ew-list-reset" aria-label="<?php esc_attr_e( 'Dotyczy', 'eurowet-2026' ); ?>">
						<?php foreach ( $species as $sp ) : ?>
							<li><span class="ew-chip"><?php echo esc_html( $sp->name ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</header>

		<div class="ew-container ew-container--narrow ew-need__body">
			<?php
			echo ew_theme_render( 'tldr', array( 'text' => $answer ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			if ( 'urgent' === $level ) {
				echo ew_theme_render( 'vet-notice', array( 'level' => 'urgent', 'items' => $flags ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>

		<?php if ( $primary ) : ?>
			<section class="ew-section ew-section--tight" aria-labelledby="ew-need-products">
				<div class="ew-container">
					<h2 id="ew-need-products"><?php esc_html_e( 'Produkt dopasowany do tej potrzeby', 'eurowet-2026' ); ?></h2>
					<?php get_template_part( 'template-parts/need/primary', null, array( 'rel' => $primary ) ); ?>
					<?php
					foreach ( array(
						'complementary' => __( 'Produkty uzupełniające', 'eurowet-2026' ),
						'similar'       => __( 'Inne produkty o podobnym przeznaczeniu', 'eurowet-2026' ),
					) as $role => $label ) :
						if ( ! $by_role[ $role ] ) {
							continue;
						}
						?>
						<h3 class="ew-need__group-title"><?php echo esc_html( $label ); ?></h3>
						<ul class="ew-grid ew-grid--fill ew-list-reset" role="list">
							<?php foreach ( array_slice( $by_role[ $role ], 0, 4 ) as $rel ) : ?>
								<li><?php echo ew_theme_render( 'product-card', array( 'product' => $rel['product'], 'reason' => (string) $rel['reason'], 'context' => 'need', 'heading_level' => 4 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<div class="ew-container ew-container--narrow ew-need__body">
			<?php if ( '' !== trim( wp_strip_all_tags( $care ) ) ) : ?>
				<section class="ew-need__section" aria-labelledby="ew-need-care">
					<h2 id="ew-need-care"><?php esc_html_e( 'Jak pielęgnować', 'eurowet-2026' ); ?></h2>
					<div class="ew-prose"><?php echo wp_kses_post( $care ); ?></div>
				</section>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $avoid ) ) ) : ?>
				<section class="ew-need__section" aria-labelledby="ew-need-avoid">
					<h2 id="ew-need-avoid"><?php esc_html_e( 'Czego unikać', 'eurowet-2026' ); ?></h2>
					<div class="ew-prose"><?php echo wp_kses_post( $avoid ); ?></div>
				</section>
			<?php endif; ?>

			<?php if ( $has_body ) : ?>
				<div class="ew-prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php
			if ( 'urgent' !== $level ) {
				echo ew_theme_render( 'vet-notice', array( 'level' => 'caution', 'items' => $flags ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>

		<?php if ( $guides ) : ?>
			<section class="ew-section" aria-label="<?php esc_attr_e( 'Porady', 'eurowet-2026' ); ?>">
				<div class="ew-container">
					<?php echo ew_theme_render( 'guide-grid', array( 'posts' => $guides, 'heading' => __( 'Przeczytaj więcej', 'eurowet-2026' ), 'id' => 'ew-need-guides' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</section>
		<?php endif; ?>

		<div class="ew-container ew-container--narrow ew-need__body">
			<?php echo ew_theme_render( 'faq', array( 'items' => $faq ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<section class="ew-need__more" aria-labelledby="ew-need-more">
				<h2 id="ew-need-more" class="ew-h3"><?php esc_html_e( 'Szukasz czegoś innego?', 'eurowet-2026' ); ?></h2>
				<?php echo ew_theme_render( 'finder', array( 'variant' => 'compact' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</section>
		</div>
	</article>
	<?php
endwhile;

get_footer();
