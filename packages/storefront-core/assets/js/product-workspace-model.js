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

	function buildProductsUrl( restBase, query ) {
		const url = new URL( 'products', restBase );
		url.searchParams.set( 'search', String( query ).trim() );
		url.searchParams.set( 'per_page', String( MAX_RESULTS ) );
		url.searchParams.set( 'catalog_visibility', 'search' );
		return url.toString();
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
		buildProductsUrl,
		canDirectAdd,
		findCartItemForProduct,
		formatMinorMoney,
		productPrice,
		cartSummary,
	};
} );
