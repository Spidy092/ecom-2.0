import test from 'node:test';
import assert from 'node:assert/strict';

// Mock minimal browser globals for Node test environment
global.window = {
	location: { pathname: '/shop' },
	storefrontConfig: {
		restUrl: 'https://example.com/wp-json/',
		nonce: 'test_nonce_123',
		currency: '₹',
		isUser: true,
	},
};

const { restUrl } = await import( '../assets/js/storefront-interactions.js' );

test( 'restUrl formats API endpoint URLs correctly', () => {
	const url = restUrl( '/storefront-core/v1/delivery/check' );
	assert.equal( url, 'https://example.com/wp-json/storefront-core/v1/delivery/check' );
} );

test( 'restUrl strips trailing slash from base URL to avoid double slash', () => {
	global.window.storefrontConfig.restUrl = 'https://example.com/wp-json/';
	const url = restUrl( '/wc/store/v1/cart' );
	assert.equal( url, 'https://example.com/wp-json/wc/store/v1/cart' );
} );
