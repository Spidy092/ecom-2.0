( function ( root, factory ) {
	if ( typeof module === 'object' && module.exports ) {
		module.exports = factory();
		return;
	}

	root.BhaivaTechProductFiltersModel = factory();
} )( typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	const MAX_RESULTS = 12;
	const MAX_FILTER_ATTRIBUTES = 4;
	const MAX_TERMS_PER_ATTRIBUTE = 12;

	function safeUrl( endpoint ) {
		try {
			return new URL( endpoint );
		} catch ( error ) {
			return null;
		}
	}

	function normalizeContext( context ) {
		if ( ! context || typeof context !== 'object' ) {
			return null;
		}

		if ( context.type === 'search' ) {
			const query = String( context.query || '' ).trim();
			return query ? { type: 'search', query } : null;
		}

		if ( context.type === 'department' ) {
			const candidate = context.category || {};
			const id = Number( candidate.id );
			if ( Number.isSafeInteger( id ) && id > 0 ) {
				return {
					type: 'department',
					category: {
						id,
						slug: String( candidate.slug || '' ).trim(),
						name: String( candidate.name || '' ).trim(),
					},
				};
			}

			const slug = String( candidate.slug || '' ).trim();
			if ( slug ) {
				return {
					type: 'department',
					category: {
						id: 0,
						slug,
						name: String( candidate.name || '' ).trim(),
					},
				};
			}
		}

		return null;
	}

	function appendContext( url, context ) {
		const normalized = normalizeContext( context );
		if ( ! url || ! normalized ) {
			return false;
		}

		if ( normalized.type === 'search' ) {
			url.searchParams.set( 'search', normalized.query );
			url.searchParams.set( 'catalog_visibility', 'search' );
			return true;
		}

		const categoryValue = normalized.category.id > 0
			? String( normalized.category.id )
			: normalized.category.slug;
		url.searchParams.set( 'category', categoryValue );
		url.searchParams.set( 'catalog_visibility', 'catalog' );
		return true;
	}

	function validAttribute( attribute ) {
		if ( ! attribute || typeof attribute !== 'object' ) {
			return false;
		}
		const id = Number( attribute.id );
		const taxonomy = String( attribute.taxonomy || '' ).trim();
		return Number.isSafeInteger( id ) && id > 0 && /^pa_[a-z0-9_-]+$/i.test( taxonomy );
	}

	function limitFilterAttributes( attributes ) {
		const seen = new Set();
		return ( Array.isArray( attributes ) ? attributes : [] )
			.filter( function ( attribute ) {
				if ( ! validAttribute( attribute ) ) {
					return false;
				}
				const taxonomy = String( attribute.taxonomy ).toLowerCase();
				if ( seen.has( taxonomy ) ) {
					return false;
				}
				seen.add( taxonomy );
				return true;
			} )
			.sort( function ( left, right ) {
				return String( left.name || left.taxonomy ).localeCompare( String( right.name || right.taxonomy ) );
			} )
			.slice( 0, MAX_FILTER_ATTRIBUTES )
			.map( function ( attribute ) {
				return {
					id: Number( attribute.id ),
					name: String( attribute.name || attribute.taxonomy ),
					taxonomy: String( attribute.taxonomy ),
				};
			} );
	}

	function normalizeTerm( term ) {
		const id = Number( term && term.id );
		if ( ! Number.isSafeInteger( id ) || id <= 0 ) {
			return null;
		}
		return {
			id,
			name: String( term.name || term.slug || id ),
			slug: String( term.slug || '' ),
			count: Math.max( 0, Number( term.count || 0 ) ),
		};
	}

	function contextualTerms( terms, counts ) {
		const countMap = counts instanceof Map
			? counts
			: new Map(
				( Array.isArray( counts ) ? counts : [] ).map( function ( item ) {
					return [ Number( item.term ), Math.max( 0, Number( item.count || 0 ) ) ];
				} )
			);

		return ( Array.isArray( terms ) ? terms : [] )
			.map( normalizeTerm )
			.filter( Boolean )
			.map( function ( term ) {
				return Object.assign( {}, term, { count: countMap.get( term.id ) || 0 } );
			} )
			.filter( function ( term ) {
				return term.count > 0;
			} )
			.sort( function ( left, right ) {
				return left.name.localeCompare( right.name );
			} )
			.slice( 0, MAX_TERMS_PER_ATTRIBUTE );
	}

	function buildCollectionDataUrl( endpoint, context, attributes ) {
		const url = safeUrl( endpoint );
		if ( ! url || ! appendContext( url, context ) ) {
			return '';
		}

		url.searchParams.set( 'calculate_price_range', 'true' );
		url.searchParams.set( 'calculate_stock_status_counts', 'true' );

		limitFilterAttributes( attributes ).forEach( function ( attribute, index ) {
			url.searchParams.set( 'calculate_attribute_counts[' + index + '][taxonomy]', attribute.taxonomy );
			url.searchParams.set( 'calculate_attribute_counts[' + index + '][query_type]', 'or' );
		} );

		return url.toString();
	}

	function buildAttributeTermsUrl( template, attributeId ) {
		const id = Number( attributeId );
		if ( ! Number.isSafeInteger( id ) || id <= 0 ) {
			return '';
		}

		const candidate = String( template || '' ).replace( '__ATTRIBUTE_ID__', String( id ) );
		const url = safeUrl( candidate );
		if ( ! url ) {
			return '';
		}
		url.searchParams.set( 'orderby', 'name' );
		url.searchParams.set( 'order', 'asc' );
		return url.toString();
	}

	function decimalToMinor( value, minorUnit ) {
		const unit = Math.max( 0, Math.min( 6, Number( minorUnit ) || 0 ) );
		const text = String( value == null ? '' : value ).trim();
		if ( ! text || ! /^\d+(?:\.\d+)?$/.test( text ) ) {
			return '';
		}

		const parts = text.split( '.' );
		const whole = ( parts[ 0 ] || '0' ).replace( /^0+(?=\d)/, '' ) || '0';
		const fraction = unit
			? ( parts[ 1 ] || '' ).slice( 0, unit ).padEnd( unit, '0' )
			: '';
		const combined = ( whole + fraction ).replace( /^0+(?=\d)/, '' ) || '0';
		return combined;
	}

	function minorToDecimal( value, minorUnit ) {
		const unit = Math.max( 0, Math.min( 6, Number( minorUnit ) || 0 ) );
		let digits = String( value == null ? '' : value ).replace( /\D/g, '' );
		if ( ! digits ) {
			return '';
		}
		if ( ! unit ) {
			return digits.replace( /^0+(?=\d)/, '' ) || '0';
		}
		digits = digits.padStart( unit + 1, '0' );
		const whole = digits.slice( 0, -unit ).replace( /^0+(?=\d)/, '' ) || '0';
		const fraction = digits.slice( -unit );
		return whole + '.' + fraction;
	}

	function compareMinor( left, right ) {
		const a = BigInt( String( left || '0' ) );
		const b = BigInt( String( right || '0' ) );
		return a === b ? 0 : a < b ? -1 : 1;
	}

	function normalizePriceFilters( minimum, maximum, priceRange ) {
		const range = priceRange || {};
		const minorUnit = Math.max( 0, Math.min( 6, Number( range.currency_minor_unit ) || 0 ) );
		const rangeMin = String( range.min_price || '' ).replace( /\D/g, '' );
		const rangeMax = String( range.max_price || '' ).replace( /\D/g, '' );
		let min = decimalToMinor( minimum, minorUnit );
		let max = decimalToMinor( maximum, minorUnit );

		if ( min && rangeMin && compareMinor( min, rangeMin ) < 0 ) {
			min = rangeMin;
		}
		if ( min && rangeMax && compareMinor( min, rangeMax ) > 0 ) {
			min = rangeMax;
		}
		if ( max && rangeMin && compareMinor( max, rangeMin ) < 0 ) {
			max = rangeMin;
		}
		if ( max && rangeMax && compareMinor( max, rangeMax ) > 0 ) {
			max = rangeMax;
		}

		if ( min && max && compareMinor( min, max ) > 0 ) {
			const swap = min;
			min = max;
			max = swap;
		}

		return { min, max };
	}

	function normalizeSelections( selections ) {
		return ( Array.isArray( selections ) ? selections : [] )
			.map( function ( selection ) {
				const taxonomy = String( selection && selection.taxonomy || '' ).trim();
				if ( ! /^pa_[a-z0-9_-]+$/i.test( taxonomy ) ) {
					return null;
				}
				const terms = [];
				const seen = new Set();
				( Array.isArray( selection.termIds ) ? selection.termIds : [] ).forEach( function ( candidate ) {
					const id = Number( candidate );
					if ( Number.isSafeInteger( id ) && id > 0 && ! seen.has( id ) ) {
						seen.add( id );
						terms.push( id );
					}
				} );
				if ( ! terms.length ) {
					return null;
				}
				return { taxonomy, termIds: terms.slice( 0, MAX_TERMS_PER_ATTRIBUTE ) };
			} )
			.filter( Boolean )
			.slice( 0, MAX_FILTER_ATTRIBUTES );
	}

	function normalizeFilters( filters ) {
		const candidate = filters || {};
		return {
			inStock: Boolean( candidate.inStock ),
			minPrice: String( candidate.minPrice || '' ).replace( /\D/g, '' ),
			maxPrice: String( candidate.maxPrice || '' ).replace( /\D/g, '' ),
			attributes: normalizeSelections( candidate.attributes ),
		};
	}

	function activeFilterCount( filters ) {
		const normalized = normalizeFilters( filters );
		let count = normalized.inStock ? 1 : 0;
		if ( normalized.minPrice || normalized.maxPrice ) {
			count += 1;
		}
		count += normalized.attributes.length;
		return count;
	}

	function buildFilteredProductsUrl( endpoint, context, filters ) {
		const url = safeUrl( endpoint );
		if ( ! url || ! appendContext( url, context ) ) {
			return '';
		}

		url.searchParams.set( 'per_page', String( MAX_RESULTS ) );
		const normalized = normalizeFilters( filters );

		if ( normalized.inStock ) {
			url.searchParams.set( 'stock_status[0]', 'instock' );
		}
		if ( normalized.minPrice ) {
			url.searchParams.set( 'min_price', normalized.minPrice );
		}
		if ( normalized.maxPrice ) {
			url.searchParams.set( 'max_price', normalized.maxPrice );
		}

		normalized.attributes.forEach( function ( attribute, index ) {
			url.searchParams.set( 'attributes[' + index + '][attribute]', attribute.taxonomy );
			url.searchParams.set( 'attributes[' + index + '][operator]', 'in' );
			attribute.termIds.forEach( function ( termId, termIndex ) {
				url.searchParams.set(
					'attributes[' + index + '][term_id][' + termIndex + ']',
					String( termId )
				);
			} );
		} );

		if ( normalized.attributes.length > 1 ) {
			url.searchParams.set( 'attribute_relation', 'and' );
		}

		return url.toString();
	}

	return {
		MAX_RESULTS,
		MAX_FILTER_ATTRIBUTES,
		MAX_TERMS_PER_ATTRIBUTE,
		normalizeContext,
		limitFilterAttributes,
		contextualTerms,
		buildCollectionDataUrl,
		buildAttributeTermsUrl,
		decimalToMinor,
		minorToDecimal,
		normalizePriceFilters,
		normalizeFilters,
		activeFilterCount,
		buildFilteredProductsUrl,
	};
} );
