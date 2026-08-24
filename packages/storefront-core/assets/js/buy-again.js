( function () {
	'use strict';

	const config = window.BhaivaTechBuyAgainConfig || {};
	const model = window.BhaivaTechProductWorkspaceModel;
	const buyAgainModel = window.BhaivaTechBuyAgainModel;
	const root = document.querySelector( '[data-bt-buy-again]' );

	if ( ! root || ! model || ! buyAgainModel || ! config.products || ! config.buyAgain || ! config.addItem ) {
		return;
	}

	const results = root.querySelector( '[data-bt-buy-again-results]' );
	const status = root.querySelector( '[data-bt-buy-again-status]' );
	let storeNonce = config.nonce || '';

	function textElement( tag, className, text ) {
		const element = document.createElement( tag );
		element.className = className;
		element.textContent = text;
		return element;
	}

	function announce( message ) {
		if ( status ) {
			status.textContent = message || '';
		}
	}

	function safeUrl( value ) {
		try {
			const url = new URL( value, window.location.origin );
			return url.protocol === window.location.protocol && url.host === window.location.host ? url.href : '';
		} catch ( error ) {
			return '';
		}
	}

	async function jsonResponse( response ) {
		if ( response.headers.get( 'Nonce' ) ) {
			storeNonce = response.headers.get( 'Nonce' );
		}

		let payload = {};
		try {
			payload = await response.json();
		} catch ( error ) {
			payload = {};
		}

		if ( ! response.ok ) {
			throw new Error( payload.message || config.messages?.unavailable || 'Request failed.' );
		}

		return payload;
	}

	async function getBuyAgain() {
		const response = await fetch( config.buyAgain, {
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-WP-Nonce': config.restNonce || '',
			},
		} );
		return jsonResponse( response );
	}

	async function getProducts( ids ) {
		const endpoint = model.buildProductsByIdsUrl( config.products, ids );
		if ( ! endpoint ) {
			return [];
		}

		const response = await fetch( endpoint, {
			credentials: 'same-origin',
			headers: { Accept: 'application/json' },
		} );
		const payload = await jsonResponse( response );
		if ( ! Array.isArray( payload ) ) {
			throw new Error( config.messages?.productsError || 'Products could not be loaded.' );
		}
		return payload;
	}

	function makeProductLink( product, className, label ) {
		const href = safeUrl( product.permalink );
		if ( ! href ) {
			return null;
		}

		const link = document.createElement( 'a' );
		link.className = className;
		link.href = href;
		if ( label ) {
			link.textContent = label;
		}
		return link;
	}

	function makeImage( product ) {
		if ( ! Array.isArray( product.images ) || ! product.images[ 0 ] ) {
			return null;
		}

		const imageData = product.images[ 0 ];
		const src = safeUrl( imageData.thumbnail || imageData.src );
		if ( ! src ) {
			return null;
		}

		const image = document.createElement( 'img' );
		image.src = src;
		image.alt = String( imageData.alt || product.name || '' );
		image.loading = 'lazy';
		image.decoding = 'async';
		return image;
	}

	function makeCard( product, item ) {
		const article = document.createElement( 'article' );
		article.className = 'bt-buy-again__card';
		article.dataset.productId = String( product.id );

		const imageLink = makeProductLink( product, 'bt-buy-again__image-link' );
		const image = makeImage( product );
		if ( imageLink && image ) {
			imageLink.appendChild( image );
			article.appendChild( imageLink );
		}

		const body = document.createElement( 'div' );
		body.className = 'bt-buy-again__card-body';
		const title = makeProductLink( product, 'bt-buy-again__title', product.name );
		if ( title ) {
			body.appendChild( title );
		}
		body.appendChild( textElement( 'span', 'bt-buy-again__price', model.productPrice( product ) ) );
		body.appendChild( textElement( 'span', 'bt-buy-again__quantity', ( config.messages?.boughtQuantity || 'Bought %d last time' ).replace( '%d', String( item.purchased_quantity ) ) ) );

		const action = document.createElement( 'div' );
		action.className = 'bt-buy-again__action';
		if ( ! product.is_in_stock ) {
			action.appendChild( textElement( 'span', 'bt-buy-again__state', config.messages?.outOfStock || 'Out of stock' ) );
		} else if ( product.has_options ) {
			const choose = makeProductLink( product, 'bt-buy-again__choose', config.messages?.chooseOptions || 'Choose options' );
			if ( choose ) {
				action.appendChild( choose );
			} else {
				action.appendChild( textElement( 'span', 'bt-buy-again__state', config.messages?.unavailableProduct || 'Unavailable' ) );
			}
		} else if ( ! model.canDirectAdd( product ) ) {
			action.appendChild( textElement( 'span', 'bt-buy-again__state', config.messages?.unavailableProduct || 'Unavailable' ) );
		} else {
			const add = document.createElement( 'button' );
			add.type = 'button';
			add.className = 'bt-buy-again__add';
			add.dataset.btBuyAgainAdd = '';
			add.dataset.productId = String( product.id );
			add.dataset.quantity = String( item.purchased_quantity );
			add.textContent = config.messages?.addAgain || 'Add again';
			action.appendChild( add );
		}

		body.appendChild( action );
		article.appendChild( body );
		return article;
	}

	function render( payload, products ) {
		results.replaceChildren();
		const productMap = new Map( products.map( ( product ) => [ Number( product.id ), product ] ) );
		let rendered = 0;

		payload.items.forEach( ( item ) => {
			const product = productMap.get( Number( item.product_id ) );
			if ( ! product ) {
				return;
			}

			results.appendChild( makeCard( product, item ) );
			rendered++;
		} );

		if ( ! rendered ) {
			results.appendChild( textElement( 'p', 'bt-buy-again__empty', config.messages?.empty || 'Buy Again will appear after an eligible order.' ) );
		}

		const skipped = Number( payload.skipped_count ) || 0;
		if ( skipped > 0 ) {
			results.prepend( textElement( 'p', 'bt-buy-again__note', ( config.messages?.skipped || '%d recent products are no longer available.' ).replace( '%d', String( skipped ) ) ) );
		}
	}

	async function getAuthoritativeCart() {
		if ( ! config.cart ) {
			return null;
		}

		const response = await fetch( config.cart, {
			credentials: 'same-origin',
			headers: { Accept: 'application/json', Nonce: storeNonce },
		} );
		return jsonResponse( response );
	}

	async function addToCart( button ) {
		const productId = Number( button.dataset.productId );
		const quantity = buyAgainModel.normalizeQuantity( button.dataset.quantity );
		const card = button.closest( '.bt-buy-again__card' );
		const title = card ? card.querySelector( '.bt-buy-again__title' ) : null;
		const productName = title ? title.textContent.trim() : ( config.messages?.addAgain || 'Product' );

		button.disabled = true;
		announce( ( config.messages?.adding || 'Adding %s…' ).replace( '%s', productName ) );

		try {
			const response = await fetch( config.addItem, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
					Nonce: storeNonce,
				},
				body: JSON.stringify( { id: productId, quantity } ),
			} );
			const addPayload = await jsonResponse( response );
			// The mutation response is already authoritative. A follow-up cart
			// read refreshes the account surface when available, but a transient
			// read failure must not report a successful add as a failed mutation.
			let cart = addPayload.cart || addPayload;
			try {
				cart = ( await getAuthoritativeCart() ) || cart;
			} catch ( error ) {
				// Keep the authoritative mutation payload as the safe fallback.
			}
			root.dispatchEvent( new CustomEvent( 'bhaivatech:cart-updated', { detail: { cart } } ) );
			announce( ( config.messages?.added || 'Added %d × %s to your cart.' ).replace( '%d', String( quantity ) ).replace( '%s', productName ) );
		} catch ( error ) {
			announce( ( config.messages?.addFailed || '%s could not be added. Try again.' ).replace( '%s', productName ) );
			button.disabled = false;
		}
	}

	root.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-bt-buy-again-add]' );
		if ( button ) {
			addToCart( button );
		}
	} );

	async function load() {
		announce( config.messages?.loading || 'Loading recent purchases…' );
		results.replaceChildren();

		try {
			const payload = buyAgainModel.normalizeResponse( await getBuyAgain() );
			const products = payload.items.length ? await getProducts( payload.items.map( ( item ) => item.product_id ) ) : [];
			render( payload, products );
			announce( '' );
		} catch ( error ) {
			results.replaceChildren( textElement( 'p', 'bt-buy-again__error', error.message || config.messages?.unavailable || 'Buy Again could not be loaded.' ) );
			const retry = document.createElement( 'button' );
			retry.type = 'button';
			retry.className = 'bt-buy-again__retry';
			retry.textContent = config.messages?.retry || 'Try again';
			retry.addEventListener( 'click', load );
			results.appendChild( retry );
			announce( error.message || config.messages?.unavailable || 'Buy Again could not be loaded.' );
		}
	}

	load();
} )();
