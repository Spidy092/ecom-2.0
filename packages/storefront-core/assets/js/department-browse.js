( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const model = window.BhaivaTechProductWorkspaceModel;
	const endpoints = config.endpoints || {};
	const messages = config.messages || {};

	if ( ! model || ! endpoints.categories || ! endpoints.products || ! messages.browseChooseDepartment ) {
		return;
	}

	document.querySelectorAll( '[data-bt-product-workspace]' ).forEach( function ( root ) {
		const browse = root.querySelector( '[data-bt-browse]' );
		if ( ! browse ) {
			return;
		}

		const controls = browse.querySelector( '[data-bt-departments]' );
		const selectedBar = browse.querySelector( '[data-bt-selected-department]' );
		const selectedName = browse.querySelector( '[data-bt-selected-department-name]' );
		const showDepartments = browse.querySelector( '[data-bt-show-departments]' );
		const browseState = browse.querySelector( '[data-bt-browse-state]' );
		const fallback = browse.querySelector( '[data-bt-browse-fallback]' );

		if ( ! controls || ! selectedBar || ! selectedName || ! showDepartments || ! browseState ) {
			return;
		}

		let categories = [];
		let selectedCategory = null;
		let chooserOpen = true;
		let categoryController = null;
		let productsController = null;

		function setBrowseState( text ) {
			browseState.textContent = text || '';
		}

		function setBusy( busy ) {
			browse.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		}

		function responseTotal( response, fallbackCount ) {
			const header = Number( response.headers.get( 'X-WP-Total' ) );
			return Number.isSafeInteger( header ) && header >= 0 ? header : fallbackCount;
		}

		async function readCollection( url, controller ) {
			const response = await fetch( url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller.signal,
			} );

			if ( ! response.ok ) {
				throw new Error( messages.requestFailed || 'Request failed.' );
			}

			let payload;
			try {
				payload = await response.json();
			} catch ( error ) {
				throw new Error( messages.requestFailed || 'Request failed.' );
			}

			if ( ! Array.isArray( payload ) ) {
				throw new Error( messages.requestFailed || 'Request failed.' );
			}

			return {
				items: payload,
				total: responseTotal( response, payload.length ),
			};
		}

		function presentation() {
			return model.departmentPresentation( categories.length );
		}

		function departmentButton( category ) {
			const button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'bt-department-browse__department';
			button.dataset.departmentId = String( category.id );
			button.textContent = category.name;
			button.setAttribute( 'aria-pressed', selectedCategory && Number( selectedCategory.id ) === Number( category.id ) ? 'true' : 'false' );
			return button;
		}

		function renderCategories() {
			const mode = presentation();
			browse.dataset.mode = mode;
			controls.className = 'bt-department-browse__departments bt-department-browse__departments--' + mode;
			controls.replaceChildren();

			if ( mode === 'chooser' && selectedCategory && ! chooserOpen ) {
				controls.hidden = true;
				selectedBar.hidden = false;
				selectedName.textContent = selectedCategory.name;
				return;
			}

			selectedBar.hidden = true;
			controls.hidden = false;
			categories.forEach( function ( category ) {
				controls.appendChild( departmentButton( category ) );
			} );
		}

		function restoreSelectionFocus() {
			if ( presentation() === 'chooser' && selectedCategory && ! chooserOpen ) {
				showDepartments.focus( { preventScroll: true } );
				return;
			}

			if ( selectedCategory ) {
				const selected = controls.querySelector( '[data-department-id="' + String( selectedCategory.id ) + '"]' );
				if ( selected ) {
					selected.focus( { preventScroll: true } );
				}
			}
		}

		function formatCountMessage( count, name ) {
			if ( Number( count ) === 1 ) {
				return String( messages.browseOneProductFound || '1 product in %s.' )
					.replace( '%s', name );
			}

			return String( messages.browseProductsFound || '%d products in %s.' )
				.replace( '%d', String( count ) )
				.replace( '%s', name );
		}

		function dispatchWorkspace( type, detail ) {
			root.dispatchEvent( new CustomEvent( type, { detail: detail || {} } ) );
		}

		async function selectDepartment( category ) {
			if ( ! category || ! category.id ) {
				return;
			}

			selectedCategory = category;
			chooserOpen = false;
			renderCategories();
			restoreSelectionFocus();

			if ( productsController ) {
				productsController.abort();
			}
			const controller = new AbortController();
			productsController = controller;
			setBusy( true );
			const loadingMessage = String( messages.browseLoadingProducts || 'Loading %s…' ).replace( '%s', category.name );
			dispatchWorkspace( 'bhaivatech:browse-loading', { message: loadingMessage } );

			try {
				const url = model.buildDepartmentProductsUrl( endpoints.products, category );
				const collection = await readCollection( url, controller );
				if ( productsController !== controller ) {
					return;
				}

				const products = collection.items;
				const total = Math.max( collection.total, products.length );
				if ( fallback ) {
					fallback.hidden = total <= products.length;
				}

				const message = products.length
					? formatCountMessage( total, category.name )
					: String( messages.browseEmptyDepartment || 'No products are available in %s right now.' ).replace( '%s', category.name );

				dispatchWorkspace( 'bhaivatech:browse-products', {
					products,
					category,
					message,
				} );
			} catch ( error ) {
				if ( error.name !== 'AbortError' && productsController === controller ) {
					if ( fallback ) {
						fallback.hidden = false;
					}
					dispatchWorkspace( 'bhaivatech:browse-error', {
						message: String( messages.browseProductsUnavailable || 'Products in %s could not be loaded. Try again.' ).replace( '%s', category.name ),
					} );
				}
			} finally {
				if ( productsController === controller ) {
					productsController = null;
					setBusy( false );
				}
			}
		}

		controls.addEventListener( 'click', function ( event ) {
			const button = event.target.closest( 'button[data-department-id]' );
			if ( ! button ) {
				return;
			}
			const category = categories.find( function ( item ) {
				return Number( item.id ) === Number( button.dataset.departmentId );
			} );
			if ( category ) {
				selectDepartment( category );
			}
		} );

		showDepartments.addEventListener( 'click', function () {
			chooserOpen = true;
			renderCategories();
			const selected = selectedCategory
				? controls.querySelector( '[data-department-id="' + String( selectedCategory.id ) + '"]' )
				: controls.querySelector( 'button' );
			if ( selected ) {
				selected.focus();
			}
		} );

		root.addEventListener( 'bhaivatech:search-activated', function () {
			selectedCategory = null;
			chooserOpen = true;
			if ( categories.length ) {
				renderCategories();
				if ( fallback ) {
					fallback.hidden = true;
				}
			}
		} );

		async function loadDepartments() {
			if ( categoryController ) {
				categoryController.abort();
			}
			const controller = new AbortController();
			categoryController = controller;
			setBusy( true );
			setBrowseState( messages.browseLoadingDepartments || 'Loading departments…' );

			try {
				const collection = await readCollection( model.buildTopCategoriesUrl( endpoints.categories ), controller );
				if ( categoryController !== controller ) {
					return;
				}

				if ( collection.total > model.MAX_DEPARTMENT_CATEGORIES ) {
					categories = [];
					setBrowseState( messages.browseDepartmentsUnavailable || 'Departments could not be loaded. Browse the full shop instead.' );
					return;
				}

				categories = model.filterShopperDepartments(
					collection.items,
					config.defaultProductCategoryId
				);

				if ( ! categories.length ) {
					setBrowseState( messages.browseNoDepartments || 'No grocery departments are available yet.' );
					return;
				}

				if ( fallback ) {
					fallback.hidden = true;
				}
				renderCategories();
				setBrowseState( messages.browseChooseDepartment || 'Choose a department.' );
			} catch ( error ) {
				if ( error.name !== 'AbortError' && categoryController === controller ) {
					if ( fallback ) {
						fallback.hidden = false;
					}
					setBrowseState( messages.browseDepartmentsUnavailable || 'Departments could not be loaded. Browse the full shop instead.' );
				}
			} finally {
				if ( categoryController === controller ) {
					categoryController = null;
					setBusy( false );
				}
			}
		}

		loadDepartments();
	} );
} )();
