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
	var mutationQueue = Promise.resolve();
	var pulseTimer = null;
	var refreshTimer = null;
	var nativeObserver = null;
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

	function hideNativeCartIndicators( button ) {
		if ( ! enhancedReady ) {
			return;
		}

		var nativeLabel = button.getAttribute( 'data-grovia-native-label' );
		var currentLabel = button.textContent ? button.textContent.trim() : '';

		if ( ! nativeLabel ) {
			nativeLabel = currentLabel || strings.add || 'Add to cart';
			button.setAttribute( 'data-grovia-native-label', nativeLabel );
		}

		if ( currentLabel !== nativeLabel ) {
			button.textContent = nativeLabel;
		}

		button.classList.remove( 'added' );
		button.classList.remove( 'loading' );

		var card = button.closest( 'li.product' );
		if ( ! card ) {
			return;
		}

		card.querySelectorAll( 'a.added_to_cart, a.wc-forward.added_to_cart' ).forEach( function ( link ) {
			link.hidden = true;
			link.setAttribute( 'aria-hidden', 'true' );
			link.setAttribute( 'tabindex', '-1' );
		} );
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

		hideNativeCartIndicators( button );

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

	function emitCartUpdate() {
		document.dispatchEvent( new CustomEvent( 'grovia:cart-updated', {
			detail: {
				count: itemCount(),
			},
		} ) );
	}

	function applyCart( nextCart ) {
		if ( ! nextCart || ! Array.isArray( nextCart.items ) ) {
			return;
		}

		cart = nextCart;
		renderProducts();
		renderBasket();
		emitCartUpdate();
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

	function queueProductMutation( productId, button, operation ) {
		if ( ! productId || pendingProducts.has( productId ) ) {
			return;
		}

		clearError( button );
		setPending( productId, true );

		var run = mutationQueue.then( async function () {
			try {
				await operation();
			} catch ( error ) {
				showError( button, error.message );
			} finally {
				setPending( productId, false );
			}
		} );

		// A rejected mutation must never poison later cart writes.
		mutationQueue = run.catch( function () {} );
	}

	function addProduct( button ) {
		var productId = productIdFromButton( button );

		queueProductMutation( productId, button, async function () {
			var nextCart = await mutateCart( 'add-item', {
				id: productId,
				quantity: 1,
			} );
			applyCart( nextCart );

			var item = getCartItem( productId );
			var suffix = item ? ' ×' + item.quantity : '';
			announce( ( strings.added || 'Added' ) + ': ' + productTitle( button ) + suffix );
		} );
	}

	function changeQuantity( control, direction ) {
		var productId = Number.parseInt( control.getAttribute( 'data-product-id' ), 10 );
		var button = control.parentElement ? control.parentElement.querySelector( 'a.add_to_cart_button[data-product_id="' + productId + '"]' ) : null;

		if ( ! button ) {
			return;
		}

		queueProductMutation( productId, button, async function () {
			// Read the item only when this queued write begins. Earlier queued writes
			// may have changed its key, quantity or limits.
			var item = getCartItem( productId );
			if ( ! item ) {
				var latestCart = await getCart();
				applyCart( latestCart );
				item = getCartItem( productId );
			}

			if ( ! item ) {
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
			var nextCart;

			if ( targetQuantity < minimum ) {
				nextCart = await mutateCart( 'remove-item', {
					key: item.key,
				} );
				applyCart( nextCart );
				announce( ( strings.removed || 'Removed' ) + ': ' + productTitle( button ) );
				return;
			}

			nextCart = await mutateCart( 'update-item', {
				key: item.key,
				quantity: targetQuantity,
			} );
			applyCart( nextCart );
			var updatedItem = getCartItem( productId );
			announce( ( strings.updated || 'Updated' ) + ': ' + productTitle( button ) + ( updatedItem ? ' ×' + updatedItem.quantity : '' ) );
		} );
	}

	async function refreshAuthoritativeCart() {
		try {
			var nextCart = await getCart();
			applyCart( nextCart );
		} catch ( error ) {
			// A failed background refresh must not replace the last known cart or
			// disable the progressive enhancement after it has already hydrated.
		}
	}

	function scheduleCartRefresh() {
		if ( ! enhancedReady ) {
			return;
		}

		window.clearTimeout( refreshTimer );
		refreshTimer = window.setTimeout( function () {
			var run = mutationQueue.then( refreshAuthoritativeCart );
			mutationQueue = run.catch( function () {} );
		}, 80 );
	}

	function startNativeUiObserver() {
		if ( nativeObserver || ! document.body ) {
			return;
		}

		nativeObserver = new MutationObserver( function ( mutations ) {
			var relevant = mutations.some( function ( mutation ) {
				if ( mutation.type === 'childList' ) {
					return mutation.addedNodes.length > 0;
				}

				return mutation.type === 'attributes' && mutation.target instanceof Element && mutation.target.matches( '.products a.add_to_cart_button' );
			} );

			if ( relevant && enhancedReady ) {
				window.requestAnimationFrame( renderProducts );
			}
		} );

		nativeObserver.observe( document.body, {
			subtree: true,
			childList: true,
			attributes: true,
			attributeFilter: [ 'class' ],
		} );
	}

	function enableEnhancement( initialCart ) {
		cart = initialCart;
		enhancedReady = true;
		document.body.classList.add( 'grovia-cart-enhanced' );
		renderProducts();
		renderBasket();
		startNativeUiObserver();
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

	window.addEventListener( 'pageshow', scheduleCartRefresh );
	document.addEventListener( 'visibilitychange', function () {
		if ( document.visibilityState === 'visible' ) {
			scheduleCartRefresh();
		}
	} );
	document.addEventListener( 'wc-blocks_added_to_cart', scheduleCartRefresh );
	document.addEventListener( 'wc-blocks_removed_from_cart', scheduleCartRefresh );

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart removed_from_cart wc_fragments_refreshed updated_wc_div', scheduleCartRefresh );
	}

	getCart().then( enableEnhancement ).catch( function () {
		// Keep native WooCommerce add-to-cart behavior and duplicate/native cart
		// feedback untouched when Grovia cannot hydrate authoritative cart state.
		enhancedReady = false;
		document.body.classList.remove( 'grovia-cart-enhanced' );
	} );
}() );