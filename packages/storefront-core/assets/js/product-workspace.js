( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const model = window.BhaivaTechProductWorkspaceModel;

	if ( ! model || ! config.restUrl ) {
		return;
	}

	const roots = document.querySelectorAll( '[data-bt-product-workspace]' );

	roots.forEach( function ( root ) {
		const searchInput = root.querySelector( '[data-bt-search]' );
		const results = root.querySelector( '[data-bt-results]' );
		const status = root.querySelector( '[data-bt-status]' );
		const cartCount = root.querySelector( '[data-bt-cart-count]' );
		const cartTotal = root.querySelector( '[data-bt-cart-total]' );

		let cart = null;
		let products = [];
		let searchTimer = null;
		let searchController = null;
		let mutationInFlight = false;
		let nonce = config.nonce || '';

		function setStatus( message ) {
			status.textContent = message;
		}

		function setBusy( busy ) {
			root.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		}

		function updateNonce( response ) {
			const refreshed = response.headers.get( 'Nonce' );
			if ( refreshed ) {
				nonce = refreshed;
			}
		}

		async function parseResponse( response ) {
			updateNonce( response );

			let payload = {};
			try {
				payload = await response.json();
			} catch ( error ) {
				payload = {};
			}

			if ( response.ok ) {
				return payload;
			}

			const requestError = new Error(
				payload && payload.message ? payload.message : config.messages.requestFailed
			);
			requestError.cart = payload && payload.data ? payload.data.cart : null;
			throw requestError;
		}

		async function request( pathOrUrl, options ) {
			const requestOptions = Object.assign(
				{
					credentials: 'same-origin',
					headers: { Accept: 'application/json' },
				},
				options || {}
			);

			if ( requestOptions.body ) {
				requestOptions.headers = Object.assign(
					{},
					requestOptions.headers,
					{ 'Content-Type': 'application/json' }
				);
			}

			if ( requestOptions.method && requestOptions.method !== 'GET' ) {
				requestOptions.headers = Object.assign(
					{},
					requestOptions.headers,
					{ Nonce: nonce }
				);
			}

			const url = /^https?:\/\//.test( pathOrUrl )
				? pathOrUrl
				: new URL( pathOrUrl, config.restUrl ).toString();

			return parseResponse( await fetch( url, requestOptions ) );
		}

		function reconcileCart( nextCart ) {
			cart = nextCart || null;
			const summary = model.cartSummary( cart );
			cartCount.textContent = summary.count === 1
				? config.messages.oneItem
				: config.messages.manyItems.replace( '%d', String( summary.count ) );
			cartTotal.textContent = summary.total;
			renderProducts();
		}

		function makeTextElement( tag, className, text ) {
			const element = document.createElement( tag );
			if ( className ) {
				element.className = className;
			}
			element.textContent = text;
			return element;
		}

		function makeProductAction( product ) {
			const action = document.createElement( 'div' );
			action.className = 'bt-product-card__action';

			if ( ! product.is_in_stock ) {
				action.appendChild( makeTextElement( 'span', 'bt-product-card__stock', config.messages.outOfStock ) );
				return action;
			}

			if ( product.has_options ) {
				const choose = document.createElement( 'a' );
				choose.className = 'bt-product-card__choose';
				choose.href = product.permalink;
				choose.textContent = config.messages.chooseOptions;
				action.appendChild( choose );
				return action;
			}

			if ( ! model.canDirectAdd( product ) ) {
				action.appendChild( makeTextElement( 'span', 'bt-product-card__stock', config.messages.unavailable ) );
				return action;
			}

			const cartItem = model.findCartItemForProduct( cart, product.id );
			if ( ! cartItem ) {
				const add = document.createElement( 'button' );
				add.type = 'button';
				add.dataset.action = 'add';
				add.dataset.productId = String( product.id );
				add.textContent = config.messages.add;
				action.appendChild( add );
				return action;
			}

			const controls = document.createElement( 'div' );
			controls.className = 'bt-product-card__quantity';
			controls.setAttribute( 'aria-label', config.messages.quantityFor.replace( '%s', product.name ) );

			const decrement = document.createElement( 'button' );
			decrement.type = 'button';
			decrement.dataset.action = 'decrement';
			decrement.dataset.productId = String( product.id );
			decrement.dataset.cartKey = cartItem.key;
			decrement.setAttribute( 'aria-label', config.messages.decrease.replace( '%s', product.name ) );
			decrement.textContent = '−';

			const quantity = makeTextElement( 'span', 'bt-product-card__quantity-value', String( cartItem.quantity ) );
			quantity.setAttribute( 'aria-live', 'off' );

			const increment = document.createElement( 'button' );
			increment.type = 'button';
			increment.dataset.action = 'increment';
			increment.dataset.productId = String( product.id );
			increment.dataset.cartKey = cartItem.key;
			increment.setAttribute( 'aria-label', config.messages.increase.replace( '%s', product.name ) );
			increment.textContent = '+';

			controls.append( decrement, quantity, increment );
			action.appendChild( controls );
			return action;
		}

		function renderProducts() {
			results.replaceChildren();

			products.forEach( function ( product ) {
				const article = document.createElement( 'article' );
				article.className = 'bt-product-card';
				article.dataset.productId = String( product.id );

				const imageLink = document.createElement( 'a' );
				imageLink.className = 'bt-product-card__image-link';
				imageLink.href = product.permalink;

				if ( Array.isArray( product.images ) && product.images[ 0 ] ) {
					const image = document.createElement( 'img' );
					image.src = product.images[ 0 ].thumbnail || product.images[ 0 ].src;
					image.alt = product.images[ 0 ].alt || '';
					image.loading = 'lazy';
					image.decoding = 'async';
					imageLink.appendChild( image );
				}

				const body = document.createElement( 'div' );
				body.className = 'bt-product-card__body';

				const titleLink = document.createElement( 'a' );
				titleLink.className = 'bt-product-card__title';
				titleLink.href = product.permalink;
				titleLink.textContent = product.name;

				const price = makeTextElement( 'span', 'bt-product-card__price', model.productPrice( product ) );
				body.append( titleLink, price, makeProductAction( product ) );
				article.append( imageLink, body );
				results.appendChild( article );
			} );
		}

		async function loadCart() {
			try {
				reconcileCart( await request( 'cart', { method: 'GET' } ) );
			} catch ( error ) {
				setStatus( config.messages.cartUnavailable );
			}
		}

		async function search( query ) {
			if ( searchController ) {
				searchController.abort();
			}

			searchController = new AbortController();
			setBusy( true );
			setStatus( config.messages.searching );

			try {
				products = await request( model.buildProductsUrl( config.restUrl, query ), {
					method: 'GET',
					signal: searchController.signal,
				} );
				renderProducts();
				setStatus(
					products.length
						? config.messages.results.replace( '%d', String( products.length ) )
						: config.messages.noResults
				);
			} catch ( error ) {
				if ( error.name !== 'AbortError' ) {
					products = [];
					renderProducts();
					setStatus( error.message || config.messages.requestFailed );
				}
			} finally {
				setBusy( false );
			}
		}

		async function mutateCart( path, body, successMessage ) {
			if ( mutationInFlight ) {
				return;
			}

			mutationInFlight = true;
			setBusy( true );

			try {
				const nextCart = await request( path, {
					method: 'POST',
					body: JSON.stringify( body ),
				} );
				reconcileCart( nextCart );
				setStatus( successMessage );
			} catch ( error ) {
				if ( error.cart ) {
					reconcileCart( error.cart );
				}
				setStatus( error.message || config.messages.requestFailed );
			} finally {
				mutationInFlight = false;
				setBusy( false );
			}
		}

		searchInput.addEventListener( 'input', function () {
			window.clearTimeout( searchTimer );
			const query = searchInput.value.trim();

			if ( query.length < model.MIN_QUERY_LENGTH ) {
				if ( searchController ) {
					searchController.abort();
				}
				products = [];
				renderProducts();
				setStatus( config.messages.keepTyping );
				return;
			}

			searchTimer = window.setTimeout( function () {
				search( query );
			}, 250 );
		} );

		results.addEventListener( 'click', function ( event ) {
			const button = event.target.closest( 'button[data-action]' );
			if ( ! button || mutationInFlight ) {
				return;
			}

			const action = button.dataset.action;
			const productId = Number( button.dataset.productId );
			const cartItem = model.findCartItemForProduct( cart, productId );

			if ( action === 'add' ) {
				mutateCart( 'cart/add-item', { id: productId, quantity: 1 }, config.messages.added );
				return;
			}

			if ( ! cartItem ) {
				loadCart();
				return;
			}

			if ( action === 'decrement' && Number( cartItem.quantity ) <= 1 ) {
				mutateCart( 'cart/remove-item', { key: cartItem.key }, config.messages.removed );
				return;
			}

			const delta = action === 'increment' ? 1 : -1;
			mutateCart(
				'cart/update-item',
				{ key: cartItem.key, quantity: Number( cartItem.quantity ) + delta },
				config.messages.cartUpdated
			);
		} );

		loadCart();
	} );
} )();
