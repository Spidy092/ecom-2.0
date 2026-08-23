/**
 * Storefront Interactions — main entry orchestrating sub-modules.
 *
 * @module storefront-interactions
 */

import { initDeliveryChecker } from './modules/delivery-checker.js';
import { initBasketPulse, formatPrice } from './modules/basket-pulse.js';
import { initShoppingList } from './modules/shopping-list.js';

/* global storefrontConfig */

'use strict';

const cfg = typeof window !== 'undefined' && window.storefrontConfig ? window.storefrontConfig : { restUrl: '/', nonce: '', currency: '₹', isUser: false };
const restUrl = ( path ) => `${cfg.restUrl.replace( /\/$/, '' )}${path}`;

function initActiveNav() {
	if ( typeof window === 'undefined' ) return;
	const links = document.querySelectorAll( '.grovia-bottom-nav a, .storefront-mobile-nav__link' );
	const path  = window.location.pathname;

	links.forEach( ( link ) => {
		if ( link.getAttribute( 'href' ) === path ) {
			link.setAttribute( 'aria-current', 'page' );
		}
	} );
}

if ( typeof document !== 'undefined' ) {
	document.addEventListener( 'DOMContentLoaded', () => {
		initDeliveryChecker( cfg, restUrl );
		initBasketPulse( cfg, restUrl );
		initShoppingList( cfg, restUrl );
		initActiveNav();
	} );
}

export { restUrl, formatPrice, initDeliveryChecker, initBasketPulse, initShoppingList, initActiveNav };
