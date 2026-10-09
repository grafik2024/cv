<?php
/**
 * Icons: inline SVG sprite + ew_theme_icon() helper, favicons / app icons / web manifest.
 *
 * Sprite: assets/icons/sprite.svg — <symbol id="ew-i-{name}">, 24×24, stroke 1.75, currentColor.
 * Symbols marked data-src="lucide:*" come from Lucide (ISC, see assets/icons/LICENSE-lucide.txt),
 * data-src="eurowet" were drawn for this theme (tooth, skin, coat).
 *
 * The sprite is printed once, hidden, right after <body> (wp_body_open; wp_footer as a fallback), so
 * every <use href="#ew-i-…"> is a same-document reference: no cross-origin or caching issues and it also
 * works for markup that JavaScript creates later (e.g. finder results):
 *   <svg class="ew-icon" aria-hidden="true" width="24" height="24"><use href="#ew-i-check"/></svg>
 *
 * Sizing/colour: the icon follows `color`; size via CSS (.ew-icon { width: 1.25em; height: 1.25em }),
 * the width/height attributes are only the no-CSS default.
 *
 * @package Eurowet2026
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ew_theme_icon_names' ) ) {
	/**
	 * Names of all icons in the sprite (without the "ew-i-" prefix).
	 *
	 * @return string[]
	 */
	function ew_theme_icon_names(): array {
		static $names = null;
		if ( null === $names ) {
			$names = array();
			if ( preg_match_all( '/<symbol\s+id="ew-i-([a-z0-9-]+)"/', ew_theme_icon_sprite_markup(), $m ) ) {
				$names = $m[1];
			}
		}
		return $names;
	}
}

if ( ! function_exists( 'ew_theme_icon_sprite_markup' ) ) {
	/**
	 * Raw sprite file contents ('' when missing).
	 */
	function ew_theme_icon_sprite_markup(): string {
		static $svg = null;
		if ( null === $svg ) {
			$file = get_theme_file_path( 'assets/icons/sprite.svg' );
			$svg  = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		}
		return $svg;
	}
}

if ( ! function_exists( 'ew_theme_icon_sprite_url' ) ) {
	/**
	 * Public URL of the sprite file (for external <use href="…/sprite.svg#ew-i-name"> on the same origin).
	 */
	function ew_theme_icon_sprite_url(): string {
		return (string) apply_filters( 'ew_theme_icon_sprite_url', get_theme_file_uri( 'assets/icons/sprite.svg' ) );
	}
}

if ( ! function_exists( 'ew_theme_icon' ) ) {
	/**
	 * SVG icon referencing the inline sprite.
	 *
	 * Decorative (no $label): aria-hidden="true". Meaningful ($label): role="img" + <title>, named via
	 * aria-labelledby. Unknown names return '' (and a _doing_it_wrong notice in WP_DEBUG).
	 *
	 * @param string $name  Icon name, e.g. 'paw', 'search' (see ew_theme_icon_names()).
	 * @param string $label Accessible name; '' for decorative icons next to visible text.
	 * @param array  $args  Optional: 'class' (string, extra classes), 'size' (int px, default 24).
	 * @return string Escaped SVG markup.
	 */
	function ew_theme_icon( string $name, string $label = '', array $args = array() ): string {
		$name = strtolower( $name );
		if ( ! in_array( $name, ew_theme_icon_names(), true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				_doing_it_wrong( __FUNCTION__, esc_html( sprintf( 'Unknown icon "%s".', $name ) ), '1.0.0' );
			}
			return '';
		}

		// The sprite must be on the page; covers templates that skip wp_body_open().
		if ( ! has_action( 'wp_footer', 'ew_theme_print_icon_sprite' ) && ! did_action( 'wp_footer' ) ) {
			add_action( 'wp_footer', 'ew_theme_print_icon_sprite', 1 );
		}

		$size    = isset( $args['size'] ) ? max( 8, (int) $args['size'] ) : 24;
		$classes = trim( 'ew-icon ew-icon--' . $name . ' ' . ( isset( $args['class'] ) ? (string) $args['class'] : '' ) );
		$label   = trim( $label );

		$attrs = sprintf(
			'class="%1$s" width="%2$d" height="%2$d" focusable="false"',
			esc_attr( $classes ),
			$size
		);

		if ( '' === $label ) {
			return '<svg ' . $attrs . ' aria-hidden="true"><use href="#ew-i-' . esc_attr( $name ) . '"></use></svg>';
		}

		$title_id = wp_unique_id( 'ew-icon-title-' );
		return '<svg ' . $attrs . ' role="img" aria-labelledby="' . esc_attr( $title_id ) . '">'
			. '<title id="' . esc_attr( $title_id ) . '">' . esc_html( $label ) . '</title>'
			. '<use href="#ew-i-' . esc_attr( $name ) . '"></use></svg>';
	}
}

if ( ! function_exists( 'ew_theme_print_icon_sprite' ) ) {
	/**
	 * Print the sprite once per request (hidden, outside the accessibility tree).
	 */
	function ew_theme_print_icon_sprite(): void {
		static $printed = false;
		if ( $printed || ! apply_filters( 'ew_theme_inline_icon_sprite', true ) ) {
			return;
		}
		$svg = ew_theme_icon_sprite_markup();
		if ( '' === $svg ) {
			return;
		}
		$printed = true;

		// Trusted static theme file: drop the XML comment and source annotations, hide the container.
		$svg = (string) preg_replace( '/<!--.*?-->\s*/s', '', $svg );
		$svg = (string) preg_replace( '/\s+data-src="[^"]*"/', '', $svg );
		$svg = (string) preg_replace(
			'/^\s*<svg\b[^>]*>/',
			'<svg xmlns="http://www.w3.org/2000/svg" class="ew-sprite" aria-hidden="true" focusable="false" width="0" height="0" style="position:absolute;width:0;height:0;overflow:hidden">',
			$svg,
			1
		);

		echo "\n<!-- Icons: Lucide (ISC) + Eurowet, see assets/icons/LICENSE-lucide.txt -->\n" . trim( $svg ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static theme asset.
	}
}
add_action( 'wp_body_open', 'ew_theme_print_icon_sprite', 1 );

/*
 * Favicons, app icons, web manifest, theme-color.
 * Files are built by tools/build_app_icons.py into assets/icons/.
 * A Site Icon set in Settings → General / Customizer wins (WordPress prints its own tags) unless the
 * filter 'ew_theme_app_icons_override_site_icon' returns true.
 */

if ( ! function_exists( 'ew_theme_app_icons_override' ) ) {
	/**
	 * Whether the theme's icon set replaces WordPress' Site Icon output.
	 */
	function ew_theme_app_icons_override(): bool {
		return (bool) apply_filters( 'ew_theme_app_icons_override_site_icon', false );
	}
}

if ( ! function_exists( 'ew_theme_theme_colors' ) ) {
	/**
	 * Browser UI colours for light / dark (match --ew-color-bg in tokens.css).
	 *
	 * @return array{light:string,dark:string}
	 */
	function ew_theme_theme_colors(): array {
		$colors = (array) apply_filters(
			'ew_theme_theme_colors',
			array(
				'light' => '#FFFFFF',
				'dark'  => '#0B1622',
			)
		);
		return array(
			'light' => sanitize_hex_color( (string) ( $colors['light'] ?? '' ) ) ?: '#FFFFFF',
			'dark'  => sanitize_hex_color( (string) ( $colors['dark'] ?? '' ) ) ?: '#0B1622',
		);
	}
}

if ( ! function_exists( 'ew_theme_print_app_icons' ) ) {
	/**
	 * <head> tags: favicon (ICO + SVG), apple-touch-icon, manifest, theme-color (light/dark).
	 */
	function ew_theme_print_app_icons(): void {
		if ( ! apply_filters( 'ew_theme_print_app_icons', true ) ) {
			return;
		}
		$base = trailingslashit( get_theme_file_uri( 'assets/icons' ) );
		$ver  = defined( 'EW_THEME_VERSION' ) ? (string) EW_THEME_VERSION : '';
		$v    = static fn ( string $file ): string => esc_url( $ver ? add_query_arg( 'ver', $ver, $base . $file ) : $base . $file );

		if ( ! has_site_icon() || ew_theme_app_icons_override() ) {
			printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", $v( 'favicon.ico' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $v.
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", $v( 'favicon.svg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", $v( 'apple-touch-icon.png' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		printf( '<link rel="manifest" href="%s">' . "\n", $v( 'site.webmanifest' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$colors = ew_theme_theme_colors();
		printf( '<meta name="theme-color" content="%s" media="(prefers-color-scheme: light)">' . "\n", esc_attr( $colors['light'] ) );
		printf( '<meta name="theme-color" content="%s" media="(prefers-color-scheme: dark)">' . "\n", esc_attr( $colors['dark'] ) );
	}
}
add_action( 'wp_head', 'ew_theme_print_app_icons', 4 );

add_action(
	'wp_head',
	static function (): void {
		if ( ew_theme_app_icons_override() ) {
			remove_action( 'wp_head', 'wp_site_icon', 99 );
		}
	},
	0
);

/*
 * /favicon.ico requests that reach WordPress (no physical file in the web root): serve the theme icon
 * instead of the WordPress logo when no Site Icon is configured.
 */
add_action(
	'do_faviconico',
	static function (): void {
		if ( has_site_icon() && ! ew_theme_app_icons_override() ) {
			return;
		}
		wp_safe_redirect( get_theme_file_uri( 'assets/icons/favicon.ico' ), 302, 'Eurowet 2026' );
		exit;
	}
);
