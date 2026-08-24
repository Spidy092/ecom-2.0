( function ( root, factory ) {
	if ( typeof module === 'object' && module.exports ) {
		module.exports = factory();
		return;
	}

	root.BhaivaTechProductWorkspaceModel = factory();
} )( typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	const MAX_RESULTS = 12;
	const MIN_QUERY_LENGTH = 2;
	const MAX_QUERY_LENGTH = 80;
	const RECOVERY_PREFIX_LENGTH = 3;
	const MAX_SAVED_PRODUCTS_QUERY = 100;
	const MAX_DEPARTMENT_CATEGORIES = 100;
	const DEPARTMENT_RAIL_MAX = 8;

	function normalizeSearchText( value ) {
		return String( value == null ? '' : value )
			.normalize( 'NFKD' )
			.replace( /\p{M}+/gu, '' )
			.toLocaleLowerCase()
			.replace( /[^\p{L}\p{N}]+/gu, ' ' )
			.trim()
			.replace( /\s+/g, ' ' );
	}

	function queryRecoveryToken( query ) {
		const normalized = normalizeSearchText( query );
		if ( ! normalized ) {
			return '';
		}

		const tokens = normalized.split( ' ' );
		return tokens[ tokens.length - 1 ] || '';
	}

	function recoveryPrefix( query ) {
		const token = queryRecoveryToken( query );
		const characters = Array.from( token );

		if ( characters.length < 4 ) {
			return '';
		}

		return characters.slice( 0, RECOVERY_PREFIX_LENGTH ).join( '' );
	}

	function boundedSearchQuery( query ) {
		return String( query == null ? '' : query ).trim().slice( 0, MAX_QUERY_LENGTH );
	}

	function buildProductsUrl( productsEndpoint, query ) {
		const url = new URL( productsEndpoint );
		url.searchParams.set( 'search', boundedSearchQuery( query ) );
		url.searchParams.set( 'per_page', String( MAX_RESULTS ) );
		url.searchParams.set( 'catalog_visibility', 'search' );
		return url.toString();
	}

	function buildRecoveryUrl( productsEndpoint, query ) {
		const prefix = recoveryPrefix( query );
		return prefix ? buildProductsUrl( productsEndpoint, prefix ) : '';
	}

	function buildConventionalSearchUrl( shopEndpoint, query ) {
		const url = new URL( shopEndpoint );
		url.searchParams.set( 's', boundedSearchQuery( query ) );
		return url.toString();
	}

	function buildProductsByIdsUrl( productsEndpoint, ids ) {
		const normalized = [];
		const seen = new Set();

		( Array.isArray( ids ) ? ids : [] ).forEach( function ( candidate ) {
			const id = Number( candidate );
			if ( ! Number.isSafeInteger( id ) || id <= 0 || seen.has( id ) ) {
				return;
			}
			seen.add( id );
			normalized.push( id );
		} );

		const bounded = normalized.slice( 0, MAX_SAVED_PRODUCTS_QUERY );
		if ( ! bounded.length ) {
			return '';
		}

		const url = new URL( productsEndpoint );
		url.searchParams.set( 'include', bounded.join( ',' ) );
		url.searchParams.set( 'orderby', 'include' );
		url.searchParams.set( 'per_page', String( bounded.length ) );
		return url.toString();
	}

	function buildTopCategoriesUrl( categoriesEndpoint ) {
		const url = new URL( categoriesEndpoint );
		url.searchParams.set( 'parent', '0' );
		url.searchParams.set( 'hide_empty', 'true' );
		url.searchParams.set( 'per_page', String( MAX_DEPARTMENT_CATEGORIES ) );
		url.searchParams.set( 'orderby', 'name' );
		url.searchParams.set( 'order', 'asc' );
		return url.toString();
	}

	function filterShopperDepartments( categories, defaultCategoryId ) {
		const normalizedDefaultId = Number( defaultCategoryId );

		return ( Array.isArray( categories ) ? categories : [] ).filter( function ( category ) {
			if ( ! category || Number( category.parent || 0 ) !== 0 || Number( category.count || 0 ) <= 0 ) {
				return false;
			}

			return ! Number.isSafeInteger( normalizedDefaultId ) || normalizedDefaultId <= 0 || Number( category.id ) !== normalizedDefaultId;
		} );
	}

	function normalizeCategoryValue( category ) {
		if ( category && typeof category === 'object' ) {
			if ( Number.isSafeInteger( Number( category.id ) ) && Number( category.id ) > 0 ) {
				return String( Number( category.id ) );
			}
			return String( category.slug || '' ).trim();
		}

		const numeric = Number( category );
		if ( Number.isSafeInteger( numeric ) && numeric > 0 ) {
			return String( numeric );
		}

		return String( category || '' ).trim();
	}

	function buildDepartmentProductsUrl( productsEndpoint, category ) {
		const value = normalizeCategoryValue( category );
		if ( ! value ) {
			return '';
		}

		const url = new URL( productsEndpoint );
		url.searchParams.set( 'category', value );
		url.searchParams.set( 'per_page', String( MAX_RESULTS ) );
		url.searchParams.set( 'catalog_visibility', 'catalog' );
		return url.toString();
	}

	function departmentPresentation( count ) {
		const normalized = Math.max( 0, Number( count ) || 0 );
		return normalized >= 2 && normalized <= DEPARTMENT_RAIL_MAX ? 'rail' : 'chooser';
	}

	function editDistance( left, right ) {
		const a = Array.from( normalizeSearchText( left ) );
		const b = Array.from( normalizeSearchText( right ) );

		if ( ! a.length ) {
			return b.length;
		}
		if ( ! b.length ) {
			return a.length;
		}

		let previous = Array.from( { length: b.length + 1 }, function ( _, index ) {
			return index;
		} );

		a.forEach( function ( leftCharacter, leftIndex ) {
			const current = [ leftIndex + 1 ];

			b.forEach( function ( rightCharacter, rightIndex ) {
				const insertion = current[ rightIndex ] + 1;
				const deletion = previous[ rightIndex + 1 ] + 1;
				const substitution = previous[ rightIndex ] + ( leftCharacter === rightCharacter ? 0 : 1 );
				current.push( Math.min( insertion, deletion, substitution ) );
			} );

			previous = current;
		} );

		return previous[ b.length ];
	}

	function suggestionDistanceLimit( token ) {
		const length = Array.from( token ).length;
		if ( length < 4 ) {
			return 0;
		}

		return length <= 6 ? 1 : 2;
	}

	function productNameTokens( product ) {
		return String( product && product.name ? product.name : '' )
			.split( /[^\p{L}\p{N}]+/u )
			.filter( Boolean )
			.map( function ( original ) {
				return {
					original,
					normalized: normalizeSearchText( original ),
				};
			} );
	}

	function suggestSearchTerm( query, candidateProducts ) {
		const queryToken = queryRecoveryToken( query );
		const prefix = recoveryPrefix( query );
		const distanceLimit = suggestionDistanceLimit( queryToken );

		if ( ! prefix || ! distanceLimit || ! Array.isArray( candidateProducts ) ) {
			return '';
		}

		let best = null;

		candidateProducts.slice( 0, MAX_RESULTS ).forEach( function ( product ) {
			productNameTokens( product ).forEach( function ( token ) {
				if ( ! token.normalized.startsWith( prefix ) || token.normalized === queryToken ) {
					return;
				}

				const distance = editDistance( queryToken, token.normalized );
				if ( distance > distanceLimit ) {
					return;
				}

				const score = distance * 100 + Math.abs( token.normalized.length - queryToken.length );
				if ( ! best || score < best.score ) {
					best = { term: token.original, score };
				}
			} );
		} );

		return best ? best.term : '';
	}

	function canDirectAdd( product ) {
		return Boolean(
			product &&
			! product.has_options &&
			product.is_purchasable &&
			product.is_in_stock
		);
	}

	function findCartItemForProduct( cart, productId ) {
		if ( ! cart || ! Array.isArray( cart.items ) ) {
			return null;
		}

		return cart.items.find( function ( item ) {
			return Number( item.id ) === Number( productId ) &&
				( ! Array.isArray( item.variation ) || item.variation.length === 0 );
		} ) || null;
	}

	function formatMinorMoney( value, currency ) {
		const options = currency || {};
		const minorUnit = Math.max( 0, Number( options.currency_minor_unit || 0 ) );
		const decimalSeparator = options.currency_decimal_separator || '.';
		const thousandSeparator = options.currency_thousand_separator || ',';
		const prefix = options.currency_prefix || options.currency_symbol || '';
		const suffix = options.currency_suffix || '';
		let digits = String( value == null ? '0' : value ).replace( /[^0-9-]/g, '' );
		const negative = digits.startsWith( '-' );

		if ( negative ) {
			digits = digits.slice( 1 );
		}

		digits = digits || '0';
		digits = digits.padStart( minorUnit + 1, '0' );

		let whole = minorUnit ? digits.slice( 0, -minorUnit ) : digits;
		const fraction = minorUnit ? digits.slice( -minorUnit ) : '';
		whole = whole.replace( /\B(?=(\d{3})+(?!\d))/g, thousandSeparator );

		const amount = whole + ( minorUnit ? decimalSeparator + fraction : '' );
		return ( negative ? '-' : '' ) + prefix + amount + suffix;
	}

	function productPrice( product ) {
		if ( ! product || ! product.prices ) {
			return '';
		}

		return formatMinorMoney( product.prices.price, product.prices );
	}

	function cartSummary( cart ) {
		if ( ! cart ) {
			return { count: 0, total: '' };
		}

		return {
			count: Number( cart.items_count || 0 ),
			total: cart.totals ? formatMinorMoney( cart.totals.total_price, cart.totals ) : '',
		};
	}

	return {
		MAX_RESULTS,
		MIN_QUERY_LENGTH,
		MAX_QUERY_LENGTH,
		boundedSearchQuery,
		RECOVERY_PREFIX_LENGTH,
		MAX_SAVED_PRODUCTS_QUERY,
		MAX_DEPARTMENT_CATEGORIES,
		DEPARTMENT_RAIL_MAX,
		normalizeSearchText,
		recoveryPrefix,
		buildProductsUrl,
		buildRecoveryUrl,
		buildConventionalSearchUrl,
		buildProductsByIdsUrl,
		buildTopCategoriesUrl,
		filterShopperDepartments,
		buildDepartmentProductsUrl,
		departmentPresentation,
		editDistance,
		suggestSearchTerm,
		canDirectAdd,
		findCartItemForProduct,
		formatMinorMoney,
		productPrice,
		cartSummary,
	};
} );
