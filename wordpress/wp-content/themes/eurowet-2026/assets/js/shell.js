/* Site shell: mega menu disclosure, mobile drawer, search dialog, theme toggle, header state. Vanilla ES module. */
const html = document.documentElement;

// Mega menu: click/keyboard disclosure; Esc closes; outside click and focus-out close.
document.querySelectorAll( '.ew-nav__toggle' ).forEach( ( btn ) => {
	const item = btn.parentElement;
	const panel = item.querySelector( '.ew-mega' );
	if ( ! panel ) return;
	const inDrawer = !! btn.closest( '.ew-drawer' );
	const set = ( open ) => { btn.setAttribute( 'aria-expanded', String( open ) ); panel.hidden = ! open; };
	btn.addEventListener( 'click', () => {
		const open = btn.getAttribute( 'aria-expanded' ) !== 'true';
		if ( ! inDrawer ) {
			document.querySelectorAll( '.ew-nav .ew-nav__toggle[aria-expanded="true"]' ).forEach( ( o ) => {
				if ( o !== btn ) { o.setAttribute( 'aria-expanded', 'false' ); const p = o.parentElement.querySelector( '.ew-mega' ); if ( p ) p.hidden = true; }
			} );
		}
		set( open );
	} );
	item.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Escape' && ! panel.hidden ) { set( false ); btn.focus(); } } );
	if ( ! inDrawer ) {
		document.addEventListener( 'click', ( e ) => { if ( ! item.contains( e.target ) ) set( false ); } );
		item.addEventListener( 'focusout', ( e ) => { if ( e.relatedTarget && ! item.contains( e.relatedTarget ) ) set( false ); } );
	}
} );

// Dialogs (native <dialog>: focus containment, Esc and inert background are provided by the browser).
const wireDialog = ( dialog, openSel, closeSel, onOpen ) => {
	if ( ! dialog ) return;
	let opener = null;
	document.querySelectorAll( openSel ).forEach( ( b ) => b.addEventListener( 'click', () => {
		opener = b;
		dialog.showModal();
		b.setAttribute( 'aria-expanded', 'true' );
		if ( onOpen ) onOpen( dialog );
	} ) );
	dialog.querySelectorAll( closeSel ).forEach( ( b ) => b.addEventListener( 'click', () => dialog.close() ) );
	dialog.addEventListener( 'click', ( e ) => { if ( e.target === dialog ) dialog.close(); } );
	dialog.addEventListener( 'close', () => { if ( opener ) { opener.setAttribute( 'aria-expanded', 'false' ); opener.focus(); } } );
};
wireDialog( document.querySelector( '[data-ew-drawer]' ), '[data-ew-drawer-open]', '[data-ew-drawer-close]' );
wireDialog( document.querySelector( '[data-ew-search]' ), '[data-ew-search-open]', '[data-ew-search-close]', ( d ) => { const i = d.querySelector( 'input[type=search]' ); if ( i ) i.focus(); } );
document.addEventListener( 'keydown', ( e ) => {
	if ( e.key === '/' && ! /input|textarea|select/i.test( document.activeElement.tagName ) && ! document.activeElement.isContentEditable ) {
		const btn = document.querySelector( '[data-ew-search-open]' );
		if ( btn ) { e.preventDefault(); btn.click(); }
	}
} );

// Theme toggle cycles system → dark → light (stored in localStorage 'ew-theme'; head script applies it before paint).
const toggle = document.querySelector( '[data-ew-theme-toggle]' );
if ( toggle ) {
	const label = toggle.querySelector( '[data-ew-theme-label]' );
	const names = { system: 'Motyw: systemowy (kliknij, aby zmienić)', dark: 'Motyw: ciemny (kliknij, aby zmienić)', light: 'Motyw: jasny (kliknij, aby zmienić)' };
	const read = () => { try { return localStorage.getItem( 'ew-theme' ) || 'system'; } catch ( e ) { return 'system'; } };
	const apply = ( mode ) => {
		try {
			if ( mode === 'system' ) { html.removeAttribute( 'data-theme' ); localStorage.removeItem( 'ew-theme' ); }
			else { html.setAttribute( 'data-theme', mode ); localStorage.setItem( 'ew-theme', mode ); }
		} catch ( e ) { /* storage blocked: still switch for this page view */ if ( mode !== 'system' ) html.setAttribute( 'data-theme', mode ); }
		if ( label ) label.textContent = names[ mode ];
		toggle.title = names[ mode ];
	};
	apply( read() );
	toggle.addEventListener( 'click', () => apply( { system: 'dark', dark: 'light', light: 'system' }[ read() ] || 'system' ) );
}

// Header elevation after scrolling (class only — no layout change, no CLS).
const header = document.querySelector( '[data-ew-header]' );
if ( header && 'IntersectionObserver' in window ) {
	const sentinel = document.createElement( 'div' );
	sentinel.setAttribute( 'aria-hidden', 'true' );
	sentinel.style.cssText = 'position:absolute;top:0;left:0;height:1px;width:1px;pointer-events:none';
	document.body.prepend( sentinel );
	new IntersectionObserver( ( [ e ] ) => header.classList.toggle( 'is-scrolled', ! e.isIntersecting ) ).observe( sentinel );
}
