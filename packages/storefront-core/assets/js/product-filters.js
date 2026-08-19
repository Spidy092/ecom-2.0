( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const model = window.BhaivaTechProductFiltersModel;
	const endpoints = config.endpoints || {};
	const messages = config.messages || {};

	if ( ! model || ! endpoints.products || ! endpoints.collectionData || ! endpoints.attributes || ! endpoints.attributeTermsTemplate ) {
		return;
	}

	let instance = 0;

	function createShell( root ) {
		const results = root.querySelector( '[data-bt-results]' );
		if ( ! results ) {
			return null;
		}

		instance += 1;
		const shell = document.createElement( 'section' );
		shell.className = 'bt-product-filters';
		shell.dataset.btFilters = '';
		shell.setAttribute( 'aria-busy', 'false' );

		const panelId = 'bt-product-filters-panel-' + String( instance );
		shell.innerHTML =
			'<div class="bt-product-filters__toolbar">' +
				'<button type="button" class="bt-product-filters__toggle" data-bt-filters-toggle disabled aria-expanded="false" aria-controls="' + panelId + '">' +
					'<span>' + ( messages.filtersToggle || 'Filters' ) + '</span>' +
					'<span class="bt-product-filters__count" data-bt-filters-count hidden></span>' +
				'</button>' +
			'</div>' +
			'<div id="' + panelId + '" class="bt-product-filters__panel" data-bt-filters-panel hidden>' +
				'<p class="bt-product-filters__state" role="status" aria-live="polite" data-bt-filters-state></p>' +
				'<form class="bt-product-filters__form" data-bt-filters-form>' +
					'<label class="bt-product-filters__stock"><input type="checkbox" data-bt-filter-stock><span>' + ( messages.filtersInStock || 'In stock only' ) + '</span></label>' +
					'<fieldset class="bt-product-filters__price" data-bt-filter-price-group hidden><legend>' + ( messages.filtersPrice || 'Price' ) + '</legend>' +
						'<div class="bt-product-filters__price-inputs">' +
							'<label><span>' + ( messages.filtersMinPrice || 'Minimum price' ) + '</span><input type="number" min="0" inputmode="decimal" data-bt-filter-min-price></label>' +
							'<label><span>' + ( messages.filtersMaxPrice || 'Maximum price' ) + '</span><input type="number" min="0" inputmode="decimal" data-bt-filter-max-price></label>' +
						'</div>' +
					'</fieldset>' +
					'<div class="bt-product-filters__attributes" data-bt-filter-attributes></div>' +
					'<div class="bt-product-filters__actions">' +
						'<button type="submit" data-bt-filters-apply>' + ( messages.filtersApply || 'Apply filters' ) + '</button>' +
						'<button type="button" data-bt-filters-clear disabled>' + ( messages.filtersClear || 'Clear filters' ) + '</button>' +
					'</div>' +
				'</form>' +
			'</div>';
		results.before( shell );
		return shell;
	}

	document.querySelectorAll( '[data-bt-product-workspace]' ).forEach( function ( root ) {
		const shell = createShell( root );
		if ( ! shell ) {
			return;
		}

		const toggle = shell.querySelector( '[data-bt-filters-toggle]' );
		const countBadge = shell.querySelector( '[data-bt-filters-count]' );
		const panel = shell.querySelector( '[data-bt-filters-panel]' );
		const state = shell.querySelector( '[data-bt-filters-state]' );
		const form = shell.querySelector( '[data-bt-filters-form]' );
		const inStock = shell.querySelector( '[data-bt-filter-stock]' );
		const priceGroup = shell.querySelector( '[data-bt-filter-price-group]' );
		const minPrice = shell.querySelector( '[data-bt-filter-min-price]' );
		const maxPrice = shell.querySelector( '[data-bt-filter-max-price]' );
		const attributesRoot = shell.querySelector( '[data-bt-filter-attributes]' );
		const clear = shell.querySelector( '[data-bt-filters-clear]' );
		const searchInput = root.querySelector( '[data-bt-search]' );
		const sharedStatus = root.querySelector( '[data-bt-status]' );
		if ( ! toggle || ! panel || ! form || ! searchInput || ! sharedStatus ) {
			return;
		}

		let context = null;
		let pendingSearch = '';
		let metadata = null;
		let filters = model.normalizeFilters( {} );
		let metadataRequest = null;
		let productRequest = null;
		let attributesPromise = null;
		const terms = new Map();
		let version = 0;
		let handingOff = false;

		function key( candidate ) {
			const normalized = model.normalizeContext( candidate );
			if ( ! normalized ) {
				return '';
			}
			return normalized.type === 'search'
				? 'search:' + normalized.query
				: 'department:' + String( normalized.category.id || normalized.category.slug );
		}

		function busy( value ) {
			shell.setAttribute( 'aria-busy', value ? 'true' : 'false' );
		}

		function panelOpen( value, restoreFocus ) {
			panel.hidden = ! value;
			toggle.setAttribute( 'aria-expanded', value ? 'true' : 'false' );
			if ( restoreFocus ) {
				toggle.focus( { preventScroll: true } );
			}
		}

		function controlsDisabled( value ) {
			form.querySelectorAll( 'input, button' ).forEach( function ( control ) {
				control.disabled = Boolean( value );
			} );
			if ( ! value ) {
				clear.disabled = model.activeFilterCount( filters ) === 0;
			}
		}

		function updateCount() {
			const count = model.activeFilterCount( filters );
			countBadge.textContent = count ? String( count ) : '';
			countBadge.hidden = count === 0;
			toggle.setAttribute( 'aria-label', count
				? String( messages.filtersToggleActive || 'Filters, %d active' ).replace( '%d', String( count ) )
				: String( messages.filtersToggle || 'Filters' )
			);
			clear.disabled = count === 0 || ! metadata;
		}

		function abortRequests() {
			if ( metadataRequest ) {
				metadataRequest.abort();
				metadataRequest = null;
			}
			if ( productRequest ) {
				productRequest.abort();
				productRequest = null;
			}
		}

		function reset() {
			inStock.checked = false;
			minPrice.value = '';
			maxPrice.value = '';
			priceGroup.hidden = true;
			attributesRoot.replaceChildren();
			metadata = null;
			filters = model.normalizeFilters( {} );
			updateCount();
		}

		function deactivate( message ) {
			version += 1;
			abortRequests();
			context = null;
			reset();
			toggle.disabled = true;
			busy( false );
			panelOpen( false, false );
			state.textContent = message || messages.filtersChooseContext || 'Search or choose a department to use filters.';
		}

		async function json( url, controller ) {
			const response = await fetch( url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller ? controller.signal : undefined,
			} );
			let payload = null;
			try {
				payload = await response.json();
			} catch ( error ) {
				payload = null;
			}
			if ( ! response.ok ) {
				throw new Error( messages.filtersUnavailable || messages.requestFailed || 'Filters unavailable.' );
			}
			return payload;
		}

		function globalAttributes() {
			if ( ! attributesPromise ) {
				attributesPromise = json( endpoints.attributes, null )
					.then( model.limitFilterAttributes )
					.catch( function ( error ) {
						attributesPromise = null;
						throw error;
					} );
			}
			return attributesPromise;
		}

		function attributeTerms( attribute ) {
			if ( terms.has( attribute.id ) ) {
				return terms.get( attribute.id );
			}
			const request = json( model.buildAttributeTermsUrl( endpoints.attributeTermsTemplate, attribute.id ), null )
				.catch( function ( error ) {
					terms.delete( attribute.id );
					throw error;
				} );
			terms.set( attribute.id, request );
			return request;
		}

		function renderPrice( range ) {
			if ( ! range || range.min_price == null || range.max_price == null ) {
				priceGroup.hidden = true;
				return;
			}
			const unit = Math.max( 0, Number( range.currency_minor_unit ) || 0 );
			const minimum = model.minorToDecimal( range.min_price, unit );
			const maximum = model.minorToDecimal( range.max_price, unit );
			if ( ! minimum || ! maximum ) {
				priceGroup.hidden = true;
				return;
			}
			const step = unit ? '0.' + '0'.repeat( Math.max( 0, unit - 1 ) ) + '1' : '1';
			priceGroup.hidden = false;
			[ minPrice, maxPrice ].forEach( function ( input ) {
				input.min = minimum;
				input.max = maximum;
				input.step = step;
			} );
			minPrice.placeholder = minimum;
			maxPrice.placeholder = maximum;
		}

		function renderAttributes( groups ) {
			attributesRoot.replaceChildren();
			groups.forEach( function ( group ) {
				const fieldset = document.createElement( 'fieldset' );
				fieldset.className = 'bt-product-filters__attribute';
				const legend = document.createElement( 'legend' );
				legend.textContent = group.attribute.name;
				const options = document.createElement( 'div' );
				options.className = 'bt-product-filters__options';
				group.terms.forEach( function ( term ) {
					const label = document.createElement( 'label' );
					label.className = 'bt-product-filters__option';
					const checkbox = document.createElement( 'input' );
					checkbox.type = 'checkbox';
					checkbox.dataset.filterTaxonomy = group.attribute.taxonomy;
					checkbox.dataset.filterTermId = String( term.id );
					const text = document.createElement( 'span' );
					text.textContent = term.name + ' (' + String( term.count ) + ')';
					label.append( checkbox, text );
					options.appendChild( label );
				} );
				fieldset.append( legend, options );
				attributesRoot.appendChild( fieldset );
			} );
		}

		async function loadMetadata( loadVersion ) {
			if ( ! context || loadVersion !== version ) {
				return;
			}
			if ( metadataRequest ) {
				metadataRequest.abort();
			}
			const controller = new AbortController();
			metadataRequest = controller;
			busy( true );
			controlsDisabled( true );
			state.textContent = messages.filtersLoading || 'Loading filters…';

			try {
				const attributes = await globalAttributes();
				const payloads = await Promise.all( [
					json( model.buildCollectionDataUrl( endpoints.collectionData, context, attributes ), controller ),
					Promise.all( attributes.map( attributeTerms ) ),
				] );
				if ( controller.signal.aborted || loadVersion !== version ) {
					return;
				}
				const collection = payloads[ 0 ] || {};
				const termLists = payloads[ 1 ] || [];
				const groups = attributes.map( function ( attribute, index ) {
					return { attribute, terms: model.contextualTerms( termLists[ index ], collection.attribute_counts ) };
				} ).filter( function ( group ) {
					return group.terms.length > 0;
				} );
				metadata = { priceRange: collection.price_range || null, groups };
				renderPrice( metadata.priceRange );
				renderAttributes( groups );
				controlsDisabled( false );
				updateCount();
				state.textContent = groups.length || ! priceGroup.hidden
					? ( messages.filtersReady || 'Filters ready.' )
					: ( messages.filtersNoAdditional || 'Availability is the only filter for this selection.' );
			} catch ( error ) {
				if ( error.name !== 'AbortError' && loadVersion === version ) {
					metadata = null;
					controlsDisabled( true );
					state.textContent = messages.filtersUnavailable || 'Filters could not be loaded. Shopping is still available.';
				}
			} finally {
				if ( metadataRequest === controller ) {
					metadataRequest = null;
				}
				if ( loadVersion === version ) {
					busy( false );
				}
			}
		}

		function activate( candidate ) {
			const normalized = model.normalizeContext( candidate );
			const nextKey = key( normalized );
			if ( ! normalized || ! nextKey ) {
				deactivate();
				return;
			}
			if ( key( context ) === nextKey ) {
				if ( ! metadata && ! metadataRequest ) {
					loadMetadata( version );
				}
				return;
			}
			version += 1;
			abortRequests();
			context = normalized;
			reset();
			toggle.disabled = false;
			panelOpen( false, false );
			loadMetadata( version );
		}

		function readFilters() {
			const price = model.normalizePriceFilters( minPrice.value, maxPrice.value, metadata ? metadata.priceRange : {} );
			const selected = new Map();
			attributesRoot.querySelectorAll( 'input[data-filter-taxonomy]:checked' ).forEach( function ( checkbox ) {
				const taxonomy = checkbox.dataset.filterTaxonomy || '';
				if ( ! selected.has( taxonomy ) ) {
					selected.set( taxonomy, [] );
				}
				selected.get( taxonomy ).push( Number( checkbox.dataset.filterTermId ) );
			} );
			return model.normalizeFilters( {
				inStock: inStock.checked,
				minPrice: price.min,
				maxPrice: price.max,
				attributes: Array.from( selected.entries() ).map( function ( item ) {
					return { taxonomy: item[ 0 ], termIds: item[ 1 ] };
				} ),
			} );
		}

		function handoff( products, message ) {
			const searchQuery = context && context.type === 'search' ? context.query : '';
			handingOff = true;
			root.dispatchEvent( new CustomEvent( 'bhaivatech:browse-products', {
				detail: {
					products,
					category: context && context.type === 'department' ? context.category : null,
					message,
					source: 'filters',
				},
			} ) );
			if ( searchQuery ) {
				searchInput.value = searchQuery;
			}
			handingOff = false;
		}

		async function applyFilters( nextFilters, cleared ) {
			if ( ! context || ! metadata ) {
				return;
			}
			if ( productRequest ) {
				productRequest.abort();
			}
			const controller = new AbortController();
			productRequest = controller;
			const requestVersion = version;
			busy( true );
			controlsDisabled( true );
			sharedStatus.textContent = cleared
				? ( messages.filtersClearing || 'Clearing filters…' )
				: ( messages.filtersApplying || 'Applying filters…' );
			try {
				const response = await json( model.buildFilteredProductsUrl( endpoints.products, context, nextFilters ), controller );
				if ( controller.signal.aborted || productRequest !== controller || requestVersion !== version ) {
					return;
				}
				const products = Array.isArray( response ) ? response : [];
				filters = model.normalizeFilters( nextFilters );
				updateCount();
				const message = products.length
					? String( cleared ? ( messages.filtersCleared || 'Filters cleared. Showing %d products.' ) : ( messages.filtersProductsShown || 'Showing %d filtered products.' ) ).replace( '%d', String( products.length ) )
					: ( messages.filtersNoResults || 'No products match these filters.' );
				handoff( products, message );
				panelOpen( false, true );
			} catch ( error ) {
				if ( error.name !== 'AbortError' && productRequest === controller && requestVersion === version ) {
					sharedStatus.textContent = messages.filtersApplyFailed || 'Filters could not be applied. Try again.';
				}
			} finally {
				if ( productRequest === controller ) {
					productRequest = null;
				}
				if ( requestVersion === version && metadata ) {
					controlsDisabled( false );
					updateCount();
					busy( false );
				}
			}
		}

		toggle.addEventListener( 'click', function () {
			if ( toggle.disabled ) {
				return;
			}
			panelOpen( panel.hidden, false );
			if ( ! panel.hidden ) {
				const first = form.querySelector( 'input:not([disabled]), button:not([disabled])' );
				if ( first ) {
					first.focus();
				}
			}
		} );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			applyFilters( readFilters(), false );
		} );

		clear.addEventListener( 'click', function () {
			inStock.checked = false;
			minPrice.value = '';
			maxPrice.value = '';
			attributesRoot.querySelectorAll( 'input[type="checkbox"]' ).forEach( function ( checkbox ) {
				checkbox.checked = false;
			} );
			applyFilters( model.normalizeFilters( {} ), true );
		} );

		root.addEventListener( 'bhaivatech:search-activated', function () {
			const query = searchInput.value.trim();
			pendingSearch = query.length >= 2 ? query : '';
			if ( ! pendingSearch ) {
				deactivate();
				return;
			}
			if ( key( context ) !== 'search:' + pendingSearch ) {
				deactivate( messages.filtersWaitingForResults || 'Waiting for search results…' );
				pendingSearch = query;
			}
		} );

		root.addEventListener( 'bhaivatech:products-rendered', function () {
			if ( handingOff ) {
				return;
			}
			const query = searchInput.value.trim();
			if ( query.length >= 2 && ( pendingSearch === query || key( context ) === 'search:' + query ) ) {
				pendingSearch = query;
				activate( { type: 'search', query } );
			}
		} );

		root.addEventListener( 'bhaivatech:browse-loading', function ( event ) {
			if ( ! event.detail || event.detail.source !== 'filters' ) {
				pendingSearch = '';
				deactivate();
			}
		} );

		root.addEventListener( 'bhaivatech:browse-products', function ( event ) {
			if ( event.detail && event.detail.source === 'filters' ) {
				return;
			}
			const category = event.detail && event.detail.category ? event.detail.category : null;
			if ( category ) {
				pendingSearch = '';
				activate( { type: 'department', category } );
			}
		} );

		deactivate();
	} );
} )();
