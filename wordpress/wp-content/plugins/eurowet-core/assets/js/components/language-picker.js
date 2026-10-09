/* Language picker: disclosure with Esc/outside-click close and live filtering of extra languages. */
document.querySelectorAll( '[data-ew-lang]' ).forEach( ( root ) => {
	const btn = root.querySelector( '.ew-lang__toggle' );
	const panel = root.querySelector( '.ew-lang__panel' );
	const close = () => { btn.setAttribute( 'aria-expanded', 'false' ); panel.hidden = true; };
	btn.addEventListener( 'click', () => {
		const open = btn.getAttribute( 'aria-expanded' ) !== 'true';
		btn.setAttribute( 'aria-expanded', String( open ) );
		panel.hidden = ! open;
		if ( open ) panel.querySelector( 'a' )?.focus();
	} );
	root.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Escape' && ! panel.hidden ) { close(); btn.focus(); } } );
	document.addEventListener( 'click', ( e ) => { if ( ! root.contains( e.target ) ) close(); } );
	const search = root.querySelector( '[data-ew-lang-search]' );
	if ( search ) search.addEventListener( 'input', () => {
		const q = search.value.trim().toLowerCase();
		root.querySelectorAll( '.ew-lang__extra li' ).forEach( ( li ) => { li.hidden = q && ! li.textContent.toLowerCase().includes( q ) && ! ( li.querySelector( 'a' ).hreflang || '' ).startsWith( q ); } );
	} );
} );
