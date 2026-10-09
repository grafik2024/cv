<?php
/**
 * Elementor integration: an "Eurowet" widget category whose widgets render the same components as the
 * PHP templates (single source of markup). Works with free Elementor; Pro adds Theme Builder on top.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Elementor;

use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	/** component => [title, icon, controls{name: [label, type, options?]}] */
	public const WIDGETS = array(
		'finder'            => array( 'Dobierz produkt (Finder)', 'eicon-search', array( 'variant' => array( 'Wariant', 'select', array( 'hero' => 'Hero', 'page' => 'Strona', 'compact' => 'Kompaktowy' ) ) ) ),
		'need-tiles'        => array( 'Najczęstsze potrzeby', 'eicon-gallery-grid', array( 'heading' => array( 'Nagłówek', 'text' ), 'limit' => array( 'Liczba', 'number' ), 'species' => array( 'Gatunek (slug)', 'text' ), 'area' => array( 'Obszar (slug)', 'text' ) ) ),
		'product-grid'      => array( 'Siatka produktów', 'eicon-products', array( 'heading' => array( 'Nagłówek', 'text' ), 'category' => array( 'Kategoria (slug)', 'text' ), 'featured' => array( 'Tylko polecane', 'switcher' ), 'limit' => array( 'Liczba', 'number' ) ) ),
		'guide-grid'        => array( 'Siatka porad', 'eicon-posts-grid', array( 'heading' => array( 'Nagłówek', 'text' ), 'hub' => array( 'Hub (slug)', 'text' ), 'limit' => array( 'Liczba', 'number' ) ) ),
		'rep-finder'        => array( 'Znajdź przedstawiciela', 'eicon-map-pin', array( 'variant' => array( 'Wariant', 'select', array( 'full' => 'Mapa + lista', 'compact' => 'Lista wyboru' ) ) ) ),
		'lead-form'         => array( 'Formularz B2B', 'eicon-form-horizontal', array( 'form' => array( 'Formularz', 'select', array( 'b2b' => 'B2B', 'private_label' => 'Marka własna', 'rep_contact' => 'Kontakt z PH', 'contact' => 'Kontakt' ) ) ) ),
		'material-list'     => array( 'Materiały do pobrania', 'eicon-download-button', array( 'type' => array( 'Typ (slug)', 'text' ) ) ),
		'ingredient-list'   => array( 'Składniki', 'eicon-bullet-list', array() ),
		'vet-notice'        => array( 'Kiedy do weterynarza', 'eicon-alert', array( 'level' => array( 'Poziom', 'select', array( 'caution' => 'Ostrożność', 'urgent' => 'Pilne' ) ) ) ),
		'language-picker'   => array( 'Wybór języka', 'eicon-globe', array() ),
	);

	public function register(): void {
		add_action( 'elementor/elements/categories_registered', static fn( $m ) => $m->add_category( 'eurowet', array( 'title' => 'Eurowet', 'icon' => 'fa fa-paw' ) ) );
		add_action( 'elementor/widgets/register', array( self::class, 'widgets' ) );
		add_action( 'init', array( self::class, 'shortcode' ) );
	}

	public static function shortcode(): void {
		if ( shortcode_exists( 'ew_component' ) ) {
			return;
		}
		add_shortcode(
			'ew_component',
			static function ( $atts ): string {
				$atts = is_array( $atts ) ? array_map( 'sanitize_text_field', $atts ) : array();
				$name = sanitize_key( (string) ( $atts['name'] ?? '' ) );
				unset( $atts['name'] );
				return '' === $name ? '' : ew_render( $name, $atts );
			}
		);
	}

	public static function widgets( $manager ): void {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}
		require_once __DIR__ . '/Widget.php';
		foreach ( self::WIDGETS as $component => $def ) {
			$manager->register( new Widget( array(), array( 'ew_component' => $component ) ) );
		}
	}
}
