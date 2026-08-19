const test = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const model = require( '../../assets/js/product-filters-model.js' );

test( 'filter attributes are global, unique, sorted and bounded to four', () => {
	const attributes = [
		{ id: 4, name: 'Size', taxonomy: 'pa_size' },
		{ id: 1, name: 'Dietary', taxonomy: 'pa_dietary' },
		{ id: 2, name: 'Brand', taxonomy: 'pa_brand' },
		{ id: 3, name: 'Pack', taxonomy: 'pa_pack' },
		{ id: 5, name: 'Origin', taxonomy: 'pa_origin' },
		{ id: 6, name: 'Custom', taxonomy: 'custom' },
		{ id: 7, name: 'Duplicate', taxonomy: 'pa_pack' },
	];
	assert.deepEqual(
		model.limitFilterAttributes( attributes ).map( ( item ) => item.taxonomy ),
		[ 'pa_brand', 'pa_dietary', 'pa_origin', 'pa_pack' ]
	);
} );

test( 'collection data stays inside the active department and requests bounded facets', () => {
	const url = new URL( model.buildCollectionDataUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts%2Fcollection-data',
		{ type: 'department', category: { id: 44, name: 'Produce' } },
		[
			{ id: 1, name: 'Dietary', taxonomy: 'pa_dietary' },
			{ id: 2, name: 'Pack', taxonomy: 'pa_pack' },
		]
	) );
	assert.equal( url.searchParams.get( 'category' ), '44' );
	assert.equal( url.searchParams.get( 'catalog_visibility' ), 'catalog' );
	assert.equal( url.searchParams.get( 'calculate_price_range' ), 'true' );
	assert.equal( url.searchParams.get( 'calculate_stock_status_counts' ), 'true' );
	assert.equal( url.searchParams.get( 'calculate_attribute_counts[0][taxonomy]' ), 'pa_dietary' );
	assert.equal( url.searchParams.get( 'calculate_attribute_counts[1][taxonomy]' ), 'pa_pack' );
} );

test( 'collection data can be scoped to a search query', () => {
	const url = new URL( model.buildCollectionDataUrl(
		'https://example.test/wp-json/wc/store/v1/products/collection-data',
		{ type: 'search', query: ' milk ' },
		[]
	) );
	assert.equal( url.searchParams.get( 'search' ), 'milk' );
	assert.equal( url.searchParams.get( 'catalog_visibility' ), 'search' );
} );

test( 'attribute terms URL supports plain-permalink templates', () => {
	const url = new URL( model.buildAttributeTermsUrl(
		'https://example.test/?rest_route=%2Fwc%2Fstore%2Fv1%2Fproducts%2Fattributes%2F__ATTRIBUTE_ID__%2Fterms',
		7
	) );
	assert.equal( url.searchParams.get( 'rest_route' ), '/wc/store/v1/products/attributes/7/terms' );
	assert.equal( url.searchParams.get( 'orderby' ), 'name' );
} );

test( 'decimal prices convert to exact Woo minor units', () => {
	assert.equal( model.decimalToMinor( '59', 2 ), '5900' );
	assert.equal( model.decimalToMinor( '59.5', 2 ), '5950' );
	assert.equal( model.decimalToMinor( '59.999', 2 ), '5999' );
	assert.equal( model.decimalToMinor( '1025', 0 ), '1025' );
	assert.equal( model.decimalToMinor( '-1', 2 ), '' );
	assert.equal( model.minorToDecimal( '5950', 2 ), '59.50' );
} );

test( 'price filters clamp to context range and normalize reversed bounds', () => {
	assert.deepEqual( model.normalizePriceFilters( '90', '20', {
		min_price: '1000', max_price: '8000', currency_minor_unit: 2,
	} ), { min: '2000', max: '8000' } );
	assert.deepEqual( model.normalizePriceFilters( '1', '999', {
		min_price: '1000', max_price: '8000', currency_minor_unit: 2,
	} ), { min: '1000', max: '8000' } );
} );

test( 'contextual terms hide zero-count values', () => {
	assert.deepEqual( model.contextualTerms(
		[
			{ id: 12, name: 'Organic', slug: 'organic' },
			{ id: 13, name: 'Vegan', slug: 'vegan' },
			{ id: 14, name: 'Gluten Free', slug: 'gluten-free' },
		],
		[ { term: 12, count: 2 }, { term: 13, count: 0 }, { term: 14, count: 1 } ]
	), [
		{ id: 14, name: 'Gluten Free', slug: 'gluten-free', count: 1 },
		{ id: 12, name: 'Organic', slug: 'organic', count: 2 },
	] );
} );

test( 'filtered query uses Woo stock, price and attribute schema', () => {
	const url = new URL( model.buildFilteredProductsUrl(
		'https://example.test/wp-json/wc/store/v1/products',
		{ type: 'department', category: { id: 44 } },
		{
			inStock: true,
			minPrice: '5000',
			maxPrice: '10000',
			attributes: [
				{ taxonomy: 'pa_dietary', termIds: [ 12, 13 ] },
				{ taxonomy: 'pa_pack', termIds: [ 21 ] },
			],
		}
	) );
	assert.equal( url.searchParams.get( 'category' ), '44' );
	assert.equal( url.searchParams.get( 'stock_status[0]' ), 'instock' );
	assert.equal( url.searchParams.get( 'min_price' ), '5000' );
	assert.equal( url.searchParams.get( 'attributes[0][attribute]' ), 'pa_dietary' );
	assert.equal( url.searchParams.get( 'attributes[0][term_id][0]' ), '12' );
	assert.equal( url.searchParams.get( 'attributes[0][term_id][1]' ), '13' );
	assert.equal( url.searchParams.get( 'attribute_relation' ), 'and' );
} );

test( 'active filter count counts groups rather than every selected term', () => {
	assert.equal( model.activeFilterCount( {
		inStock: true,
		minPrice: '5000',
		attributes: [
			{ taxonomy: 'pa_dietary', termIds: [ 12, 13 ] },
			{ taxonomy: 'pa_pack', termIds: [ 21 ] },
		],
	} ), 4 );
} );
