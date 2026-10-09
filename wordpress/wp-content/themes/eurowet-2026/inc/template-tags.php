<?php
/**
 * Template tags shared by templates.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

/** Page header with breadcrumbs, title (H1), optional intro. */
function ew_theme_page_header( string $title, string $intro = '', string $eyebrow = '' ): void {
	?>
	<header class="ew-page-header">
		<div class="ew-container">
			<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $eyebrow ) : ?><p class="ew-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h1 class="ew-page-header__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?><div class="ew-page-header__intro"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div><?php endif; ?>
		</div>
	</header>
	<?php
}

/** Metadata of bundled theme images (assets/img/images.json: alt_pl, disclosure_pl, ai, w, h). */
function ew_theme_image_meta( string $slug ): array {
	static $data = null;
	if ( null === $data ) {
		$file = EW_THEME_DIR . '/assets/img/images.json';
		$data = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return (array) ( $data[ $slug ] ?? array() );
}

/**
 * <picture> (AVIF → WebP → JPEG, 800/1600 w) for a bundled image: assets/img/{dir}/{slug}-{w}.{ext}.
 * Hub images: dir "hubs", slug = ew_hub term slug. Alt defaults to the manifest text.
 *
 * @param array<string,string> $attr class, sizes, loading, dir.
 */
function ew_theme_hub_picture( string $slug, string $alt = '', array $attr = array() ): string {
	$dir  = $attr['dir'] ?? 'hubs';
	$base = 'assets/img/' . $dir . '/' . $slug;
	if ( ! is_readable( EW_THEME_DIR . '/' . $base . '-1600.jpg' ) ) {
		return '';
	}
	$meta    = ew_theme_image_meta( $slug );
	$alt     = '' !== $alt ? $alt : (string) ( $meta['alt_pl'] ?? '' );
	$height  = (int) ( $meta['h'] ?? 893 );
	$src     = static fn( string $ext ) => esc_url( ew_theme_asset_uri( $base . '-800.' . $ext ) ) . ' 800w, ' . esc_url( ew_theme_asset_uri( $base . '-1600.' . $ext ) ) . ' 1600w';
	$sizes   = $attr['sizes'] ?? '(min-width: 1280px) 1200px, 100vw';
	$loading = $attr['loading'] ?? 'lazy';
	return sprintf(
		'<picture class="%s"><source type="image/avif" srcset="%s" sizes="%s"><source type="image/webp" srcset="%s" sizes="%s"><img src="%s" srcset="%s" sizes="%s" alt="%s" width="1600" height="%d" loading="%s" decoding="async"%s></picture>',
		esc_attr( $attr['class'] ?? 'ew-hub-picture' ),
		$src( 'avif' ),
		esc_attr( $sizes ),
		$src( 'webp' ),
		esc_attr( $sizes ),
		esc_url( ew_theme_asset_uri( $base . '-1600.jpg' ) ),
		$src( 'jpg' ),
		esc_attr( $sizes ),
		esc_attr( $alt ),
		$height,
		esc_attr( $loading ),
		'eager' === $loading ? ' fetchpriority="high"' : ''
	);
}

/** Disclosure line for a bundled image (AI imagery / AI background), from the manifest. */
function ew_theme_image_disclosure( string $slug ): string {
	$meta = ew_theme_image_meta( $slug );
	if ( empty( $meta['ai'] ) ) {
		return '';
	}
	$text = (string) ( $meta['disclosure_pl'] ?? '' );
	return '' !== $text && 'pl' === ew_theme_lang() ? '<p class="ew-ai-note">' . esc_html( $text ) . '</p>' : ew_theme_ai_note();
}

/** Current language slug (Polylang) or "pl". */
function ew_theme_lang(): string {
	return function_exists( 'pll_current_language' ) ? ( (string) pll_current_language( 'slug' ) ?: 'pl' ) : 'pl';
}

/** Small AI-imagery disclosure under generated pictures (brief §43). */
function ew_theme_ai_note(): string {
	return '<p class="ew-ai-note">' . esc_html__( 'Ilustracja wygenerowana z pomocą AI.', 'eurowet-2026' ) . '</p>';
}

/** Section wrapper open/close helpers for consistent spacing. */
function ew_theme_section_open( string $id, string $class = '', string $label_id = '' ): void {
	printf( '<section class="ew-section %s" id="%s"%s><div class="ew-container">', esc_attr( $class ), esc_attr( $id ), $label_id ? ' aria-labelledby="' . esc_attr( $label_id ) . '"' : '' );
}

function ew_theme_section_close(): void {
	echo '</div></section>';
}

/**
 * Adds ids to H2 headings of rendered content and returns the table of contents.
 *
 * @return array{html:string, toc:array<int, array{id:string, text:string}>}
 */
function ew_theme_content_toc( string $html ): array {
	$toc  = array();
	$used = array();
	$html = (string) preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#is',
		static function ( array $m ) use ( &$toc, &$used ): string {
			$text = trim( wp_strip_all_tags( $m[2] ) );
			if ( '' === $text ) {
				return $m[0];
			}
			if ( preg_match( '#\sid=["\']([^"\']+)["\']#', $m[1], $idm ) ) {
				$id = $idm[1];
				$attrs = $m[1];
			} else {
				$base = sanitize_title( remove_accents( $text ) ) ?: 'sekcja';
				$id   = $base;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs = $m[1] . ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			$toc[]       = array( 'id' => $id, 'text' => $text );
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);
	return array( 'html' => $html, 'toc' => $toc );
}

/** Table of contents navigation (only when there are at least 3 sections). */
function ew_theme_toc( array $toc ): string {
	if ( count( $toc ) < 3 ) {
		return '';
	}
	$out = '<nav class="ew-toc" aria-labelledby="ew-toc-title"><p class="ew-toc__title" id="ew-toc-title">' . esc_html__( 'Spis treści', 'eurowet-2026' ) . '</p><ol class="ew-toc__list">';
	foreach ( $toc as $item ) {
		$out .= '<li><a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['text'] ) . '</a></li>';
	}
	return $out . '</ol></nav>';
}

/** Featured image as <figure> with caption and the AI disclosure when the attachment is AI-assisted. */
function ew_theme_featured_figure( int $post_id, string $context = 'content', bool $eager = false ): string {
	$aid = (int) get_post_thumbnail_id( $post_id );
	if ( ! $aid ) {
		return '';
	}
	$img = ew_theme_image( $aid, 'ew-hero', $context, $eager ? array( 'loading' => 'eager', 'fetchpriority' => 'high' ) : array() );
	if ( '' === $img ) {
		return '';
	}
	$caption = wp_get_attachment_caption( $aid );
	$ai      = '1' === (string) get_post_meta( $aid, '_ew_ai_generated', true );
	$out     = '<figure class="ew-figure">' . $img;
	if ( $caption || $ai ) {
		$out .= '<figcaption>' . ( $caption ? esc_html( $caption ) : '' ) . ( $ai ? ew_theme_ai_note() : '' ) . '</figcaption>';
	}
	return $out . '</figure>';
}

/** First term of a taxonomy for a post (primary Yoast term when set). */
function ew_theme_primary_term( int $post_id, string $taxonomy ): ?WP_Term {
	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_' . $taxonomy, true );
	if ( $primary ) {
		$t = get_term( $primary, $taxonomy );
		if ( $t instanceof WP_Term ) {
			return $t;
		}
	}
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( is_array( $terms ) && $terms ) ? $terms[0] : null;
}

/** Posts from an ids meta value, mapped to the current language, published only. */
function ew_theme_posts_from_ids( array $ids, string $post_type ): array {
	$out = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
		$p = get_post( ew_theme_tr_id( $id ) );
		if ( $p instanceof WP_Post && $post_type === $p->post_type && 'publish' === $p->post_status ) {
			$out[] = $p;
		}
	}
	return $out;
}

/** Products from ids (current language, visible). */
function ew_theme_products_from_ids( array $ids ): array {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}
	$out = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
		$p = wc_get_product( ew_theme_tr_id( $id ) );
		if ( $p && 'publish' === $p->get_status() && $p->is_visible() ) {
			$out[] = $p;
		}
	}
	return $out;
}

/** Simple pagination with accessible labels. */
function ew_theme_pagination(): void {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => '<span aria-hidden="true">‹</span> ' . esc_html__( 'Poprzednia', 'eurowet-2026' ),
			'next_text' => esc_html__( 'Następna', 'eurowet-2026' ) . ' <span aria-hidden="true">›</span>',
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="ew-pagination" aria-label="' . esc_attr__( 'Stronicowanie', 'eurowet-2026' ) . '"><ul>';
	foreach ( $links as $l ) {
		echo '<li>' . wp_kses_post( $l ) . '</li>';
	}
	echo '</ul></nav>';
}

/** Knowledge hubs of a type (topic|species), ordered; empty without eurowet-core. */
function ew_theme_hubs( string $type ): array {
	$class = '\Eurowet\Core\Graph\Hubs';
	if ( ! class_exists( $class ) ) {
		return array();
	}
	try {
		return 'species' === $type ? $class::speciesHubs() : $class::topicHubs();
	} catch ( \Throwable $e ) {
		return array();
	}
}

/** Term meta (ew_hub intro etc.). */
function ew_theme_term_meta( int $term_id, string $key, $default = '' ) {
	$v = get_term_meta( $term_id, $key, true );
	return ( '' === $v || null === $v || false === $v ) ? $default : $v;
}

/** Hub cards: image (when bundled), name, count of guides. */
function ew_theme_hub_cards( array $hubs, string $heading_id = '' ): void {
	if ( ! $hubs ) {
		return;
	}
	echo '<ul class="ew-hub-cards" role="list"' . ( $heading_id ? ' aria-labelledby="' . esc_attr( $heading_id ) . '"' : '' ) . '>';
	foreach ( $hubs as $hub ) {
		$pic = ew_theme_hub_picture( $hub->slug, '', array( 'class' => 'ew-hub-card__media', 'sizes' => '(min-width: 1280px) 296px, (min-width: 768px) 30vw, 90vw' ) );
		echo '<li class="ew-hub-card' . ( $pic ? '' : ' ew-hub-card--plain' ) . '">';
		echo $pic; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<a class="ew-hub-card__link" href="' . esc_url( get_term_link( $hub ) ) . '"><span class="ew-hub-card__name">' . esc_html( $hub->name ) . '</span>';
		if ( $hub->count ) {
			/* translators: %d number of guides */
			echo '<span class="ew-hub-card__count">' . esc_html( sprintf( _n( '%d porada', '%d porad', (int) $hub->count, 'eurowet-2026' ), (int) $hub->count ) ) . '</span>';
		}
		echo '</a></li>';
	}
	echo '</ul>';
}
