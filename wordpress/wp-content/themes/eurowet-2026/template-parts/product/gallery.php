<?php
/**
 * Product media: main packshot on the stage, thumbnails (links to full size; JS swaps the main image),
 * optional 360° image sequence (_ew_spin360, loaded on demand) and video (_ew_video) with transcript.
 * Args: product, spin (int[]), video (int).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}
$main  = (int) $product->get_image_id();
$ids   = array_values( array_unique( array_filter( array_merge( array( $main ), array_map( 'intval', $product->get_gallery_image_ids() ) ) ) ) );
$spin  = array_values( array_filter( array_map( 'intval', (array) ( $args['spin'] ?? array() ) ) ) );
$video = (int) ( $args['video'] ?? 0 );
$name  = $product->get_name();
?>
<div class="ew-product__media" data-ew-gallery>
	<figure class="ew-product__stage">
		<?php
		if ( $main ) {
			echo ew_theme_image( $main, 'ew-stage', 'stage', array( 'fetchpriority' => 'high', 'data-ew-gallery-main' => '1', 'alt' => trim( (string) get_post_meta( $main, '_wp_attachment_image_alt', true ) ) ?: $name ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo '<div class="ew-product__noimage">' . ew_theme_ui_icon( 'paw' ) . '<span class="ew-visually-hidden">' . esc_html__( 'Brak zdjęcia produktu', 'eurowet-2026' ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
		<?php if ( count( $spin ) >= 8 ) : ?>
			<div class="ew-spin" data-ew-spin='<?php echo esc_attr( (string) wp_json_encode( array_map( static fn( $i ) => wp_get_attachment_image_url( $i, 'ew-stage' ), $spin ) ) ); ?>' hidden>
				<img class="ew-spin__img" alt="<?php echo esc_attr( sprintf( /* translators: %s product name */ __( '%s — widok 360°', 'eurowet-2026' ), $name ) ); ?>" width="600" height="600" draggable="false">
				<input class="ew-spin__range" type="range" min="0" max="<?php echo (int) ( count( $spin ) - 1 ); ?>" value="0" aria-label="<?php esc_attr_e( 'Obróć produkt', 'eurowet-2026' ); ?>">
			</div>
		<?php endif; ?>
	</figure>

	<?php if ( count( $ids ) > 1 ) : ?>
		<ul class="ew-product__thumbs ew-list-reset" aria-label="<?php esc_attr_e( 'Zdjęcia produktu', 'eurowet-2026' ); ?>">
			<?php foreach ( $ids as $i => $aid ) : ?>
				<?php $full = wp_get_attachment_image_src( $aid, 'ew-stage' ); ?>
				<li><a class="ew-product__thumb" href="<?php echo esc_url( (string) wp_get_attachment_url( $aid ) ); ?>" data-ew-gallery-src="<?php echo esc_url( $full ? $full[0] : '' ); ?>" data-ew-gallery-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $aid, 'ew-stage' ) ); ?>" <?php echo 0 === $i ? 'aria-current="true"' : ''; ?>><?php echo ew_theme_image( $aid, 'thumbnail', 'thumb', array( 'alt' => sprintf( /* translators: 1: number 2: product */ __( 'Zdjęcie %1$d: %2$s', 'eurowet-2026' ), $i + 1, $name ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( count( $spin ) >= 8 || $video ) : ?>
		<p class="ew-cluster ew-product__media-tools">
			<?php if ( count( $spin ) >= 8 ) : ?>
				<button type="button" class="ew-btn ew-btn--ghost ew-btn--sm ew-requires-js" data-ew-spin-open aria-pressed="false"><?php echo ew_theme_ui_icon( 'rotate' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Widok 360°', 'eurowet-2026' ); ?></button>
			<?php endif; ?>
			<?php if ( $video ) : ?>
				<a class="ew-btn ew-btn--ghost ew-btn--sm" href="#wideo"><?php echo ew_theme_ui_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Wideo', 'eurowet-2026' ); ?></a>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( $video && wp_get_attachment_url( $video ) ) : ?>
		<?php $poster = (int) get_post_thumbnail_id( $video ); ?>
		<section class="ew-product__video" id="wideo" aria-labelledby="wideo-h">
			<h2 id="wideo-h" class="ew-h4"><?php echo esc_html( get_the_title( $video ) ?: __( 'Wideo', 'eurowet-2026' ) ); ?></h2>
			<video controls preload="none" playsinline <?php echo $poster ? 'poster="' . esc_url( (string) wp_get_attachment_image_url( $poster, 'ew-stage' ) ) . '"' : ''; ?> width="1280" height="720">
				<source src="<?php echo esc_url( (string) wp_get_attachment_url( $video ) ); ?>" type="<?php echo esc_attr( (string) get_post_mime_type( $video ) ); ?>">
			</video>
			<?php $transcript = (string) ew_theme_meta( $product->get_id(), '_ew_video_transcript', '' ); ?>
			<?php if ( '' !== trim( wp_strip_all_tags( $transcript ) ) ) : ?>
				<details class="ew-disclosure"><summary><?php esc_html_e( 'Transkrypcja', 'eurowet-2026' ); ?></summary><div class="ew-disclosure__body ew-prose"><?php echo wp_kses_post( $transcript ); ?></div></details>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</div>
