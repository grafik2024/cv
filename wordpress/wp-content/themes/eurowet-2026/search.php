<?php
/**
 * Site search. The query first goes through the Product Finder (controlled needs → verified products),
 * then ordinary content results follow.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

$finder = function_exists( 'ew_finder_for_search' ) ? ew_finder_for_search() : null;
/* translators: %s search query */
ew_theme_page_header( sprintf( __( 'Wyniki wyszukiwania: %s', 'eurowet-2026' ), get_search_query() ) );
?>
<div class="ew-section ew-section--tight">
	<div class="ew-container ew-container--narrow">
		<?php echo ew_theme_render( 'finder', array( 'variant' => 'page', 'value' => get_search_query( false ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $finder ) : ?>
			<div class="ew-finder-server" data-ew-finder-server><?php echo ew_theme_render( 'finder-results', array( 'result' => $finder ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</div>
</div>
<section class="ew-section ew-section--tight" aria-labelledby="ew-search-content">
	<div class="ew-container">
		<h2 id="ew-search-content"><?php esc_html_e( 'Strony, produkty i porady', 'eurowet-2026' ); ?></h2>
		<?php if ( have_posts() ) : ?>
			<ul class="ew-search-list ew-list-reset" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					$type = get_post_type_object( (string) get_post_type() );
					?>
					<li class="ew-search-list__item">
						<p class="ew-eyebrow"><?php echo esc_html( 'product' === get_post_type() ? __( 'Produkt', 'eurowet-2026' ) : ( $type ? $type->labels->singular_name : '' ) ); ?></p>
						<h3 class="ew-h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php ew_theme_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nie znaleźliśmy stron zawierających te słowa. Spróbuj opisać problem innymi słowami albo przejdź do porad.', 'eurowet-2026' ); ?></p>
			<p class="ew-cluster"><a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( ew_theme_url( 'guides' ) ); ?>"><?php esc_html_e( 'Porady i wiedza', 'eurowet-2026' ); ?></a> <a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'products' ) ); ?>"><?php esc_html_e( 'Wszystkie produkty', 'eurowet-2026' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
