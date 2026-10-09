<?php
/**
 * Consent manager (RODO / ePrivacy): Akceptuj / Odrzuć / Ustawienia with equal weight, Google Consent
 * Mode v2 defaults denied before any tag, analytics/marketing tags injected only after consent.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Consent;

use Eurowet\Core\Admin\Settings;
use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public const COOKIE  = 'ew_consent';
	public const VERSION = 1;

	public function register(): void {
		add_action( 'wp_head', array( self::class, 'head' ), 1 );
		add_action( 'wp_footer', array( self::class, 'footer' ), 5 );
		add_action(
			'ew_settings_sections',
			static function (): void {
				Settings::addSection(
					'consent',
					__( 'Zgody i analityka', 'eurowet-core' ),
					array(
						array( 'id' => 'ga4_id', 'type' => 'text', 'label' => __( 'Google Analytics 4 — identyfikator (G-…)', 'eurowet-core' ), 'help' => __( 'Ładowany dopiero po zgodzie na analitykę.', 'eurowet-core' ) ),
						array( 'id' => 'gtm_id', 'type' => 'text', 'label' => __( 'Google Tag Manager — identyfikator (GTM-…)', 'eurowet-core' ), 'help' => __( 'Ładowany dopiero po zgodzie (analityka lub marketing).', 'eurowet-core' ) ),
						array( 'id' => 'meta_pixel_id', 'type' => 'text', 'label' => __( 'Meta Pixel — identyfikator', 'eurowet-core' ), 'help' => __( 'Ładowany dopiero po zgodzie marketingowej.', 'eurowet-core' ) ),
					)
				);
			}
		);
	}

	/** Consent Mode defaults must run before any Google tag. */
	public static function head(): void {
		if ( is_admin() ) {
			return;
		}
		?>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('consent','default',{ad_storage:'denied',analytics_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});(function(){try{var m=document.cookie.match(/(?:^|; )ew_consent=([^;]+)/);if(!m)return;var c=JSON.parse(decodeURIComponent(m[1]));gtag('consent','update',{analytics_storage:c.analytics?'granted':'denied',ad_storage:c.marketing?'granted':'denied',ad_user_data:c.marketing?'granted':'denied',ad_personalization:c.marketing?'granted':'denied'});}catch(e){}})();</script>
		<?php
		$ga4 = preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) ew_get_option( 'ga4_id', '' ) ) );
		$gtm = preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) ew_get_option( 'gtm_id', '' ) ) );
		$px  = preg_replace( '/\D/', '', (string) ew_get_option( 'meta_pixel_id', '' ) );
		// Gated tags: type="text/plain" — activated by consent.js only for granted categories.
		if ( $ga4 ) {
			printf( '<script type="text/plain" data-ew-consent="analytics" data-src="%s"></script>' . "\n", esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . $ga4 ) );
			printf( '<script type="text/plain" data-ew-consent="analytics">gtag("js",new Date());gtag("config",%s,{anonymize_ip:true});</script>' . "\n", wp_json_encode( $ga4 ) );
		}
		if ( $gtm ) {
			printf( '<script type="text/plain" data-ew-consent="analytics">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer",%s);</script>' . "\n", wp_json_encode( $gtm ) );
		}
		if ( $px ) {
			printf( '<script type="text/plain" data-ew-consent="marketing">!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init",%s);fbq("track","PageView");</script>' . "\n", wp_json_encode( $px ) );
		}
	}

	public static function footer(): void {
		if ( is_admin() ) {
			return;
		}
		echo ew_render( 'consent-banner' ); // phpcs:ignore WordPress.Security.EscapeOutput -- component escapes.
	}
}
