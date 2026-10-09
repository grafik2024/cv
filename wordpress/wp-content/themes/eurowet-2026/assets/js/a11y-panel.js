/* Accessibility panel: text size, high contrast, underline links, spacing, reduced motion, reset (localStorage 'ew-a11y'). */
const html = document.documentElement;
const KEY = 'ew-a11y';
const panel = document.querySelector( '[data-ew-a11y]' );
const opener = document.querySelector( '[data-ew-a11y-open]' );
const status = panel ? panel.querySelector( '[data-ew-a11y-status]' ) : null;
const read = () => { try { return JSON.parse( localStorage.getItem( KEY ) || '{}' ); } catch ( e ) { return {}; } };
const write = ( s ) => { try { localStorage.setItem( KEY, JSON.stringify( s ) ); } catch ( e ) { /* blocked storage: settings last for this page */ } };
const apply = ( s ) => {
	[ 'text', 'contrast', 'links', 'spacing', 'motion' ].forEach( ( k ) => { if ( s[ k ] ) html.setAttribute( 'data-a11y-' + k, s[ k ] ); else html.removeAttribute( 'data-a11y-' + k ); } );
	if ( ! panel ) return;
	panel.querySelectorAll( '[data-ew-a11y-set="text"]' ).forEach( ( b ) => b.setAttribute( 'aria-pressed', String( ( s.text || '' ) === b.dataset.value ) ) );
	panel.querySelectorAll( '[data-ew-a11y-toggle]' ).forEach( ( c ) => { c.checked = s[ c.dataset.ewA11yToggle ] === c.value; } );
};
const announce = ( t ) => { if ( status ) { status.textContent = ''; setTimeout( () => { status.textContent = t; }, 50 ); } };

if ( panel && opener ) {
	const setOpen = ( open ) => {
		panel.hidden = ! open;
		opener.setAttribute( 'aria-expanded', String( open ) );
		if ( open ) { const f = panel.querySelector( 'button, input' ); if ( f ) f.focus(); }
	};
	opener.addEventListener( 'click', () => setOpen( panel.hidden ) );
	const close = panel.querySelector( '[data-ew-a11y-close]' );
	if ( close ) close.addEventListener( 'click', () => { setOpen( false ); opener.focus(); } );
	panel.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Escape' ) { setOpen( false ); opener.focus(); } } );
	document.addEventListener( 'click', ( e ) => { if ( ! panel.hidden && ! panel.contains( e.target ) && ! opener.contains( e.target ) ) setOpen( false ); } );
	panel.addEventListener( 'click', ( e ) => {
		const b = e.target.closest( '[data-ew-a11y-set]' );
		if ( b ) { const s = read(); s[ b.dataset.ewA11ySet ] = b.dataset.value; write( s ); apply( s ); announce( 'Rozmiar tekstu: ' + b.textContent ); }
		if ( e.target.closest( '[data-ew-a11y-reset]' ) ) { write( {} ); apply( {} ); announce( 'Przywrócono ustawienia domyślne' ); }
	} );
	panel.addEventListener( 'change', ( e ) => {
		const c = e.target.closest( '[data-ew-a11y-toggle]' );
		if ( ! c ) return;
		const s = read();
		s[ c.dataset.ewA11yToggle ] = c.checked ? c.value : '';
		write( s );
		apply( s );
		announce( c.parentElement.textContent.trim() + ( c.checked ? ': włączone' : ': wyłączone' ) );
	} );
	apply( read() );
}
