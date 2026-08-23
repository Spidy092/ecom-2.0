/**
 * Interactivity API view module for Product Quick Add block.
 *
 * @module storefrontCore/quickAdd
 */

import { store, getContext } from '@wordpress/interactivity';

store( 'storefrontCore/quickAdd', {
	actions: {
		increment() {
			const context = getContext();
			context.quantity += 1;
		},
		decrement() {
			const context = getContext();
			if ( context.quantity > 1 ) {
				context.quantity -= 1;
			}
		},
		*addToCart() {
			const context = getContext();
			if ( context.isBusy || ! context.productId ) return;

			context.isBusy = true;
			context.added  = false;

			try {
				const response = yield fetch( '/wp-json/wc/store/v1/cart/add-item', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify( {
						id: Number( context.productId ),
						quantity: Number( context.quantity ),
					} ),
				} );

				if ( response.ok ) {
					context.added = true;

					// Dispatch event to trigger Basket Pulse update
					document.body.dispatchEvent( new CustomEvent( 'wc-blocks_added_to_cart', {
						detail: { product_id: context.productId, quantity: context.quantity },
						bubbles: true,
					} ) );

					setTimeout( () => {
						context.added = false;
					}, 2000 );
				}
			} catch ( error ) {
				// Failed quietly, ready for retry
			} finally {
				context.isBusy = false;
			}
		},
	},
} );
