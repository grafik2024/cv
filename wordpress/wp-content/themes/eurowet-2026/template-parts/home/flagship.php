<?php
/**
 * 06 Flagship line: products of the Excellence line (ew_line) — or Woo "featured" products when the line is
 * empty — with the composite photo (original packshots; AI background disclosed).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}
$line = (string) apply_filters( 'ew_theme_flagship_line', 'excellence' );
$ids  = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'tax_query'      => array( array( 'taxonomy' => 'ew_line', 'field' => 'slug', 'terms' => $line ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	)
);
if ( ! $ids ) {
	$ids = wc_get_products( array( 'status' => 'publish', 'featured' => true, 'limit' => 8, 'return' => 'ids' ) );
}
// One card per family (capacity variants are switched on the product page).
$seen     = array();
$products = array();
foreach ( $ids as $id ) {
	$fam = wp_get_post_terms( (int) $id, 'ew_family', array( 'fields' => 'ids' ) );
	$key = ( is_array( $fam ) && $fam ) ? 'f' . $fam[0] : 'p' . $id;
	if ( isset( $seen[ $key ] ) ) {
		continue;
	}
	$seen[ $key ] = true;
	$p            = wc_get_product( (int) $id );
	if ( $p && $p->is_visible() ) {
		$products[] = $p;
	}
}
if ( ! $products ) {
	return;
}
$pic = ew_theme_hub_picture( 'excellence-line-bathroom', '', array( 'dir' => 'home', 'class' => 'ew-flagship__picture', 'sizes' => '(min-width: 1280px) 1200px, 100vw' ) );
?>
<section class="ew-section ew-home-flagship" aria-labelledby="ew-home-flagship-title">
	<div class="ew-container">
		<div class="ew-section__header ew-section__header--split">
			<div>
				<h2 id="ew-home-flagship-title"><?php echo esc_html( ew_theme_home_text( 'flagship_title' ) ?: __( 'Produkty flagowe', 'eurowet-2026' ) ); ?></h2>
				<?php $lead = ew_theme_home_text( 'flagship_lead' ); ?>
				<?php if ( $lead ) : ?><p class="ew-lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
			</div>
		</div>
		<?php if ( $pic ) : ?>
			<figure class="ew-flagship__figure">
				<?php echo $pic; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<figcaption><?php echo ew_theme_image_disclosure( 'excellence-line-bathroom' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figcaption>
			</figure>
		<?php endif; ?>
		<?php echo ew_theme_render( 'product-grid', array( 'products' => array_slice( $products, 0, 4 ), 'id' => 'ew-flagship-grid', 'context' => 'home_flagship' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
