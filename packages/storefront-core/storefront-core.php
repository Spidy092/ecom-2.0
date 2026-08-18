<?php
/**
 * Plugin Name: BhaivaTech Storefront Core (Internal Alpha)
 * Description: Internal engineering-alpha core plugin for the grocery-first WooCommerce product. Not a public/final product name.
 * Version: 0.0.1-alpha
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Requires Plugins: woocommerce
 * Author: BhaivaTech
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_CORE_VERSION = '0.0.1-alpha';

/**
 * Show a clear admin notice when WooCommerce is not active.
 */
function bhaivatech_storefront_core_missing_woocommerce_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'BhaivaTech Storefront Core (Internal Alpha) requires WooCommerce to be active.', 'bhaivatech-storefront-alpha' );
	echo '</p></div>';
}

/**
 * Bootstrap only after the WooCommerce plugin is available.
 */
function bhaivatech_storefront_core_bootstrap(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'bhaivatech_storefront_core_missing_woocommerce_notice' );
		return;
	}

	/**
	 * Engineering-alpha extension point. Feature services are intentionally not
	 * registered until their issue-scoped implementation work begins.
	 */
	do_action( 'bhaivatech_storefront_core_loaded' );
}
add_action( 'plugins_loaded', 'bhaivatech_storefront_core_bootstrap', 20 );
