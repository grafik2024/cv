/* Consent manager: stores the choice (first-party cookie, 180 days), updates Consent Mode, activates gated tags. */
const COOKIE = 'ew_consent';
const banner = document.querySelector( '[data-ew-consent]' );
const dialog = document.querySelector( '[data-ew-consent-dialog]' );

const read = () => {
	const m = document.cookie.match( /(?:^|; )ew_consent=([^;]+)/ );
	try { return m ? JSON.parse( decodeURIComponent( m[ 1 ] ) ) : null; } catch ( e ) { return null; }
};
const activate = ( c ) => {
	document.querySelectorAll( 'script[type="text/plain"][data-ew-consent]' ).forEach( ( s ) => {
		if ( ! c[ s.dataset.ewConsent ] || s.dataset.ewDone ) return;
		s.dataset.ewDone = '1';
		const n = document.createElement( 'script' );
		if ( s.dataset.src ) { n.src = s.dataset.src; n.async = true; } else { n.textContent = s.textContent; }
		s.after( n );
	} );
};
const save = ( c ) => {
	const v = { v: 1, analytics: !! c.analytics, marketing: !! c.marketing, ts: Date.now() };
	document.cookie = COOKIE + '=' + encodeURIComponent( JSON.stringify( v ) ) + '; Max-Age=' + 180 * 86400 + '; Path=/; SameSite=Lax' + ( location.protocol === 'https:' ? '; Secure' : '' );
	if ( typeof window.gtag === 'function' ) {
		window.gtag( 'consent', 'update', { analytics_storage: v.analytics ? 'granted' : 'denied', ad_storage: v.marketing ? 'granted' : 'denied', ad_user_data: v.marketing ? 'granted' : 'denied', ad_personalization: v.marketing ? 'granted' : 'denied' } );
	}
	activate( v );
	if ( banner ) banner.hidden = true;
	if ( dialog && dialog.open ) dialog.close();
};
const openSettings = () => {
	if ( ! dialog ) return;
	const c = read() || {};
	dialog.querySelector( '[name=analytics]' ).checked = !! c.analytics;
	dialog.querySelector( '[name=marketing]' ).checked = !! c.marketing;
	dialog.showModal();
};

document.addEventListener( 'click', ( e ) => {
	const a = e.target.closest( '[data-ew-consent-action]' );
	if ( a ) {
		const act = a.dataset.ewConsentAction;
		if ( act === 'accept' ) save( { analytics: true, marketing: true } );
		if ( act === 'reject' ) save( { analytics: false, marketing: false } );
		if ( act === 'settings' ) openSettings();
		if ( act === 'save' ) save( { analytics: dialog.querySelector( '[name=analytics]' ).checked, marketing: dialog.querySelector( '[name=marketing]' ).checked } );
		return;
	}
	if ( e.target.closest( '[data-ew-consent-open]' ) ) { e.preventDefault(); openSettings(); }
} );

const existing = read();
if ( existing ) activate( existing );
