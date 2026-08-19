const test = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const model = require( '../../assets/js/product-workspace-model.js' );

test( 'product search is bounded to 12 results with pretty REST URLs', () => {
	const url = new URL( model.buildProductsUrl( 'https://example.test/wp-json/wc/store/v1/products', '  milk  ' ) );
	assert.equal( url.pathname, '/wp-json/wc/store/v1/products' );
	assert.equal( url.searchParams.get( 'search' ), 'milk' );
	assert.equal( url.searchParams.get( 'per_page' ), '12' );
	assert.equal( url.searchParams.get( 'catalog_visibility' ), 'search' );
} );

test( 'product search preserves WordPress plain-permalink rest_route endpoints', () => {
	const url = new URL( model.buildProductsUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts',
		'tomato'
	) );

	assert.equal( url.pathname, '/' );
	assert.equal( url.searchParams.get( 'rest_route' ), '/wc/store/v1/products' );
	assert.equal( url.searchParams.get( 'search' ), 'tomato' );
	assert.equal( url.searchParams.get( 'per_page' ), '12' );
} );

test( 'only simple purchasable in-stock products direct-add', () => {
	assert.equal( model.canDirectAdd( {
		has_options: false,
		is_purchasable: true,
		is_in_stock: true,
	} ), true );

	assert.equal( model.canDirectAdd( {
		has_options: true,
		is_purchasable: true,
		is_in_stock: true,
	} ), false );

	assert.equal( model.canDirectAdd( {
		has_options: false,
		is_purchasable: true,
		is_in_stock: false,
	} ), false );
} );

test( 'cart lookup does not confuse a variation with a simple product', () => {
	const cart = {
		items: [
			{ id: 50, key: 'variation-key', quantity: 1, variation: [ { attribute: 'Size', value: '1kg' } ] },
			{ id: 50, key: 'simple-key', quantity: 3, variation: [] },
		],
	};

	assert.equal( model.findCartItemForProduct( cart, 50 ).key, 'simple-key' );
} );

test( 'Woo minor-unit money values format using response currency metadata', () => {
	assert.equal( model.formatMinorMoney( '133200', {
		currency_minor_unit: 2,
		currency_decimal_separator: '.',
		currency_thousand_separator: ',',
		currency_prefix: '₹',
		currency_suffix: '',
	} ), '₹1,332.00' );
} );

test( 'cart summary uses Woo authoritative item count and total', () => {
	assert.deepEqual( model.cartSummary( {
		items_count: 13,
		totals: {
			total_price: '133200',
			currency_minor_unit: 2,
			currency_decimal_separator: '.',
			currency_thousand_separator: ',',
			currency_prefix: '₹',
			currency_suffix: '',
		},
	} ), { count: 13, total: '₹1,332.00' } );
} );
