( function () {
	'use strict';

	const config = window.BhaivaTechBuyAgainConfig || {};
	const model = window.BhaivaTechProductWorkspaceModel;
	const buyAgainModel = window.BhaivaTechBuyAgainModel;

	if ( ! model || ! buyAgainModel || ! config.products || ! config.buyAgain || ! config.addItem || ! config.messages ) {
		return;
	}

	const root = document.querySelector( '[data-bt-buy-again]' );
	if ( ! root ) {
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
		status.textContent = message || '';
	}

	function safeUrl( value ) {
		try {
			const url = new URL( value, window.location.origin );
			return url.protocol === window.location.protocol && url.host === window.location.host
				? url.href
				: '';
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
			throw new Error( payload.message || config.messages.unavailable );
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
			throw new Error( config.messages.productsError );
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
		body.appendChild(
			textElement(
				'span',
				'bt-buy-again__quantity',
				config.messages.boughtQuantity.replace( '%d', String( item.purchased_quantity ) )
			)
		);

		const action = document.createElement( 'div' );
		action.className = 'bt-buy-again__action';
		if ( ! product.is_in_stock ) {
			action.appendChild( textElement( 'span', 'bt-buy-again__state', config.messages.outOfStock ) );
		} else if ( product.has_options ) {
			const choose = makeProductLink( product, 'bt-buy-again__choose', config.messages.chooseOptions );
			if ( choose ) {
				action.appendChild( choose );
			}
		} else if ( ! model.canDirectAdd( product ) ) {
			action.appendChild( textElement( 'span', 'bt-buy-again__state', config.messages.unavailableProduct ) );
		} else {
			const add = document.createElement( 'button' );
			add.type = 'button';
			add.className = 'bt-buy-again__add';
			add.dataset.btBuyAgainAdd = '';
			add.dataset.productId = String( product.id );
			add.dataset.quantity = String( item.purchased_quantity );
			add.textContent = config.messages.addAgain;
			action.appendChild( add );
		}

		body.appendChild( action );
		article.appendChild( body );
		return article;
	}

	function render( payload, products ) {
		results.replaceChildren();
		const productMap = new Map( products.map( function ( product ) {
			return [ Number( product.id ), product ];
		} ) );
		let rendered = 0;

		( Array.isArray( payload.items ) ? payload.items : [] ).forEach( function ( item ) {
			const product = productMap.get( Number( item.product_id ) );
		const quantity = buyAgainModel.normalizeQuantity( item.purchased_quantity );
			if ( ! product || ! Number.isSafeInteger( Number( item.product_id ) ) ) {
				return;
			}

			results.appendChild( makeCard( product, { purchased_quantity: quantity } ) );
			rendered++;
		} );

		if ( ! rendered ) {
			results.appendChild( textElement( 'p', 'bt-buy-again__empty', config.messages.empty ) );
		}

		const skipped = Number( payload.skipped_count ) || 0;
		if ( skipped > 0 ) {
			const note = textElement( 'p', 'bt-buy-again__note', config.messages.skipped.replace( '%d', String( skipped ) ) );
			results.prepend( note );
		}
	}

	async function addToCart( button ) {
		const productId = Number( button.dataset.productId );
		const quantity = Math.max( 1, Math.min( 100, Number( button.dataset.quantity ) || 1 ) );
		const card = button.closest( '.bt-buy-again__card' );
		const title = card ? card.querySelector( '.bt-buy-again__title' ) : null;
		const productName = title ? title.textContent.trim() : config.messages.addAgain;

		button.disabled = true;
		announce( config.messages.adding.replace( '%s', productName ) );

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
			const payload = await jsonResponse( response );
			const cart = payload.cart || payload;
			root.dispatchEvent( new CustomEvent( 'bhaivatech:cart-updated', { detail: { cart } } ) );
			announce( config.messages.added.replace( '%d', String( quantity ) ).replace( '%s', productName ) );
		} catch ( error ) {
			announce( config.messages.addFailed.replace( '%s', productName ) );
			button.disabled = false;
		}
	}

	root.addEventListener( 'click', function ( event ) {
		const button = event.target.closest( '[data-bt-buy-again-add]' );
		if ( button ) {
			addToCart( button );
		}
	} );

	async function load() {
		announce( config.messages.loading );
		results.replaceChildren();

		try {
			const payload = buyAgainModel.normalizeResponse( await getBuyAgain() );
			const items = payload.items;
			const products = items.length ? await getProducts( items.map( function ( item ) { return item.product_id; } ) ) : [];
			render( payload, products );
			announce( '' );
		} catch ( error ) {
			results.replaceChildren( textElement( 'p', 'bt-buy-again__error', error.message || config.messages.unavailable ) );
			const retry = document.createElement( 'button' );
			retry.type = 'button';
			retry.className = 'bt-buy-again__retry';
				retry.textContent = config.messages.retry;
			retry.addEventListener( 'click', load );
			results.appendChild( retry );
			announce( error.message || config.messages.unavailable );
		}
	}

	load();
} )();
