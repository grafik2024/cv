<?php
/**
 * Languages: Polylang setup (PL default without prefix, EN, FR, UA[locale uk]), language picker data,
 * and EXTENDED languages: TRANSLATE → REVIEW → STORE → CACHE. Machine translations are produced only from
 * the admin or WP-CLI (never on a visitor request), stored per post/language, shown under /x/{lang}/…,
 * noindex until a human marks them reviewed.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\I18n;

use Eurowet\Core\Admin\Settings;
use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public const CORE = array(
		'pl' => array( 'name' => 'Polski', 'locale' => 'pl_PL', 'hreflang' => 'pl-PL', 'flag' => 'pl', 'native' => 'Polski' ),
		'en' => array( 'name' => 'English', 'locale' => 'en_US', 'hreflang' => 'en', 'flag' => 'gb', 'native' => 'English' ),
		'fr' => array( 'name' => 'Français', 'locale' => 'fr_FR', 'hreflang' => 'fr-FR', 'flag' => 'fr', 'native' => 'Français' ),
		'ua' => array( 'name' => 'Українська', 'locale' => 'uk', 'hreflang' => 'uk-UA', 'flag' => 'ua', 'native' => 'Українська' ),
	);

	/** Extra languages offered in the searchable picker (machine translation, reviewed before indexing). */
	public const EXTRA = array(
		'de' => 'Deutsch', 'cs' => 'Čeština', 'sk' => 'Slovenčina', 'lt' => 'Lietuvių', 'lv' => 'Latviešu', 'et' => 'Eesti', 'ro' => 'Română', 'hu' => 'Magyar',
		'it' => 'Italiano', 'es' => 'Español', 'nl' => 'Nederlands', 'sv' => 'Svenska', 'da' => 'Dansk', 'fi' => 'Suomi', 'pt' => 'Português', 'hr' => 'Hrvatski',
		'sl' => 'Slovenščina', 'bg' => 'Български', 'el' => 'Ελληνικά', 'tr' => 'Türkçe',
	);

	public function register(): void {
		Extended::register();
		add_action(
			'ew_settings_sections',
			static function (): void {
				Settings::addSection(
					'languages',
					__( 'Języki', 'eurowet-core' ),
					array(
						array( 'id' => 'extra_languages', 'type' => 'text', 'label' => __( 'Dodatkowe języki (kody, przecinek)', 'eurowet-core' ), 'default' => 'de,cs,sk,lt,ro,hu,it,es', 'help' => __( 'Pokazywane w wyszukiwalnym wyborze „Inne języki”. Tłumaczenie maszynowe jest tworzone z panelu (nie przy wizycie), a do czasu weryfikacji strona ma noindex.', 'eurowet-core' ) ),
						array( 'id' => 'mt_api_key', 'type' => 'password', 'label' => __( 'Klucz API tłumaczeń (Anthropic)', 'eurowet-core' ) ),
						array( 'id' => 'mt_model', 'type' => 'text', 'label' => __( 'Model tłumaczeń', 'eurowet-core' ), 'default' => 'claude-opus-5-5' ),
					)
				);
			}
		);
	}

	/** @return array<int, array{slug:string, name:string, url:string, current:bool, hreflang:string, has_translation:bool}> */
	public static function coreLinks(): array {
		$out = array();
		if ( function_exists( 'pll_the_languages' ) ) {
			$langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) );
			foreach ( (array) $langs as $l ) {
				$out[] = array(
					'slug'            => (string) $l['slug'],
					'name'            => (string) $l['name'],
					'url'             => (string) $l['url'],
					'current'         => ! empty( $l['current_lang'] ),
					'hreflang'        => self::CORE[ $l['slug'] ]['hreflang'] ?? (string) $l['slug'],
					'has_translation' => empty( $l['no_translation'] ),
				);
			}
		}
		if ( ! $out ) {
			$out[] = array( 'slug' => 'pl', 'name' => 'Polski', 'url' => home_url( '/' ), 'current' => true, 'hreflang' => 'pl-PL', 'has_translation' => true );
		}
		return $out;
	}

	/** @return array<string, string> code => native name, enabled in settings */
	public static function extraLanguages(): array {
		$codes = array_filter( array_map( 'trim', explode( ',', (string) ew_get_option( 'extra_languages', 'de,cs,sk,lt,ro,hu,it,es' ) ) ) );
		return array_intersect_key( self::EXTRA, array_flip( $codes ) );
	}

	/**
	 * Polylang setup used by `wp eurowet languages setup`.
	 *
	 * @return string[] log lines
	 */
	public static function setupLanguages(): array {
		$log = array();
		if ( ! function_exists( 'PLL' ) || ! isset( PLL()->model ) ) {
			return array( 'Polylang is not active.' );
		}
		$model = PLL()->model;
		$order = 0;
		foreach ( self::CORE as $slug => $l ) {
			if ( $model->get_language( $slug ) ) {
				$log[] = "Language {$slug} exists.";
				continue;
			}
			$args = array( 'name' => $l['name'], 'slug' => $slug, 'locale' => $l['locale'], 'rtl' => 0, 'term_group' => $order++, 'flag' => $l['flag'] );
			$res  = method_exists( $model, 'add_language' ) ? $model->add_language( $args ) : null;
			if ( null === $res && isset( $model->languages ) && method_exists( $model->languages, 'add' ) ) {
				$res = $model->languages->add( $args );
			}
			$log[] = is_wp_error( $res ) ? "Language {$slug}: " . $res->get_error_message() : "Language {$slug} added.";
		}
		$opts = get_option( 'polylang', array() );
		if ( is_array( $opts ) ) {
			$opts['default_lang']    = 'pl';
			$opts['hide_default']    = 1;  // PL without /pl/ prefix
			$opts['force_lang']      = 1;  // language from the directory in the URL
			$opts['browser']         = 0;  // never redirect by Accept-Language (crawlers must see PL at "/")
			$opts['rewrite']         = 1;  // remove /language/ from URLs
			$opts['redirect_lang']   = 0;
			$opts['media_support']   = 0;
			$opts['post_types']      = array_values( array_unique( array_merge( (array) ( $opts['post_types'] ?? array() ), array( 'product', 'ew_need', 'ew_guide', 'ew_ingredient', 'ew_material' ) ) ) );
			$opts['taxonomies']      = array_values( array_unique( array_merge( (array) ( $opts['taxonomies'] ?? array() ), array( 'product_cat', 'ew_line', 'ew_species', 'ew_area', 'ew_hub', 'ew_material_type' ) ) ) );
			update_option( 'polylang', $opts );
			$log[] = 'Polylang options: default PL without prefix, browser detection OFF, CPT/taxonomies translatable.';
		}
		return $log;
	}
}
