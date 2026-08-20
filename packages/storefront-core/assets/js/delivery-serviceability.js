( function () {
	'use strict';

	const config = window.BhaivaTechStorefrontConfig || {};
	const endpoint = config.endpoints && config.endpoints.serviceability;
	const serviceability = config.serviceability || {};
	const messages = config.messages || {};

	if ( ! endpoint || ! messages.deliveryCheck ) {
		return;
	}

	document.querySelectorAll( '[data-bt-product-workspace]' ).forEach( function ( root ) {
		const form = root.querySelector( '[data-bt-serviceability]' );
		if ( ! form ) {
			return;
		}

		const country = form.querySelector( '[data-bt-delivery-country]' );
		const postcode = form.querySelector( '[data-bt-delivery-postcode]' );
		const stateSelectField = form.querySelector( '[data-bt-delivery-state-select-field]' );
		const stateSelect = form.querySelector( '[data-bt-delivery-state-select]' );
		const stateInputField = form.querySelector( '[data-bt-delivery-state-input-field]' );
		const stateInput = form.querySelector( '[data-bt-delivery-state-input]' );
		const submit = form.querySelector( '[data-bt-delivery-submit]' );
		const result = form.querySelector( '[data-bt-delivery-result]' );
		const stateOptions = serviceability.stateOptions || {};

		if ( ! country || ! postcode || ! submit || ! result ) {
			return;
		}

		let requestController = null;

		function resetSubmit() {
			submit.disabled = false;
			submit.textContent = messages.deliveryCheck;
		}

		function clearResult() {
			result.textContent = '';
			result.removeAttribute( 'data-status' );
		}

		function invalidateResult() {
			if ( requestController ) {
				requestController.abort();
				requestController = null;
				resetSubmit();
			}
			clearResult();
		}

		function hideStateFields() {
			if ( stateSelectField && stateSelect ) {
				stateSelectField.hidden = true;
				stateSelect.disabled = true;
				stateSelect.required = false;
				stateSelect.value = '';
			}
			if ( stateInputField && stateInput ) {
				stateInputField.hidden = true;
				stateInput.disabled = true;
				stateInput.required = false;
				stateInput.value = '';
			}
		}

		function fillStateSelect( countryCode ) {
			if ( ! stateSelect ) {
				return false;
			}

			const options = stateOptions[ countryCode ] || null;
			if ( ! options || typeof options !== 'object' || ! Object.keys( options ).length ) {
				return false;
			}

			stateSelect.replaceChildren();
			const placeholder = document.createElement( 'option' );
			placeholder.value = '';
			placeholder.textContent = messages.deliveryChooseState;
			stateSelect.appendChild( placeholder );

			Object.entries( options ).forEach( function ( entry ) {
				const option = document.createElement( 'option' );
				option.value = entry[ 0 ];
				option.textContent = entry[ 1 ];
				stateSelect.appendChild( option );
			} );

			return true;
		}

		function revealStateField() {
			const countryCode = String( country.value || '' ).toUpperCase();
			hideStateFields();

			if ( fillStateSelect( countryCode ) && stateSelectField && stateSelect ) {
				stateSelectField.hidden = false;
				stateSelect.disabled = false;
				stateSelect.required = true;
				stateSelect.focus();
				return stateSelect;
			}

			if ( stateInputField && stateInput ) {
				stateInputField.hidden = false;
				stateInput.disabled = false;
				stateInput.required = true;
				stateInput.focus();
				return stateInput;
			}

			return null;
		}

		function currentState() {
			if ( stateSelect && ! stateSelect.disabled ) {
				return stateSelect.value;
			}
			if ( stateInput && ! stateInput.disabled ) {
				return stateInput.value;
			}
			return '';
		}

		function showResult( status, text ) {
			result.dataset.status = status;
			result.textContent = text;
		}

		function handleNeedsMoreLocation( required ) {
			const fields = Array.isArray( required ) ? required : [];

			if ( fields.includes( 'country' ) ) {
				showResult( 'needs_more_location', messages.deliveryNeedCountry );
				country.focus();
				return;
			}

			if ( fields.includes( 'state' ) ) {
				showResult( 'needs_more_location', messages.deliveryNeedState );
				revealStateField();
				return;
			}

			if ( fields.includes( 'postcode' ) ) {
				showResult( 'needs_more_location', messages.deliveryNeedPostcode );
				postcode.focus();
				return;
			}

			showResult( 'unknown', messages.deliveryUnknown );
		}

		function handleResponse( payload ) {
			const status = payload && payload.status ? payload.status : 'unknown';

			if ( status === 'served' ) {
				showResult(
					'served',
					messages.deliveryServed + ' ' + messages.deliveryServedDetail
				);
				return;
			}

			if ( status === 'not_served' ) {
				showResult( 'not_served', messages.deliveryNotServed );
				return;
			}

			if ( status === 'needs_more_location' ) {
				handleNeedsMoreLocation( payload.required );
				return;
			}

			showResult( 'unknown', messages.deliveryUnknown );
		}

		async function submitCheck() {
			if ( requestController ) {
				requestController.abort();
			}

			const controller = new AbortController();
			requestController = controller;
			submit.disabled = true;
			submit.textContent = messages.deliveryChecking;
			clearResult();

			try {
				const response = await fetch( endpoint, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						Accept: 'application/json',
						'Content-Type': 'application/json',
					},
					body: JSON.stringify( {
						country: country.value,
						state: currentState(),
						postcode: postcode.value,
					} ),
					signal: controller.signal,
				} );

				let payload = {};
				try {
					payload = await response.json();
				} catch ( error ) {
					payload = {};
				}

				if ( requestController !== controller ) {
					return;
				}

				if ( ! response.ok ) {
					showResult( 'unknown', payload.message || messages.deliveryUnknown );
					return;
				}

				handleResponse( payload );
			} catch ( error ) {
				if ( error.name !== 'AbortError' && requestController === controller ) {
					showResult( 'unknown', messages.deliveryUnknown );
				}
			} finally {
				if ( requestController === controller ) {
					requestController = null;
					resetSubmit();
				}
			}
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			submitCheck();
		} );

		country.addEventListener( 'change', function () {
			hideStateFields();
			invalidateResult();
		} );

		postcode.addEventListener( 'input', invalidateResult );

		if ( stateSelect ) {
			stateSelect.addEventListener( 'change', invalidateResult );
		}
		if ( stateInput ) {
			stateInput.addEventListener( 'input', invalidateResult );
		}

		hideStateFields();
		clearResult();
	} );
} )();
