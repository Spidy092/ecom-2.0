/**
 * Interactivity API view module for Product Quick Add block.
 *
 * Handles:
 * - Quantity increment/decrement with stock ceiling
 * - WooCommerce Store API add-to-cart with proper Nonce header
 * - Visual state management (idle, busy, added, error) via data-state attribute
 * - Accessible live-region announcements
 * - Debounce protection against rapid submit clicks
 *
 * @module storefrontCore/quickAdd
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

/**
 * Retrieve the Store API nonce from the inline storefront config.
 * WooCommerce validates this token against the `wc_store_api` action.
 *
 * @return {string} Nonce value or empty string.
 */
let storeApiNonce = '';

function getStoreApiNonce() {
	if ( storeApiNonce ) {
		return storeApiNonce;
	}

	if ( typeof window !== 'undefined' ) {
		if ( window.storefrontConfig?.storeApiNonce ) {
			storeApiNonce = window.storefrontConfig.storeApiNonce;
		} else if ( window.wcStoreApiNonce ) {
			storeApiNonce = window.wcStoreApiNonce;
		}
	}

	return storeApiNonce;
}

function updateStoreApiNonce( response ) {
	const refreshed = response.headers.get( 'Nonce' );
	if ( refreshed ) {
		storeApiNonce = refreshed;
	}
}

/**
 * Return the scoped Store API endpoint supplied by the theme when available.
 * WordPress Playground prefixes REST routes with a disposable site scope, so
 * a root-relative `/wp-json` URL is not safe for every supported environment.
 *
 * @return {string} Cart add-item endpoint.
 */
function getCartAddEndpoint() {
	const configuredBase = typeof window !== 'undefined' && window.storefrontConfig
		? window.storefrontConfig.storeApiUrl
		: '';
	const base = configuredBase || '/wp-json/wc/store/v1/';
	return `${base.replace( /\/$/, '' )}/cart/add-item`;
}

/**
 * Set the visual state on the block wrapper element.
 *
 * @param {HTMLElement} el    The block root element.
 * @param {string}      state One of: idle, busy, added, error.
 */
function setBlockState( el, state ) {
	if ( el ) {
		el.dataset.state = state;
	}
}

/**
 * Update the accessible status region.
 *
 * @param {HTMLElement} el      The block root element.
 * @param {string}      message Announcement text.
 * @param {string}      level   One of: success, error, '' (clear).
 */
function announce( el, message, level ) {
	if ( ! el ) return;
	const status = el.querySelector( '.storefront-quick-add-status' );
	if ( ! status ) return;
	status.textContent = message;
	status.dataset.level = level;
}

store( 'storefrontCore/quickAdd', {
	actions: {
		increment() {
			const context = getContext();
			if ( context.isBusy ) return;

			const max = context.stockQuantity || 9999;
			if ( context.quantity < max ) {
				context.quantity += 1;
			}
			context.atLimit = context.quantity >= max;
		},

		decrement() {
			const context = getContext();
			if ( context.isBusy ) return;

			if ( context.quantity > 1 ) {
				context.quantity -= 1;
			}
			context.atLimit = context.quantity >= ( context.stockQuantity || 9999 );
		},

		*addToCart() {
			const context = getContext();
			const { ref } = getElement();
			const block = ref.closest( '.storefront-quick-add-block' );

			if ( context.isBusy || ! context.productId ) return;

			context.isBusy = true;
			context.added = false;
			context.error = false;
			setBlockState( block, 'busy' );
			announce( block, 'Adding to cart\u2026', '' );

			const nonce = context.storeApiNonce || getStoreApiNonce();
			const headers = {
				'Content-Type': 'application/json',
			};
			if ( nonce ) {
				headers[ 'Nonce' ] = nonce;
			}

			try {
				const response = yield fetch( getCartAddEndpoint(), {
					method: 'POST',
					credentials: 'same-origin',
					headers,
					body: JSON.stringify( {
						id: Number( context.productId ),
						quantity: Number( context.quantity ),
					} ),
				} );
				updateStoreApiNonce( response );

				if ( ! response.ok ) {
					const body = yield response.json().catch( () => ( {} ) );
					const errorMessage = body.message || 'Could not add to cart. Please try again.';
					context.error = true;
					setBlockState( block, 'error' );
					announce( block, errorMessage, 'error' );
					return;
				}

				// Parse cart response to update stock ceiling if available.
				const cart = yield response.json().catch( () => null );
				if ( cart && Array.isArray( cart.items ) ) {
					const item = cart.items.find(
						( i ) => Number( i.id ) === Number( context.productId )
					);
					if ( item && item.quantity_limits && item.quantity_limits.maximum ) {
						context.stockQuantity = item.quantity_limits.maximum;
					}
				}

				context.added = true;
				setBlockState( block, 'added' );
				announce(
					block,
					`Added ${context.quantity} to cart.`,
					'success'
				);

				// Dispatch event to update Basket Pulse and other listeners.
				document.body.dispatchEvent(
					new CustomEvent( 'wc-blocks_added_to_cart', {
						detail: {
							product_id: context.productId,
							quantity: context.quantity,
						},
						bubbles: true,
					} )
				);

				// Clear success state after 2.5 seconds.
				yield new Promise( ( resolve ) => setTimeout( resolve, 2500 ) );
				context.added = false;
				setBlockState( block, 'idle' );
				announce( block, '', '' );
			} catch ( networkError ) {
				context.error = true;
				setBlockState( block, 'error' );
				announce( block, 'Network error. Check your connection and retry.', 'error' );
			} finally {
				context.isBusy = false;
			}
		},
	},
} );
