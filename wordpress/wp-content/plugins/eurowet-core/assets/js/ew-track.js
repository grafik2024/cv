/* Anonymous click tracking for the knowledge graph analytics (no cookies, no IDs). */
const cfgEl = document.getElementById( 'ew-config' );
const cfg = cfgEl ? JSON.parse( cfgEl.textContent ) : { rest: '/wp-json/eurowet/v1/' };

export function track( type, targetId, logId = 0 ) {
	try {
		const body = JSON.stringify( { type, target_id: Number( targetId ) || 0, log_id: Number( logId ) || 0 } );
		const url = cfg.rest + 'finder/event';
		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( url, new Blob( [ body ], { type: 'application/json' } ) );
		} else {
			fetch( url, { method: 'POST', body, headers: { 'Content-Type': 'application/json' }, keepalive: true } );
		}
	} catch ( e ) { /* analytics must never break navigation */ }
}

if ( ! window.__ewTrack ) {
	window.__ewTrack = true;
	document.addEventListener( 'click', ( e ) => {
		const a = e.target.closest( '[data-ew-track]' );
		if ( a ) track( a.dataset.ewTrack, a.dataset.ewTarget, a.dataset.ewLog || 0 );
	}, { capture: true } );
}

export { cfg };
