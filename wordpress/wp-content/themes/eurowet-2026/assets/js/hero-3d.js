/* Hero 3D — "silhouette lathe" of the REAL packshot.
   The bottle body is a LatheGeometry built from the packshot's alpha profile; the untouched original packshot is
   projected onto the front of the body (planar UVs), so name/logo/label stay exactly as on the real product.
   Runs only on capable desktops: ≥1024 px, WebGL2, no prefers-reduced-motion / a11y "reduce motion", no Save-Data,
   after the page is idle (the static packshot stays the LCP element). Gentle drag-to-rotate, pause button,
   stops rendering when off-screen or the tab is hidden. Any failure leaves the static image in place. */
const stage = document.querySelector( '[data-ew-hero3d]' );
const html = document.documentElement;
const reduce = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches || html.getAttribute( 'data-a11y-motion' ) === 'reduce';
const capable = () => {
	if ( ! stage || window.innerWidth < 1024 || reduce() ) return false;
	if ( navigator.connection && ( navigator.connection.saveData || /2g/.test( navigator.connection.effectiveType || '' ) ) ) return false;
	try { return !! document.createElement( 'canvas' ).getContext( 'webgl2' ); } catch ( e ) { return false; }
};

async function start() {
	const [ THREE, profileRes ] = await Promise.all( [ import( stage.dataset.three ), fetch( stage.dataset.profile ) ] );
	const profile = await profileRes.json();
	const tex = await new THREE.TextureLoader().loadAsync( stage.dataset.texture );
	tex.colorSpace = THREE.SRGBColorSpace;
	tex.anisotropy = 4;

	const packshot = stage.querySelector( '.ew-hero__packshot img' );
	const box = packshot.getBoundingClientRect();
	const H = 1; // model height (profile is normalised by the bbox height)
	const pts = profile.profile.map( ( [ y, r ] ) => new THREE.Vector2( Math.max( r, 0.0005 ), y * H ) );
	const geo = new THREE.LatheGeometry( pts, 96 );
	// Planar projection from the front (−z → +z) using the packshot bbox: u from x, v from y.
	const pos = geo.attributes.position; const uv = geo.attributes.uv; const half = profile.aspect / 2;
	for ( let i = 0; i < pos.count; i++ ) uv.setXY( i, 0.5 + pos.getX( i ) / ( 2 * half ), pos.getY( i ) / H );
	uv.needsUpdate = true;
	geo.computeVertexNormals();

	// Front half shows the original packshot; the back half uses the colour sampled at the label's centre column
	// (no invented artwork on the back).
	const front = new THREE.MeshStandardMaterial( { map: tex, roughness: 0.38, metalness: 0.0 } );
	front.onBeforeCompile = ( s ) => {
		s.vertexShader = s.vertexShader.replace( '#include <common>', '#include <common>\nvarying float vZ;' ).replace( '#include <begin_vertex>', '#include <begin_vertex>\nvZ = position.z;' );
		s.fragmentShader = s.fragmentShader.replace( '#include <common>', '#include <common>\nvarying float vZ;\nuniform vec3 backColor;' ).replace( '#include <map_fragment>', '#include <map_fragment>\nif ( vZ < 0.0 ) { diffuseColor.rgb = backColor; }' );
		s.uniforms.backColor = { value: new THREE.Color( 0xf4f6f8 ) };
	};
	const mesh = new THREE.Mesh( geo, front );
	mesh.position.y = -H / 2;
	const group = new THREE.Group();
	group.add( mesh );

	const scene = new THREE.Scene();
	scene.add( new THREE.HemisphereLight( 0xffffff, 0xb8c8d6, 1.6 ) );
	const key = new THREE.DirectionalLight( 0xffffff, 1.6 ); key.position.set( 1.5, 2, 3 ); scene.add( key );
	const rim = new THREE.DirectionalLight( 0xdfeefa, 0.9 ); rim.position.set( -2, 1, -2 ); scene.add( rim );
	scene.add( group );

	const w = Math.round( box.width * 2.2 ); const h = Math.round( box.height * 1.1 );
	const camera = new THREE.PerspectiveCamera( 22, w / h, 0.1, 20 );
	camera.position.set( 0, 0, ( H * 1.1 ) / ( 2 * Math.tan( THREE.MathUtils.degToRad( 11 ) ) ) );
	const renderer = new THREE.WebGLRenderer( { antialias: true, alpha: true, powerPreference: 'low-power' } );
	renderer.setPixelRatio( Math.min( window.devicePixelRatio || 1, 2 ) );
	renderer.setSize( w, h );
	renderer.outputColorSpace = THREE.SRGBColorSpace;
	const canvas = renderer.domElement;
	canvas.className = 'ew-hero__canvas';
	canvas.setAttribute( 'aria-hidden', 'true' );
	stage.appendChild( canvas );

	let angle = 0; let target = 0; let playing = true; let visible = true; let dragging = false; let lastX = 0; let last = performance.now();
	const frame = ( now ) => {
		const dt = Math.min( 0.05, ( now - last ) / 1000 ); last = now;
		if ( playing && ! dragging ) target += dt * 0.45;
		angle += ( target - angle ) * Math.min( 1, dt * 6 );
		// Gentle swing (±40°) keeps the original label mostly facing the visitor.
		group.rotation.y = Math.sin( angle ) * 0.7;
		renderer.render( scene, camera );
		if ( visible && ! document.hidden ) raf = requestAnimationFrame( frame );
		else raf = 0;
	};
	let raf = requestAnimationFrame( frame );
	const resume = () => { if ( ! raf && visible && ! document.hidden ) { last = performance.now(); raf = requestAnimationFrame( frame ); } };

	stage.classList.add( 'is-3d' );
	new IntersectionObserver( ( [ e ] ) => { visible = e.isIntersecting; resume(); } ).observe( stage );
	document.addEventListener( 'visibilitychange', resume );
	canvas.addEventListener( 'pointerdown', ( e ) => { dragging = true; lastX = e.clientX; canvas.setPointerCapture( e.pointerId ); } );
	canvas.addEventListener( 'pointermove', ( e ) => { if ( dragging ) { target += ( e.clientX - lastX ) * 0.01; lastX = e.clientX; resume(); } } );
	canvas.addEventListener( 'pointerup', () => { dragging = false; } );

	const btn = stage.querySelector( '[data-ew-hero3d-toggle]' );
	if ( btn ) {
		const label = btn.querySelector( '[data-ew-hero3d-label]' );
		btn.hidden = false;
		btn.addEventListener( 'click', () => {
			playing = ! playing;
			btn.setAttribute( 'aria-pressed', String( ! playing ) );
			if ( label ) label.textContent = playing ? 'Zatrzymaj obrót' : 'Wznów obrót';
			resume();
		} );
	}
	// Respect a later "reduce motion" choice from the accessibility panel.
	new MutationObserver( () => { if ( reduce() ) { playing = false; target = angle = 0; } } ).observe( html, { attributes: true, attributeFilter: [ 'data-a11y-motion' ] } );
}

if ( capable() ) {
	const go = () => start().catch( () => { stage.classList.remove( 'is-3d' ); const c = stage.querySelector( 'canvas' ); if ( c ) c.remove(); } );
	const idle = window.requestIdleCallback || ( ( cb ) => setTimeout( cb, 1200 ) );
	if ( document.readyState === 'complete' ) idle( go, { timeout: 3000 } );
	else window.addEventListener( 'load', () => idle( go, { timeout: 3000 } ), { once: true } );
}
