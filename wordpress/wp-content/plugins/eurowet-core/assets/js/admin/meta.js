/* Eurowet admin meta boxes: relation pickers (REST search), media pickers, repeaters. Vanilla JS. */
( function () {
	'use strict';
	const t = ( window.ewMeta && window.ewMeta.i18n ) || {};
	const ROUTES = { product: 'wp/v2/product', ew_need: 'wp/v2/ew_need', ew_guide: 'wp/v2/ew_guide', ew_ingredient: 'wp/v2/ew_ingredient', post: 'wp/v2/posts', page: 'wp/v2/pages' };
	const esc = ( s ) => String( s ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ] ) );

	function relSync( box ) {
		const ids = Array.from( box.querySelectorAll( '[data-id]' ) ).filter( ( el ) => el.closest( '.ew-rel' ) === box ).map( ( el ) => el.dataset.id );
		box.querySelector( 'input[type=hidden]' ).value = ids.join( ',' );
	}
	function chip( id, title, single ) {
		const el = document.createElement( single ? 'span' : 'li' );
		el.className = 'ew-rel__chip';
		el.dataset.id = id;
		el.innerHTML = esc( title ) + ' <button type="button" class="ew-rel__remove" aria-label="' + esc( t.remove || 'Usuń' ) + '">×</button>';
		return el;
	}
	function initRel( box ) {
		if ( box.dataset.ready ) return;
		box.dataset.ready = '1';
		const single = box.dataset.multiple === '0';
		const chips = box.querySelector( '.ew-rel__chips' );
		chips.querySelectorAll( '[data-id]' ).forEach( ( el ) => {
			el.classList.add( 'ew-rel__chip' );
			if ( ! el.querySelector( '.ew-rel__remove' ) ) el.insertAdjacentHTML( 'beforeend', ' <button type="button" class="ew-rel__remove" aria-label="' + esc( t.remove || 'Usuń' ) + '">×</button>' );
		} );
		const input = box.querySelector( '.ew-rel__search' );
		const results = box.querySelector( '.ew-rel__results' );
		let timer;
		input.addEventListener( 'input', () => {
			clearTimeout( timer );
			const q = input.value.trim();
			if ( q.length < 2 ) { results.innerHTML = ''; return; }
			timer = setTimeout( () => {
				window.wp.apiFetch( { path: ROUTES[ box.dataset.type ] + '?per_page=10&_fields=id,title&status=publish,draft&search=' + encodeURIComponent( q ) } )
					.then( ( items ) => {
						results.innerHTML = items.length ? items.map( ( i ) => '<li role="option" tabindex="0" data-pick="' + i.id + '">' + esc( i.title.rendered || ( '#' + i.id ) ) + ' <small>#' + i.id + '</small></li>' ).join( '' ) : '<li>' + esc( t.none || '—' ) + '</li>';
					} ).catch( () => { results.innerHTML = ''; } );
			}, 250 );
		} );
		const pick = ( li ) => {
			if ( ! li || ! li.dataset.pick ) return;
			if ( single ) chips.innerHTML = '';
			if ( ! chips.querySelector( '[data-id="' + li.dataset.pick + '"]' ) ) chips.appendChild( chip( li.dataset.pick, li.textContent.replace( /\s#\d+$/, '' ), single ) );
			results.innerHTML = ''; input.value = ''; relSync( box );
		};
		results.addEventListener( 'click', ( e ) => pick( e.target.closest( '[data-pick]' ) ) );
		results.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Enter' ) { e.preventDefault(); pick( e.target.closest( '[data-pick]' ) ); } } );
		chips.addEventListener( 'click', ( e ) => {
			if ( e.target.classList.contains( 'ew-rel__remove' ) ) { e.target.parentElement.remove(); relSync( box ); }
		} );
	}
	function initMedia( box ) {
		const hidden = box.querySelector( 'input[type=hidden]' );
		const list = box.querySelector( '.ew-media__list' );
		const multiple = box.dataset.multiple === '1';
		box.querySelector( '.ew-media__pick' ).addEventListener( 'click', () => {
			const frame = window.wp.media( { title: t.choose || 'Wybierz plik', multiple } );
			frame.on( 'select', () => {
				const sel = frame.state().get( 'selection' ).toJSON();
				const ids = multiple ? hidden.value.split( ',' ).filter( Boolean ) : [];
				if ( ! multiple ) list.innerHTML = '';
				sel.forEach( ( a ) => { ids.push( String( a.id ) ); list.insertAdjacentHTML( 'beforeend', '<li data-id="' + a.id + '">' + esc( a.title || a.filename ) + '</li>' ); } );
				hidden.value = ids.join( ',' );
			} );
			frame.open();
		} );
		box.querySelector( '.ew-media__clear' ).addEventListener( 'click', () => { hidden.value = ''; list.innerHTML = ''; } );
	}
	function initRep( rep ) {
		const rows = rep.querySelector( '.ew-rep__rows' );
		const tpl = rep.querySelector( '.ew-rep__tpl' );
		const renumber = () => rows.querySelectorAll( '.ew-rep__row' ).forEach( ( row, i ) => row.querySelectorAll( '[name]' ).forEach( ( el ) => { el.name = el.name.replace( /\[(\d+|__i__)\]\[/, '[' + i + '][' ); } ) );
		rep.querySelector( '.ew-rep__add' ).addEventListener( 'click', () => {
			rows.insertAdjacentHTML( 'beforeend', tpl.innerHTML );
			renumber();
			rows.lastElementChild.querySelectorAll( '.ew-rel' ).forEach( initRel );
			const f = rows.lastElementChild.querySelector( 'input,textarea,select' );
			if ( f ) f.focus();
		} );
		rows.addEventListener( 'click', ( e ) => {
			const row = e.target.closest( '.ew-rep__row' );
			if ( ! row ) return;
			if ( e.target.classList.contains( 'ew-rep__del' ) ) { row.remove(); renumber(); }
			if ( e.target.classList.contains( 'ew-rep__up' ) && row.previousElementSibling ) { row.parentNode.insertBefore( row, row.previousElementSibling ); renumber(); }
		} );
	}
	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '.ew-meta .ew-rel' ).forEach( ( b ) => { if ( ! b.closest( 'template' ) ) initRel( b ); } );
		document.querySelectorAll( '.ew-meta .ew-media' ).forEach( initMedia );
		document.querySelectorAll( '.ew-meta .ew-rep' ).forEach( initRep );
	} );
} )();
