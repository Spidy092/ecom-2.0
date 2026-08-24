const test = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const model = require( '../../assets/js/product-workspace-model.js' );

test( 'product search trims and bounds untrusted query text', () => {
	const query = '  ' + 'a'.repeat( 100 ) + '  ';
	assert.equal( model.boundedSearchQuery( query ).length, model.MAX_QUERY_LENGTH );
	assert.equal( model.boundedSearchQuery( null ), '' );
} );

test( 'product search is bounded to 12 results with pretty REST URLs', () => {
	const url = new URL( model.buildProductsUrl( 'https://example.test/wp-json/wc/store/v1/products', '  milk  ' ) );
	assert.equal( url.pathname, '/wp-json/wc/store/v1/products' );
	assert.equal( url.searchParams.get( 'search' ), 'milk' );
	assert.equal( url.searchParams.get( 'per_page' ), '12' );
	assert.equal( url.searchParams.get( 'catalog_visibility' ), 'search' );
} );

test( 'conventional search fallback preserves the bounded query', () => {
	const url = new URL( model.buildConventionalSearchUrl(
		'https://example.test/shop/?post_type=product',
		'  bulk rice  '
	) );
	assert.equal( url.pathname, '/shop/' );
	assert.equal( url.searchParams.get( 'post_type' ), 'product' );
	assert.equal( url.searchParams.get( 's' ), 'bulk rice' );
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

test( 'department categories query requests only bounded non-empty top-level terms', () => {
	const url = new URL( model.buildTopCategoriesUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts%2Fcategories'
	) );
	assert.equal( url.searchParams.get( 'rest_route' ), '/wc/store/v1/products/categories' );
	assert.equal( url.searchParams.get( 'parent' ), '0' );
	assert.equal( url.searchParams.get( 'hide_empty' ), 'true' );
	assert.equal( url.searchParams.get( 'per_page' ), '100' );
	assert.equal( url.searchParams.get( 'orderby' ), 'name' );
	assert.equal( url.searchParams.get( 'order' ), 'asc' );
} );

test( 'department product query is bounded and catalog-scoped', () => {
	const url = new URL( model.buildDepartmentProductsUrl(
		'https://example.test/wp-json/wc/store/v1/products',
		{ id: 44, slug: 'produce' }
	) );
	assert.equal( url.searchParams.get( 'category' ), '44' );
	assert.equal( url.searchParams.get( 'per_page' ), '12' );
	assert.equal( url.searchParams.get( 'catalog_visibility' ), 'catalog' );
	assert.equal( model.buildDepartmentProductsUrl( 'https://example.test/wp-json/wc/store/v1/products', null ), '' );
} );

test( 'adaptive department presentation switches after eight top-level categories', () => {
	assert.equal( model.departmentPresentation( 2 ), 'rail' );
	assert.equal( model.departmentPresentation( 8 ), 'rail' );
	assert.equal( model.departmentPresentation( 9 ), 'chooser' );
	assert.equal( model.departmentPresentation( 25 ), 'chooser' );
	assert.equal( model.departmentPresentation( 1 ), 'chooser' );
} );

test( 'Saved product query uses supported include ordering and remains bounded', () => {
	const ids = [ 9, '7', 9, 3, 0, -1 ];
	const url = new URL( model.buildProductsByIdsUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts',
		ids
	) );

	assert.equal( url.searchParams.get( 'rest_route' ), '/wc/store/v1/products' );
	assert.equal( url.searchParams.get( 'include' ), '9,7,3' );
	assert.equal( url.searchParams.get( 'orderby' ), 'include' );
	assert.equal( url.searchParams.get( 'per_page' ), '3' );
	assert.equal( model.buildProductsByIdsUrl( 'https://example.test/wp-json/wc/store/v1/products', [] ), '' );

	const many = Array.from( { length: 130 }, ( _, index ) => index + 1 );
	const bounded = new URL( model.buildProductsByIdsUrl( 'https://example.test/wp-json/wc/store/v1/products', many ) );
	assert.equal( bounded.searchParams.get( 'per_page' ), '100' );
	assert.equal( bounded.searchParams.get( 'include' ).split( ',' ).length, 100 );
} );

test( 'recovery uses one bounded three-character prefix query', () => {
	assert.equal( model.recoveryPrefix( 'tomoto' ), 'tom' );
	assert.equal( model.recoveryPrefix( 'fresh tomoto' ), 'tom' );
	assert.equal( model.recoveryPrefix( 'egg' ), '' );

	const url = new URL( model.buildRecoveryUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts',
		'tomoto'
	) );
	assert.equal( url.searchParams.get( 'search' ), 'tom' );
	assert.equal( url.searchParams.get( 'per_page' ), '12' );
} );

test( 'recovery suggests a conservative nearby product-name token', () => {
	assert.equal( model.suggestSearchTerm( 'tomoto', [
		{ name: 'Alpha Tomato' },
		{ name: 'Tomatillo Fresh' },
	] ), 'Tomato' );
} );

test( 'recovery never invents a distant or prefix-mismatched suggestion', () => {
	assert.equal( model.suggestSearchTerm( 'tomoto', [
		{ name: 'Tomorrow Cereal' },
		{ name: 'Potato' },
	] ), '' );
	assert.equal( model.suggestSearchTerm( 'mlik', [ { name: 'Milk' } ] ), '' );
} );

test( 'search normalization handles case and diacritics before scoring', () => {
	assert.equal( model.normalizeSearchText( '  CAFÉ  Milk ' ), 'cafe milk' );
	assert.equal( model.editDistance( 'cafe', 'Café' ), 0 );
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
