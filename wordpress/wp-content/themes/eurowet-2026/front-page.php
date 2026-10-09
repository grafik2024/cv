<?php
/**
 * Homepage — 13 sections (brief §46): hero, "what does your pet need" + finder, categories, common needs,
 * flagship line, knowledge hub, ingredients, about, B2B, private label, reps, contact.
 * Every text is either the client's copy (content-defaults / Customizer) or data from the catalogue.
 * When the static front page is built with Elementor, Elementor renders the page instead.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

$front_id = (int) get_option( 'page_on_front' );
if ( $front_id && ew_theme_built_with_elementor( $front_id ) ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	get_footer();
	return;
}

// 01 HERO 3D.
get_template_part( 'template-parts/home/hero' );

// 02 + 03 CZEGO POTRZEBUJE TWOJE ZWIERZĘ? — PRODUCT FINDER.
?>
<section class="ew-section ew-home-finder" id="ew-home-finder" aria-label="<?php echo esc_attr( ew_theme_home_text( 'finder_title' ) ); ?>">
	<div class="ew-container ew-container--narrow">
		<?php
		echo ew_theme_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'finder',
			array(
				'variant' => 'hero',
				'heading' => ew_theme_home_text( 'finder_title' ),
				'lead'    => ew_theme_home_text( 'finder_lead' ),
			)
		);
		?>
	</div>
</section>
<?php

// 04 KATEGORIE.
get_template_part( 'template-parts/home/categories' );

// 05 NAJCZĘSTSZE POTRZEBY.
$needs = ew_theme_render( 'need-tiles', array( 'limit' => 8, 'heading' => ew_theme_home_text( 'needs_title' ) ?: __( 'Najczęstsze potrzeby', 'eurowet-2026' ) ) );
if ( $needs ) {
	echo '<section class="ew-section ew-section--alt ew-home-needs"><div class="ew-container">' . $needs . '<p class="ew-section__more"><a class="ew-btn ew-btn--ghost" href="' . esc_url( ew_theme_url( 'needs' ) ) . '">' . esc_html__( 'Wszystkie potrzeby', 'eurowet-2026' ) . '</a></p></div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

// 06 FLAGOWE PRODUKTY.
get_template_part( 'template-parts/home/flagship' );

// 07 PORADY / KNOWLEDGE HUB.
get_template_part( 'template-parts/home/knowledge' );

// 08 SKŁADNIKI / TECHNOLOGIE.
get_template_part( 'template-parts/home/ingredients' );

// 09 O EUROWET · 10 B2B · 11 PRIVATE LABEL.
get_template_part( 'template-parts/home/company' );

// 12 ZNAJDŹ PRZEDSTAWICIELA.
$reps = ew_theme_render( 'rep-finder', array( 'variant' => 'compact', 'heading' => ew_theme_home_text( 'reps_title' ) ?: __( 'Znajdź przedstawiciela', 'eurowet-2026' ) ) );
if ( $reps ) {
	echo '<section class="ew-section ew-home-reps" id="przedstawiciele"><div class="ew-container">' . $reps . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

// 13 KONTAKT.
?>
<section class="ew-section ew-section--alt ew-home-contact" aria-label="<?php esc_attr_e( 'Kontakt', 'eurowet-2026' ); ?>">
	<div class="ew-container ew-split ew-split--form">
		<div>
			<?php get_template_part( 'template-parts/company/card', null, array( 'heading' => ew_theme_home_text( 'contact_title' ) ?: __( 'Kontakt', 'eurowet-2026' ) ) ); ?>
		</div>
		<div>
			<?php echo ew_theme_render( 'lead-form', array( 'form' => 'contact', 'heading' => __( 'Napisz do nas', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>
<?php
get_footer();
