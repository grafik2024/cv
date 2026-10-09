<?php
/**
 * Registry-driven meta boxes: every _ew_ field of every entity is editable without a developer.
 * Scalars → inputs, html → editor, ids → relation picker (REST search), json shapes → repeaters.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Admin;

use Eurowet\Core\Data\Meta;
use Eurowet\Core\Data\Registry;
use Eurowet\Core\Reps\Voivodeships;

defined( 'ABSPATH' ) || exit;

final class MetaBoxes {

	private const NONCE = 'ew_meta_nonce';

	/** Polish labels and optional help per meta key. */
	public static function labels(): array {
		return array(
			'_ew_subtitle'          => __( 'Podtytuł / przeznaczenie w jednym zdaniu', 'eurowet-core' ),
			'_ew_badges'            => __( 'Korzyści (badge) — jedna w linii', 'eurowet-core' ),
			'_ew_properties'        => __( 'Właściwości', 'eurowet-core' ),
			'_ew_usage'             => __( 'Sposób stosowania', 'eurowet-core' ),
			'_ew_indications'       => __( 'Zastosowanie', 'eurowet-core' ),
			'_ew_intended_for'      => __( 'Przeznaczenie', 'eurowet-core' ),
			'_ew_precautions'       => __( 'Środki ostrożności', 'eurowet-core' ),
			'_ew_composition'       => __( 'Skład / INCI', 'eurowet-core' ),
			'_ew_analytical'        => __( 'Składniki analityczne / dodatki', 'eurowet-core' ),
			'_ew_notes'             => __( 'Uwagi', 'eurowet-core' ),
			'_ew_capacity'          => __( 'Pojemność / opakowanie', 'eurowet-core' ),
			'_ew_product_type'      => __( 'Typ produktu', 'eurowet-core' ),
			'_ew_catalog_only'      => __( 'Produkt katalogowy (bez sprzedaży online — CTA „Zapytaj o dostępność”)', 'eurowet-core' ),
			'_ew_where_to_buy'      => __( 'Gdzie kupić (linki)', 'eurowet-core' ),
			'_ew_documents'         => __( 'Dokumenty (PDF)', 'eurowet-core' ),
			'_ew_video'             => __( 'Film', 'eurowet-core' ),
			'_ew_video_transcript'  => __( 'Transkrypcja / opis filmu', 'eurowet-core' ),
			'_ew_spin360'           => __( 'Zdjęcia 360° (kolejne klatki)', 'eurowet-core' ),
			'_ew_model3d'           => __( 'Model 3D (GLB) — tylko zgodny z oryginalnym opakowaniem', 'eurowet-core' ),
			'_ew_rel_similar'       => __( 'Podobne produkty (ręcznie — mają pierwszeństwo)', 'eurowet-core' ),
			'_ew_rel_complementary' => __( 'Produkty uzupełniające (ręcznie — mają pierwszeństwo)', 'eurowet-core' ),
			'_ew_key_ingredients'   => __( 'Kluczowe składniki', 'eurowet-core' ),
			'_ew_evidence'          => __( 'Uzasadnienia zastosowań (cytaty z tekstu produktu)', 'eurowet-core' ),
			'_ew_short_answer'      => __( 'W skrócie (2–4 zdania)', 'eurowet-core' ),
			'_ew_synonyms'          => __( 'Synonimy i sformułowania użytkowników — jedno w linii', 'eurowet-core' ),
			'_ew_questions'         => __( 'Pytania użytkowników — jedno w linii', 'eurowet-core' ),
			'_ew_products'          => __( 'Produkty dla tej potrzeby (tylko potwierdzone tekstem produktu)', 'eurowet-core' ),
			'_ew_guides'            => __( 'Porady', 'eurowet-core' ),
			'_ew_red_flags'         => __( 'Czerwone flagi — objawy wymagające weterynarza (jedna w linii)', 'eurowet-core' ),
			'_ew_red_flag_level'    => __( 'Poziom ostrzeżenia', 'eurowet-core' ),
			'_ew_priority'          => __( 'Priorytet (0–100)', 'eurowet-core' ),
			'_ew_active'            => __( 'Aktywna (widoczna w wyszukiwarce)', 'eurowet-core' ),
			'_ew_faq'               => __( 'FAQ', 'eurowet-core' ),
			'_ew_care_steps'        => __( 'Co można zrobić pielęgnacyjnie', 'eurowet-core' ),
			'_ew_avoid'             => __( 'Czego unikać', 'eurowet-core' ),
			'_ew_tldr'              => __( 'W skrócie (odpowiedź na główne pytanie)', 'eurowet-core' ),
			'_ew_stage'             => __( 'Etap ścieżki użytkownika', 'eurowet-core' ),
			'_ew_reviewed'          => __( 'Data ostatniej weryfikacji merytorycznej', 'eurowet-core' ),
			'_ew_needs_review'      => __( 'Wymaga ponownego sprawdzenia', 'eurowet-core' ),
			'_ew_review_note'       => __( 'Notatka redakcyjna', 'eurowet-core' ),
			'_ew_author_label'      => __( 'Autor / redakcja (bez wymyślonych osób)', 'eurowet-core' ),
			'_ew_reviewer'          => __( 'Weryfikacja merytoryczna (tylko realna, zatwierdzona osoba)', 'eurowet-core' ),
			'_ew_sources'           => __( 'Źródła', 'eurowet-core' ),
			'_ew_needs'             => __( 'Powiązane potrzeby / problemy', 'eurowet-core' ),
			'_ew_related'           => __( 'Powiązane porady (ręcznie)', 'eurowet-core' ),
			'_ew_next'              => __( 'Następna porada (ręcznie)', 'eurowet-core' ),
			'_ew_red_flag'          => __( 'Pokaż ramkę „kiedy do weterynarza”', 'eurowet-core' ),
			'_ew_legacy_path'       => __( 'Stary adres URL (migracja)', 'eurowet-core' ),
			'_ew_inci'              => __( 'Nazwa INCI / w składzie', 'eurowet-core' ),
			'_ew_aliases'           => __( 'Inne nazwy — jedna w linii', 'eurowet-core' ),
			'_ew_summary'           => __( 'Czym jest (ogólnie, ze źródłem)', 'eurowet-core' ),
			'_ew_function_quotes'   => __( 'Funkcja w produktach Eurowet (cytaty)', 'eurowet-core' ),
			'_ew_first_name'        => __( 'Imię', 'eurowet-core' ),
			'_ew_last_name'         => __( 'Nazwisko', 'eurowet-core' ),
			'_ew_position'          => __( 'Stanowisko', 'eurowet-core' ),
			'_ew_phone'             => __( 'Telefon', 'eurowet-core' ),
			'_ew_email'             => __( 'E-mail', 'eurowet-core' ),
			'_ew_voivodeships'      => __( 'Województwa', 'eurowet-core' ),
			'_ew_segments'          => __( 'Obsługiwane segmenty — jeden w linii', 'eurowet-core' ),
			'_ew_order'             => __( 'Kolejność', 'eurowet-core' ),
			'_ew_file'              => __( 'Plik', 'eurowet-core' ),
			'_ew_lang'              => __( 'Język materiału', 'eurowet-core' ),
			'_ew_source_url'        => __( 'Adres źródłowy', 'eurowet-core' ),
		);
	}

	public static function register(): void {
		add_action( 'add_meta_boxes', array( self::class, 'add' ), 20 );
		add_action( 'save_post', array( self::class, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ) );
	}

	public static function add( string $post_type ): void {
		if ( ! isset( Registry::META[ $post_type ] ) || 'ew_lead' === $post_type ) {
			return;
		}
		$title = 'product' === $post_type ? __( 'Eurowet — dane produktu, relacje i wiedza', 'eurowet-core' ) : __( 'Dane i relacje', 'eurowet-core' );
		add_meta_box( 'ew-meta', $title, array( self::class, 'render' ), $post_type, 'normal', 'high' );
	}

	public static function assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! isset( Registry::META[ $screen->post_type ] ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'ew-admin-meta', EW_CORE_URL . 'assets/css/admin/meta.css', array(), EW_CORE_VERSION );
		wp_enqueue_script( 'ew-admin-meta', EW_CORE_URL . 'assets/js/admin/meta.js', array( 'wp-api-fetch' ), EW_CORE_VERSION, true );
		wp_localize_script(
			'ew-admin-meta',
			'ewMeta',
			array(
				'i18n' => array(
					'search' => __( 'Szukaj…', 'eurowet-core' ),
					'remove' => __( 'Usuń', 'eurowet-core' ),
					'add'    => __( 'Dodaj', 'eurowet-core' ),
					'up'     => __( 'W górę', 'eurowet-core' ),
					'none'   => __( 'Brak wyników', 'eurowet-core' ),
					'choose' => __( 'Wybierz plik', 'eurowet-core' ),
				),
			)
		);
	}

	public static function render( \WP_Post $post ): void {
		wp_nonce_field( 'ew_meta_save', self::NONCE );
		$labels = self::labels();
		echo '<div class="ew-meta">';
		foreach ( Registry::META[ $post->post_type ] as $key => $type ) {
			if ( in_array( $key, array( '_ew_source_key', '_ew_need_ids' ), true ) ) {
				continue; // internal
			}
			$label = $labels[ $key ] ?? $key;
			$id    = 'ew' . $key;
			$name  = 'ew_meta[' . $key . ']';
			$value = Meta::get( $post->ID, $key );
			$rel   = Registry::relation( $post->post_type, $key );
			$shape = Registry::JSON_SHAPES[ $post->post_type ][ $key ] ?? null;
			echo '<div class="ew-meta__field ew-meta__field--' . esc_attr( $type ) . '">';
			printf( '<label class="ew-meta__label" for="%s">%s</label>', esc_attr( $id ), esc_html( $label ) );
			if ( 'attachment' === $rel ) {
				$ids = is_array( $value ) ? $value : ( $value ? array( (int) $value ) : array() );
				printf( '<div class="ew-media" data-multiple="%d"><input type="hidden" id="%s" name="%s" value="%s"><ul class="ew-media__list">', 'ids' === $type ? 1 : 0, esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', array_map( 'intval', $ids ) ) ) );
				foreach ( $ids as $aid ) {
					printf( '<li data-id="%d">%s</li>', (int) $aid, esc_html( get_the_title( (int) $aid ) ?: basename( (string) get_attached_file( (int) $aid ) ) ) );
				}
				printf( '</ul><button type="button" class="button ew-media__pick">%s</button> <button type="button" class="button-link ew-media__clear">%s</button></div>', esc_html__( 'Wybierz z biblioteki', 'eurowet-core' ), esc_html__( 'Wyczyść', 'eurowet-core' ) );
			} elseif ( $rel ) {
				$ids = is_array( $value ) ? $value : ( $value ? array( (int) $value ) : array() );
				printf( '<div class="ew-rel" data-type="%s" data-multiple="%d"><input type="hidden" id="%s" name="%s" value="%s"><ul class="ew-rel__chips">', esc_attr( $rel ), 'ids' === $type ? 1 : 0, esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', array_map( 'intval', $ids ) ) ) );
				foreach ( $ids as $rid ) {
					printf( '<li data-id="%d">%s</li>', (int) $rid, esc_html( get_the_title( (int) $rid ) ) );
				}
				printf( '</ul><input type="search" class="ew-rel__search" placeholder="%s" aria-label="%s"><ul class="ew-rel__results" role="listbox"></ul></div>', esc_attr__( 'Szukaj i dodaj…', 'eurowet-core' ), esc_attr( $label ) );
			} elseif ( 'json' === $type && $shape ) {
				self::renderShape( $id, $name, $shape, is_array( $value ) ? $value : array() );
			} else {
				self::renderScalar( $post->post_type, $key, $type, $id, $name, $value );
			}
			echo '</div>';
		}
		echo '</div>';
	}

	private static function renderScalar( string $pt, string $key, string $type, string $id, string $name, $value ): void {
		$enum = Registry::ENUMS[ $pt ][ $key ] ?? null;
		if ( $enum ) {
			$labels = array(
				'none' => __( 'brak', 'eurowet-core' ), 'caution' => __( 'ostrożność — pokaż zalecenie konsultacji', 'eurowet-core' ), 'urgent' => __( 'pilne — bez rekomendacji produktu', 'eurowet-core' ),
				'informational' => __( 'zrozum problem', 'eurowet-core' ), 'practical' => __( 'jak pielęgnować', 'eurowet-core' ), 'product' => __( 'dobór produktu', 'eurowet-core' ),
			);
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $enum as $opt ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( (string) $value, $opt, false ), esc_html( $labels[ $opt ] ?? $opt ) );
			}
			echo '</select>';
			return;
		}
		switch ( $type ) {
			case 'html':
				wp_editor( (string) $value, $id, array( 'textarea_name' => $name, 'textarea_rows' => 5, 'media_buttons' => false, 'teeny' => true, 'quicktags' => true ) );
				break;
			case 'text':
				printf( '<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
				break;
			case 'bool':
				printf( '<input type="hidden" name="%s" value="0"><input type="checkbox" id="%s" name="%s" value="1" %s>', esc_attr( $name ), esc_attr( $id ), esc_attr( $name ), checked( (bool) $value, true, false ) );
				break;
			case 'int':
				printf( '<input type="number" id="%s" name="%s" value="%s" class="small-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
				break;
			case 'date':
				printf( '<input type="date" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
				break;
			case 'email':
			case 'url':
				printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
				break;
			default:
				printf( '<input type="text" id="%s" name="%s" value="%s" class="large-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
		}
	}

	private static function renderShape( string $id, string $name, string $shape_name, array $rows ): void {
		$shape = Registry::SHAPES[ $shape_name ] ?? null;
		if ( ! $shape ) {
			return;
		}
		if ( 'voivodeship' === ( $shape['item'] ?? '' ) ) {
			echo '<fieldset class="ew-checks"><legend class="screen-reader-text">' . esc_html__( 'Województwa', 'eurowet-core' ) . '</legend>';
			printf( '<input type="hidden" name="%s[]" value="">', esc_attr( $name ) );
			foreach ( Voivodeships::all() as $slug => $label ) {
				printf( '<label><input type="checkbox" name="%s[]" value="%s" %s> %s</label>', esc_attr( $name ), esc_attr( $slug ), checked( in_array( $slug, $rows, true ), true, false ), esc_html( $label ) );
			}
			echo '</fieldset>';
			return;
		}
		if ( isset( $shape['item'] ) ) { // list of strings → textarea, one per line
			printf( '<textarea id="%s" name="%s" rows="5" class="large-text" data-lines="1">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( implode( "\n", array_map( 'strval', $rows ) ) ) );
			return;
		}
		$fields = (array) $shape['fields'];
		$labels = array(
			'q' => __( 'Pytanie', 'eurowet-core' ), 'a' => __( 'Odpowiedź', 'eurowet-core' ), 'title' => __( 'Tytuł', 'eurowet-core' ), 'url' => 'URL', 'publisher' => __( 'Wydawca', 'eurowet-core' ), 'year' => __( 'Rok', 'eurowet-core' ),
			'label' => __( 'Nazwa', 'eurowet-core' ), 'product_id' => __( 'Produkt', 'eurowet-core' ), 'role' => __( 'Rola', 'eurowet-core' ), 'reason' => __( 'Dlaczego pasuje', 'eurowet-core' ), 'evidence' => __( 'Cytat z tekstu produktu', 'eurowet-core' ),
			'need' => __( 'Potrzeba', 'eurowet-core' ), 'quote' => __( 'Cytat', 'eurowet-core' ), 'field' => __( 'Pole źródłowe', 'eurowet-core' ), 'family_slug' => __( 'Produkt (rodzina)', 'eurowet-core' ),
		);
		$roles  = array( 'primary' => __( 'główny', 'eurowet-core' ), 'similar' => __( 'podobny', 'eurowet-core' ), 'complementary' => __( 'uzupełniający', 'eurowet-core' ) );
		$rels   = (array) ( $shape['relations'] ?? array() );
		$render_row = static function ( $row, $i ) use ( $fields, $labels, $roles, $rels, $name, $shape ): string {
			ob_start();
			echo '<div class="ew-rep__row">';
			foreach ( $fields as $f => $ftype ) {
				$fname = $name . '[' . $i . '][' . $f . ']';
				$val   = is_array( $row ) ? ( $row[ $f ] ?? '' ) : '';
				echo '<label class="ew-rep__cell ew-rep__cell--' . esc_attr( $f ) . '"><span>' . esc_html( $labels[ $f ] ?? $f ) . '</span>';
				if ( isset( $rels[ $f ] ) ) {
					printf( '<span class="ew-rel ew-rel--single" data-type="%s" data-multiple="0"><input type="hidden" name="%s" value="%s"><span class="ew-rel__chips">%s</span><input type="search" class="ew-rel__search" placeholder="%s"><span class="ew-rel__results" role="listbox"></span></span>', esc_attr( $rels[ $f ] ), esc_attr( $fname ), esc_attr( (string) $val ), $val ? '<span class="ew-rel__chip" data-id="' . (int) $val . '">' . esc_html( get_the_title( (int) $val ) ) . '</span>' : '', esc_attr__( 'Szukaj…', 'eurowet-core' ) );
				} elseif ( 'enum' === $ftype ) {
					printf( '<select name="%s">', esc_attr( $fname ) );
					foreach ( (array) ( $shape['enums'][ $f ] ?? array() ) as $opt ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( (string) $val, $opt, false ), esc_html( $roles[ $opt ] ?? $opt ) );
					}
					echo '</select>';
				} elseif ( in_array( $ftype, array( 'html', 'text' ), true ) ) {
					printf( '<textarea name="%s" rows="2">%s</textarea>', esc_attr( $fname ), esc_textarea( (string) $val ) );
				} else {
					printf( '<input type="%s" name="%s" value="%s">', 'url' === $ftype ? 'url' : 'text', esc_attr( $fname ), esc_attr( (string) $val ) );
				}
				echo '</label>';
			}
			printf( '<span class="ew-rep__tools"><button type="button" class="button-link ew-rep__up" aria-label="%s">↑</button> <button type="button" class="button-link-delete ew-rep__del">%s</button></span></div>', esc_attr__( 'W górę', 'eurowet-core' ), esc_html__( 'Usuń', 'eurowet-core' ) );
			return (string) ob_get_clean();
		};
		echo '<div class="ew-rep" id="' . esc_attr( $id ) . '">';
		printf( '<input type="hidden" name="%s[__present]" value="1">', esc_attr( $name ) );
		echo '<div class="ew-rep__rows">';
		foreach ( array_values( $rows ) as $i => $row ) {
			echo $render_row( $row, $i ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
		}
		echo '</div><template class="ew-rep__tpl">' . $render_row( array(), '__i__' ) . '</template>'; // phpcs:ignore WordPress.Security.EscapeOutput
		printf( '<button type="button" class="button ew-rep__add">%s</button></div>', esc_html__( 'Dodaj wiersz', 'eurowet-core' ) );
	}

	public static function save( $post_id, $post ): void {
		if ( ! $post instanceof \WP_Post || ! isset( Registry::META[ $post->post_type ] ) || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST[ self::NONCE ] ) ), 'ew_meta_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$input = isset( $_POST['ew_meta'] ) && is_array( $_POST['ew_meta'] ) ? wp_unslash( $_POST['ew_meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per key by Meta::set.
		foreach ( Registry::META[ $post->post_type ] as $key => $type ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}
			$raw   = $input[ $key ];
			$shape = Registry::JSON_SHAPES[ $post->post_type ][ $key ] ?? null;
			if ( 'ids' === $type || ( Registry::relation( $post->post_type, $key ) && 'int' === $type ) ) {
				$raw = array_values( array_filter( array_map( 'intval', explode( ',', (string) $raw ) ) ) );
				$raw = 'int' === $type ? ( $raw[0] ?? 0 ) : $raw;
			} elseif ( 'json' === $type && $shape ) {
				$def = Registry::SHAPES[ $shape ] ?? array();
				if ( isset( $def['item'] ) && 'voivodeship' !== $def['item'] ) {
					$raw = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $raw ) ?: array() ) ) );
				} elseif ( is_array( $raw ) ) {
					unset( $raw['__present'], $raw['__i__'] );
					$raw = array_values( array_filter( $raw, static fn( $v ) => is_array( $v ) ? array_filter( $v, static fn( $x ) => '' !== $x ) : '' !== $v ) );
				}
			}
			Meta::set( (int) $post_id, $key, $raw );
		}
	}
}
