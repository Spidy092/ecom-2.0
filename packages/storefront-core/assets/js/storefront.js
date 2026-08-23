( function () {
	'use strict';

	function initDeliveryChecker( root ) {
		const form = root.querySelector( '[data-delivery-form]' );
		const input = form ? form.querySelector( 'input[name="postcode"]' ) : null;
		const status = root.querySelector( '[data-delivery-status]' );

		if ( ! form || ! input || ! status ) {
			return;
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			const postcode = input.value.trim();
			if ( ! postcode ) {
				input.focus();
				return;
			}

			const endpoint = form.dataset.endpoint;
			if ( ! endpoint ) {
				return;
			}

			form.setAttribute( 'aria-busy', 'true' );
			status.className = 'grovia-delivery-checker__status is-loading';
			status.textContent = 'Checking delivery…';

			fetch( endpoint + '?postcode=' + encodeURIComponent( postcode ), {
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'delivery-check-failed' );
					}
					return response.json();
				} )
				.then( function ( data ) {
					const available = data && data.available === true;
					status.className = 'grovia-delivery-checker__status ' + ( available ? 'is-available' : 'is-unavailable' );
					status.textContent = data && data.message ? data.message : 'Delivery status is unavailable.';
				} )
				.catch( function () {
					status.className = 'grovia-delivery-checker__status is-error';
					status.textContent = 'Delivery status is unavailable. Try again.';
				} )
				.finally( function () {
					form.removeAttribute( 'aria-busy' );
				} );
		} );
	}

	function initShoppingListButton( button ) {
		button.addEventListener( 'click', function () {
			const endpoint = button.dataset.endpoint;
			const productId = button.dataset.productId;
			const nonce = button.dataset.nonce;
			const saved = button.getAttribute( 'aria-pressed' ) === 'true';

			if ( ! endpoint || ! productId || ! nonce || button.disabled ) {
				return;
			}

			button.disabled = true;
			button.textContent = saved ? 'Removing…' : 'Saving…';
			const requestEndpoint = saved
				? endpoint + '/' + encodeURIComponent( productId ) + '?variation_id=0'
				: endpoint;
			fetch( requestEndpoint + ( saved ? '' : '?product_id=' + encodeURIComponent( productId ) ), {
				method: saved ? 'DELETE' : 'POST',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
				body: saved ? undefined : JSON.stringify( { product_id: Number( productId ) } ),
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'shopping-list-failed' );
					}
					return response.json();
				} )
				.then( function () {
					button.setAttribute( 'aria-pressed', saved ? 'false' : 'true' );
					button.textContent = saved ? 'Save to list' : 'Saved';
					const item = button.closest( '[data-shopping-list-item]' );
					if ( saved && item ) {
						item.remove();
					}
				} )
				.catch( function () {
					button.textContent = 'Try again';
				} )
				.finally( function () {
					button.disabled = false;
				} );
		} );
	}

	function initCartFeedback( root ) {
		const endpoint = root.dataset.cartEndpoint;
		const count = root.querySelector( '[data-cart-count]' );
		const countLabel = root.querySelector( '[data-cart-count-label]' );
		const total = root.querySelector( '[data-cart-total]' );

		if ( ! endpoint || ! count || ! countLabel || ! total ) {
			return;
		}

		function refresh() {
			fetch( endpoint, {
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'cart-read-failed' );
					}
					return response.json();
				} )
				.then( function ( data ) {
					const itemsCount = Number( data.items_count || data.itemsCount || 0 );
					const totals = data.totals || {};
					const minorUnit = Number( totals.currency_minor_unit );

					count.textContent = String( Number.isFinite( itemsCount ) ? itemsCount : 0 );
					countLabel.textContent = itemsCount === 1 ? 'item' : 'items';
					if ( totals.total_price && totals.currency_code && Number.isFinite( minorUnit ) ) {
						const value = Number( totals.total_price ) / Math.pow( 10, minorUnit );
						total.textContent = Number.isFinite( value )
							? ' · ' + new Intl.NumberFormat( document.documentElement.lang || 'en-US', { style: 'currency', currency: totals.currency_code } ).format( value )
							: '';
					} else {
						total.textContent = '';
					}
					root.hidden = itemsCount < 1;
				} )
				.catch( function () {
					root.hidden = true;
				} );
		}

		refresh();
		document.addEventListener( 'wc-blocks_added_to_cart', refresh );
		document.addEventListener( 'wc-blocks_removed_from_cart', refresh );
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart removed_from_cart updated_wc_div', refresh );
		}
	}

	document.querySelectorAll( '[data-delivery-checker]' ).forEach( initDeliveryChecker );
	document.querySelectorAll( '[data-shopping-list-button]' ).forEach( initShoppingListButton );
	document.querySelectorAll( '[data-cart-feedback]' ).forEach( initCartFeedback );
}() );
