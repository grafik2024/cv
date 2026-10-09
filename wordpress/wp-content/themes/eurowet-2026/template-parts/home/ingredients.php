<?php
/**
 * 08 Ingredients & technologies: ingredients used in the most products (by key-ingredient relations).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$ings = get_posts( array( 'post_type' => 'ew_ingredient', 'post_status' => 'publish', 'posts_per_page' => 60, 'no_found_rows' => true ) );
if ( ! $ings ) {
	return;
}
$count = array();
foreach ( $ings as $ing ) {
	$count[ $ing->ID ] = function_exists( 'ew_ingredient_products' ) ? count( ew_ingredient_products( $ing->ID ) ) : 0;
}
usort( $ings, static fn( $a, $b ) => ( $count[ $b->ID ] <=> $count[ $a->ID ] ) ?: strcmp( $a->post_title, $b->post_title ) );
$ings = array_slice( $ings, 0, 10 );
?>
<section class="ew-section ew-home-ingredients" aria-labelledby="ew-home-ing-title">
	<div class="ew-container">
		<div class="ew-section__header ew-section__header--split">
			<div>
				<h2 id="ew-home-ing-title"><?php echo esc_html( ew_theme_home_text( 'ingredients_title' ) ?: __( 'Składniki i technologie', 'eurowet-2026' ) ); ?></h2>
				<?php $lead = ew_theme_home_text( 'ingredients_lead' ); ?>
				<?php if ( $lead ) : ?><p class="ew-lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
			</div>
			<a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'ingredients' ) ); ?>"><?php esc_html_e( 'Wszystkie składniki', 'eurowet-2026' ); ?></a>
		</div>
		<ul class="ew-ing-tiles ew-list-reset" role="list">
			<?php foreach ( $ings as $ing ) : ?>
				<li class="ew-ing-tile">
					<a class="ew-ing-tile__link" href="<?php echo esc_url( get_permalink( $ing ) ); ?>">
						<span class="ew-ing-tile__name"><?php echo esc_html( get_the_title( $ing ) ); ?></span>
						<?php $sum = wp_strip_all_tags( (string) ew_theme_meta( $ing->ID, '_ew_summary', '' ) ); ?>
						<?php if ( $sum ) : ?><span class="ew-ing-tile__text"><?php echo esc_html( wp_trim_words( $sum, 16 ) ); ?></span><?php endif; ?>
						<?php if ( $count[ $ing->ID ] ) : ?><span class="ew-ing-tile__count"><?php echo esc_html( sprintf( /* translators: %d products */ _n( 'w %d produkcie', 'w %d produktach', $count[ $ing->ID ], 'eurowet-2026' ), $count[ $ing->ID ] ) ); ?></span><?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
