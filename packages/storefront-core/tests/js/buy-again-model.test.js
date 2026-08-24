const assert = require( 'node:assert/strict' );
const test = require( 'node:test' );
const model = require( '../../assets/js/buy-again-model.js' );

test( 'bounds remembered quantities to one through one hundred', () => {
	assert.equal( model.normalizeQuantity( 0 ), 1 );
	assert.equal( model.normalizeQuantity( 2.8 ), 2 );
	assert.equal( model.normalizeQuantity( 1000 ), 100 );
	assert.equal( model.normalizeQuantity( 'invalid' ), 1 );
} );

test( 'deduplicates invalid and repeated product IDs while preserving latest order', () => {
	const response = model.normalizeResponse( {
		items: [
			{ product_id: 12, purchased_quantity: 3 },
			{ product_id: 12, purchased_quantity: 9 },
			{ product_id: 'bad', purchased_quantity: 4 },
			{ product_id: 15, purchased_quantity: 0 },
		],
		skipped_count: 2,
	} );

	assert.deepEqual( response.items, [
		{ product_id: 12, purchased_quantity: 3 },
		{ product_id: 15, purchased_quantity: 1 },
	] );
	assert.equal( response.skipped_count, 2 );
} );

test( 'caps normalized history at the V1 product bound', () => {
	const response = model.normalizeResponse( {
		items: Array.from( { length: 60 }, ( _, index ) => ( {
			product_id: index + 1,
			purchased_quantity: 1,
		} ) ),
	} );

	assert.equal( response.items.length, 50 );
} );
