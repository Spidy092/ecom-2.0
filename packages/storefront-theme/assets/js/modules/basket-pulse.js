/**
 * Basket Pulse & Mobile Cart Feedback ES module.
 */
export function initBasketPulse( cfg, restUrl ) {
	const pulse     = document.getElementById( 'storefront-basket-pulse' );
	const lastAdded = document.getElementById( 'storefront-basket-last-added' );
	const countEl   = document.getElementById( 'storefront-basket-count' );
	const totalEl   = document.getElementById( 'storefront-basket-total' );
	const cartCount = document.getElementById( 'storefront-cart-count' );

	if ( ! pulse ) return;

	document.body.addEventListener( 'wc-blocks_added_to_cart', updatePulse );
	document.body.addEventListener( 'added_to_cart', updatePulse );

	async function updatePulse( e ) {
		const detail = e.detail ?? {};
		pulse.hidden = false;

		if ( detail.product_name && lastAdded ) {
			lastAdded.textContent = `Added: ${detail.product_name}`;
			setTimeout( () => { lastAdded.textContent = ''; }, 4000 );
		}

		try {
			const res = await fetch( restUrl( '/wc/store/v1/cart' ), {
				headers: { 'X-WP-Nonce': cfg.nonce },
				credentials: 'same-origin',
			} );
			if ( ! res.ok ) return;
			const cart = await res.json();

			const count = cart.items_count ?? 0;
			const total = cart.totals?.total_price ?? '0';

			if ( countEl ) countEl.textContent = `${count} item${count === 1 ? '' : 's'}`;
			if ( totalEl ) totalEl.textContent  = formatPrice( total, cart.totals?.currency_minor_unit ?? 2, cfg.currency );

			if ( cartCount ) {
				cartCount.textContent = count > 99 ? '99+' : String( count );
				cartCount.setAttribute( 'aria-label', `${count} item${count === 1 ? '' : 's'} in cart` );
				cartCount.dataset.count = count;
			}
		} catch {
			// Pulse remains visible with last state
		}
	}
}

export function formatPrice( minor, decimals, currency = '₹' ) {
	const amount = parseInt( minor, 10 ) / Math.pow( 10, decimals );
	return `${currency}${amount.toFixed( 2 )}`;
}
