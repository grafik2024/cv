<?php
/**
 * Breadcrumbs: Yoast when available (matches BreadcrumbList schema), otherwise a simple trail.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'yoast_breadcrumb' ) ) {
	$html = yoast_breadcrumb( '<nav class="ew-breadcrumbs" aria-label="' . esc_attr__( 'Ścieżka nawigacji', 'eurowet-core' ) . '"><p>', '</p></nav>', false );
	if ( $html ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- Yoast escapes its output.
		return;
	}
}
$trail = array( array( home_url( '/' ), __( 'Strona główna', 'eurowet-core' ) ) );
if ( is_singular() ) {
	$pt  = get_post_type();
	$arc = array( 'ew_guide' => __( 'Porady i wiedza', 'eurowet-core' ), 'ew_need' => __( 'Dobierz produkt', 'eurowet-core' ), 'ew_ingredient' => __( 'Składniki', 'eurowet-core' ), 'product' => __( 'Produkty', 'eurowet-core' ) );
	if ( isset( $arc[ $pt ] ) ) {
		$link = 'product' === $pt && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : get_post_type_archive_link( $pt );
		$trail[] = array( $link, $arc[ $pt ] );
	}
	if ( 'product' === $pt ) {
		$cats = get_the_terms( get_the_ID(), 'product_cat' );
		if ( is_array( $cats ) && $cats ) {
			$trail[] = array( get_term_link( $cats[0] ), $cats[0]->name );
		}
	}
	$trail[] = array( '', get_the_title() );
} elseif ( is_tax() || is_category() ) {
	$trail[] = array( '', single_term_title( '', false ) );
} elseif ( is_post_type_archive() ) {
	$trail[] = array( '', post_type_archive_title( '', false ) );
}
?>
<nav class="ew-breadcrumbs" aria-label="<?php esc_attr_e( 'Ścieżka nawigacji', 'eurowet-core' ); ?>">
	<ol>
		<?php foreach ( $trail as $i => [ $url, $label ] ) : ?>
			<li><?php echo $url && $i < count( $trail ) - 1 ? '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>' : '<span aria-current="page">' . esc_html( $label ) . '</span>'; ?></li>
		<?php endforeach; ?>
	</ol>
</nav>
