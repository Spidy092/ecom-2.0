/**
 * Shopping List Widget ES module.
 */
export function initShoppingList( cfg, restUrl ) {
	const widget = document.getElementById( 'storefront-shopping-list' );
	if ( ! widget ) return;

	const body   = document.getElementById( 'storefront-shopping-list-body' );
	const footer = document.getElementById( 'storefront-shopping-list-footer' );
	const addAll = document.getElementById( 'storefront-shopping-list-add-all' );
	const tpl    = document.getElementById( 'storefront-list-item-tpl' );

	if ( ! cfg.isUser ) {
		if ( body ) body.innerHTML = '<p class="storefront-shopping-list__empty"><a href="/my-account">Sign in</a> to see your list.</p>';
		return;
	}

	loadList();

	async function loadList() {
		try {
			const res = await fetch( restUrl( '/storefront-core/v1/shopping-list' ), {
				headers: { 'X-WP-Nonce': cfg.nonce },
				credentials: 'same-origin',
			} );
			if ( ! res.ok ) throw new Error();
			const data = await res.json();
			renderList( data.items ?? [] );
		} catch {
			if ( body ) body.innerHTML = '<p class="storefront-shopping-list__error">Could not load list.</p>';
		}
	}

	function renderList( items ) {
		if ( ! body || ! tpl ) return;
		body.innerHTML = '';

		if ( items.length === 0 ) {
			body.innerHTML = '<p class="storefront-shopping-list__empty">Your list is empty.</p>';
			return;
		}

		items.forEach( ( item ) => {
			const node = tpl.content.cloneNode( true );
			const row  = node.querySelector( '.storefront-shopping-list__item' );
			row.dataset.productId = item.product_id;

			const name   = row.querySelector( '.storefront-shopping-list__item-name' );
			const remove = row.querySelector( '.storefront-shopping-list__item-remove' );

			name.textContent = item.name ?? `Product #${item.product_id}`;
			remove.addEventListener( 'click', () => removeItem( item.product_id, row ) );
			body.appendChild( node );
		} );

		if ( footer ) footer.hidden = false;
	}

	async function removeItem( productId, row ) {
		try {
			const res = await fetch( restUrl( `/storefront-core/v1/shopping-list/items/${productId}` ), {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': cfg.nonce },
				credentials: 'same-origin',
			} );
			if ( res.ok ) row.remove();
		} catch {}
	}
}
