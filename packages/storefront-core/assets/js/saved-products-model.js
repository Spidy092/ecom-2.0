( function ( root, factory ) {
	if ( typeof module === 'object' && module.exports ) {
		module.exports = factory();
		return;
	}

	root.BhaivaTechSavedProductsModel = factory();
} )( typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	const GUEST_MAX = 50;
	const STORAGE_KEY = 'bhaivatech_storefront_saved_v1';

	function normalizeIds( ids, limit ) {
		const max = Number.isInteger( limit ) && limit > 0 ? limit : GUEST_MAX;
		if ( ! Array.isArray( ids ) ) {
			return [];
		}

		const seen = new Set();
		const normalized = [];

		for ( const candidate of ids ) {
			const id = Number( candidate );
			if ( ! Number.isSafeInteger( id ) || id <= 0 || seen.has( id ) ) {
				continue;
			}

			seen.add( id );
			normalized.push( id );

			if ( normalized.length >= max ) {
				break;
			}
		}

		return normalized;
	}

	function contains( ids, productId ) {
		const id = Number( productId );
		return Number.isSafeInteger( id ) && normalizeIds( ids ).includes( id );
	}

	function add( ids, productId, limit ) {
		const max = Number.isInteger( limit ) && limit > 0 ? limit : GUEST_MAX;
		const current = normalizeIds( ids, max );
		const id = Number( productId );

		if ( ! Number.isSafeInteger( id ) || id <= 0 || current.includes( id ) ) {
			return current;
		}

		if ( current.length >= max ) {
			return current;
		}

		return current.concat( id );
	}

	function remove( ids, productId, limit ) {
		const id = Number( productId );
		return normalizeIds( ids, limit ).filter( function ( savedId ) {
			return savedId !== id;
		} );
	}

	function read( storage ) {
		if ( ! storage || typeof storage.getItem !== 'function' ) {
			return [];
		}

		try {
			const raw = storage.getItem( STORAGE_KEY );
			if ( ! raw ) {
				return [];
			}

			return normalizeIds( JSON.parse( raw ) );
		} catch ( error ) {
			return [];
		}
	}

	function write( storage, ids ) {
		const normalized = normalizeIds( ids );
		if ( ! storage || typeof storage.setItem !== 'function' ) {
			return { ids: normalized, persisted: false };
		}

		try {
			storage.setItem( STORAGE_KEY, JSON.stringify( normalized ) );
			return { ids: normalized, persisted: true };
		} catch ( error ) {
			return { ids: normalized, persisted: false };
		}
	}

	return {
		GUEST_MAX,
		STORAGE_KEY,
		normalizeIds,
		contains,
		add,
		remove,
		read,
		write,
	};
} );
