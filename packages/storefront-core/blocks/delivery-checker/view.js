/**
 * Interactivity API view module for Delivery Checker block.
 *
 * @module storefrontCore/delivery
 */

import { store, getContext } from '@wordpress/interactivity';

store( 'storefrontCore/delivery', {
	state: {
		isLoading: false,
		buttonLabel() {
			const { state } = store( 'storefrontCore/delivery' );
			return state.isLoading ? 'Checking…' : 'Check';
		},
	},
	actions: {
		updatePostcode( event ) {
			const context = getContext();
			context.postcode = event.target.value;
		},
		*checkDelivery( event ) {
			event.preventDefault();
			const context = getContext();
			const { state } = store( 'storefrontCore/delivery' );

			const postcode = ( context.postcode || '' ).trim();
			if ( ! postcode ) {
				context.status  = 'error';
				context.message = 'Please enter a valid postcode.';
				return;
			}

			state.isLoading = true;
			context.status  = 'loading';
			context.message = '';

			try {
				const response = yield fetch(
					`/wp-json/storefront-core/v1/delivery/check?postcode=${encodeURIComponent( postcode )}`
				);

				if ( ! response.ok ) {
					const errData   = yield response.json();
					context.status  = 'error';
					context.message = errData.message || 'Unable to check delivery.';
					return;
				}

				const data = yield response.json();
				if ( data.available ) {
					context.status  = 'available';
					context.message = `✓ Delivery available to ${postcode}`;
				} else {
					context.status  = 'unavailable';
					context.message = `Sorry, delivery is not available to ${postcode} yet.`;
				}
			} catch {
				context.status  = 'error';
				context.message = 'Network error. Please try again.';
			} finally {
				state.isLoading = false;
			}
		},
	},
} );
