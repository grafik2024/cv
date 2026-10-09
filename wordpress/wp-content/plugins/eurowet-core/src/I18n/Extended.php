<?php
/**
 * Extended (non-core) languages: stored, reviewable machine translations of selected posts.
 * URL: /x/{lang}/{original-path}/  →  original template with translated title/excerpt/content.
 * Status machine → noindex; reviewed → indexable + hreflang. Nothing is translated on a visitor request.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\I18n;

defined( 'ABSPATH' ) || exit;

final class Extended {

	private const META = '_ew_xlang';

	/** @var array{lang:string, post_id:int}|null */
	private static ?array $current = null;

	public static function register(): void {
		add_action( 'ew_add_rewrite_rules', static fn() => add_rewrite_rule( '^x/([a-z]{2})/(.+?)/?$', 'index.php?ew_xlang=$matches[1]&ew_xpath=$matches[2]', 'top' ) );
		add_filter( 'query_vars', static fn( $v ) => array_merge( (array) $v, array( 'ew_xlang', 'ew_xpath' ) ) );
		add_filter( 'request', array( self::class, 'request' ), 30 );
		add_filter( 'the_title', array( self::class, 'title' ), 20, 2 );
		add_filter( 'the_content', array( self::class, 'content' ), 5 );
		add_filter( 'language_attributes', array( self::class, 'htmlLang' ) );
		add_filter( 'wp_robots', array( self::class, 'robots' ) );
		add_action( 'add_meta_boxes', array( self::class, 'metaBox' ) );
		add_action( 'admin_post_ew_xlang_translate', array( self::class, 'adminTranslate' ) );
		add_action( 'admin_post_ew_xlang_review', array( self::class, 'adminReview' ) );
	}

	/** Maps /x/de/porady/slug/ to the original request and remembers the language. */
	public static function request( $vars ) {
		if ( empty( $vars['ew_xlang'] ) || empty( $vars['ew_xpath'] ) ) {
			return $vars;
		}
		$lang = sanitize_key( (string) $vars['ew_xlang'] );
		if ( ! isset( Module::extraLanguages()[ $lang ] ) ) {
			return array( 'error' => '404' );
		}
		$url = home_url( '/' . trim( (string) $vars['ew_xpath'], '/' ) . '/' );
		$pid = url_to_postid( $url );
		if ( ! $pid ) {
			return array( 'error' => '404' );
		}
		self::$current = array( 'lang' => $lang, 'post_id' => $pid );
		return array( 'p' => $pid, 'post_type' => get_post_type( $pid ) );
	}

	/** @return array<string, mixed>|null */
	public static function stored( int $post_id, string $lang ): ?array {
		$all = get_post_meta( $post_id, self::META, true );
		return is_array( $all ) && isset( $all[ $lang ] ) && is_array( $all[ $lang ] ) ? $all[ $lang ] : null;
	}

	private static function active(): ?array {
		if ( ! self::$current ) {
			return null;
		}
		$t = self::stored( self::$current['post_id'], self::$current['lang'] );
		return $t && 'rejected' !== ( $t['status'] ?? '' ) ? $t : null;
	}

	public static function title( $title, $id = 0 ) {
		$t = self::active();
		return ( $t && (int) $id === self::$current['post_id'] && ! empty( $t['title'] ) ) ? esc_html( (string) $t['title'] ) : $title;
	}

	public static function content( $content ) {
		if ( ! self::$current || get_the_ID() !== self::$current['post_id'] ) {
			return $content;
		}
		$t = self::active();
		if ( ! $t ) {
			return '<p class="ew-alert ew-alert--info">' . esc_html__( 'Tłumaczenie na ten język nie jest jeszcze dostępne. Wyświetlamy wersję polską.', 'eurowet-core' ) . '</p>' . $content;
		}
		$note = 'reviewed' === ( $t['status'] ?? '' ) ? '' : '<p class="ew-alert ew-alert--info">' . esc_html__( 'Tłumaczenie automatyczne — może zawierać nieścisłości.', 'eurowet-core' ) . '</p>';
		return $note . wp_kses_post( (string) $t['content'] );
	}

	public static function htmlLang( $attr ) {
		return self::$current ? preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( self::$current['lang'] ) . '"', (string) $attr ) : $attr;
	}

	public static function robots( array $robots ): array {
		if ( self::$current ) {
			$t = self::active();
			if ( ! $t || 'reviewed' !== ( $t['status'] ?? '' ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
			}
		}
		return $robots;
	}

	/**
	 * Translates a post into an extra language and stores it as "machine" (admin / CLI only).
	 */
	public static function translate( int $post_id, string $lang ): array {
		$key = (string) ew_get_option( 'mt_api_key', '' );
		if ( '' === $key ) {
			return array( 'ok' => false, 'message' => 'No API key configured (Ustawienia → Języki).' );
		}
		$post = get_post( $post_id );
		if ( ! $post || ! isset( Module::EXTRA[ $lang ] ) ) {
			return array( 'ok' => false, 'message' => 'Unknown post or language.' );
		}
		$source = array( 'title' => $post->post_title, 'content' => apply_filters( 'the_content', $post->post_content ) );
		$schema = array( 'type' => 'object', 'properties' => array( 'title' => array( 'type' => 'string' ), 'content' => array( 'type' => 'string' ) ), 'required' => array( 'title', 'content' ), 'additionalProperties' => false );
		$res    = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 120,
				'headers' => array( 'x-api-key' => $key, 'anthropic-version' => '2023-06-01', 'anthropic-beta' => 'server-side-fallback-2026-07-01', 'content-type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'model'         => (string) ew_get_option( 'mt_model', 'claude-opus-5-5' ),
						'max_tokens'    => 16000,
						'fallbacks'     => 'default',
						'output_config' => array( 'effort' => 'medium', 'format' => array( 'type' => 'json_schema', 'schema' => $schema ) ),
						'system'        => 'Translate the given Polish web page (title + HTML content) for a veterinary care brand into ' . Module::EXTRA[ $lang ] . '. Keep HTML tags and links intact, keep product names, INCI names and brand names unchanged, do not add or remove information, keep cautionary/veterinary advice wording faithful.',
						'messages'      => array( array( 'role' => 'user', 'content' => wp_json_encode( $source ) ) ),
					)
				),
			)
		);
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			return array( 'ok' => false, 'message' => is_wp_error( $res ) ? $res->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $res ) );
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $data ) || 'refusal' === ( $data['stop_reason'] ?? '' ) ) {
			return array( 'ok' => false, 'message' => 'Model declined the request.' );
		}
		$text = '';
		foreach ( (array) ( $data['content'] ?? array() ) as $b ) {
			$text .= is_array( $b ) && 'text' === ( $b['type'] ?? '' ) ? (string) $b['text'] : '';
		}
		$out = json_decode( $text, true );
		if ( ! is_array( $out ) || empty( $out['content'] ) ) {
			return array( 'ok' => false, 'message' => 'Unexpected response.' );
		}
		$all          = (array) get_post_meta( $post_id, self::META, true );
		$all[ $lang ] = array( 'title' => sanitize_text_field( (string) $out['title'] ), 'content' => wp_kses_post( (string) $out['content'] ), 'status' => 'machine', 'source_hash' => md5( $post->post_title . $post->post_content ), 'updated' => gmdate( 'c' ) );
		update_post_meta( $post_id, self::META, $all );
		return array( 'ok' => true, 'message' => 'Stored as machine translation (noindex until reviewed).' );
	}

	public static function metaBox(): void {
		foreach ( array( 'ew_guide', 'ew_need', 'ew_ingredient', 'page' ) as $pt ) {
			add_meta_box(
				'ew-xlang',
				__( 'Dodatkowe języki (tłumaczenie → weryfikacja)', 'eurowet-core' ),
				static function ( \WP_Post $p ): void {
					$all = (array) get_post_meta( $p->ID, self::META, true );
					echo '<ul>';
					foreach ( Module::extraLanguages() as $code => $name ) {
						$t      = $all[ $code ] ?? null;
						$status = is_array( $t ) ? (string) $t['status'] : __( 'brak', 'eurowet-core' );
						printf(
							'<li><strong>%s</strong>: %s — <a href="%s">%s</a>%s</li>',
							esc_html( $name ),
							esc_html( $status ),
							esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ew_xlang_translate&post=' . $p->ID . '&lang=' . $code ), 'ew_xlang_' . $p->ID ) ),
							esc_html__( 'Przetłumacz', 'eurowet-core' ),
							is_array( $t ) && 'reviewed' !== $t['status'] ? ' · <a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ew_xlang_review&post=' . $p->ID . '&lang=' . $code ), 'ew_xlang_' . $p->ID ) ) . '">' . esc_html__( 'Oznacz jako zweryfikowane', 'eurowet-core' ) . '</a>' : ''
						);
					}
					echo '</ul>';
				},
				$pt,
				'side'
			);
		}
	}

	public static function adminTranslate(): void {
		$pid  = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$lang = isset( $_GET['lang'] ) ? sanitize_key( (string) $_GET['lang'] ) : '';
		if ( ! current_user_can( 'edit_post', $pid ) || ! check_admin_referer( 'ew_xlang_' . $pid ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		self::translate( $pid, $lang );
		wp_safe_redirect( get_edit_post_link( $pid, 'raw' ) );
		exit;
	}

	public static function adminReview(): void {
		$pid  = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$lang = isset( $_GET['lang'] ) ? sanitize_key( (string) $_GET['lang'] ) : '';
		if ( ! current_user_can( 'edit_post', $pid ) || ! check_admin_referer( 'ew_xlang_' . $pid ) ) {
			wp_die( esc_html__( 'Brak uprawnień.', 'eurowet-core' ) );
		}
		$all = (array) get_post_meta( $pid, self::META, true );
		if ( isset( $all[ $lang ] ) ) {
			$all[ $lang ]['status'] = 'reviewed';
			update_post_meta( $pid, self::META, $all );
		}
		wp_safe_redirect( get_edit_post_link( $pid, 'raw' ) );
		exit;
	}
}
