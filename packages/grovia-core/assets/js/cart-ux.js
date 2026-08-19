( function () {
	'use strict';

	var config = window.GroviaCartUx;

	if ( ! config || ! config.cartEndpoint || ! config.nonce ) {
		return;
	}

	var endpoint = String( config.cartEndpoint ).replace( /\/$/, '' );
	var nonce = config.nonce;
	var cart = null;
	var enhancedReady = false;
	var pendingProducts = new Set();
	var pulseTimer = null;
	var strings = config.strings || {};

	function productButtons() {
		return document.querySelectorAll( '.products a.product_type_simple.add_to_cart_button[data-product_id]' );
	}

	function productIdFromButton( button ) {
		return Number.parseInt( button.getAttribute( 'data-product_id' ), 10 );
	}

	function productTitle( element ) {
		var card = element.closest( 'li.product' );
		var title = card ? card.querySelector( '.woocommerce-loop-product__title' ) : null;

		return title && title.textContent ? title.textContent.trim() : '';
	}

	function getCartItem( productId ) {
		if ( ! cart || ! Array.isArray( cart.items ) ) {
			return null;
		}

		return cart.items.find( function ( item ) {
			return Number( item.id ) === Number( productId );
		} ) || null;
	}

	function ensureErrorNode( button ) {
		var card = button.closest( 'li.product' );
		var existing = card ? card.querySelector( '.grovia-cart-error' ) : null;

		if ( existing ) {
			return existing;
		}

		if ( ! card ) {
			return null;
		}

		var error = document.createElement( 'p' );
		error.className = 'grovia-cart-error';
		error.setAttribute( 'role', 'alert' );
		error.hidden = true;
		card.appendChild( error );
		return error;
	}

	function clearError( button ) {
		var error = ensureErrorNode( button );

		if ( error ) {
			error.hidden = true;
			error.textContent = '';
		}
	}

	function showError( button, message ) {
		var error = ensureErrorNode( button );

		if ( error ) {
			error.textContent = message || strings.genericError || 'Basket update failed. Please try again.';
			error.hidden = false;
		}
	}

	function ensureQuantityControl( button ) {
		var productId = productIdFromButton( button );
		var card = button.closest( 'li.product' );
		var existing = card ? card.querySelector( '.grovia-quantity-control[data-product-id="' + productId + '"]' ) : null;

		if ( existing ) {
			return existing;
		}

		var title = productTitle( button );
		var control = document.createElement( 'div' );
		var decrease = document.createElement( 'button' );
		var quantity = document.createElement( 'span' );
		var increase = document.createElement( 'button' );

		control.className = 'grovia-quantity-control';
		control.setAttribute( 'data-product-id', String( productId ) );
		control.hidden = true;

		decrease.type = 'button';
		decrease.className = 'grovia-quantity-control__button';
		decrease.setAttribute( 'data-grovia-quantity-action', 'decrease' );
		decrease.setAttribute( 'aria-label', ( strings.decrease || 'Decrease quantity for' ) + ' ' + title );
		decrease.textContent = '−';

		quantity.className = 'grovia-quantity-control__value';
		quantity.setAttribute( 'aria-hidden', 'true' );
		quantity.textContent = '1';

		increase.type = 'button';
		increase.className = 'grovia-quantity-control__button';
		increase.setAttribute( 'data-grovia-quantity-action', 'increase' );
		increase.setAttribute( 'aria-label', ( strings.increase || 'Increase quantity for' ) + ' ' + title );
		increase.textContent = '+';

		control.appendChild( decrease );
		control.appendChild( quantity );
		control.appendChild( increase );
		button.insertAdjacentElement( 'afterend', control );

		return control;
	}

	function renderProductButton( button ) {
		var productId = productIdFromButton( button );

		if ( ! productId ) {
			return;
		}

		var item = getCartItem( productId );
		var control = ensureQuantityControl( button );
		var busy = pendingProducts.has( productId );
		var decrease = control.querySelector( '[data-grovia-quantity-action="decrease"]' );
		var increase = control.querySelector( '[data-grovia-quantity-action="increase"]' );
		var value = control.querySelector( '.grovia-quantity-control__value' );

		button.classList.add( 'grovia-add-button' );
		button.classList.toggle( 'is-loading', busy );
		button.setAttribute( 'aria-disabled', busy ? 'true' : 'false' );

		if ( ! item ) {
			button.hidden = false;
			control.hidden = true;
			return;
		}

		var limits = item.quantity_limits || {};
		var maximum = Number.isFinite( Number( limits.maximum ) ) ? Number( limits.maximum ) : Infinity;
		var editable = limits.editable !== false;

		button.hidden = true;
		control.hidden = false;
		control.setAttribute( 'data-cart-key', item.key || '' );
		control.setAttribute( 'aria-label', productTitle( button ) + ' quantity ' + item.quantity );
		value.textContent = String( item.quantity );
		decrease.disabled = busy || ! editable;
		increase.disabled = busy || ! editable || Number( item.quantity ) >= maximum;
	}

	function renderProducts() {
		productButtons().forEach( renderProductButton );
	}

	function formatMoney( totals ) {
		if ( ! totals ) {
			return '';
		}

		var minorUnit = Number.parseInt( totals.currency_minor_unit, 10 );
		var raw = Number( totals.total_price );
		var code = totals.currency_code || '';

		if ( ! Number.isFinite( minorUnit ) ) {
			minorUnit = 2;
		}

		if ( ! Number.isFinite( raw ) ) {
			return '';
		}

		var amount = raw / Math.pow( 10, minorUnit );

		try {
			if ( code ) {
				return new Intl.NumberFormat( undefined, {
					style: 'currency',
					currency: code,
				} ).format( amount );
			}
		} catch ( error ) {
			// Fall through to a simple, non-throwing representation.
		}

		return ( code ? code + ' ' : '' ) + amount.toFixed( minorUnit );
	}

	function ensureBasketPulse() {
		var existing = document.querySelector( '.grovia-basket-pulse' );

		if ( existing ) {
			return existing;
		}

		var pulse = document.createElement( 'aside' );
		var status = document.createElement( 'span' );
		var summary = document.createElement( 'strong' );
		var link = document.createElement( 'a' );
		var bottomNavigation = document.querySelector( '.grovia-bottom-navigation' );

		pulse.className = 'grovia-basket-pulse';
		pulse.hidden = true;

		status.className = 'grovia-basket-pulse__status';
		status.setAttribute( 'role', 'status' );
		status.setAttribute( 'aria-live', 'polite' );
		status.setAttribute( 'aria-atomic', 'true' );

		summary.className = 'grovia-basket-pulse__summary';

		link.className = 'grovia-basket-pulse__link';
		link.href = config.cartUrl || '/cart/';
		link.textContent = strings.viewBasket || 'View basket';

		pulse.appendChild( status );
		pulse.appendChild( summary );
		pulse.appendChild( link );

		if ( bottomNavigation && bottomNavigation.parentNode ) {
			bottomNavigation.parentNode.insertBefore( pulse, bottomNavigation );
		} else {
			document.body.appendChild( pulse );
		}

		return pulse;
	}

	function itemCount() {
		if ( ! cart || ! Array.isArray( cart.items ) ) {
			return 0;
		}

		return cart.items.reduce( function ( total, item ) {
			return total + Number( item.quantity || 0 );
		}, 0 );
	}

	function renderBasket() {
		var pulse = ensureBasketPulse();
		var count = itemCount();
		var summary = pulse.querySelector( '.grovia-basket-pulse__summary' );

		if ( count < 1 ) {
			pulse.hidden = true;
			document.body.classList.remove( 'grovia-has-basket-pulse' );
			return;
		}

		var countLabel = count === 1 ? ( strings.item || 'item' ) : ( strings.items || 'items' );
		var total = formatMoney( cart.totals );

		summary.textContent = count + ' ' + countLabel + ( total ? ' · ' + total : '' );
		pulse.hidden = false;
		document.body.classList.add( 'grovia-has-basket-pulse' );
	}

	function announce( message ) {
		var pulse = ensureBasketPulse();
		var status = pulse.querySelector( '.grovia-basket-pulse__status' );

		window.clearTimeout( pulseTimer );
		status.textContent = message;

		pulseTimer = window.setTimeout( function () {
			status.textContent = '';
		}, 2800 );
	}

	function applyCart( nextCart ) {
		cart = nextCart;
		renderProducts();
		renderBasket();
	}

	function updateNonce( response ) {
		var nextNonce = response.headers.get( 'Nonce' );

		if ( nextNonce ) {
			nonce = nextNonce;
		}
	}

	async function parseResponse( response ) {
		var data = null;

		try {
			data = await response.json();
		} catch ( error ) {
			data = null;
		}

		if ( ! response.ok ) {
			var message = data && data.message ? data.message : ( strings.genericError || 'Basket update failed. Please try again.' );
			throw new Error( message );
		}

		return data;
	}

	async function getCart() {
		var response = await fetch( endpoint, {
			method: 'GET',
			credentials: 'same-origin',
			headers: {
				'Accept': 'application/json',
			},
		} );

		updateNonce( response );
		return parseResponse( response );
	}

	async function mutateCart( path, payload ) {
		var response = await fetch( endpoint + '/' + path, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json',
				'Nonce': nonce,
			},
			body: JSON.stringify( payload ),
		} );

		updateNonce( response );
		return parseResponse( response );
	}

	function setPending( productId, pending ) {
		if ( pending ) {
			pendingProducts.add( productId );
		} else {
			pendingProducts.delete( productId );
		}

		renderProducts();
	}

	async function addProduct( button ) {
		var productId = productIdFromButton( button );

		if ( ! productId || pendingProducts.has( productId ) ) {
			return;
		}

		clearError( button );
		setPending( productId, true );

		try {
			var nextCart = await mutateCart( 'add-item', {
				id: productId,
				quantity: 1,
			} );
			applyCart( nextCart );

			var item = getCartItem( productId );
			var suffix = item ? ' ×' + item.quantity : '';
			announce( ( strings.added || 'Added' ) + ': ' + productTitle( button ) + suffix );
		} catch ( error ) {
			showError( button, error.message );
		} finally {
			setPending( productId, false );
		}
	}

	async function changeQuantity( control, direction ) {
		var productId = Number.parseInt( control.getAttribute( 'data-product-id' ), 10 );
		var item = getCartItem( productId );
		var button = control.parentElement ? control.parentElement.querySelector( 'a.add_to_cart_button[data-product_id="' + productId + '"]' ) : null;

		if ( ! item || ! button || pendingProducts.has( productId ) ) {
			return;
		}

		var limits = item.quantity_limits || {};
		var step = Number.parseInt( limits.multiple_of, 10 );
		var minimum = Number.parseInt( limits.minimum, 10 );

		if ( ! Number.isFinite( step ) || step < 1 ) {
			step = 1;
		}

		if ( ! Number.isFinite( minimum ) || minimum < 1 ) {
			minimum = 1;
		}

		var currentQuantity = Number( item.quantity );
		var targetQuantity = direction === 'increase' ? currentQuantity + step : currentQuantity - step;

		clearError( button );
		setPending( productId, true );

		try {
			var nextCart;

			if ( targetQuantity < minimum ) {
				nextCart = await mutateCart( 'remove-item', {
					key: item.key,
				} );
				applyCart( nextCart );
				announce( ( strings.removed || 'Removed' ) + ': ' + productTitle( button ) );
			} else {
				nextCart = await mutateCart( 'update-item', {
					key: item.key,
					quantity: targetQuantity,
				} );
				applyCart( nextCart );
				var updatedItem = getCartItem( productId );
				announce( ( strings.updated || 'Updated' ) + ': ' + productTitle( button ) + ( updatedItem ? ' ×' + updatedItem.quantity : '' ) );
			}
		} catch ( error ) {
			showError( button, error.message );
		} finally {
			setPending( productId, false );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		if ( ! enhancedReady || ! ( event.target instanceof Element ) ) {
			return;
		}

		var addButton = event.target.closest( '.products a.product_type_simple.add_to_cart_button[data-product_id]' );

		if ( addButton && ! event.metaKey && ! event.ctrlKey && ! event.shiftKey && ! event.altKey ) {
			event.preventDefault();
			event.stopImmediatePropagation();
			addProduct( addButton );
			return;
		}

		var quantityButton = event.target.closest( '[data-grovia-quantity-action]' );

		if ( quantityButton ) {
			var control = quantityButton.closest( '.grovia-quantity-control' );
			if ( control ) {
				event.preventDefault();
				changeQuantity( control, quantityButton.getAttribute( 'data-grovia-quantity-action' ) );
			}
		}
	}, true );

	getCart().then( function ( initialCart ) {
		cart = initialCart;
		enhancedReady = true;
		renderProducts();
		renderBasket();
	} ).catch( function () {
		// Keep native WooCommerce add-to-cart behavior as the fallback.
		enhancedReady = false;
	} );
}() );
