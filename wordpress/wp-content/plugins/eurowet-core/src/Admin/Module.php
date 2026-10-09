<?php
/**
 * Admin structure: client-requested section order (Produkty, Potrzeby / Problemy, Porady, Składniki,
 * Przedstawiciele, Materiały, B2B Leads, Strony, Aktualności), "Eurowet" tools menu, columns, meta boxes.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Admin;

use Eurowet\Core\Contracts\ModuleInterface;
use Eurowet\Core\Data\Meta;
use Eurowet\Core\Plugin;
use Eurowet\Core\Reps\Voivodeships;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public const PARENT = 'eurowet';

	public function register(): void {
		if ( ! is_admin() ) {
			return;
		}
		MetaBoxes::register();
		add_action( 'admin_init', array( Settings::class, 'register' ) );
		add_action( 'admin_menu', array( self::class, 'menu' ) );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( self::class, 'order' ) );
		add_filter( 'post_type_labels_post', array( self::class, 'newsLabels' ) );
		foreach ( array( 'ew_need', 'ew_guide', 'ew_rep', 'ew_lead' ) as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( self::class, 'columns_' . $pt ) );
			add_action( "manage_{$pt}_posts_custom_column", array( self::class, 'column' ), 10, 2 );
		}
	}

	public static function menu(): void {
		add_menu_page( 'Eurowet', 'Eurowet', 'edit_others_posts', self::PARENT, array( self::class, 'dashboard' ), 'dashicons-pets', 3 );
		add_submenu_page( self::PARENT, __( 'Przegląd', 'eurowet-core' ), __( 'Przegląd', 'eurowet-core' ), 'edit_others_posts', self::PARENT, array( self::class, 'dashboard' ) );
		do_action( 'ew_admin_menu', self::PARENT );
		add_submenu_page( self::PARENT, __( 'Ustawienia', 'eurowet-core' ), __( 'Ustawienia', 'eurowet-core' ), 'manage_options', Settings::PAGE, array( Settings::class, 'render' ) );
	}

	/** @param string[] $order */
	public static function order( $order ): array {
		$wanted = array( 'index.php', self::PARENT, 'edit.php?post_type=product', 'edit.php?post_type=ew_need', 'edit.php?post_type=ew_guide', 'edit.php?post_type=ew_ingredient', 'edit.php?post_type=ew_rep', 'edit.php?post_type=ew_material', 'edit.php?post_type=ew_lead', 'edit.php?post_type=page', 'edit.php', 'separator1' );
		$order  = is_array( $order ) ? $order : array();
		return array_values( array_unique( array_merge( array_values( array_intersect( $wanted, $order ) ), $order ) ) );
	}

	public static function newsLabels( $labels ) {
		$labels->name          = __( 'Aktualności', 'eurowet-core' );
		$labels->menu_name     = __( 'Aktualności', 'eurowet-core' );
		$labels->all_items     = __( 'Wszystkie aktualności', 'eurowet-core' );
		$labels->singular_name = __( 'Aktualność', 'eurowet-core' );
		$labels->add_new_item  = __( 'Dodaj aktualność', 'eurowet-core' );
		return $labels;
	}

	public static function dashboard(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'Eurowet — przegląd systemu', 'eurowet-core' ) . '</h1>';
		$counts = array();
		foreach ( array( 'product' => __( 'Produkty', 'eurowet-core' ), 'ew_need' => __( 'Potrzeby / problemy', 'eurowet-core' ), 'ew_guide' => __( 'Porady', 'eurowet-core' ), 'ew_ingredient' => __( 'Składniki', 'eurowet-core' ), 'ew_rep' => __( 'Przedstawiciele', 'eurowet-core' ), 'ew_material' => __( 'Materiały', 'eurowet-core' ), 'ew_lead' => __( 'Leady B2B', 'eurowet-core' ) ) as $pt => $label ) {
			$c        = wp_count_posts( $pt );
			$counts[] = sprintf( '<li><a href="%s">%s</a>: <strong>%d</strong></li>', esc_url( admin_url( 'edit.php?post_type=' . $pt ) ), esc_html( $label ), (int) ( $c->publish ?? 0 ) + (int) ( $c->private ?? 0 ) );
		}
		echo '<ul>' . implode( '', $counts ) . '</ul><h2>' . esc_html__( 'Moduły', 'eurowet-core' ) . '</h2><table class="widefat striped"><tbody>'; // phpcs:ignore WordPress.Security.EscapeOutput
		foreach ( Plugin::instance()->status() as $id => $s ) {
			printf( '<tr><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $id ), esc_html( $s['state'] ), esc_html( $s['error'] ) );
		}
		echo '</tbody></table></div>';
	}

	public static function columns_ew_need( $c ) {
		return array_merge( array_slice( $c, 0, 2 ), array( 'ew_species' => __( 'Gatunki', 'eurowet-core' ), 'ew_products' => __( 'Produkty', 'eurowet-core' ), 'ew_priority' => __( 'Priorytet', 'eurowet-core' ), 'ew_active' => __( 'Aktywna', 'eurowet-core' ) ), array_slice( $c, 2 ) );
	}

	public static function columns_ew_guide( $c ) {
		return array_merge( array_slice( $c, 0, 2 ), array( 'ew_hub' => __( 'Hub', 'eurowet-core' ), 'ew_reviewed' => __( 'Weryfikacja', 'eurowet-core' ) ), array_slice( $c, 2 ) );
	}

	public static function columns_ew_rep( $c ) {
		return array_merge( array_slice( $c, 0, 2 ), array( 'ew_voiv' => __( 'Województwa', 'eurowet-core' ), 'ew_active' => __( 'Aktywny', 'eurowet-core' ) ), array_slice( $c, 2 ) );
	}

	public static function columns_ew_lead( $c ) {
		return array_merge( array_slice( $c, 0, 2 ), array( 'ew_form' => __( 'Formularz', 'eurowet-core' ), 'ew_company' => __( 'Firma', 'eurowet-core' ), 'ew_status' => __( 'Status', 'eurowet-core' ) ), array_slice( $c, 2 ) );
	}

	public static function column( string $col, int $id ): void {
		switch ( $col ) {
			case 'ew_species':
				echo esc_html( implode( ', ', wp_get_post_terms( $id, 'ew_species', array( 'fields' => 'names' ) ) ) );
				break;
			case 'ew_products':
				echo (int) count( Meta::json( $id, '_ew_products' ) );
				break;
			case 'ew_priority':
				echo (int) Meta::int( $id, '_ew_priority' );
				break;
			case 'ew_active':
				echo ( ! metadata_exists( 'post', $id, '_ew_active' ) || Meta::bool( $id, '_ew_active' ) ) ? '✔' : '—';
				break;
			case 'ew_hub':
				echo esc_html( implode( ', ', wp_get_post_terms( $id, 'ew_hub', array( 'fields' => 'names' ) ) ) );
				break;
			case 'ew_reviewed':
				$d = (string) Meta::get( $id, '_ew_reviewed', '' );
				echo esc_html( $d ?: '—' ) . ( Meta::bool( $id, '_ew_needs_review' ) ? ' <span class="dashicons dashicons-warning" title="' . esc_attr__( 'Wymaga sprawdzenia', 'eurowet-core' ) . '"></span>' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
				break;
			case 'ew_voiv':
				echo esc_html( implode( ', ', array_filter( array_map( array( Voivodeships::class, 'name' ), (array) Meta::json( $id, '_ew_voivodeships' ) ) ) ) );
				break;
			case 'ew_form':
				echo esc_html( (string) Meta::get( $id, '_ew_form', '' ) );
				break;
			case 'ew_company':
				echo esc_html( (string) Meta::get( $id, '_ew_company', '' ) );
				break;
			case 'ew_status':
				echo esc_html( (string) Meta::get( $id, '_ew_status', 'new' ) );
				break;
		}
	}
}
