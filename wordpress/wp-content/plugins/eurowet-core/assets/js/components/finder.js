/* Product Finder: live results (debounced, not logged) and in-place results on /potrzeby/. */
import { cfg } from '../ew-track.js';

const debounce = ( fn, ms ) => { let t; return ( ...a ) => { clearTimeout( t ); t = setTimeout( () => fn( ...a ), ms ); }; };

document.querySelectorAll( '[data-ew-finder]' ).forEach( ( root ) => {
	const form = root.querySelector( 'form' );
	const input = root.querySelector( 'input[name=q]' );
	const out = root.querySelector( '[data-ew-finder-results]' );
	const isPage = root.classList.contains( 'ew-finder--page' );
	const species = form.querySelector( 'input[name=gatunek]' );
	let ctrl;

	const run = async ( q, log ) => {
		if ( ctrl ) ctrl.abort();
		ctrl = new AbortController();
		const params = new URLSearchParams( { q, format: 'html', log: log ? '1' : '0', lang: cfg.lang || '' } );
		if ( species ) params.set( 'species', species.value );
		out.setAttribute( 'aria-busy', 'true' );
		try {
			const res = await fetch( cfg.rest + 'finder?' + params.toString(), { signal: ctrl.signal, headers: { Accept: 'application/json' } } );
			if ( ! res.ok ) throw new Error( res.status );
			const data = await res.json();
			out.innerHTML = data.html || '';
		} catch ( e ) {
			if ( e.name !== 'AbortError' ) out.innerHTML = '';
		} finally {
			out.removeAttribute( 'aria-busy' );
		}
	};

	const live = debounce( () => {
		const q = input.value.trim();
		if ( q.length >= 4 ) run( q, false );
		else out.innerHTML = '';
	}, 450 );
	input.addEventListener( 'input', live );

	if ( isPage ) {
		form.addEventListener( 'submit', ( e ) => {
			const q = input.value.trim();
			if ( ! q ) return;
			e.preventDefault();
			const url = new URL( window.location.href );
			url.searchParams.set( 'q', q );
			history.replaceState( null, '', url );
			const server = document.querySelector( '[data-ew-finder-server]' );
			if ( server ) server.remove();
			run( q, true ).then( () => { const h = out.querySelector( 'h2' ); if ( h ) { h.tabIndex = -1; h.focus(); } } );
		} );
	}
	root.querySelectorAll( '[data-ew-example]' ).forEach( ( chip ) => chip.addEventListener( 'click', ( e ) => {
		if ( ! isPage ) return;
		e.preventDefault();
		input.value = chip.dataset.ewExample;
		form.requestSubmit();
	} ) );
} );
