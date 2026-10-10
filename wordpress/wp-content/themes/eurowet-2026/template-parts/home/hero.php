<?php
/**
 * 01 Hero: H1 + lead + CTAs; stage with the REAL packshot of the hero product (LCP image, AVIF/WebP/PNG).
 * On capable desktops (≥1024 px, WebGL2, no reduced motion, no Save-Data) hero-3d.js swaps the stage for a
 * rotating "silhouette lathe" built from the same packshot (profile: assets/3d/{profile}.profile.json) —
 * the label is the untouched original image projected on the bottle shape, never AI re-texturing.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$profile = sanitize_key( (string) get_theme_mod( 'ew_hero_profile', 'triaderm-excellence-200ml' ) );
$slug    = preg_replace( '/-\d+ml$/', '', $profile ); // packshot files: assets/img/hero/{slug}-{h}.{ext}
$pid     = (int) get_theme_mod( 'ew_hero_product', 0 );
if ( ! $pid ) {
	$found = get_posts( array( 'post_type' => 'product', 'name' => $profile, 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ) );
	$pid   = $found ? (int) $found[0] : 0;
}
$pid     = $pid ? ew_theme_tr_id( $pid ) : 0;
$product = ( $pid && function_exists( 'wc_get_product' ) ) ? wc_get_product( $pid ) : null;
$name    = $product ? $product->get_name() : '';
$has_img = is_readable( EW_THEME_DIR . '/assets/img/hero/' . $slug . '-1000.png' );
$meta    = $has_img ? (array) json_decode( (string) file_get_contents( EW_THEME_DIR . '/assets/img/hero/' . $slug . '.json' ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
$w1000   = (int) ( $meta['files']['1000']['w'] ?? 296 );
$w560    = (int) ( $meta['files']['560']['w'] ?? 166 );
$src     = static fn( string $ext ) => esc_url( ew_theme_asset_uri( 'assets/img/hero/' . $slug . '-560.' . $ext ) ) . ' ' . $w560 . 'w, ' . esc_url( ew_theme_asset_uri( 'assets/img/hero/' . $slug . '-1000.' . $ext ) ) . ' ' . $w1000 . 'w';
$sizes   = '(min-width: 1024px) 220px, 150px';
$has_3d  = $has_img && is_readable( EW_THEME_DIR . '/assets/3d/' . $profile . '.profile.json' ) && is_readable( EW_THEME_DIR . '/assets/img/hero/' . $slug . '-texture.webp' );
?>
<section class="ew-hero" aria-labelledby="ew-hero-title">
	<div class="ew-container ew-hero__grid">
		<div class="ew-hero__text">
			<?php $eyebrow = ew_theme_home_text( 'hero_eyebrow' ); ?>
			<?php if ( $eyebrow ) : ?><p class="ew-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h1 class="ew-hero__title" id="ew-hero-title"><?php echo esc_html( ew_theme_home_text( 'hero_title' ) ?: get_bloginfo( 'name' ) ); ?></h1>
			<?php $lead = ew_theme_home_text( 'hero_lead' ); ?>
			<?php if ( $lead ) : ?><p class="ew-hero__lead ew-lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
			<p class="ew-cluster ew-hero__ctas">
				<a class="ew-btn ew-btn--lg" href="#ew-home-finder"><?php echo ew_theme_ui_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Dobierz produkt', 'eurowet-2026' ); ?></a>
				<a class="ew-btn ew-btn--lg ew-btn--secondary" href="<?php echo esc_url( ew_theme_url( 'products' ) ); ?>"><?php esc_html_e( 'Zobacz produkty', 'eurowet-2026' ); ?></a>
			</p>
		</div>
		<?php if ( $has_img ) : ?>
			<figure class="ew-hero__stage" <?php echo $has_3d ? 'data-ew-hero3d data-profile="' . esc_url( ew_theme_asset_uri( 'assets/3d/' . $profile . '.profile.json' ) ) . '" data-texture="' . esc_url( ew_theme_asset_uri( 'assets/img/hero/' . $slug . '-texture.webp' ) ) . '" data-three="' . esc_url( ew_theme_asset_uri( 'assets/vendor/three/three.module.min.js' ) ) . '"' : ''; ?>>
				<div class="ew-hero__halo" aria-hidden="true"></div>
				<picture class="ew-hero__packshot">
					<source type="image/avif" srcset="<?php echo $src( 'avif' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" sizes="<?php echo esc_attr( $sizes ); ?>">
					<source type="image/webp" srcset="<?php echo $src( 'webp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" sizes="<?php echo esc_attr( $sizes ); ?>">
					<img src="<?php echo esc_url( ew_theme_asset_uri( 'assets/img/hero/' . $slug . '-1000.png' ) ); ?>" srcset="<?php echo $src( 'png' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" sizes="<?php echo esc_attr( $sizes ); ?>" width="<?php echo (int) $w1000; ?>" height="1000" alt="<?php echo esc_attr( $name ? sprintf( /* translators: %s product name */ __( '%s — opakowanie produktu', 'eurowet-2026' ), $name ) : __( 'Opakowanie produktu Eurowet', 'eurowet-2026' ) ); ?>" fetchpriority="high" decoding="async">
				</picture>
				<?php if ( $product ) : ?>
					<figcaption class="ew-hero__caption">
						<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><span class="ew-hero__caption-name"><?php echo esc_html( $name ); ?></span>
						<?php $sub = (string) ew_theme_meta( $product->get_id(), '_ew_subtitle', '' ); ?>
						<?php if ( $sub ) : ?><span class="ew-hero__caption-sub"><?php echo esc_html( ew_theme_sentence_case( $sub ) ); ?></span><?php endif; ?></a>
					</figcaption>
				<?php endif; ?>
				<?php if ( $has_3d ) : ?>
					<button type="button" class="ew-hero__motion ew-btn ew-btn--ghost ew-btn--sm" data-ew-hero3d-toggle hidden aria-pressed="false" data-label-pause="<?php esc_attr_e( 'Zatrzymaj obrót', 'eurowet-2026' ); ?>" data-label-play="<?php esc_attr_e( 'Wznów obrót', 'eurowet-2026' ); ?>"><?php echo ew_theme_ui_icon( 'rotate' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span data-ew-hero3d-label><?php esc_html_e( 'Zatrzymaj obrót', 'eurowet-2026' ); ?></span></button>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</div>
</section>
