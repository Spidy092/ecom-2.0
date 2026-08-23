const { chromium } = require( 'playwright' );

( async () => {
	const base = process.env.GROVIA_PLAYGROUND_URL || 'http://127.0.0.1:9406';
	const browser = await chromium.launch( { headless: true } );
	const page = await browser.newPage();
	const failures = [];
	const check = ( condition, message ) => {
		if ( ! condition ) failures.push( message );
	};

	const home = await page.goto( `${ base }/`, { waitUntil: 'networkidle' } );
	const html = await page.content();
	check( home?.status() === 200, `homepage status ${ home?.status() }` );
	check( html.includes( 'Fresh Tomatoes' ), 'homepage product collection did not render products' );
	check( html.includes( 'grovia-cart-feedback' ), 'cart feedback surface missing' );
	check( html.includes( 'grovia-site-header' ) && html.includes( 'grovia-site-footer' ), 'namespaced theme parts missing' );
	check( ! html.includes( '<?php' ), 'raw PHP leaked into rendered HTML' );

	const validDelivery = await page.request.get( `${ base }/wp-json/storefront-core/v1/delivery/check?postcode=560001` );
	const invalidDelivery = await page.request.get( `${ base }/wp-json/storefront-core/v1/delivery/check?postcode=560@01` );
	check( validDelivery.status() === 200 && ( await validDelivery.json() ).available === true, 'valid delivery check failed' );
	check( invalidDelivery.status() === 400, 'invalid delivery input was not rejected' );

	const anonymousBuyAgain = await page.request.get( `${ base }/wp-json/storefront-core/v1/buy-again` );
	check( anonymousBuyAgain.status() === 401, 'anonymous Buy Again access was not denied' );

	await page.goto( `${ base }/wp-login.php`, { waitUntil: 'domcontentloaded' } );
	await page.locator( '#user_login' ).fill( 'admin' );
	await page.locator( '#user_pass' ).fill( 'password' );
	await page.locator( '#wp-submit' ).click();
	await page.waitForLoadState( 'networkidle' );
	check( ! page.url().includes( 'wp-login.php' ), 'Playground admin login failed' );

	const nonce = await page.evaluate( () => window.wpApiSettings?.nonce || document.querySelector( 'meta[name="wp-rest-nonce"]' )?.content || '' );
	const authHeaders = { 'X-WP-Nonce': nonce };
	const listBefore = await page.request.get( `${ base }/wp-json/storefront-core/v1/shopping-list`, { headers: authHeaders } );
	check( listBefore.status() === 200, `authenticated Shopping List GET status ${ listBefore.status() }` );
	const productId = 10;
	const listAdd = await page.request.post( `${ base }/wp-json/storefront-core/v1/shopping-list/items`, {
		data: { product_id: productId },
		headers: { 'X-WP-Nonce': nonce },
	} );
	check( [ 200, 201 ].includes( listAdd.status() ), `Shopping List POST status ${ listAdd.status() }` );

	const listAfter = await page.request.get( `${ base }/wp-json/storefront-core/v1/shopping-list`, { headers: authHeaders } );
	const listData = await listAfter.json();
	check( listAfter.status() === 200 && listData.items?.some( ( item ) => item.product_id === productId ), 'Shopping List item was not persisted' );

	const listDelete = await page.request.delete( `${ base }/wp-json/storefront-core/v1/shopping-list/items/${ productId }`, { headers: { 'X-WP-Nonce': nonce } } );
	check( listDelete.status() === 200, `Shopping List DELETE status ${ listDelete.status() }` );

	const buyAgain = await page.request.get( `${ base }/wp-json/storefront-core/v1/buy-again`, { headers: authHeaders } );
	check( buyAgain.status() === 200 && Array.isArray( ( await buyAgain.json() ).items ), 'authenticated Buy Again response failed' );

	const setup = await page.goto( `${ base }/wp-admin/admin.php?page=bhaivatech-storefront-setup`, { waitUntil: 'networkidle' } );
	check( setup?.status() === 200 && ( await page.locator( 'h1' ).first().textContent() )?.includes( 'Storefront setup' ), 'setup wizard page failed' );

	await browser.close();
	if ( failures.length ) {
		console.error( failures.join( '\n' ) );
		process.exit( 1 );
	}
	console.log( 'Playground browser smoke passed' );
} )();
