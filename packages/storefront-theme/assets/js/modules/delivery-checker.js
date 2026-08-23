/**
 * Delivery Checker ES module.
 */
export function initDeliveryChecker( cfg, restUrl ) {
	const form   = document.getElementById( 'storefront-delivery-form' );
	const input  = document.getElementById( 'storefront-postcode-input' );
	const result = document.getElementById( 'storefront-delivery-result' );

	if ( ! form || ! input || ! result ) return;

	const saved = sessionStorage.getItem( 'storefront_postcode' );
	if ( saved ) {
		input.value = saved;
		checkDelivery( saved );
	}

	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		const postcode = input.value.trim();
		if ( ! postcode ) {
			showResult( result, 'error', 'Please enter a postcode.' );
			return;
		}
		checkDelivery( postcode );
	} );

	async function checkDelivery( postcode ) {
		result.textContent = 'Checking…';
		result.className   = 'storefront-delivery-form__result storefront-delivery-form__result--loading';

		try {
			const res = await fetch(
				restUrl( `/storefront-core/v1/delivery/check?postcode=${encodeURIComponent( postcode )}` ),
				{ headers: { 'X-WP-Nonce': cfg.nonce } }
			);

			if ( ! res.ok ) {
				const err = await res.json().catch( () => ({}) );
				showResult( result, 'error', err.message ?? 'Unable to check delivery.' );
				return;
			}

			const data = await res.json();
			if ( data.available ) {
				sessionStorage.setItem( 'storefront_postcode', postcode );
				showResult( result, 'success', `✓ Delivery available to ${postcode}` );
			} else {
				showResult( result, 'unavailable', `Sorry, we don't deliver to ${postcode} yet.` );
			}
		} catch {
			showResult( result, 'error', 'Network error. Please try again.' );
		}
	}

	function showResult( el, type, message ) {
		el.className   = `storefront-delivery-form__result storefront-delivery-form__result--${type}`;
		el.textContent = message;
	}
}
