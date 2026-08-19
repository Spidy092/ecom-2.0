const test = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const model = require( '../../assets/js/saved-products-model.js' );

function memoryStorage() {
	const values = new Map();
	return {
		getItem( key ) {
			return values.has( key ) ? values.get( key ) : null;
		},
		setItem( key, value ) {
			values.set( key, String( value ) );
		},
	};
}

test( 'guest Saved IDs are positive, unique and bounded', () => {
	const input = [ 7, '8', 7, 0, -2, 'bad' ];
	assert.deepEqual( model.normalizeIds( input ), [ 7, 8 ] );

	const many = Array.from( { length: 80 }, ( _, index ) => index + 1 );
	assert.equal( model.normalizeIds( many ).length, model.GUEST_MAX );
	assert.equal( model.normalizeIds( many ).at( -1 ), 50 );
} );

test( 'adding and removing Saved IDs does not mutate input arrays', () => {
	const original = [ 10, 20 ];
	assert.deepEqual( model.add( original, 30 ), [ 10, 20, 30 ] );
	assert.deepEqual( original, [ 10, 20 ] );
	assert.deepEqual( model.remove( original, 10 ), [ 20 ] );
	assert.deepEqual( original, [ 10, 20 ] );
} );

test( 'guest Saved cap rejects additional products without evicting existing IDs', () => {
	const full = Array.from( { length: model.GUEST_MAX }, ( _, index ) => index + 1 );
	assert.deepEqual( model.add( full, 999 ), full );
} );

test( 'guest Saved state survives storage round-trip', () => {
	const storage = memoryStorage();
	const write = model.write( storage, [ 11, 22, 33 ] );
	assert.equal( write.persisted, true );
	assert.deepEqual( model.read( storage ), [ 11, 22, 33 ] );
} );

test( 'corrupt or unavailable storage fails closed to an empty list', () => {
	const corrupt = memoryStorage();
	corrupt.setItem( model.STORAGE_KEY, '{not-json' );
	assert.deepEqual( model.read( corrupt ), [] );

	const throwing = {
		getItem() {
			throw new Error( 'blocked' );
		},
		setItem() {
			throw new Error( 'quota' );
		},
	};
	assert.deepEqual( model.read( throwing ), [] );
	assert.deepEqual( model.write( throwing, [ 5 ] ), { ids: [ 5 ], persisted: false } );
} );

test( 'contains accepts only valid normalized product IDs', () => {
	assert.equal( model.contains( [ 4, 8 ], 8 ), true );
	assert.equal( model.contains( [ 4, 8 ], '8' ), true );
	assert.equal( model.contains( [ 4, 8 ], 0 ), false );
	assert.equal( model.contains( [ 4, 8 ], 'nope' ), false );
} );
