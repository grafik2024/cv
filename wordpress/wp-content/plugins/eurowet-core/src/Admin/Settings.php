<?php
/**
 * Tabbed settings page (option 'ew_settings'). Modules add sections on the 'ew_settings_sections' action.
 * Secret fields are never echoed back.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Admin;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const OPTION = 'ew_settings';
	public const PAGE   = 'eurowet-settings';

	/** @var array<string, array{title: string, fields: array<int, array<string, mixed>>}> */
	private static array $sections = array();

	public static function addSection( string $id, string $title, array $fields ): void {
		self::$sections[ $id ] = array( 'title' => $title, 'fields' => $fields );
	}

	/** @return array<string, array{title: string, fields: array<int, array<string, mixed>>}> */
	public static function sections(): array {
		if ( ! did_action( 'ew_settings_sections' ) ) {
			do_action( 'ew_settings_sections' );
		}
		return self::$sections;
	}

	public static function register(): void {
		register_setting(
			'ew_settings_group',
			self::OPTION,
			array( 'type' => 'array', 'sanitize_callback' => array( self::class, 'sanitize' ), 'default' => array() )
		);
	}

	/** @param mixed $input */
	public static function sanitize( $input ): array {
		$old = (array) get_option( self::OPTION, array() );
		$out = $old;
		$in  = is_array( $input ) ? $input : array();
		$tab = isset( $in['__tab'] ) ? sanitize_key( (string) $in['__tab'] ) : '';
		foreach ( self::sections() as $sid => $section ) {
			if ( '' !== $tab && $tab !== $sid ) {
				continue;
			}
			foreach ( $section['fields'] as $f ) {
				$id   = (string) $f['id'];
				$type = (string) ( $f['type'] ?? 'text' );
				$v    = $in[ $id ] ?? null;
				switch ( $type ) {
					case 'checkbox':
						$out[ $id ] = empty( $v ) ? 0 : 1;
						break;
					case 'number':
						$out[ $id ] = is_numeric( $v ) ? 0 + $v : ( $f['default'] ?? 0 );
						if ( isset( $f['min'] ) ) {
							$out[ $id ] = max( $f['min'], $out[ $id ] );
						}
						if ( isset( $f['max'] ) ) {
							$out[ $id ] = min( $f['max'], $out[ $id ] );
						}
						break;
					case 'email':
						$out[ $id ] = sanitize_email( (string) $v );
						break;
					case 'url':
						$out[ $id ] = esc_url_raw( (string) $v );
						break;
					case 'textarea':
						$out[ $id ] = sanitize_textarea_field( (string) $v );
						break;
					case 'password':
						if ( is_string( $v ) && '' !== $v ) {
							$out[ $id ] = sanitize_text_field( $v ); // keep the old secret when left empty
						}
						break;
					case 'select':
						$opts       = array_keys( (array) ( $f['options'] ?? array() ) );
						$out[ $id ] = in_array( (string) $v, array_map( 'strval', $opts ), true ) ? (string) $v : (string) ( $f['default'] ?? '' );
						break;
					default:
						$out[ $id ] = sanitize_text_field( (string) $v );
				}
			}
		}
		unset( $out['__tab'] );
		return $out;
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		$sections = self::sections();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : (string) array_key_first( $sections );
		$opts = (array) get_option( self::OPTION, array() );
		echo '<div class="wrap"><h1>' . esc_html__( 'Eurowet — ustawienia', 'eurowet-core' ) . '</h1><nav class="nav-tab-wrapper">';
		foreach ( $sections as $sid => $s ) {
			printf( '<a class="nav-tab %s" href="%s">%s</a>', $sid === $tab ? 'nav-tab-active' : '', esc_url( add_query_arg( array( 'page' => self::PAGE, 'tab' => $sid ), admin_url( 'admin.php' ) ) ), esc_html( $s['title'] ) );
		}
		echo '</nav><form method="post" action="options.php">';
		settings_fields( 'ew_settings_group' );
		printf( '<input type="hidden" name="%s[__tab]" value="%s">', esc_attr( self::OPTION ), esc_attr( $tab ) );
		echo '<table class="form-table" role="presentation">';
		foreach ( $sections[ $tab ]['fields'] ?? array() as $f ) {
			$id    = (string) $f['id'];
			$name  = self::OPTION . '[' . $id . ']';
			$val   = $opts[ $id ] ?? ( $f['default'] ?? '' );
			$type  = (string) ( $f['type'] ?? 'text' );
			$field = 'ew-set-' . $id;
			echo '<tr><th scope="row"><label for="' . esc_attr( $field ) . '">' . esc_html( (string) $f['label'] ) . '</label></th><td>';
			switch ( $type ) {
				case 'checkbox':
					printf( '<input type="checkbox" id="%s" name="%s" value="1" %s>', esc_attr( $field ), esc_attr( $name ), checked( (bool) $val, true, false ) );
					break;
				case 'textarea':
					printf( '<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>', esc_attr( $field ), esc_attr( $name ), esc_textarea( (string) $val ) );
					break;
				case 'select':
					printf( '<select id="%s" name="%s">', esc_attr( $field ), esc_attr( $name ) );
					foreach ( (array) ( $f['options'] ?? array() ) as $k => $label ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( (string) $k ), selected( (string) $val, (string) $k, false ), esc_html( (string) $label ) );
					}
					echo '</select>';
					break;
				case 'password':
					$has = ! empty( $opts[ $id ] );
					printf( '<input type="password" id="%s" name="%s" value="" class="regular-text" autocomplete="new-password" placeholder="%s">', esc_attr( $field ), esc_attr( $name ), esc_attr( $has ? __( '•••••• (zapisany — wpisz, aby zmienić)', 'eurowet-core' ) : '' ) );
					break;
				default:
					$attrs = '';
					foreach ( array( 'min', 'max', 'step' ) as $a ) {
						if ( isset( $f[ $a ] ) ) {
							$attrs .= sprintf( ' %s="%s"', $a, esc_attr( (string) $f[ $a ] ) );
						}
					}
					printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text"%s>', esc_attr( in_array( $type, array( 'number', 'email', 'url' ), true ) ? $type : 'text' ), esc_attr( $field ), esc_attr( $name ), esc_attr( (string) $val ), $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( ! empty( $f['help'] ) ) {
				echo '<p class="description">' . esc_html( (string) $f['help'] ) . '</p>';
			}
			echo '</td></tr>';
		}
		echo '</table>';
		submit_button();
		echo '</form></div>';
	}
}
