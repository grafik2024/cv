/* Rep finder: map + select drive one result panel; deep link #ph-{slug}; the full list stays for no-JS. */
document.querySelectorAll( '[data-ew-reps]' ).forEach( ( root ) => {
	const map = root.querySelector( '[data-ew-reps-map]' );
	const select = root.querySelector( '[data-ew-reps-select]' );
	const result = root.querySelector( '[data-ew-reps-result]' );
	const all = root.querySelector( '[data-ew-reps-all]' );
	if ( ! select || ! result || ! all ) return;
	root.classList.add( 'is-enhanced' );
	if ( map ) map.hidden = false;
	all.hidden = true;

	const show = ( slug, focusResult ) => {
		const entry = all.querySelector( '[data-voiv="' + CSS.escape( slug ) + '"]' );
		root.querySelectorAll( '.ew-reps__region' ).forEach( ( r ) => {
			const on = r.dataset.voiv === slug;
			r.classList.toggle( 'is-selected', on );
			if ( on ) r.setAttribute( 'aria-current', 'true' ); else r.removeAttribute( 'aria-current' );
		} );
		select.value = slug;
		if ( ! entry ) { result.innerHTML = ''; return; }
		result.innerHTML = '<h3 class="ew-reps__result-title" tabindex="-1">' + entry.querySelector( 'dt' ).textContent + '</h3>' + entry.querySelector( 'dd' ).innerHTML;
		if ( focusResult ) result.querySelector( 'h3' ).focus();
		history.replaceState( null, '', '#ph-' + slug );
	};
	select.addEventListener( 'change', () => select.value && show( select.value, false ) );
	root.querySelectorAll( '.ew-reps__region' ).forEach( ( r ) => r.addEventListener( 'click', ( e ) => { e.preventDefault(); show( r.dataset.voiv, true ); } ) );
	const m = /^#ph-([a-z-]+)$/.exec( location.hash );
	if ( m ) show( m[ 1 ], false );
} );
