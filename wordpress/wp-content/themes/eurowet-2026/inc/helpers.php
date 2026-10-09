<?php
/**
 * Small, dependency-free helpers shared by the theme. Every call into eurowet-core or other
 * plugins goes through a guarded wrapper here, so templates never call ew_* directly unguarded.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: file modification time in development, theme version otherwise.
 */
function ew_theme_asset_version( string $relative_path ): string {
	$file = EW_THEME_DIR . '/' . ltrim( $relative_path, '/' );
	if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && is_readable( $file ) ) {
		return EW_THEME_VERSION . '.' . (string) filemtime( $file );
	}
	return EW_THEME_VERSION;
}

function ew_theme_asset_uri( string $relative_path ): string {
	return EW_THEME_URI . '/' . ltrim( $relative_path, '/' );
}

function ew_theme_has_core(): bool {
	return function_exists( 'ew_render' );
}

function ew_theme_has_woo(): bool {
	return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
}

/**
 * True on every WooCommerce screen (shop, product, product taxonomies, cart, checkout, account).
 */
function ew_theme_is_woo_page(): bool {
	if ( ! ew_theme_has_woo() ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Render an eurowet-core component; empty string when the plugin or component is unavailable.
 * The plugin escapes its own output (contract §4).
 *
 * @param array<string,mixed> $args Component arguments.
 */
function ew_theme_render( string $component, array $args = array() ): string {
	if ( ! function_exists( 'ew_render' ) ) {
		return '';
	}
	try {
		$html = ew_render( $component, $args );
	} catch ( \Throwable $e ) {
		return '';
	}
	return is_string( $html ) ? trim( $html ) : '';
}

/**
 * Typed meta read: ew_meta() when available, otherwise get_post_meta() with JSON decoding for
 * structured values (json/ids meta stored as JSON strings by older imports).
 *
 * @param mixed $default Default value.
 * @return mixed
 */
function ew_theme_meta( int $post_id, string $key, $default = null ) {
	if ( $post_id <= 0 ) {
		return $default;
	}
	if ( function_exists( 'ew_meta' ) ) {
		try {
			$value = ew_meta( $post_id, $key, $default );
		} catch ( \Throwable $e ) {
			$value = $default;
		}
		return $value;
	}
	$value = get_post_meta( $post_id, $key, true );
	if ( '' === $value || null === $value || false === $value ) {
		return $default;
	}
	if ( is_string( $value ) && '' !== $value && ( '[' === $value[0] || '{' === $value[0] ) ) {
		$decoded = json_decode( $value, true );
		if ( JSON_ERROR_NONE === json_last_error() ) {
			return $decoded;
		}
	}
	return $value;
}

/**
 * Plugin setting via ew_get_option(); null when unavailable.
 *
 * @param mixed $default Default value.
 * @return mixed
 */
function ew_theme_option( string $key, $default = null ) {
	if ( ! function_exists( 'ew_get_option' ) ) {
		return $default;
	}
	try {
		$value = ew_get_option( $key, $default );
	} catch ( \Throwable $e ) {
		$value = $default;
	}
	return $value;
}

/**
 * Map a post ID to the current language (Polylang) — ew_tr_id() first, then pll_get_post().
 */
function ew_theme_tr_id( int $post_id ): int {
	if ( $post_id <= 0 ) {
		return 0;
	}
	if ( function_exists( 'ew_tr_id' ) ) {
		return (int) ew_tr_id( $post_id );
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $post_id );
		if ( $translated ) {
			return (int) $translated;
		}
	}
	return $post_id;
}

/**
 * URL of a key page of the information architecture (docs/ARCHITECTURE.md §3).
 * Resolves archives / pages in the current language; falls back to the Polish path.
 */
function ew_theme_url( string $key ): string {
	static $cache = array();
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$paths = array(
		'products'       => 'produkty',
		'needs'          => 'potrzeby',
		'guides'         => 'porady',
		'ingredients'    => 'skladniki',
		'b2b'            => 'wspolpraca-b2b',
		'private_label'  => 'marka-wlasna',
		'reps'           => 'znajdz-przedstawiciela',
		'contact'        => 'kontakt',
		'downloads'      => 'pobierz',
	);
	$archives = array(
		'needs'       => 'ew_need',
		'guides'      => 'ew_guide',
		'ingredients' => 'ew_ingredient',
	);

	$url = '';
	if ( 'products' === $key && ew_theme_has_woo() ) {
		$shop_id = (int) wc_get_page_id( 'shop' );
		if ( $shop_id > 0 ) {
			$url = (string) get_permalink( ew_theme_tr_id( $shop_id ) );
		}
	} elseif ( isset( $archives[ $key ] ) && post_type_exists( $archives[ $key ] ) ) {
		$url = (string) get_post_type_archive_link( $archives[ $key ] );
	}

	if ( '' === $url && isset( $paths[ $key ] ) ) {
		$page = get_page_by_path( $paths[ $key ] );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$url = (string) get_permalink( ew_theme_tr_id( (int) $page->ID ) );
		}
	}
	if ( '' === $url ) {
		$url = home_url( user_trailingslashit( $paths[ $key ] ?? '' ) );
	}

	/**
	 * Filter the URL of an information-architecture key page.
	 *
	 * @param string $url URL.
	 * @param string $key Key (products, needs, guides, ingredients, b2b, private_label, reps, contact, downloads).
	 */
	$cache[ $key ] = (string) apply_filters( 'ew_theme_url', $url, $key );
	return $cache[ $key ];
}

/**
 * Company data for the footer: plugin settings first (ew_get_option('company') array or
 * ew_get_option('company_{field}')), then option 'ew_company'. Only non-empty fields are returned —
 * the theme never invents company facts.
 *
 * @return array<string,string>
 */
function ew_theme_company(): array {
	static $company = null;
	if ( null !== $company ) {
		return $company;
	}
	$fields = array( 'name', 'legal_name', 'street', 'postcode', 'city', 'country', 'address', 'phone', 'phone_2', 'fax', 'email', 'nip', 'regon', 'krs', 'bdo', 'hours', 'description' );
	$data   = array();

	$from_plugin = ew_theme_option( 'company' );
	if ( is_array( $from_plugin ) ) {
		$data = $from_plugin;
	}
	foreach ( $fields as $field ) {
		if ( empty( $data[ $field ] ) ) {
			$value = ew_theme_option( 'company_' . $field );
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$data[ $field ] = (string) $value;
			}
		}
	}
	$from_option = get_option( 'ew_company' );
	if ( is_array( $from_option ) ) {
		foreach ( $from_option as $field => $value ) {
			if ( empty( $data[ $field ] ) ) {
				$data[ $field ] = $value;
			}
		}
	}

	$clean = array();
	foreach ( $data as $field => $value ) {
		if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
			$clean[ (string) $field ] = trim( (string) $value );
		}
	}
	/**
	 * Filter company data shown by the theme (footer, contact snippets).
	 *
	 * @param array<string,string> $clean Company fields.
	 */
	$company = (array) apply_filters( 'ew_theme_company', $clean );
	return $company;
}

/**
 * Social profiles from plugin settings. Accepts ['facebook' => url], [['network'=>..,'url'=>..]]
 * or [['label'=>..,'url'=>..]] shapes.
 *
 * @return array<int,array{label:string,url:string,network:string}>
 */
function ew_theme_social_links(): array {
	$labels = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'linkedin'  => 'LinkedIn',
		'tiktok'    => 'TikTok',
		'x'         => 'X',
		'twitter'   => 'X',
		'pinterest' => 'Pinterest',
	);
	$raw = ew_theme_option( 'social' );
	if ( empty( $raw ) ) {
		$raw = ew_theme_option( 'company_social' );
	}
	if ( empty( $raw ) ) {
		$opt = get_option( 'ew_company' );
		$raw = is_array( $opt ) && ! empty( $opt['social'] ) ? $opt['social'] : array();
	}
	if ( empty( $raw ) ) {
		foreach ( array_keys( $labels ) as $network ) {
			$value = ew_theme_option( 'social_' . $network );
			if ( is_string( $value ) && '' !== $value ) {
				$raw[ $network ] = $value;
			}
		}
	}

	$links = array();
	foreach ( (array) $raw as $key => $item ) {
		$network = is_string( $key ) ? strtolower( $key ) : '';
		$url     = '';
		$label   = '';
		if ( is_string( $item ) ) {
			$url = $item;
		} elseif ( is_array( $item ) ) {
			$url     = (string) ( $item['url'] ?? '' );
			$network = strtolower( (string) ( $item['network'] ?? $network ) );
			$label   = (string) ( $item['label'] ?? '' );
		}
		$url = esc_url_raw( $url );
		if ( '' === $url ) {
			continue;
		}
		if ( '' === $label ) {
			$label = $labels[ $network ] ?? (string) wp_parse_url( $url, PHP_URL_HOST );
		}
		$links[] = array(
			'label'   => $label,
			'url'     => $url,
			'network' => $network,
		);
	}
	return $links;
}

/**
 * Turn an ALL-CAPS label (as typed on legacy product pages) into sentence case for legibility.
 * Mixed-case text is returned untouched. Disable with add_filter( 'ew_theme_sentence_case', '__return_false' ).
 */
function ew_theme_sentence_case( string $text ): string {
	$text = trim( $text );
	if ( '' === $text || ! apply_filters( 'ew_theme_sentence_case', true ) ) {
		return $text;
	}
	$letters = (string) preg_replace( '/[^\p{L}]/u', '', $text );
	if ( mb_strlen( $letters ) < 4 || mb_strtoupper( $text ) !== $text ) {
		return $text;
	}
	$lower = mb_strtolower( $text );
	return mb_strtoupper( mb_substr( $lower, 0, 1 ) ) . mb_substr( $lower, 1 );
}

/**
 * Inline SVG icon wrapped in <span class="ew-icon">. Uses the theme sprite helper ew_theme_icon()
 * (inc/icons.php) when it provides the icon, otherwise a built-in 24px stroke fallback.
 *
 * @param array{class?:string,label?:string} $args Extra class; label makes the icon meaningful (role=img).
 */
function ew_theme_ui_icon( string $name, array $args = array() ): string {
	$svg = '';
	if ( function_exists( 'ew_theme_icon' ) ) {
		try {
			ob_start();
			$returned = ew_theme_icon( $name );
			$echoed   = (string) ob_get_clean();
			$svg      = is_string( $returned ) && '' !== trim( $returned ) ? $returned : $echoed;
		} catch ( \Throwable $e ) {
			if ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
			$svg = '';
		}
		if ( false === stripos( $svg, '<svg' ) ) {
			$svg = '';
		}
	}
	if ( '' === $svg ) {
		$paths = ew_theme_fallback_icons();
		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}
		$svg = '<svg class="ew-icon__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">' . $paths[ $name ] . '</svg>';
	}

	$class = trim( 'ew-icon ew-icon--' . sanitize_html_class( $name ) . ' ' . ( $args['class'] ?? '' ) );
	if ( ! empty( $args['label'] ) ) {
		return '<span class="' . esc_attr( $class ) . '" role="img" aria-label="' . esc_attr( $args['label'] ) . '">' . $svg . '</span>';
	}
	return '<span class="' . esc_attr( $class ) . '" aria-hidden="true">' . $svg . '</span>';
}

/**
 * Built-in stroke icons (24×24, drawn for this theme) used when the sprite helper is not loaded
 * or lacks a glyph. Markup is static and trusted.
 *
 * @return array<string,string>
 */
function ew_theme_fallback_icons(): array {
	return array(
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/>',
		'moon'     => '<path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z"/>',
		'monitor'  => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
		'a11y'     => '<circle cx="12" cy="4.6" r="1.8"/><path d="M4.5 8.5 12 10l7.5-1.5M12 10v4.5M8.5 21l3.5-6.5 3.5 6.5"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
		'cart'     => '<path d="M3 4h2.2l2.1 10.2a1.5 1.5 0 0 0 1.5 1.2h8.4a1.5 1.5 0 0 0 1.5-1.1L20.5 8H6.1"/><circle cx="9.5" cy="19.5" r="1.3"/><circle cx="17" cy="19.5" r="1.3"/>',
		'phone'    => '<path d="M5 4h3.5l1.7 4.3-2.2 1.4a11 11 0 0 0 6.3 6.3l1.4-2.2L20 15.5V19a1.5 1.5 0 0 1-1.6 1.5A16.5 16.5 0 0 1 3.5 5.6 1.5 1.5 0 0 1 5 4z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'download' => '<path d="M12 4v11M7 10.5l5 5 5-5M5 20h14"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'alert'    => '<path d="M12 3.5 2.5 20h19L12 3.5z"/><path d="M12 10v4.5M12 17.5v.01"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5M12 7.5v.01"/>',
		'paw'      => '<circle cx="6.5" cy="10" r="1.8"/><circle cx="10" cy="5.8" r="1.8"/><circle cx="14" cy="5.8" r="1.8"/><circle cx="17.5" cy="10" r="1.8"/><path d="M12 11.5c-2.7 0-5 3.6-5 6 0 1.7 1.3 2.5 2.8 2.5 1 0 1.5-.5 2.2-.5s1.2.5 2.2.5c1.5 0 2.8-.8 2.8-2.5 0-2.4-2.3-6-5-6z"/>',
		'play'     => '<circle cx="12" cy="12" r="9"/><path d="m10 8.5 5.5 3.5-5.5 3.5z"/>',
		'rotate'   => '<path d="M21 12c0 2.2-4 4-9 4s-9-1.8-9-4 4-4 9-4"/><path d="m9.5 5.5 2.5 2.5-2.5 2.5"/><path d="M12 16v3"/>',
		'cube'     => '<path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M4 7.5 12 12l8-4.5M12 12v9"/>',
		'file'     => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
		'shield'   => '<path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.2 7.5 9.5 4.3-1.3 7.5-4.9 7.5-9.5V6L12 3z"/><path d="m9 12 2 2 4-4"/>',
		'leaf'     => '<path d="M5 19c0-8 5-13 15-14-1 10-6 15-14 15"/><path d="M5 19c3-4 6-6.5 9.5-8"/>',
		'filter'   => '<path d="M4 6h16M7 12h10M10 18h4"/>',
		'store'    => '<path d="M4 10v10h16V10M3 10l1.8-6h14.4L21 10"/><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0M10 20v-5h4v5"/>',
		'drop'     => '<path d="M12 3.5s-6 6.4-6 10.5a6 6 0 0 0 12 0c0-4.1-6-10.5-6-10.5z"/>',
		'book'     => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/>',
	);
}

/**
 * Responsive "sizes" attribute for the theme's image contexts.
 */
function ew_theme_sizes( string $context ): string {
	$map = array(
		'card'  => '(min-width: 1280px) 296px, (min-width: 1024px) 23vw, (min-width: 640px) 31vw, 46vw',
		'tile'  => '(min-width: 1280px) 220px, (min-width: 768px) 18vw, 40vw',
		'stage' => '(min-width: 1280px) 600px, (min-width: 960px) 46vw, 88vw',
		'thumb' => '72px',
		'hero'  => '100vw',
	);
	/**
	 * Filter the sizes attribute for an image context.
	 *
	 * @param string $sizes   Sizes attribute.
	 * @param string $context Context key.
	 */
	return (string) apply_filters( 'ew_theme_sizes', $map[ $context ] ?? '100vw', $context );
}

/**
 * Attachment <img> with srcset (WordPress), explicit sizes, lazy loading and async decoding by default.
 *
 * @param array<string,string> $attr Extra attributes (fetchpriority, loading, class, alt…).
 */
function ew_theme_image( int $attachment_id, string $size, string $context, array $attr = array() ): string {
	if ( $attachment_id <= 0 ) {
		return '';
	}
	$attr = array_merge(
		array(
			'sizes'    => ew_theme_sizes( $context ),
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attr
	);
	if ( isset( $attr['fetchpriority'] ) && 'high' === $attr['fetchpriority'] ) {
		$attr['loading'] = 'eager';
		unset( $attr['decoding'] );
	}
	return (string) wp_get_attachment_image( $attachment_id, $size, false, $attr );
}

/**
 * Allowed HTML for rich product/content fields rendered by the theme.
 *
 * @return array<string,array<string,bool>>
 */
function ew_theme_kses_rich(): array {
	$allowed = wp_kses_allowed_html( 'post' );
	// Headings inside meta fields would break the document outline: demote them to paragraphs.
	foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) {
		unset( $allowed[ $tag ] );
	}
	return $allowed;
}

/**
 * Sanitize a rich text field: demote headings to <p><strong>, keep the rest of post HTML.
 */
function ew_theme_rich_text( string $html ): string {
	$html = trim( $html );
	if ( '' === $html ) {
		return '';
	}
	$html = (string) preg_replace( '#<h[1-6][^>]*>(.*?)</h[1-6]>#is', '<p><strong>$1</strong></p>', $html );
	$html = wp_kses( $html, ew_theme_kses_rich() );
	if ( false === strpos( $html, '<p' ) && false === strpos( $html, '<ul' ) && false === strpos( $html, '<ol' ) && false === strpos( $html, '<table' ) ) {
		$html = wpautop( $html );
	}
	return $html;
}
