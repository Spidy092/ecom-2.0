( function ( root, factory ) {
	if ( typeof module === 'object' && module.exports ) {
		module.exports = factory();
		return;
	}

	root.BhaivaTechBuyAgainModel = factory();
} )( typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	const MAX_ITEMS = 50;
	const MAX_QUANTITY = 100;

	function normalizeQuantity( value ) {
		const quantity = Number( value );
		return Number.isFinite( quantity ) && quantity > 0
			? Math.max( 1, Math.min( MAX_QUANTITY, Math.floor( quantity ) ) )
			: 1;
	}

	function normalizeResponse( payload ) {
		const seen = new Set();
		const items = [];

		( payload && Array.isArray( payload.items ) ? payload.items : [] ).forEach( ( candidate ) => {
			const productId = Number( candidate && candidate.product_id );
			if ( ! Number.isSafeInteger( productId ) || productId <= 0 || seen.has( productId ) ) {
				return;
			}

			seen.add( productId );
			items.push( {
				product_id: productId,
				purchased_quantity: normalizeQuantity( candidate.purchased_quantity ),
			} );
		} );

		return {
			items: items.slice( 0, MAX_ITEMS ),
			skipped_count: Math.max( 0, Number( payload && payload.skipped_count ) || 0 ),
		};
	}

	return {
		MAX_ITEMS,
		MAX_QUANTITY,
		normalizeQuantity,
		normalizeResponse,
	};
} );
