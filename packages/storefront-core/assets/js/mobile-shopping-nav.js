( function () {
	'use strict';

	const navs = document.querySelectorAll( '[data-bt-mobile-shopping-nav]' );
	if ( ! navs.length ) {
		return;
	}

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function focusSearch() {
		const search = document.querySelector( '[data-bt-search]' );
		if ( ! search ) {
			return false;
		}

		search.scrollIntoView( {
			block: 'center',
			behavior: prefersReducedMotion() ? 'auto' : 'smooth',
		} );
		search.focus( { preventScroll: true } );
		return true;
	}

	function updateCartBadge( nav, count ) {
		const badge = nav.querySelector( '[data-bt-mobile-cart-count]' );
		if ( ! badge ) {
			return;
		}

		const normalized = Math.max( 0, Number( count ) || 0 );
		const label = normalized === 1
			? badge.dataset.labelOne
			: String( badge.dataset.labelMany || '%d items in cart' ).replace( '%d', String( normalized ) );

		badge.textContent = String( normalized );
		badge.setAttribute( 'aria-label', label || String( normalized ) );
	}

	document.body.classList.add( 'bt-has-mobile-shopping-nav' );

	navs.forEach( function ( nav ) {
		const searchLink = nav.querySelector( '[data-bt-mobile-search-link]' );
		if ( searchLink ) {
			searchLink.addEventListener( 'click', function ( event ) {
				if ( focusSearch() ) {
					event.preventDefault();
					if ( window.location.hash !== '#grocery-search' ) {
						window.history.replaceState( null, '', '#grocery-search' );
					}
				}
			} );
		}

		document.querySelectorAll( '[data-bt-product-workspace]' ).forEach( function ( workspace ) {
			workspace.addEventListener( 'bhaivatech:cart-updated', function ( event ) {
				const cart = event.detail && event.detail.cart ? event.detail.cart : null;
				if ( cart ) {
					updateCartBadge( nav, cart.items_count );
				}
			} );
		} );
	} );

	if ( window.location.hash === '#grocery-search' ) {
		window.requestAnimationFrame( function () {
			focusSearch();
		} );
	}
} )();
