<?php
/**
 * Component renderer behind ew_render(): loads templates/components/{name}.php (theme override first),
 * enqueues the component's CSS/JS once, isolates output buffering.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Components;

defined( 'ABSPATH' ) || exit;

final class Renderer {

	/** @var array<string, bool> */
	private static array $enqueued = array();

	public static function render( string $name, array $args = array() ): string {
		$name = sanitize_key( $name );
		$file = self::locate( $name );
		if ( null === $file ) {
			return '';
		}
		/** Filters component arguments before rendering. */
		$args = (array) apply_filters( 'ew_render_args', $args, $name );
		self::enqueue( $name );
		do_action( 'ew_before_render', $name, $args );
		ob_start();
		( static function ( string $__file, array $args ): void {
			include $__file; // Templates read $args explicitly; no extract() on untrusted keys.
		} )( $file, $args );
		$html = (string) ob_get_clean();
		do_action( 'ew_after_render', $name, $args );
		return (string) apply_filters( 'ew_render_html', $html, $name, $args );
	}

	public static function locate( string $name ): ?string {
		$candidates = array(
			get_stylesheet_directory() . '/eurowet/components/' . $name . '.php',
			get_template_directory() . '/eurowet/components/' . $name . '.php',
			EW_CORE_DIR . 'templates/components/' . $name . '.php',
		);
		foreach ( $candidates as $file ) {
			if ( is_readable( $file ) ) {
				return $file;
			}
		}
		return null;
	}

	public static function enqueue( string $name ): void {
		if ( isset( self::$enqueued[ $name ] ) ) {
			return;
		}
		self::$enqueued[ $name ] = true;
		$css = 'assets/css/components/' . $name . '.css';
		if ( is_readable( EW_CORE_DIR . $css ) ) {
			$handle = 'ew-c-' . $name;
			wp_enqueue_style( $handle, EW_CORE_URL . $css, array(), (string) filemtime( EW_CORE_DIR . $css ) );
			// Late renders (after wp_head) still get their CSS: print it in the footer.
			if ( did_action( 'wp_head' ) && ! wp_style_is( $handle, 'done' ) ) {
				add_action( 'wp_footer', static fn() => wp_print_styles( $handle ), 1 );
			}
		}
		$js = 'assets/js/components/' . $name . '.js';
		if ( is_readable( EW_CORE_DIR . $js ) ) {
			self::enqueueModule( 'ew-c-' . $name, EW_CORE_URL . $js, (string) filemtime( EW_CORE_DIR . $js ) );
		}
	}

	/**
	 * Enqueues an ES module script (WP 6.5+ script modules, with a classic fallback).
	 */
	public static function enqueueModule( string $handle, string $src, string $ver ): void {
		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( $handle, $src, array(), $ver );
			return;
		}
		wp_enqueue_script( $handle, $src, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		add_filter(
			'script_loader_tag',
			static fn( $tag, $h ) => $h === $handle ? str_replace( '<script ', '<script type="module" ', $tag ) : $tag,
			10,
			2
		);
	}

	/**
	 * Shared JS config (REST root, nonce, language) printed once as JSON for component modules.
	 */
	public static function printConfig(): void {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done   = true;
		$config = array(
			'rest'  => esc_url_raw( rest_url( 'eurowet/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'lang'  => \Eurowet\Core\I18n\Polylang::currentLang(),
		);
		printf( '<script id="ew-config" type="application/json">%s</script>' . "\n", wp_json_encode( $config ) );
	}
}
