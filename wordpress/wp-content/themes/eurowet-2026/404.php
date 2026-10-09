<?php
/**
 * 404 — helpful recovery: finder, main sections. (Old URLs are covered by the 301 map in eurowet-core.)
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();
ew_theme_page_header( __( 'Nie znaleźliśmy tej strony', 'eurowet-2026' ), __( 'Strona mogła zostać przeniesiona albo adres zawiera błąd. Opisz, czego szukasz, albo wybierz jeden z działów.', 'eurowet-2026' ), '404' );
?>
<div class="ew-section ew-section--tight">
	<div class="ew-container ew-container--narrow ew-stack">
		<?php echo ew_theme_render( 'finder', array( 'variant' => 'page' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<nav aria-label="<?php esc_attr_e( 'Główne działy', 'eurowet-2026' ); ?>">
			<ul class="ew-chips ew-list-reset">
				<li><a class="ew-chip" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Strona główna', 'eurowet-2026' ); ?></a></li>
				<li><a class="ew-chip" href="<?php echo esc_url( ew_theme_url( 'products' ) ); ?>"><?php esc_html_e( 'Produkty', 'eurowet-2026' ); ?></a></li>
				<li><a class="ew-chip" href="<?php echo esc_url( ew_theme_url( 'needs' ) ); ?>"><?php esc_html_e( 'Dobierz produkt', 'eurowet-2026' ); ?></a></li>
				<li><a class="ew-chip" href="<?php echo esc_url( ew_theme_url( 'guides' ) ); ?>"><?php esc_html_e( 'Porady i wiedza', 'eurowet-2026' ); ?></a></li>
				<li><a class="ew-chip" href="<?php echo esc_url( ew_theme_url( 'contact' ) ); ?>"><?php esc_html_e( 'Kontakt', 'eurowet-2026' ); ?></a></li>
			</ul>
		</nav>
	</div>
</div>
<?php
get_footer();
