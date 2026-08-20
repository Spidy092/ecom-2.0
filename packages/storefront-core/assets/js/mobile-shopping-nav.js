( function () {
	'use strict';

	const navs = document.querySelectorAll( '[data-bt-mobile-shopping-nav]' );
	if ( ! navs.length ) {
		return;
	}

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function focusTarget( selector ) {
		const target = document.querySelector( selector );
		if ( ! target ) {
			return false;
		}

		target.scrollIntoView( {
			block: 'center',
			behavior: prefersReducedMotion() ? 'auto' : 'smooth',
		} );
		target.focus( { preventScroll: true } );
		return true;
	}

	function focusSearch() {
		return focusTarget( '[data-bt-search]' );
	}

	function focusBrowse() {
		return focusTarget( '[data-bt-browse]' );
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

		const browseLink = nav.querySelector( '[data-bt-mobile-browse-link]' );
		if ( browseLink ) {
			browseLink.addEventListener( 'click', function ( event ) {
				if ( focusBrowse() ) {
					event.preventDefault();
					if ( window.location.hash !== '#grocery-browse' ) {
						window.history.replaceState( null, '', '#grocery-browse' );
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

	if ( window.location.hash === '#grocery-browse' ) {
		window.requestAnimationFrame( function () {
			focusBrowse();
		} );
	}
} )();
