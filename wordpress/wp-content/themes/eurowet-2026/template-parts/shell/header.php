<?php
/**
 * Header bar: logo, primary navigation (mega menu), search (finder dialog), language, theme, a11y, cart.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$cart_count = ( ew_theme_has_woo() && function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$cart_url   = ew_theme_has_woo() ? wc_get_cart_url() : '';
?>
<header class="ew-header" data-ew-header>
	<div class="ew-container ew-header__bar">
		<a class="ew-header__logo" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" rel="home">
			<img class="ew-logo ew-logo--light" src="<?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-360.png' ) ); ?>" srcset="<?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-180.png' ) ); ?> 180w, <?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-360.png' ) ); ?> 360w, <?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-540.png' ) ); ?> 540w" sizes="180px" width="180" height="37" alt="<?php esc_attr_e( 'EUROWET — strona główna', 'eurowet-2026' ); ?>">
			<img class="ew-logo ew-logo--dark" src="<?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-white-360.png' ) ); ?>" srcset="<?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-white-180.png' ) ); ?> 180w, <?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-white-360.png' ) ); ?> 360w" sizes="180px" width="180" height="37" alt="" aria-hidden="true">
		</a>
		<nav class="ew-nav" aria-label="<?php esc_attr_e( 'Menu główne', 'eurowet-2026' ); ?>" id="ew-nav" data-ew-nav>
			<?php ew_theme_primary_menu(); ?>
		</nav>
		<div class="ew-header__tools">
			<button type="button" class="ew-tool" data-ew-search-open aria-haspopup="dialog">
				<?php echo ew_theme_ui_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-tool__label"><?php esc_html_e( 'Szukaj', 'eurowet-2026' ); ?></span>
			</button>
			<?php echo ew_theme_render( 'language-picker' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<button type="button" class="ew-tool" data-ew-theme-toggle aria-label="<?php esc_attr_e( 'Motyw kolorystyczny', 'eurowet-2026' ); ?>">
				<span class="ew-theme-icon ew-theme-icon--light"><?php echo ew_theme_ui_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<span class="ew-theme-icon ew-theme-icon--dark"><?php echo ew_theme_ui_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<span class="ew-visually-hidden" data-ew-theme-label></span>
			</button>
			<button type="button" class="ew-tool" data-ew-a11y-open aria-expanded="false" aria-controls="ew-a11y-panel">
				<?php echo ew_theme_ui_icon( 'a11y' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-tool__label"><?php esc_html_e( 'Dostępność', 'eurowet-2026' ); ?></span>
			</button>
			<?php if ( $cart_url ) : ?>
				<a class="ew-tool ew-tool--cart" href="<?php echo esc_url( $cart_url ); ?>">
					<?php echo ew_theme_ui_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="ew-visually-hidden"><?php esc_html_e( 'Koszyk', 'eurowet-2026' ); ?>, </span>
					<span class="ew-cart-count" <?php echo $cart_count ? '' : 'hidden'; ?>><?php echo (int) $cart_count; ?><span class="ew-visually-hidden"> <?php esc_html_e( 'produktów', 'eurowet-2026' ); ?></span></span>
				</a>
			<?php endif; ?>
			<button type="button" class="ew-tool ew-tool--menu" data-ew-drawer-open aria-expanded="false" aria-controls="ew-drawer">
				<?php echo ew_theme_ui_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-visually-hidden"><?php esc_html_e( 'Menu', 'eurowet-2026' ); ?></span>
			</button>
		</div>
	</div>
	<?php get_template_part( 'template-parts/shell/a11y-panel' ); ?>
</header>
<dialog class="ew-drawer" id="ew-drawer" aria-label="<?php esc_attr_e( 'Menu', 'eurowet-2026' ); ?>" data-ew-drawer>
	<div class="ew-drawer__head">
		<span class="ew-drawer__title"><?php esc_html_e( 'Menu', 'eurowet-2026' ); ?></span>
		<button type="button" class="ew-tool" data-ew-drawer-close><?php echo ew_theme_ui_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-visually-hidden"><?php esc_html_e( 'Zamknij menu', 'eurowet-2026' ); ?></span></button>
	</div>
	<nav class="ew-drawer__nav" aria-label="<?php esc_attr_e( 'Menu główne (mobilne)', 'eurowet-2026' ); ?>"><?php ew_theme_primary_menu(); ?></nav>
</dialog>
<dialog class="ew-search" aria-label="<?php esc_attr_e( 'Szukaj i dobierz produkt', 'eurowet-2026' ); ?>" data-ew-search>
	<div class="ew-search__inner">
		<button type="button" class="ew-tool ew-search__close" data-ew-search-close><?php echo ew_theme_ui_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-visually-hidden"><?php esc_html_e( 'Zamknij wyszukiwanie', 'eurowet-2026' ); ?></span></button>
		<?php
		$finder = ew_theme_render( 'finder', array( 'variant' => 'compact' ) );
		echo $finder ? $finder : get_search_form( array( 'echo' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>
	</div>
</dialog>
