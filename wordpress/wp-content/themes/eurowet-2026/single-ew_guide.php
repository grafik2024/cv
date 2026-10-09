<?php
/**
 * Single guide (Porady i wiedza). Order fixed by the content model (brief §21/§27):
 * H1 → byline/dates → "W skrócie" → image → article (causes → care → avoid) → vet notice
 * → matching products → FAQ → sources → related guides → next best article.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$gid      = get_the_ID();
	$hub      = ew_theme_primary_term( $gid, 'ew_hub' );
	$tldr     = (string) ew_theme_meta( $gid, '_ew_tldr', '' );
	$content  = ew_theme_content_toc( (string) apply_filters( 'the_content', get_the_content() ) );
	$products = ew_theme_products_from_ids( (array) ew_theme_meta( $gid, '_ew_products', array() ) );
	$faq      = (array) ew_theme_meta( $gid, '_ew_faq', array() );
	$sources  = (array) ew_theme_meta( $gid, '_ew_sources', array() );
	$related  = function_exists( 'ew_related_guides' ) ? ew_related_guides( $gid, 3 ) : array();
	?>
	<article <?php post_class( 'ew-article' ); ?> aria-labelledby="ew-article-title">
		<header class="ew-article__header">
			<div class="ew-container">
				<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $hub ) : ?>
					<p class="ew-eyebrow"><a href="<?php echo esc_url( get_term_link( $hub ) ); ?>"><?php echo esc_html( $hub->name ); ?></a></p>
				<?php endif; ?>
				<h1 class="ew-article__title" id="ew-article-title"><?php the_title(); ?></h1>
				<?php echo ew_theme_render( 'article-meta', array( 'post' => $gid ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</header>

		<div class="ew-container ew-article__layout">
			<div class="ew-article__main">
				<?php echo ew_theme_render( 'tldr', array( 'text' => '' !== trim( wp_strip_all_tags( $tldr ) ) ? $tldr : (string) get_the_excerpt() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

				<?php echo ew_theme_featured_figure( $gid, 'hero', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

				<div class="ew-prose ew-article__body">
					<?php echo $content['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?>
				</div>

				<?php
				if ( ew_theme_meta( $gid, '_ew_red_flag', false ) ) {
					echo ew_theme_render( 'vet-notice', array( 'level' => 'caution', 'items' => (array) ew_theme_meta( $gid, '_ew_red_flags', array() ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}

				if ( $products ) {
					echo ew_theme_render( // phpcs:ignore WordPress.Security.EscapeOutput
						'product-grid',
						array(
							'products' => array_slice( $products, 0, 4 ),
							'heading'  => __( 'Produkty Eurowet związane z tematem', 'eurowet-2026' ),
							'id'       => 'ew-guide-products',
							'context'  => 'guide',
						)
					);
				}

				echo ew_theme_render( 'faq', array( 'items' => $faq ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ew_theme_render( 'sources', array( 'items' => $sources ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>

			<aside class="ew-article__aside" aria-label="<?php esc_attr_e( 'Nawigacja po artykule', 'eurowet-2026' ); ?>">
				<div class="ew-article__sticky">
					<?php echo ew_theme_toc( $content['toc'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo ew_theme_render( 'finder', array( 'variant' => 'compact' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</aside>
		</div>

		<footer class="ew-article__footer">
			<div class="ew-container">
				<?php
				echo ew_theme_render( 'next-guide', array( 'guide_id' => $gid ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				if ( $related ) {
					echo ew_theme_render( 'guide-grid', array( 'posts' => $related, 'heading' => __( 'Powiązane porady', 'eurowet-2026' ), 'id' => 'ew-related-guides' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
