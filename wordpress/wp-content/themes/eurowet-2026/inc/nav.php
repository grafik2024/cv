<?php
/**
 * Primary navigation: accessible disclosure mega menu (buttons with aria-expanded; no hover-only traps).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Walker producing: <li><a>Top</a><button aria-expanded aria-controls>…</button><div class="ew-mega"><ul>…</ul></div></li>
 */
class Ew_Theme_Mega_Walker extends Walker_Nav_Menu {

	private int $panel = 0;

	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		if ( 0 === $depth ) {
			++$this->panel;
			$output .= '<div class="ew-mega" id="ew-mega-' . $this->panel . '" hidden><ul class="ew-mega__list">';
			return;
		}
		$output .= '<ul class="ew-mega__sub">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= 0 === $depth ? '</ul></div>' : '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
		$current      = in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current-menu-ancestor', (array) $item->classes, true );
		$output      .= '<li class="ew-nav__item' . ( $has_children ? ' has-children' : '' ) . '">';
		$output      .= sprintf(
			'<a class="%s" href="%s"%s>%s</a>',
			0 === $depth ? 'ew-nav__link' : 'ew-mega__link',
			esc_url( (string) $item->url ),
			$current && in_array( 'current-menu-item', (array) $item->classes, true ) ? ' aria-current="page"' : '',
			esc_html( (string) $item->title )
		);
		if ( 0 === $depth && $has_children ) {
			$output .= sprintf(
				'<button type="button" class="ew-nav__toggle" aria-expanded="false" aria-controls="ew-mega-%d"><span class="ew-visually-hidden">%s</span>%s</button>',
				$this->panel + 1,
				/* translators: %s: menu item */
				esc_html( sprintf( __( 'Rozwiń: %s', 'eurowet-2026' ), $item->title ) ),
				ew_theme_ui_icon( 'chevron' )
			);
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ): void {
		$output .= '</li>';
	}
}

/**
 * Fallback information architecture when no menu is assigned (docs/ARCHITECTURE.md).
 */
function ew_theme_fallback_menu(): void {
	$items = array(
		array( ew_theme_url( 'products' ), __( 'Produkty', 'eurowet-2026' ) ),
		array( ew_theme_url( 'needs' ), __( 'Dobierz produkt', 'eurowet-2026' ) ),
		array( ew_theme_url( 'guides' ), __( 'Porady i wiedza', 'eurowet-2026' ) ),
		array( ew_theme_url( 'ingredients' ), __( 'Składniki', 'eurowet-2026' ) ),
		array( ew_theme_url( 'b2b' ), __( 'B2B', 'eurowet-2026' ) ),
		array( ew_theme_url( 'private_label' ), __( 'Marka własna', 'eurowet-2026' ) ),
		array( ew_theme_url( 'contact' ), __( 'Kontakt', 'eurowet-2026' ) ),
	);
	echo '<ul class="ew-nav__list">';
	foreach ( $items as [ $url, $label ] ) {
		printf( '<li class="ew-nav__item"><a class="ew-nav__link" href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

function ew_theme_primary_menu(): void {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'ew-nav__list',
				'depth'          => 3,
				'walker'         => new Ew_Theme_Mega_Walker(),
				'fallback_cb'    => 'ew_theme_fallback_menu',
			)
		);
		return;
	}
	ew_theme_fallback_menu();
}
