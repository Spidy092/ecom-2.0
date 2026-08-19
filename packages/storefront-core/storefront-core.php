<?php
/**
 * Plugin Name: BhaivaTech Storefront Core (Internal Alpha)
 * Description: Internal engineering-alpha core plugin for the grocery-first WooCommerce product. Not a public/final product name.
 * Version: 0.0.7-alpha
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Requires Plugins: woocommerce
 * Author: BhaivaTech
 * License: GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_CORE_VERSION = '0.0.7-alpha';
const BHAIVATECH_STOREFRONT_CORE_FILE    = __FILE__;

require_once __DIR__ . '/includes/product-workspace.php';
require_once __DIR__ . '/includes/mobile-shopping-nav.php';
require_once __DIR__ . '/includes/saved-products.php';
require_once __DIR__ . '/includes/serviceability.php';
require_once __DIR__ . '/includes/starter-import-transaction.php';
require_once __DIR__ . '/includes/starter-preflight.php';
require_once __DIR__ . '/includes/setup-status.php';
require_once __DIR__ . '/includes/buyer-onboarding.php';

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
	add_action( 'admin_menu', 'bhaivatech_storefront_register_setup_status_page' );
	add_action( 'admin_menu', 'bhaivatech_storefront_register_buyer_onboarding_page' );
	add_action( 'admin_post_bhaivatech_storefront_export_status', 'bhaivatech_storefront_export_system_status' );

	/**
	 * Engineering-alpha extension point. Feature services are registered only
	 * through issue-scoped implementation work.
	 */
	do_action( 'bhaivatech_storefront_core_loaded' );
}
add_action( 'plugins_loaded', 'bhaivatech_storefront_core_bootstrap', 20 );
