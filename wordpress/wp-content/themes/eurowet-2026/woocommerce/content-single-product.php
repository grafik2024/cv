<?php
/**
 * Product page (brief §49): breadcrumbs, packshot gallery, 360 / video, name, capacity switcher, purpose,
 * benefits, composition / INCI, usage, documents, shop, needs, related products, guides, B2B.
 * All product facts come verbatim from the product fields (imported from eurowet.pl) — the template adds none.
 *
 * Overrides woocommerce/templates/content-single-product.php (version 3.6.0 contract).
 *
 * @package Eurowet2026
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
if ( ! $product instanceof WC_Product ) {
	return;
}

$pid          = $product->get_id();
$m            = static fn( string $k, $d = '' ) => ew_theme_meta( $pid, $k, $d );
$subtitle     = (string) $m( '_ew_subtitle' );
$badges       = array_filter( array_map( 'strval', (array) $m( '_ew_badges', array() ) ) );
$catalog_only = (bool) $m( '_ew_catalog_only', false );
$where        = array_filter( (array) $m( '_ew_where_to_buy', array() ), static fn( $l ) => is_array( $l ) && ! empty( $l['url'] ) );
$line         = ew_theme_primary_term( $pid, 'ew_line' );
$species      = get_the_terms( $pid, 'ew_species' );
$needs        = function_exists( 'ew_needs_for_product' ) ? ew_needs_for_product( $pid ) : array();
$docs         = array_filter( array_map( 'intval', (array) $m( '_ew_documents', array() ) ) );
$video        = (int) $m( '_ew_video', 0 );
$spin         = array_filter( array_map( 'intval', (array) $m( '_ew_spin360', array() ) ) );

$sections = array(
	'zastosowanie'      => array( __( 'Zastosowanie', 'eurowet-2026' ), (string) $m( '_ew_indications' ) ),
	'przeznaczenie'     => array( __( 'Przeznaczenie', 'eurowet-2026' ), (string) $m( '_ew_intended_for' ) ),
	'wlasciwosci'       => array( __( 'Właściwości', 'eurowet-2026' ), (string) $m( '_ew_properties' ) ),
	'sposob-stosowania' => array( __( 'Sposób stosowania', 'eurowet-2026' ), (string) $m( '_ew_usage' ) ),
	'srodki-ostroznosci' => array( __( 'Środki ostrożności', 'eurowet-2026' ), (string) $m( '_ew_precautions' ) ),
	'sklad'             => array( __( 'Skład', 'eurowet-2026' ), (string) $m( '_ew_composition' ) ),
	'skladniki-analityczne' => array( __( 'Składniki analityczne i dodatki', 'eurowet-2026' ), (string) $m( '_ew_analytical' ) ),
	'uwagi'             => array( __( 'Uwagi', 'eurowet-2026' ), (string) $m( '_ew_notes' ) ),
);
$sections = array_filter( $sections, static fn( $s ) => '' !== trim( wp_strip_all_tags( $s[1] ) ) );
// Short "intended for" (one sentence) is shown in the summary; keep the section only when it is longer.
if ( isset( $sections['przeznaczenie'] ) && mb_strlen( wp_strip_all_tags( $sections['przeznaczenie'][1] ) ) < 120 ) {
	$intended_short = trim( wp_strip_all_tags( $sections['przeznaczenie'][1] ) );
	unset( $sections['przeznaczenie'] );
}
$ingredients = ew_theme_render( 'ingredient-list', array( 'product_id' => $pid ) );
$caution     = false;
foreach ( $needs as $n ) {
	if ( in_array( (string) ew_theme_meta( $n->ID, '_ew_red_flag_level', 'none' ), array( 'caution', 'urgent' ), true ) ) {
		$caution = true;
		break;
	}
}
?>
<div id="product-<?php echo (int) $pid; ?>" <?php wc_product_class( 'ew-product', $product ); ?>>
	<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<div class="ew-product__top">
		<?php get_template_part( 'template-parts/product/gallery', null, array( 'product' => $product, 'spin' => $spin, 'video' => $video ) ); ?>

		<div class="ew-product__summary summary entry-summary">
			<?php if ( $line ) : ?>
				<p class="ew-eyebrow"><?php echo esc_html( sprintf( /* translators: %s product line */ __( 'Linia %s', 'eurowet-2026' ), $line->name ) ); ?></p>
			<?php endif; ?>
			<h1 class="ew-product__title product_title entry-title"><?php echo esc_html( $product->get_name() ); ?></h1>
			<?php if ( $subtitle ) : ?><p class="ew-product__subtitle"><?php echo esc_html( ew_theme_sentence_case( $subtitle ) ); ?></p><?php endif; ?>

			<?php echo ew_theme_render( 'family-switcher', array( 'product_id' => $pid ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<?php if ( $badges ) : ?>
				<ul class="ew-chips ew-list-reset ew-product__badges" aria-label="<?php esc_attr_e( 'Najważniejsze cechy', 'eurowet-2026' ); ?>">
					<?php foreach ( $badges as $b ) : ?>
						<li><span class="ew-chip ew-chip--benefit"><?php echo ew_theme_ui_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( ew_theme_sentence_case( $b ) ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<dl class="ew-product__facts">
				<?php if ( ! empty( $intended_short ) ) : ?>
					<div><dt><?php esc_html_e( 'Przeznaczenie', 'eurowet-2026' ); ?></dt><dd><?php echo esc_html( $intended_short ); ?></dd></div>
				<?php endif; ?>
				<?php if ( is_array( $species ) && $species ) : ?>
					<div><dt><?php esc_html_e( 'Dla', 'eurowet-2026' ); ?></dt><dd><?php echo esc_html( implode( ', ', wp_list_pluck( $species, 'name' ) ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( (string) $m( '_ew_capacity' ) ) : ?>
					<div><dt><?php esc_html_e( 'Pojemność / ilość', 'eurowet-2026' ); ?></dt><dd><?php echo esc_html( (string) $m( '_ew_capacity' ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( $product->get_sku() ) : ?>
					<div><dt><?php esc_html_e( 'Kod produktu', 'eurowet-2026' ); ?></dt><dd><?php echo esc_html( $product->get_sku() ); ?></dd></div>
				<?php endif; ?>
			</dl>

			<div class="ew-product__buy">
				<?php
				$pl_of  = (int) $m( '_ew_i18n_of', 0 ); // translated catalogue page → the Polish product sells it
				$pl_buy = $pl_of ? wc_get_product( $pl_of ) : null;
				?>
				<?php if ( $catalog_only && $pl_buy && $pl_buy->is_purchasable() && $pl_buy->is_in_stock() && ! ew_theme_meta( $pl_of, '_ew_catalog_only', false ) ) : ?>
					<?php echo wp_kses_post( '<p class="price">' . $pl_buy->get_price_html() . '</p>' ); ?>
					<p class="ew-product__availability"><?php esc_html_e( 'Sprzedaż internetową prowadzimy w polskim sklepie (PLN, wysyłka na terenie Polski).', 'eurowet-2026' ); ?></p>
					<p class="ew-cluster"><a class="ew-btn" href="<?php echo esc_url( get_permalink( $pl_of ) ); ?>" hreflang="pl" lang="pl"><?php esc_html_e( 'Kup w sklepie internetowym', 'eurowet-2026' ); ?></a> <a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( ew_theme_url( 'contact' ) ); ?>"><?php esc_html_e( 'Zapytanie eksportowe', 'eurowet-2026' ); ?></a></p>
				<?php elseif ( $catalog_only ) : ?>
					<p class="ew-product__availability"><?php esc_html_e( 'Tego produktu nie sprzedajemy w sklepie internetowym. O dostępność zapytaj przedstawiciela handlowego.', 'eurowet-2026' ); ?></p>
					<p class="ew-cluster"><a class="ew-btn" href="<?php echo esc_url( ew_theme_url( 'reps' ) ); ?>"><?php esc_html_e( 'Zapytaj o dostępność', 'eurowet-2026' ); ?></a></p>
				<?php else : ?>
					<?php woocommerce_template_single_price(); ?>
					<?php woocommerce_template_single_add_to_cart(); ?>
				<?php endif; ?>
				<?php if ( $where ) : ?>
					<p class="ew-product__where"><strong><?php esc_html_e( 'Kup także w:', 'eurowet-2026' ); ?></strong>
						<?php foreach ( $where as $i => $l ) : ?>
							<?php echo $i ? ' · ' : ''; ?><a href="<?php echo esc_url( (string) $l['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( (string) ( $l['label'] ?? wp_parse_url( (string) $l['url'], PHP_URL_HOST ) ) ); ?><span class="ew-visually-hidden"> <?php esc_html_e( '(otwiera się w nowej karcie)', 'eurowet-2026' ); ?></span></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $needs ) : ?>
				<div class="ew-product__needs">
					<p class="ew-product__needs-title"><?php esc_html_e( 'Pomaga w potrzebach:', 'eurowet-2026' ); ?></p>
					<ul class="ew-chips ew-list-reset">
						<?php foreach ( array_slice( $needs, 0, 6 ) as $n ) : ?>
							<li><a class="ew-chip" href="<?php echo esc_url( get_permalink( $n ) ); ?>" data-ew-track="need_click" data-ew-target="<?php echo (int) $n->ID; ?>"><?php echo esc_html( get_the_title( $n ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
	$toc = array();
	foreach ( $sections as $id => $s ) {
		$toc[ $id ] = $s[0];
	}
	if ( $ingredients ) {
		$toc['kluczowe-skladniki'] = __( 'Kluczowe składniki', 'eurowet-2026' );
	}
	if ( $docs ) {
		$toc['dokumenty'] = __( 'Dokumenty', 'eurowet-2026' );
	}
	if ( count( $toc ) > 2 ) :
		?>
		<nav class="ew-product__toc" aria-label="<?php esc_attr_e( 'Informacje o produkcie', 'eurowet-2026' ); ?>">
			<ul class="ew-list-reset">
				<?php foreach ( $toc as $id => $label ) : ?>
					<li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<div class="ew-product__details">
		<div class="ew-product__sections">
			<?php foreach ( $sections as $id => $s ) : ?>
				<section class="ew-product__section" id="<?php echo esc_attr( $id ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-h">
					<h2 id="<?php echo esc_attr( $id ); ?>-h"><?php echo esc_html( $s[0] ); ?></h2>
					<div class="ew-prose<?php echo 'sklad' === $id ? ' ew-product__inci' : ''; ?>"><?php echo ew_theme_rich_text( $s[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</section>
			<?php endforeach; ?>

			<?php if ( $ingredients ) : ?>
				<div class="ew-product__section" id="kluczowe-skladniki"><?php echo $ingredients; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>

			<?php
			if ( $caution ) {
				echo ew_theme_render( 'vet-notice', array( 'level' => 'caution' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>

			<?php if ( $docs ) : ?>
				<section class="ew-product__section" id="dokumenty" aria-labelledby="dokumenty-h">
					<h2 id="dokumenty-h"><?php esc_html_e( 'Dokumenty', 'eurowet-2026' ); ?></h2>
					<ul class="ew-doc-list ew-list-reset">
						<?php foreach ( $docs as $doc ) : ?>
							<?php
							$url = wp_get_attachment_url( $doc );
							if ( ! $url ) {
								continue;
							}
							$file = get_attached_file( $doc );
							$size = ( $file && is_readable( $file ) ) ? size_format( (int) filesize( $file ) ) : '';
							?>
							<li><a class="ew-doc" href="<?php echo esc_url( $url ); ?>" download><?php echo ew_theme_ui_icon( 'file' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( get_the_title( $doc ) ); ?></span> <span class="ew-text-muted">(<?php echo esc_html( strtoupper( (string) pathinfo( $url, PATHINFO_EXTENSION ) ) . ( $size ? ', ' . $size : '' ) ); ?>)</span></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>
	</div>

	<?php
	echo ew_theme_render( 'guides-for-product', array( 'product_id' => $pid, 'limit' => 3 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo ew_theme_render( 'related-products', array( 'product_id' => $pid ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	?>

	<aside class="ew-band ew-product__b2b" aria-labelledby="ew-product-b2b">
		<div class="ew-product__b2b-inner">
			<h2 id="ew-product-b2b" class="ew-h3"><?php esc_html_e( 'Prowadzisz gabinet, hurtownię lub sklep?', 'eurowet-2026' ); ?></h2>
			<p><?php esc_html_e( 'Skontaktuj się z przedstawicielem handlowym w Twoim województwie lub wyślij zapytanie o współpracę.', 'eurowet-2026' ); ?></p>
			<p class="ew-cluster">
				<a class="ew-btn ew-btn--light" href="<?php echo esc_url( ew_theme_url( 'reps' ) ); ?>"><?php esc_html_e( 'Znajdź przedstawiciela', 'eurowet-2026' ); ?></a>
				<a class="ew-btn ew-btn--outline-light" href="<?php echo esc_url( ew_theme_url( 'b2b' ) ); ?>"><?php esc_html_e( 'Współpraca B2B', 'eurowet-2026' ); ?></a>
			</p>
		</div>
	</aside>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
