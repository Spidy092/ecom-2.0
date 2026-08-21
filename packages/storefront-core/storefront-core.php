<?php
/**
 * Plugin Name: BhaivaTech Storefront Core (Internal Alpha)
 * Description: Internal engineering-alpha core plugin for the grocery-first WooCommerce product. Not a public/final product name.
 * Version: 0.0.4-alpha
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Requires Plugins: woocommerce
 * Author: BhaivaTech
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_CORE_VERSION = '0.0.4-alpha';
const BHAIVATECH_STOREFRONT_CORE_FILE    = __FILE__;

require_once __DIR__ . '/includes/product-workspace.php';
require_once __DIR__ . '/includes/mobile-shopping-nav.php';
require_once __DIR__ . '/includes/saved-products.php';
require_once __DIR__ . '/includes/serviceability.php';
require_once __DIR__ . '/includes/buy-again.php';

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

	add_action( 'init', 'bhaivatech_storefront_register_product_workspace' );
	add_action( 'init', 'bhaivatech_storefront_register_mobile_shopping_nav' );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_saved_routes' );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_serviceability_route' );
	add_action( 'init', 'bhaivatech_storefront_register_buy_again_endpoint' );
	add_action( 'init', 'bhaivatech_storefront_register_buy_again_assets' );
	add_filter( 'woocommerce_get_query_vars', 'bhaivatech_storefront_buy_again_query_vars' );
	add_filter( 'woocommerce_account_menu_items', 'bhaivatech_storefront_buy_again_account_menu' );
	add_action( 'woocommerce_account_dashboard', 'bhaivatech_storefront_render_buy_again_dashboard_link', 20 );
	add_action( 'woocommerce_account_buy-again_endpoint', 'bhaivatech_storefront_render_buy_again_endpoint' );
	add_filter( 'the_content', 'bhaivatech_storefront_buy_again_account_content', 20 );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_buy_again_routes' );
	add_action( 'wp_enqueue_scripts', 'bhaivatech_storefront_enqueue_buy_again_assets' );

	/**
	 * Engineering-alpha extension point. Feature services are registered only
	 * through issue-scoped implementation work.
	 */
	do_action( 'bhaivatech_storefront_core_loaded' );
}
add_action( 'plugins_loaded', 'bhaivatech_storefront_core_bootstrap', 20 );

/**
 * Flush endpoint rules only when the plugin is activated/deactivated.
 */
function bhaivatech_storefront_core_activate(): void {
	bhaivatech_storefront_register_buy_again_endpoint();
	flush_rewrite_rules();
}

function bhaivatech_storefront_core_deactivate(): void {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'bhaivatech_storefront_core_activate' );
register_deactivation_hook( __FILE__, 'bhaivatech_storefront_core_deactivate' );
