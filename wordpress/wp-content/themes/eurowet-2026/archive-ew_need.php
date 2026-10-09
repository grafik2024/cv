<?php
/**
 * Product Finder page (/potrzeby/): natural-language search with server-rendered results (works without JS),
 * then the controlled list of needs grouped by area. Results never invent products: they come only from
 * verified need → product relations (eurowet-core Finder\Service).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

$result = function_exists( 'ew_finder_result_from_request' ) ? ew_finder_result_from_request() : null;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search.
$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';

ew_theme_page_header(
	__( 'Dobierz produkt do potrzeby zwierzęcia', 'eurowet-2026' ),
	ew_theme_home_text( 'finder_lead' ),
	__( 'Wyszukiwarka potrzeb', 'eurowet-2026' )
);
?>

<section class="ew-section ew-section--tight" aria-label="<?php esc_attr_e( 'Wyszukiwarka', 'eurowet-2026' ); ?>">
	<div class="ew-container ew-container--narrow">
		<?php echo ew_theme_render( 'finder', array( 'variant' => 'page', 'value' => $q ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $result ) : ?>
			<div class="ew-finder-server" data-ew-finder-server>
				<?php echo ew_theme_render( 'finder-results', array( 'result' => $result ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
// Controlled taxonomy: every active need, grouped by area (crawlable entry points to need pages).
$areas = get_terms( array( 'taxonomy' => 'ew_area', 'hide_empty' => true ) );
$areas = is_array( $areas ) ? $areas : array();
usort( $areas, static fn( $a, $b ) => (int) ew_theme_term_meta( $a->term_id, 'order', 0 ) <=> (int) ew_theme_term_meta( $b->term_id, 'order', 0 ) );
$shown = array();
?>
<section class="ew-section ew-section--alt" aria-labelledby="ew-needs-all">
	<div class="ew-container">
		<div class="ew-section__header">
			<h2 id="ew-needs-all"><?php esc_html_e( 'Przeglądaj potrzeby', 'eurowet-2026' ); ?></h2>
		</div>
		<?php
		foreach ( $areas as $area ) :
			$needs = get_posts(
				array(
					'post_type'      => 'ew_need',
					'post_status'    => 'publish',
					'posts_per_page' => 50,
					'no_found_rows'  => true,
					'tax_query'      => array( array( 'taxonomy' => 'ew_area', 'terms' => $area->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_key'       => '_ew_priority', // phpcs:ignore WordPress.DB.SlowDBQuery
					'orderby'        => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
				)
			);
			$needs = array_values( array_filter( $needs, static fn( $n ) => (bool) ew_theme_meta( $n->ID, '_ew_active', true ) && ! isset( $shown[ $n->ID ] ) ) );
			if ( ! $needs ) {
				continue;
			}
			foreach ( $needs as $n ) {
				$shown[ $n->ID ] = true;
			}
			echo ew_theme_render( 'need-tiles', array( 'needs' => $needs, 'heading' => $area->name, 'heading_level' => 3 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		endforeach;

		// Needs without an area.
		if ( have_posts() ) {
			$rest = array();
			while ( have_posts() ) {
				the_post();
				if ( ! isset( $shown[ get_the_ID() ] ) && (bool) ew_theme_meta( get_the_ID(), '_ew_active', true ) ) {
					$rest[] = get_post();
				}
			}
			if ( $rest ) {
				echo ew_theme_render( 'need-tiles', array( 'needs' => $rest, 'heading' => $shown ? __( 'Inne potrzeby', 'eurowet-2026' ) : __( 'Potrzeby', 'eurowet-2026' ), 'heading_level' => 3 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
		?>
	</div>
</section>

<section class="ew-section" aria-labelledby="ew-finder-help">
	<div class="ew-container ew-container--narrow">
		<h2 id="ew-finder-help"><?php esc_html_e( 'Jak działa wyszukiwarka', 'eurowet-2026' ); ?></h2>
		<div class="ew-prose">
			<p><?php esc_html_e( 'Wyszukiwarka dopasowuje Twój opis do listy potrzeb przygotowanej przez Eurowet, a następnie pokazuje wyłącznie produkty, których przeznaczenie potwierdza opis producenta. Nie stawia diagnozy i nie zastępuje wizyty u lekarza weterynarii.', 'eurowet-2026' ); ?></p>
			<p><?php esc_html_e( 'Wpisywane zapytania zapisujemy anonimowo (bez adresu IP i plików cookie), aby uzupełniać porady o tematy, których brakuje.', 'eurowet-2026' ); ?></p>
		</div>
		<?php echo ew_theme_render( 'vet-notice', array( 'level' => 'caution' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>

<?php
get_footer();
