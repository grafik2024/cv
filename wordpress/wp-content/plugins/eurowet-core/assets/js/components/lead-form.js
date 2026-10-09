/* Lead form: fetch submission with inline, accessible errors (falls back to normal POST without JS). */
const cfgEl = document.getElementById( 'ew-config' );
const cfg = cfgEl ? JSON.parse( cfgEl.textContent ) : { rest: '/wp-json/eurowet/v1/' };

document.querySelectorAll( '[data-ew-lead]' ).forEach( ( form ) => {
	// Fresh signed timestamp (the HTML may come from a full-page cache).
	fetch( cfg.rest + 'lead/token', { headers: { Accept: 'application/json' } } ).then( ( r ) => r.json() ).then( ( d ) => { if ( d.ts ) form.querySelector( '[name=ew_ts]' ).value = d.ts; } ).catch( () => {} );
	const status = form.parentElement.querySelector( '[data-ew-lead-status]' );
	form.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();
		form.querySelectorAll( '.ew-field__error' ).forEach( ( el ) => { el.hidden = true; el.textContent = ''; } );
		form.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => el.removeAttribute( 'aria-invalid' ) );
		const btn = form.querySelector( '[type=submit]' );
		btn.disabled = true;
		const data = Object.fromEntries( new FormData( form ).entries() );
		try {
			const res = await fetch( cfg.rest + 'lead', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify( data ) } );
			const out = await res.json();
			if ( out.ok ) {
				status.innerHTML = '<p class="ew-alert ew-alert--success"></p>';
				status.firstChild.textContent = out.message;
				form.reset();
				form.hidden = true;
				status.focus();
				return;
			}
			status.innerHTML = '<p class="ew-alert ew-alert--danger"></p>';
			status.firstChild.textContent = out.message || 'Błąd';
			let first = null;
			Object.entries( out.errors || {} ).forEach( ( [ name, msg ] ) => {
				const input = form.querySelector( '[name="' + name + '"]' );
				if ( ! input ) return;
				input.setAttribute( 'aria-invalid', 'true' );
				const err = document.getElementById( input.id + '-err' );
				if ( err ) { err.textContent = msg; err.hidden = false; }
				first = first || input;
			} );
			( first || status ).focus();
		} catch ( err ) {
			form.submit();
		} finally {
			btn.disabled = false;
		}
	} );
} );
