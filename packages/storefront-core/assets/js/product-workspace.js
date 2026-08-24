( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const model = window.BhaivaTechProductWorkspaceModel;
	const buyAgainModel = window.BhaivaTechBuyAgainModel;
	const endpoints = config.endpoints || {};

	if ( ! model || ! endpoints.products || ! endpoints.cart || ! config.messages ) {
		return;
	}

	const roots = document.querySelectorAll( '[data-bt-product-workspace]' );

	roots.forEach( function ( root ) {
		const searchInput = root.querySelector( '[data-bt-search]' );
		const searchForm = root.querySelector( '[data-bt-search-form]' );
		const results = root.querySelector( '[data-bt-results]' );
		const status = root.querySelector( '[data-bt-status]' );
		const cartCount = root.querySelector( '[data-bt-cart-count]' );
		const cartTotal = root.querySelector( '[data-bt-cart-total]' );

		let cart = null;
		let products = [];
		let lastSearchQuery = '';
		let searchHasMore = false;
		let recoverySuggestion = '';
		let searchTimer = null;
		let searchController = null;
		let mutationInFlight = false;
		let nonce = config.nonce || '';
		let pendingOperations = 0;
		let repeatHistory = new Map();
		let repeatHistoryPromise = null;

		function setStatus( message ) {
			status.textContent = message;
		}

		function beginBusy() {
			pendingOperations += 1;
			root.setAttribute( 'aria-busy', 'true' );
		}

		function endBusy() {
			pendingOperations = Math.max( 0, pendingOperations - 1 );
			root.setAttribute( 'aria-busy', pendingOperations > 0 ? 'true' : 'false' );
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

		async function request( endpoint, options ) {
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

			const returnMeta = Boolean( requestOptions.returnMeta );
			delete requestOptions.returnMeta;
			const response = await fetch( endpoint, requestOptions );
			const payload = await parseResponse( response );
			return returnMeta ? { payload, response } : payload;
		}

		function focusProductAction( productId, preferredAction ) {
			if ( ! productId || ! preferredAction ) {
				return;
			}

			const selector = '[data-product-id="' + String( Number( productId ) ) + '"] button[data-action="' + preferredAction + '"]';
			const target = results.querySelector( selector );
			if ( target ) {
				target.focus( { preventScroll: true } );
			}
		}

		function reconcileCart( nextCart, focusProductId, focusAction ) {
			cart = nextCart || null;
			const summary = model.cartSummary( cart );
			cartCount.textContent = summary.count === 1
				? config.messages.oneItem
				: config.messages.manyItems.replace( '%d', String( summary.count ) );
			cartTotal.textContent = summary.total;
			renderProducts();
			focusProductAction( focusProductId, focusAction );
			root.dispatchEvent( new CustomEvent( 'bhaivatech:cart-updated', { detail: { cart } } ) );
		}

		function makeTextElement( tag, className, text ) {
			const element = document.createElement( tag );
			if ( className ) {
				element.className = className;
			}
			element.textContent = text;
			return element;
		}

		function makeRecoveryPanel() {
			if ( ! lastSearchQuery || products.length ) {
				return null;
			}

			const panel = document.createElement( 'div' );
			panel.className = 'bt-search-recovery';
			panel.appendChild(
				makeTextElement(
					'p',
					'bt-search-recovery__message',
					config.messages.noResultsFor.replace( '%s', lastSearchQuery )
				)
			);

			if ( recoverySuggestion ) {
				panel.appendChild(
					makeTextElement(
						'p',
						'bt-search-recovery__suggestion',
						config.messages.didYouMean.replace( '%s', recoverySuggestion )
					)
				);

				const searchSuggestion = document.createElement( 'button' );
				searchSuggestion.type = 'button';
				searchSuggestion.dataset.action = 'search-suggestion';
				searchSuggestion.dataset.query = recoverySuggestion;
				searchSuggestion.textContent = config.messages.searchSuggestion.replace( '%s', recoverySuggestion );
				panel.appendChild( searchSuggestion );
			}

			const browse = document.createElement( 'a' );
			browse.className = 'bt-search-recovery__browse';
			browse.href = '#grocery-browse';
			browse.textContent = config.messages.browseProducts;
			panel.appendChild( browse );
			return panel;
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
				const repeatItem = repeatHistory.get( Number( product.id ) );
				const repeatQuantity = repeatItem ? repeatItem.purchased_quantity : 1;
				add.type = 'button';
				add.dataset.action = 'add';
				add.dataset.productId = String( product.id );
				add.dataset.quantity = String( repeatQuantity );
				add.textContent = repeatItem
					? config.messages.addAgainQuantity.replace( '%d', String( repeatQuantity ) )
					: config.messages.add;
				add.setAttribute( 'aria-label', repeatItem
					? config.messages.addAgainQuantity.replace( '%d', String( repeatQuantity ) ) + ' ' + product.name
					: config.messages.add + ' ' + product.name );
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

			const recoveryPanel = makeRecoveryPanel();
			if ( recoveryPanel ) {
				results.appendChild( recoveryPanel );
				root.dispatchEvent( new CustomEvent( 'bhaivatech:products-rendered' ) );
				return;
			}

			products.forEach( function ( product ) {
				const article = document.createElement( 'article' );
				article.className = 'bt-product-card';
				article.dataset.productId = String( product.id );

				const body = document.createElement( 'div' );
				body.className = 'bt-product-card__body';

				if ( Array.isArray( product.images ) && product.images[ 0 ] ) {
					const imageLink = document.createElement( 'a' );
					imageLink.className = 'bt-product-card__image-link';
					imageLink.href = product.permalink;
					imageLink.setAttribute( 'aria-label', product.name );

					const image = document.createElement( 'img' );
					image.src = product.images[ 0 ].thumbnail || product.images[ 0 ].src;
					image.alt = product.images[ 0 ].alt || '';
					image.loading = 'lazy';
					image.decoding = 'async';
					imageLink.appendChild( image );
					article.appendChild( imageLink );
				}

				const titleLink = document.createElement( 'a' );
				titleLink.className = 'bt-product-card__title';
				titleLink.href = product.permalink;
				titleLink.textContent = product.name;

				const price = makeTextElement( 'span', 'bt-product-card__price', model.productPrice( product ) );
				const repeatItem = repeatHistory.get( Number( product.id ) );
				body.appendChild( titleLink );
				if ( repeatItem ) {
					body.appendChild(
						makeTextElement(
							'span',
							'bt-product-card__repeat',
							config.messages.boughtBefore.replace( '%d', String( repeatItem.purchased_quantity ) )
						)
					);
				}
				body.append( price, makeProductAction( product ) );
				article.appendChild( body );
				results.appendChild( article );
			} );

			if ( searchHasMore && lastSearchQuery ) {
				const moreResults = document.createElement( 'a' );
				moreResults.className = 'bt-product-workspace__more-results';
				moreResults.href = model.buildConventionalSearchUrl( config.shopUrl, lastSearchQuery );
				moreResults.textContent = config.messages.showAllResults.replace( '%s', lastSearchQuery );
				results.appendChild( moreResults );
			}

			root.dispatchEvent( new CustomEvent( 'bhaivatech:products-rendered' ) );
		}

		function renderProductsPreservingFocus() {
			const activeElement = document.activeElement;
			const activeProduct = activeElement && activeElement.closest
				? activeElement.closest( '[data-product-id]' )
				: null;
			const activeAction = activeElement && activeElement.dataset ? activeElement.dataset.action : '';
			renderProducts();

			if ( activeElement === searchInput ) {
				searchInput.focus( { preventScroll: true } );
			} else if ( activeProduct && activeAction ) {
				focusProductAction( activeProduct.dataset.productId, activeAction );
			}
		}

		async function loadRepeatHistory() {
			if ( repeatHistoryPromise || ! buyAgainModel || ! config.buyAgain || ! config.buyAgainNonce ) {
				return repeatHistoryPromise;
			}

			repeatHistoryPromise = fetch( config.buyAgain, {
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'X-WP-Nonce': config.buyAgainNonce,
				},
			} )
				.then( async function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Repeat history unavailable.' );
					}
					return buyAgainModel.normalizeResponse( await response.json() );
				} )
				.then( function ( payload ) {
					repeatHistory = new Map( payload.items.map( function ( item ) {
						return [ Number( item.product_id ), item ];
					} ) );
					if ( products.length ) {
						renderProductsPreservingFocus();
					}
				} )
				.catch( function () {
					// Search remains useful when private history is unavailable.
					repeatHistory = new Map();
				} );

			return repeatHistoryPromise;
		}

		function stopSearchForBrowse() {
			window.clearTimeout( searchTimer );
			if ( searchController ) {
				searchController.abort();
				searchController = null;
			}
			searchInput.value = '';
			lastSearchQuery = '';
			searchHasMore = false;
			recoverySuggestion = '';
		}

		root.addEventListener( 'bhaivatech:browse-loading', function ( event ) {
			stopSearchForBrowse();
			products = [];
			renderProducts();
			setStatus( event.detail && event.detail.message ? event.detail.message : config.messages.requestFailed );
		} );

		root.addEventListener( 'bhaivatech:browse-products', function ( event ) {
			stopSearchForBrowse();
			products = event.detail && Array.isArray( event.detail.products ) ? event.detail.products : [];
			renderProducts();
			setStatus( event.detail && event.detail.message ? event.detail.message : config.messages.browseChooseDepartment );
		} );

		root.addEventListener( 'bhaivatech:browse-error', function ( event ) {
			stopSearchForBrowse();
			products = [];
			renderProducts();
			setStatus( event.detail && event.detail.message ? event.detail.message : config.messages.requestFailed );
		} );

		async function loadCart() {
			beginBusy();
			try {
				reconcileCart( await request( endpoints.cart, { method: 'GET' } ) );
			} catch ( error ) {
				setStatus( config.messages.cartUnavailable );
			} finally {
				endBusy();
			}
		}

		async function recoveryFor( query, controller ) {
			const recoveryUrl = model.buildRecoveryUrl( endpoints.products, query );
			if ( ! recoveryUrl ) {
				return '';
			}

			try {
				const candidates = await request( recoveryUrl, {
					method: 'GET',
					signal: controller.signal,
				} );
				return model.suggestSearchTerm( query, candidates );
			} catch ( error ) {
				if ( error.name === 'AbortError' ) {
					throw error;
				}
				return '';
			}
		}

		function clearSearchTimer() {
			window.clearTimeout( searchTimer );
			searchTimer = null;
		}

		function queueSearch( query, immediate ) {
			clearSearchTimer();
			const boundedQuery = model.boundedSearchQuery( query );
			if ( boundedQuery.length < model.MIN_QUERY_LENGTH ) {
				return;
			}

			if ( immediate ) {
				search( boundedQuery );
				return;
			}

			searchTimer = window.setTimeout( function () {
				search( boundedQuery );
			}, 250 );
		}

		async function search( query ) {
			if ( searchController ) {
				searchController.abort();
			}

			const controller = new AbortController();
			searchController = controller;
			beginBusy();
			setStatus( config.messages.searching );

			try {
				const searchResponse = await request( model.buildProductsUrl( endpoints.products, query ), {
					method: 'GET',
					signal: controller.signal,
					returnMeta: true,
				} );
				const nextProducts = searchResponse.payload;
				const totalHeader = searchResponse.response.headers.get( 'X-WP-Total' );
				const total = Number( totalHeader );
				const hasTotal = totalHeader !== null && totalHeader !== '' && Number.isSafeInteger( total ) && total >= 0;
				const nextHasMore = hasTotal ? total > nextProducts.length : nextProducts.length === model.MAX_RESULTS;

				if ( searchController !== controller ) {
					return;
				}

				let nextSuggestion = '';
				if ( nextProducts.length === 0 ) {
					nextSuggestion = await recoveryFor( query, controller );
				}

				if ( searchController !== controller ) {
					return;
				}

				products = nextProducts;
				lastSearchQuery = query;
				searchHasMore = nextHasMore;
				recoverySuggestion = nextSuggestion;
				renderProducts();
				setStatus(
					products.length
						? config.messages.results.replace( '%d', String( products.length ) )
						: config.messages.noResults
				);
			} catch ( error ) {
				if ( error.name !== 'AbortError' && searchController === controller ) {
					products = [];
					lastSearchQuery = '';
					searchHasMore = false;
					recoverySuggestion = '';
					renderProducts();
					setStatus( error.message || config.messages.requestFailed );
				}
			} finally {
				endBusy();
			}
		}

		async function mutateCart( endpoint, body, successMessage, focusProductId, focusAction ) {
			if ( mutationInFlight ) {
				return;
			}

			mutationInFlight = true;
			beginBusy();

			try {
				const nextCart = await request( endpoint, {
					method: 'POST',
					body: JSON.stringify( body ),
				} );
				reconcileCart( nextCart, focusProductId, focusAction );
				setStatus( successMessage );
			} catch ( error ) {
				if ( error.cart ) {
					reconcileCart( error.cart, focusProductId, focusAction );
				} else {
					renderProducts();
					focusProductAction( focusProductId, focusAction );
				}
				setStatus( error.message || config.messages.requestFailed );
			} finally {
				mutationInFlight = false;
				endBusy();
			}
		}

		searchInput.addEventListener( 'input', function () {
			clearSearchTimer();
			const query = model.boundedSearchQuery( searchInput.value );
			if ( searchInput.value !== query ) {
				searchInput.value = query;
			}
			root.dispatchEvent( new CustomEvent( 'bhaivatech:search-activated' ) );

			if ( query.length < model.MIN_QUERY_LENGTH ) {
				if ( searchController ) {
					searchController.abort();
					searchController = null;
				}
				products = [];
				lastSearchQuery = '';
				searchHasMore = false;
				recoverySuggestion = '';
				renderProducts();
				setStatus( config.messages.keepTyping );
				return;
			}

			queueSearch( query );
		} );

		if ( searchForm ) {
			searchForm.addEventListener( 'submit', function ( event ) {
				const query = model.boundedSearchQuery( searchInput.value );
				if ( query.length < model.MIN_QUERY_LENGTH ) {
					return;
				}

				event.preventDefault();
				searchInput.value = query;
				root.dispatchEvent( new CustomEvent( 'bhaivatech:search-activated' ) );
				queueSearch( query, true );
			} );
		}

		results.addEventListener( 'click', function ( event ) {
			const button = event.target.closest( 'button[data-action]' );
			if ( ! button ) {
				return;
			}

			const action = button.dataset.action;
			if ( action === 'search-suggestion' ) {
				const suggestedQuery = button.dataset.query || '';
				if ( suggestedQuery ) {
					searchInput.value = suggestedQuery;
					searchInput.focus();
					root.dispatchEvent( new CustomEvent( 'bhaivatech:search-activated' ) );
					search( suggestedQuery );
				}
				return;
			}

			if ( mutationInFlight ) {
				return;
			}

			button.disabled = true;
			const productId = Number( button.dataset.productId );
			const cartItem = model.findCartItemForProduct( cart, productId );

			if ( action === 'add' ) {
				const requestedQuantity = Math.max(
					1,
					Math.min(
						buyAgainModel ? buyAgainModel.MAX_QUANTITY : 100,
						Number( button.dataset.quantity ) || 1
					)
				);
				mutateCart(
					endpoints.addItem,
					{ id: productId, quantity: requestedQuantity },
					config.messages.added,
					productId,
					'increment'
				);
				return;
			}

			if ( ! cartItem ) {
				button.disabled = false;
				loadCart();
				return;
			}

			if ( action === 'decrement' && Number( cartItem.quantity ) <= 1 ) {
				mutateCart(
					endpoints.removeItem,
					{ key: cartItem.key },
					config.messages.removed,
					productId,
					'add'
				);
				return;
			}

			const delta = action === 'increment' ? 1 : -1;
			mutateCart(
				endpoints.updateItem,
				{ key: cartItem.key, quantity: Number( cartItem.quantity ) + delta },
				config.messages.cartUpdated,
				productId,
				action
			);
		} );

		root.addEventListener( 'bhaivatech:saved-add-to-cart', function ( event ) {
			const product = event.detail && event.detail.product ? event.detail.product : null;
			if ( mutationInFlight || ! model.canDirectAdd( product ) ) {
				return;
			}

			mutateCart(
				endpoints.addItem,
				{ id: Number( product.id ), quantity: 1 },
				config.messages.added,
				null,
				null
			);
		} );

		loadCart();
		loadRepeatHistory();
	} );
} )();
