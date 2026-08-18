<?php
/**
 * Mobile shopping navigation block registration.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the mobile shopping navigation block and its small behavior layer.
 */
function bhaivatech_storefront_register_mobile_shopping_nav(): void {
	$view_handle = 'bhaivatech-storefront-mobile-shopping-nav';

	wp_register_script(
		$view_handle,
		plugins_url( 'assets/js/mobile-shopping-nav.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array(),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	register_block_type( dirname( __DIR__ ) . '/blocks/mobile-shopping-nav' );
}
