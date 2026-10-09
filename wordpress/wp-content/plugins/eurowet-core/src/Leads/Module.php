<?php
/**
 * B2B / private label / rep contact forms: REST + no-JS admin-post handler, stored as private ew_lead
 * posts, e-mail notification, consent record, retention cron. Spam: honeypot, min fill time, rate limit.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Leads;

use Eurowet\Core\Admin\Settings;
use Eurowet\Core\Contracts\ModuleInterface;
use Eurowet\Core\Data\Meta;
use Eurowet\Core\Reps\Voivodeships;
use Eurowet\Core\Security\RateLimiter;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public const FORMS          = array( 'b2b', 'private_label', 'rep_contact', 'contact' );
	public const CONSENT_VERSION = '2026-10';
	private const CRON          = 'ew_leads_retention';

	public static function segments(): array {
		return array(
			'hurtownia'   => __( 'Hurtownia / dystrybutor', 'eurowet-core' ),
			'sklep'       => __( 'Sklep zoologiczny', 'eurowet-core' ),
			'ecommerce'   => __( 'Sklep internetowy / e-commerce', 'eurowet-core' ),
			'lecznica'    => __( 'Lecznica / gabinet weterynaryjny', 'eurowet-core' ),
			'groomer'     => __( 'Groomer / salon pielęgnacji', 'eurowet-core' ),
			'hodowla'     => __( 'Hodowla', 'eurowet-core' ),
			'siec'        => __( 'Sieć handlowa', 'eurowet-core' ),
			'importer'    => __( 'Importer / partner zagraniczny', 'eurowet-core' ),
			'inne'        => __( 'Inne', 'eurowet-core' ),
		);
	}

	public static function consentText(): string {
		return sprintf(
			/* translators: %s: privacy policy URL */
			__( 'Wyrażam zgodę na przetwarzanie moich danych przez Eurowet w celu odpowiedzi na zapytanie. Szczegóły w <a href="%s">polityce prywatności</a>.', 'eurowet-core' ),
			esc_url( home_url( '/polityka-prywatnosci/' ) )
		);
	}

	public function register(): void {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
		add_action( 'admin_post_nopriv_ew_lead', array( self::class, 'postHandler' ) );
		add_action( 'admin_post_ew_lead', array( self::class, 'postHandler' ) );
		add_action( self::CRON, array( self::class, 'retention' ) );
		add_action( 'init', static fn() => wp_next_scheduled( self::CRON ) || wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', self::CRON ) );
		add_action( 'add_meta_boxes_ew_lead', array( self::class, 'metaBox' ) );
		add_action( 'save_post_ew_lead', array( self::class, 'saveStatus' ), 10, 2 );
		add_action(
			'ew_settings_sections',
			static function (): void {
				Settings::addSection(
					'contact',
					__( 'Kontakt i formularze', 'eurowet-core' ),
					array(
						array( 'id' => 'contact_phone', 'type' => 'text', 'label' => __( 'Telefon (biuro)', 'eurowet-core' ) ),
						array( 'id' => 'contact_phone_sales', 'type' => 'text', 'label' => __( 'Telefon (sprzedaż)', 'eurowet-core' ) ),
						array( 'id' => 'contact_email', 'type' => 'email', 'label' => __( 'E-mail kontaktowy', 'eurowet-core' ) ),
						array( 'id' => 'company_name', 'type' => 'text', 'label' => __( 'Nazwa firmy', 'eurowet-core' ) ),
						array( 'id' => 'company_address', 'type' => 'textarea', 'label' => __( 'Adres', 'eurowet-core' ) ),
						array( 'id' => 'company_ids', 'type' => 'text', 'label' => __( 'NIP / REGON / KRS', 'eurowet-core' ) ),
						array( 'id' => 'company_social', 'type' => 'textarea', 'label' => __( 'Profile społecznościowe (URL, jeden w linii)', 'eurowet-core' ) ),
						array( 'id' => 'leads_email_b2b', 'type' => 'email', 'label' => __( 'Odbiorca zapytań B2B', 'eurowet-core' ) ),
						array( 'id' => 'leads_email_private_label', 'type' => 'email', 'label' => __( 'Odbiorca zapytań — marka własna', 'eurowet-core' ) ),
						array( 'id' => 'leads_autoreply', 'type' => 'checkbox', 'label' => __( 'Wysyłaj automatyczne potwierdzenie do nadawcy', 'eurowet-core' ), 'default' => 1 ),
						array( 'id' => 'leads_retention_months', 'type' => 'number', 'label' => __( 'Przechowywanie zapytań (miesiące)', 'eurowet-core' ), 'default' => 24, 'min' => 1, 'max' => 120 ),
					)
				);
			}
		);
	}

	public static function routes(): void {
		register_rest_route(
			'eurowet/v1',
			'/lead/token',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => static function () {
					$r = new \WP_REST_Response( array( 'ts' => self::timestamp() ), 200 );
					$r->header( 'Cache-Control', 'no-store' );
					return $r;
				},
			)
		);
		register_rest_route(
			'eurowet/v1',
			'/lead',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => static function ( \WP_REST_Request $r ) {
					$res = self::process( (array) $r->get_params() );
					return new \WP_REST_Response( $res, $res['ok'] ? 201 : ( 'rate_limited' === ( $res['code'] ?? '' ) ? 429 : 422 ) );
				},
			)
		);
	}

	/**
	 * Validates and stores a lead.
	 *
	 * @param array<string, mixed> $in
	 * @return array{ok: bool, code?: string, message: string, errors?: array<string, string>}
	 */
	public static function process( array $in ): array {
		if ( ! empty( $in['ew_hp'] ) ) {
			return array( 'ok' => true, 'message' => __( 'Dziękujemy. Odezwiemy się wkrótce.', 'eurowet-core' ) ); // silently drop bots
		}
		$ts = (string) ( $in['ew_ts'] ?? '' );
		if ( ! self::validTimestamp( $ts ) ) {
			return array( 'ok' => false, 'code' => 'too_fast', 'message' => __( 'Formularz został wysłany zbyt szybko lub wygasł. Odśwież stronę i spróbuj ponownie.', 'eurowet-core' ) );
		}
		if ( ! RateLimiter::hit( 'lead', 5, HOUR_IN_SECONDS ) ) {
			return array( 'ok' => false, 'code' => 'rate_limited', 'message' => __( 'Zbyt wiele wysłanych formularzy. Spróbuj później lub zadzwoń do nas.', 'eurowet-core' ) );
		}
		$form = in_array( (string) ( $in['form'] ?? '' ), self::FORMS, true ) ? (string) $in['form'] : 'contact';
		$data = array(
			'company'     => sanitize_text_field( (string) ( $in['company'] ?? '' ) ),
			'name'        => sanitize_text_field( (string) ( $in['name'] ?? '' ) ),
			'email'       => sanitize_email( (string) ( $in['email'] ?? '' ) ),
			'phone'       => sanitize_text_field( (string) ( $in['phone'] ?? '' ) ),
			'voivodeship' => sanitize_key( (string) ( $in['voivodeship'] ?? '' ) ),
			'segment'     => sanitize_key( (string) ( $in['segment'] ?? '' ) ),
			'message'     => sanitize_textarea_field( (string) ( $in['message'] ?? '' ) ),
		);
		$errors = array();
		if ( '' === $data['name'] ) {
			$errors['name'] = __( 'Podaj imię i nazwisko.', 'eurowet-core' );
		}
		if ( ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'Podaj poprawny adres e-mail.', 'eurowet-core' );
		}
		if ( in_array( $form, array( 'b2b', 'private_label' ), true ) && '' === $data['company'] ) {
			$errors['company'] = __( 'Podaj nazwę firmy.', 'eurowet-core' );
		}
		if ( mb_strlen( $data['message'] ) < 10 ) {
			$errors['message'] = __( 'Opisz krótko, w czym możemy pomóc (min. 10 znaków).', 'eurowet-core' );
		}
		if ( '' !== $data['voivodeship'] && ! Voivodeships::isValid( $data['voivodeship'] ) ) {
			$errors['voivodeship'] = __( 'Wybierz województwo z listy.', 'eurowet-core' );
		}
		if ( '' !== $data['segment'] && ! isset( self::segments()[ $data['segment'] ] ) ) {
			$data['segment'] = 'inne';
		}
		if ( empty( $in['consent'] ) ) {
			$errors['consent'] = __( 'Zgoda jest wymagana, abyśmy mogli odpowiedzieć na zapytanie.', 'eurowet-core' );
		}
		if ( $errors ) {
			return array( 'ok' => false, 'code' => 'invalid', 'message' => __( 'Popraw zaznaczone pola.', 'eurowet-core' ), 'errors' => $errors );
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'ew_lead',
				'post_status' => 'private',
				'post_title'  => wp_strip_all_tags( ( $data['company'] ?: $data['name'] ) . ' — ' . $form ),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return array( 'ok' => false, 'code' => 'store', 'message' => __( 'Nie udało się zapisać zapytania. Zadzwoń do nas.', 'eurowet-core' ) );
		}
		foreach ( $data as $k => $v ) {
			Meta::set( (int) $id, '_ew_' . $k, $v );
		}
		Meta::set( (int) $id, '_ew_form', $form );
		Meta::set( (int) $id, '_ew_status', 'new' );
		Meta::set( (int) $id, '_ew_lang', function_exists( 'pll_current_language' ) ? (string) pll_current_language() : 'pl' );
		Meta::set( (int) $id, '_ew_source_url', esc_url_raw( (string) ( $in['source_url'] ?? wp_get_referer() ) ) );
		Meta::set( (int) $id, '_ew_consent', array( 'text' => wp_strip_all_tags( self::consentText() ), 'version' => self::CONSENT_VERSION, 'at' => gmdate( 'c' ) ) );
		self::notify( (int) $id, $form, $data );
		return array( 'ok' => true, 'message' => __( 'Dziękujemy! Zapytanie zostało wysłane — odpowiemy najszybciej, jak to możliwe.', 'eurowet-core' ) );
	}

	private static function notify( int $id, string $form, array $d ): void {
		$to = (string) ew_get_option( 'leads_email_' . $form, '' ) ?: (string) ew_get_option( 'contact_email', '' ) ?: (string) get_option( 'admin_email' );
		$labels = array( 'b2b' => 'B2B', 'private_label' => __( 'Marka własna', 'eurowet-core' ), 'rep_contact' => __( 'Kontakt z przedstawicielem', 'eurowet-core' ), 'contact' => __( 'Kontakt', 'eurowet-core' ) );
		$body   = array();
		foreach ( $d as $k => $v ) {
			if ( '' !== $v ) {
				$body[] = ucfirst( $k ) . ': ' . ( 'voivodeship' === $k ? Voivodeships::name( $v ) : ( 'segment' === $k ? ( self::segments()[ $v ] ?? $v ) : $v ) );
			}
		}
		$body[] = '';
		$body[] = admin_url( 'post.php?post=' . $id . '&action=edit' );
		wp_mail( $to, sprintf( '[Eurowet] %s: %s', $labels[ $form ] ?? $form, $d['company'] ?: $d['name'] ), implode( "\n", $body ), array( 'Reply-To: ' . $d['name'] . ' <' . $d['email'] . '>' ) );
		if ( ew_get_option( 'leads_autoreply', 1 ) ) {
			wp_mail( $d['email'], __( 'Eurowet — potwierdzenie otrzymania zapytania', 'eurowet-core' ), __( "Dziękujemy za wiadomość. Otrzymaliśmy Twoje zapytanie i odpowiemy najszybciej, jak to możliwe.\n\nZespół Eurowet", 'eurowet-core' ) );
		}
	}

	public static function timestamp(): string {
		$t = (string) time();
		return $t . '.' . substr( hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) ), 0, 16 );
	}

	private static function validTimestamp( string $ts ): bool {
		[ $t, $sig ] = array_pad( explode( '.', $ts, 2 ), 2, '' );
		if ( ! ctype_digit( $t ) || ! hash_equals( substr( hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) ), 0, 16 ), $sig ) ) {
			return false;
		}
		$age = time() - (int) $t;
		return $age >= 3 && $age <= WEEK_IN_SECONDS; // pages may be served from full-page cache for days
	}

	public static function postHandler(): void {
		$res  = self::process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- public form; protected by signed timestamp, honeypot and rate limit.
		$back = wp_get_referer() ?: home_url( '/' );
		set_transient( 'ew_lead_flash_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ), $res, 120 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_safe_redirect( add_query_arg( 'ew_lead', $res['ok'] ? 'ok' : 'error', $back ) . '#ew-lead-form' );
		exit;
	}

	public static function flash(): ?array {
		$key = 'ew_lead_flash_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$v   = get_transient( $key );
		if ( is_array( $v ) ) {
			delete_transient( $key );
			return $v;
		}
		return null;
	}

	public static function metaBox(): void {
		add_meta_box(
			'ew-lead',
			__( 'Zapytanie', 'eurowet-core' ),
			static function ( \WP_Post $p ): void {
				wp_nonce_field( 'ew_lead_status', 'ew_lead_nonce' );
				echo '<table class="form-table"><tbody>';
				foreach ( array( '_ew_form', '_ew_company', '_ew_name', '_ew_email', '_ew_phone', '_ew_voivodeship', '_ew_segment', '_ew_message', '_ew_lang', '_ew_source_url' ) as $k ) {
					printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( str_replace( '_ew_', '', $k ) ), nl2br( esc_html( (string) Meta::get( $p->ID, $k, '' ) ) ) );
				}
				$c = Meta::json( $p->ID, '_ew_consent' );
				printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Zgoda', 'eurowet-core' ), esc_html( ( $c['at'] ?? '' ) . ' — v' . ( $c['version'] ?? '' ) ) );
				$status = (string) Meta::get( $p->ID, '_ew_status', 'new' );
				echo '<tr><th><label for="ew-lead-status">' . esc_html__( 'Status', 'eurowet-core' ) . '</label></th><td><select id="ew-lead-status" name="ew_lead_status">';
				foreach ( array( 'new' => __( 'nowe', 'eurowet-core' ), 'in_progress' => __( 'w realizacji', 'eurowet-core' ), 'closed' => __( 'zamknięte', 'eurowet-core' ) ) as $k => $l ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $status, $k, false ), esc_html( $l ) );
				}
				echo '</select></td></tr></tbody></table>';
			},
			'ew_lead',
			'normal',
			'high'
		);
	}

	public static function saveStatus( $post_id, $post ): void {
		if ( ! isset( $_POST['ew_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['ew_lead_nonce'] ) ), 'ew_lead_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		Meta::set( (int) $post_id, '_ew_status', sanitize_key( wp_unslash( (string) ( $_POST['ew_lead_status'] ?? 'new' ) ) ) );
	}

	public static function retention(): void {
		$months = max( 1, (int) ew_get_option( 'leads_retention_months', 24 ) );
		$old    = get_posts( array( 'post_type' => 'ew_lead', 'post_status' => 'any', 'posts_per_page' => 200, 'fields' => 'ids', 'date_query' => array( array( 'before' => "-{$months} months" ) ) ) );
		foreach ( $old as $id ) {
			wp_delete_post( (int) $id, true );
		}
	}
}
