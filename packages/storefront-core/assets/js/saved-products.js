( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const productModel = window.BhaivaTechProductWorkspaceModel;
	const savedModel = window.BhaivaTechSavedProductsModel;
	const savedConfig = config.saved || {};

	if ( ! productModel || ! savedModel || ! config.endpoints || ! config.messages ) {
		return;
	}

	function browserStorage() {
		try {
			return window.localStorage;
		} catch ( error ) {
			return null;
		}
	}

	function safeJsonResponse( response ) {
		return response.json().catch( function () {
			return {};
		} );
	}

	document.querySelectorAll( '[data-bt-product-workspace]' ).forEach( function ( root ) {
		const results = root.querySelector( '[data-bt-results]' );
		const status = root.querySelector( '[data-bt-status]' );
		const toggle = root.querySelector( '[data-bt-saved-toggle]' );
		const count = root.querySelector( '[data-bt-saved-count]' );
		const panel = root.querySelector( '[data-bt-saved-panel]' );
		const close = root.querySelector( '[data-bt-saved-close]' );
		const scope = root.querySelector( '[data-bt-saved-scope]' );
		const savedProductsRoot = root.querySelector( '[data-bt-saved-products]' );
		const storage = browserStorage();

		if ( ! results || ! status || ! toggle || ! count || ! panel || ! savedProductsRoot ) {
			return;
		}

		let savedIds = savedConfig.loggedIn
			? []
			: savedModel.read( storage );
		let savedProducts = [];
		let savedMutationInFlight = false;
		let savedProductsLoading = false;
		let accountLoadPromise = null;

		function announce( message ) {
			status.textContent = message;
		}

		function isSaved( productId ) {
			return savedIds.includes( Number( productId ) );
		}

		function updateCount() {
			count.textContent = String( savedIds.length );
		}

		function savedProductEndpoint( productId ) {
			return String( savedConfig.productTemplate || '' ).replace(
				'__PRODUCT_ID__',
				encodeURIComponent( String( Number( productId ) ) )
			);
		}

		async function accountRequest( endpoint, method ) {
			const response = await fetch( endpoint, {
				method: method || 'GET',
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'X-WP-Nonce': savedConfig.restNonce || '',
				},
			} );
			const payload = await safeJsonResponse( response );

			if ( ! response.ok ) {
				throw new Error( payload.message || config.messages.savedUnavailable );
			}

			return payload;
		}

		function updateSaveButton( button, productId, productName ) {
			const saved = isSaved( productId );
			button.setAttribute( 'aria-pressed', saved ? 'true' : 'false' );
			button.textContent = saved ? config.messages.saved : config.messages.saveForLater;
			button.setAttribute(
				'aria-label',
				( saved ? config.messages.removeSavedProduct : config.messages.saveProduct ).replace( '%s', productName )
			);
		}

		function decorateSearchCards() {
			results.querySelectorAll( '.bt-product-card[data-product-id]' ).forEach( function ( card ) {
				const productId = Number( card.dataset.productId );
				const body = card.querySelector( '.bt-product-card__body' );
				const title = card.querySelector( '.bt-product-card__title' );

				if ( ! productId || ! body || ! title ) {
					return;
				}

				let button = card.querySelector( '[data-bt-save-product]' );
				if ( ! button ) {
					button = document.createElement( 'button' );
					button.type = 'button';
					button.className = 'bt-product-card__save';
					button.dataset.btSaveProduct = '';
					button.dataset.productId = String( productId );
					body.appendChild( button );
				}

				updateSaveButton( button, productId, title.textContent.trim() );
			} );
		}

		function makeTextElement( tag, className, text ) {
			const element = document.createElement( tag );
			element.className = className;
			element.textContent = text;
			return element;
		}

		function makeSavedCard( product ) {
			const article = document.createElement( 'article' );
			article.className = 'bt-saved-card';
			article.dataset.productId = String( product.id );

			if ( Array.isArray( product.images ) && product.images[ 0 ] ) {
				const imageLink = document.createElement( 'a' );
				imageLink.className = 'bt-saved-card__image-link';
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

			const body = document.createElement( 'div' );
			body.className = 'bt-saved-card__body';

			const title = document.createElement( 'a' );
			title.className = 'bt-saved-card__title';
			title.href = product.permalink;
			title.textContent = product.name;
			body.appendChild( title );
			body.appendChild( makeTextElement( 'span', 'bt-saved-card__price', productModel.productPrice( product ) ) );

			const actions = document.createElement( 'div' );
			actions.className = 'bt-saved-card__actions';

			if ( ! product.is_in_stock ) {
				actions.appendChild( makeTextElement( 'span', 'bt-saved-card__state', config.messages.outOfStock ) );
			} else if ( product.has_options ) {
				const choose = document.createElement( 'a' );
				choose.href = product.permalink;
				choose.className = 'bt-saved-card__choose';
				choose.textContent = config.messages.chooseOptions;
				actions.appendChild( choose );
			} else if ( productModel.canDirectAdd( product ) ) {
				const add = document.createElement( 'button' );
				add.type = 'button';
				add.dataset.btSavedAddCart = '';
				add.dataset.productId = String( product.id );
				add.textContent = config.messages.addToCart;
				actions.appendChild( add );
			} else {
				actions.appendChild( makeTextElement( 'span', 'bt-saved-card__state', config.messages.unavailable ) );
			}

			const remove = document.createElement( 'button' );
			remove.type = 'button';
			remove.className = 'bt-saved-card__remove';
			remove.dataset.btRemoveSaved = '';
			remove.dataset.productId = String( product.id );
			remove.textContent = config.messages.removeFromSaved;
			remove.setAttribute( 'aria-label', config.messages.removeSavedProduct.replace( '%s', product.name ) );
			actions.appendChild( remove );

			body.appendChild( actions );
			article.appendChild( body );
			return article;
		}

		function renderSavedPanel() {
			savedProductsRoot.replaceChildren();

			if ( savedProductsLoading ) {
				savedProductsRoot.appendChild(
					makeTextElement( 'p', 'bt-saved-panel__empty', config.messages.savedLoading )
				);
				return;
			}

			if ( ! savedIds.length ) {
				savedProductsRoot.appendChild(
					makeTextElement( 'p', 'bt-saved-panel__empty', config.messages.savedEmpty )
				);
				return;
			}

			if ( savedProducts.length < savedIds.length ) {
				savedProductsRoot.appendChild(
					makeTextElement(
						'p',
						'bt-saved-panel__unavailable',
						config.messages.savedUnavailableCount.replace( '%d', String( savedIds.length - savedProducts.length ) )
					)
				);
			}

			savedProducts.forEach( function ( product ) {
				savedProductsRoot.appendChild( makeSavedCard( product ) );
			} );
		}

		async function loadSavedProducts() {
			if ( ! savedIds.length ) {
				savedProducts = [];
				renderSavedPanel();
				return;
			}

			const endpoint = productModel.buildProductsByIdsUrl( config.endpoints.products, savedIds );
			if ( ! endpoint ) {
				savedProducts = [];
				renderSavedPanel();
				return;
			}

			savedProductsLoading = true;
			renderSavedPanel();

			try {
				const response = await fetch( endpoint, {
					method: 'GET',
					credentials: 'same-origin',
					headers: { Accept: 'application/json' },
				} );
				const payload = await safeJsonResponse( response );
				if ( ! response.ok || ! Array.isArray( payload ) ) {
					throw new Error( config.messages.savedUnavailable );
				}
				savedProducts = payload;
			} catch ( error ) {
				savedProducts = [];
				announce( error.message || config.messages.savedUnavailable );
			} finally {
				savedProductsLoading = false;
				renderSavedPanel();
			}
		}

		function focusAfterSavedRemoval() {
			const nextRemove = savedProductsRoot.querySelector( '[data-bt-remove-saved]' );
			const target = nextRemove || close || toggle;
			if ( target ) {
				target.focus( { preventScroll: true } );
			}
		}

		function applyGuestSaved( productId, shouldSave ) {
			const before = savedIds.slice();
			const next = shouldSave
				? savedModel.add( savedIds, productId )
				: savedModel.remove( savedIds, productId );
			const changed = next.length !== before.length || next.some( function ( id, index ) {
				return id !== before[ index ];
			} );
			const result = savedModel.write( storage, next );
			savedIds = result.ids;
			return {
				changed,
				persisted: result.persisted,
			};
		}

		async function setSaved( productId, shouldSave, focusPanelAfter ) {
			if ( savedMutationInFlight ) {
				return;
			}

			savedMutationInFlight = true;

			try {
				let notice = shouldSave ? config.messages.savedAdded : config.messages.savedRemoved;

				if ( savedConfig.loggedIn ) {
					if ( accountLoadPromise ) {
						await accountLoadPromise;
					}

					const payload = await accountRequest(
						savedProductEndpoint( productId ),
						shouldSave ? 'POST' : 'DELETE'
					);
					savedIds = savedModel.normalizeIds( payload.ids, Number( savedConfig.accountMax || 100 ) );
				} else {
					const guestResult = applyGuestSaved( productId, shouldSave );

					if ( shouldSave && ! guestResult.changed ) {
						announce( config.messages.savedGuestLimit );
						return;
					}

					if ( ! guestResult.persisted ) {
						notice = config.messages.savedSessionOnly;
					}
				}

				updateCount();
				decorateSearchCards();
				announce( notice );

				if ( ! panel.hidden ) {
					await loadSavedProducts();
					if ( focusPanelAfter ) {
						focusAfterSavedRemoval();
					}
				}
			} catch ( error ) {
				announce( error.message || config.messages.savedUnavailable );
			} finally {
				savedMutationInFlight = false;
			}
		}

		async function loadAccountSaved() {
			if ( ! savedConfig.loggedIn ) {
				return;
			}

			try {
				const payload = await accountRequest( savedConfig.collection, 'GET' );
				savedIds = savedModel.normalizeIds( payload.ids, Number( savedConfig.accountMax || 100 ) );
				updateCount();
				decorateSearchCards();

				if ( ! panel.hidden ) {
					await loadSavedProducts();
				}
			} catch ( error ) {
				announce( error.message || config.messages.savedUnavailable );
			}
		}

		async function openPanel() {
			panel.hidden = false;
			toggle.setAttribute( 'aria-expanded', 'true' );
			await loadSavedProducts();
		}

		function closePanel() {
			panel.hidden = true;
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus( { preventScroll: true } );
		}

		toggle.addEventListener( 'click', function () {
			if ( panel.hidden ) {
				openPanel();
			} else {
				closePanel();
			}
		} );

		if ( close ) {
			close.addEventListener( 'click', closePanel );
		}

		root.addEventListener( 'bhaivatech:products-rendered', decorateSearchCards );

		root.addEventListener( 'click', function ( event ) {
			const saveButton = event.target.closest( '[data-bt-save-product]' );
			if ( saveButton ) {
				const productId = Number( saveButton.dataset.productId );
				if ( productId ) {
					setSaved( productId, ! isSaved( productId ), false );
				}
				return;
			}

			const removeButton = event.target.closest( '[data-bt-remove-saved]' );
			if ( removeButton ) {
				const productId = Number( removeButton.dataset.productId );
				if ( productId ) {
					setSaved( productId, false, true );
				}
				return;
			}

			const addButton = event.target.closest( '[data-bt-saved-add-cart]' );
			if ( addButton ) {
				const productId = Number( addButton.dataset.productId );
				const product = savedProducts.find( function ( candidate ) {
					return Number( candidate.id ) === productId;
				} );
				if ( product && productModel.canDirectAdd( product ) ) {
					root.dispatchEvent(
						new CustomEvent( 'bhaivatech:saved-add-to-cart', {
							detail: { product },
						} )
					);
				}
			}
		} );

		scope.textContent = savedConfig.loggedIn
			? config.messages.savedAccountScope
			: config.messages.savedBrowserScope;
		updateCount();
		decorateSearchCards();
		renderSavedPanel();
		accountLoadPromise = loadAccountSaved();
	} );
} )();
