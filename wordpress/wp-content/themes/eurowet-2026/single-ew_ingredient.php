<?php
/**
 * Ingredient / technology page (/skladniki/{slug}/). Only properties confirmed by Eurowet product texts
 * (quoted with their product) and cited sources — no claims beyond them.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$iid      = get_the_ID();
	$inci     = (string) ew_theme_meta( $iid, '_ew_inci', '' );
	$aliases  = array_filter( array_map( 'strval', (array) ew_theme_meta( $iid, '_ew_aliases', array() ) ) );
	$summary  = (string) ew_theme_meta( $iid, '_ew_summary', '' );
	$quotes   = (array) ew_theme_meta( $iid, '_ew_function_quotes', array() );
	$sources  = (array) ew_theme_meta( $iid, '_ew_sources', array() );
	$guides   = ew_theme_posts_from_ids( (array) ew_theme_meta( $iid, '_ew_guides', array() ), 'ew_guide' );
	$products = function_exists( 'ew_ingredient_products' ) ? ew_ingredient_products( $iid ) : array();
	?>
	<article <?php post_class( 'ew-ingredient' ); ?> aria-labelledby="ew-ing-title">
		<header class="ew-page-header">
			<div class="ew-container ew-container--narrow">
				<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="ew-eyebrow"><?php esc_html_e( 'Składnik', 'eurowet-2026' ); ?></p>
				<h1 class="ew-page-header__title" id="ew-ing-title"><?php the_title(); ?></h1>
				<?php if ( $inci || $aliases ) : ?>
					<dl class="ew-meta-line">
						<?php if ( $inci ) : ?><div><dt><?php esc_html_e( 'Nazwa INCI', 'eurowet-2026' ); ?></dt><dd lang="la"><?php echo esc_html( $inci ); ?></dd></div><?php endif; ?>
						<?php if ( $aliases ) : ?><div><dt><?php esc_html_e( 'Inne nazwy', 'eurowet-2026' ); ?></dt><dd><?php echo esc_html( implode( ', ', $aliases ) ); ?></dd></div><?php endif; ?>
					</dl>
				<?php endif; ?>
			</div>
		</header>

		<div class="ew-container ew-container--narrow">
			<?php echo ew_theme_render( 'tldr', array( 'text' => $summary ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( '' !== trim( wp_strip_all_tags( (string) get_the_content() ) ) ) : ?>
				<div class="ew-prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php
			$rows = array();
			foreach ( $quotes as $q ) {
				$pid = isset( $q['product_id'] ) ? ew_theme_tr_id( (int) $q['product_id'] ) : 0;
				if ( $pid && ! empty( $q['quote'] ) && 'publish' === get_post_status( $pid ) ) {
					$rows[] = array( 'pid' => $pid, 'quote' => (string) $q['quote'] );
				}
			}
			if ( $rows ) :
				?>
				<section class="ew-ingredient__quotes" aria-labelledby="ew-ing-quotes">
					<h2 id="ew-ing-quotes"><?php esc_html_e( 'Rola składnika w produktach Eurowet', 'eurowet-2026' ); ?></h2>
					<?php foreach ( $rows as $row ) : ?>
						<figure class="ew-quote">
							<blockquote><p>„<?php echo esc_html( $row['quote'] ); ?>”</p></blockquote>
							<figcaption><?php esc_html_e( 'Opis produktu:', 'eurowet-2026' ); ?> <a href="<?php echo esc_url( get_permalink( $row['pid'] ) ); ?>"><?php echo esc_html( get_the_title( $row['pid'] ) ); ?></a></figcaption>
						</figure>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
		</div>

		<?php if ( $products ) : ?>
			<section class="ew-section ew-section--tight">
				<div class="ew-container">
					<?php echo ew_theme_render( 'product-grid', array( 'products' => $products, 'heading' => __( 'Produkty zawierające ten składnik', 'eurowet-2026' ), 'id' => 'ew-ing-products', 'context' => 'ingredient' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</section>
		<?php endif; ?>

		<div class="ew-container ew-container--narrow">
			<?php echo ew_theme_render( 'sources', array( 'items' => $sources ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php
			if ( $guides ) {
				echo ew_theme_render( 'guide-grid', array( 'posts' => $guides, 'heading' => __( 'Powiązane porady', 'eurowet-2026' ), 'id' => 'ew-ing-guides' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
			<p class="ew-ingredient__all"><a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'ingredients' ) ); ?>"><?php esc_html_e( 'Wszystkie składniki', 'eurowet-2026' ); ?></a></p>
		</div>
	</article>
	<?php
endwhile;

get_footer();
