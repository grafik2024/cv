<?php
/**
 * <head> essentials printed before any stylesheet: colour-scheme + accessibility preferences applied
 * before first paint (no flash), theme colours, font preload, LCP preload hook.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	static function (): void {
		?>
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#0074A8" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0B1622" media="(prefers-color-scheme: dark)">
<script>(function(d){var h=d.documentElement;h.classList.add('ew-js');try{var t=localStorage.getItem('ew-theme');if(t==='light'||t==='dark'){h.setAttribute('data-theme',t);}var a=JSON.parse(localStorage.getItem('ew-a11y')||'{}');['text','contrast','links','spacing','motion'].forEach(function(k){if(a[k]){h.setAttribute('data-a11y-'+k,a[k]);}});}catch(e){}})(document);</script>
		<?php
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( EW_THEME_URI . '/assets/fonts/manrope/manrope-latin-ext-wght-normal.woff2' ) );
		$lcp = (string) apply_filters( 'ew_theme_lcp_image', '' );
		if ( '' !== $lcp ) {
			printf( '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url( $lcp ) );
		}
	},
	1
);
