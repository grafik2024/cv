/* Product page: thumbnail → main image swap (links still work without JS) and an on-demand 360° image sequence
   (drag, arrow keys or the range slider; no autoplay). */
const gallery = document.querySelector( '[data-ew-gallery]' );
if ( gallery ) {
	const main = gallery.querySelector( '[data-ew-gallery-main]' );
	gallery.querySelectorAll( '[data-ew-gallery-src]' ).forEach( ( a ) => a.addEventListener( 'click', ( e ) => {
		if ( ! main || ! a.dataset.ewGallerySrc ) return;
		e.preventDefault();
		main.src = a.dataset.ewGallerySrc;
		if ( a.dataset.ewGallerySrcset ) main.srcset = a.dataset.ewGallerySrcset; else main.removeAttribute( 'srcset' );
		const img = a.querySelector( 'img' );
		if ( img ) main.alt = img.alt;
		gallery.querySelectorAll( '[data-ew-gallery-src]' ).forEach( ( o ) => o.removeAttribute( 'aria-current' ) );
		a.setAttribute( 'aria-current', 'true' );
	} ) );

	const spin = gallery.querySelector( '[data-ew-spin]' );
	const open = gallery.querySelector( '[data-ew-spin-open]' );
	if ( spin && open ) {
		let frames = [];
		try { frames = JSON.parse( spin.dataset.ewSpin || '[]' ).filter( Boolean ); } catch ( e ) { frames = []; }
		const img = spin.querySelector( 'img' );
		const range = spin.querySelector( 'input[type=range]' );
		let loaded = false;
		const show = ( i ) => { const n = frames.length; const k = ( ( i % n ) + n ) % n; img.src = frames[ k ]; range.value = String( k ); };
		const load = () => { if ( loaded ) return; loaded = true; frames.forEach( ( src ) => { const p = new Image(); p.decoding = 'async'; p.src = src; } ); show( 0 ); };
		open.addEventListener( 'click', () => {
			const on = spin.hidden;
			if ( on ) load();
			spin.hidden = ! on;
			if ( main ) main.hidden = on;
			open.setAttribute( 'aria-pressed', String( on ) );
			if ( on ) range.focus();
		} );
		range.addEventListener( 'input', () => show( parseInt( range.value, 10 ) ) );
		let startX = null; let startI = 0;
		img.addEventListener( 'pointerdown', ( e ) => { startX = e.clientX; startI = parseInt( range.value, 10 ); img.setPointerCapture( e.pointerId ); } );
		img.addEventListener( 'pointermove', ( e ) => { if ( startX === null ) return; show( startI + Math.round( ( e.clientX - startX ) / 12 ) ); } );
		img.addEventListener( 'pointerup', () => { startX = null; } );
	}
}
