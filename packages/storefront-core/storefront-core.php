<?php
/**
 * Plugin Name: BhaivaTech Storefront Core (Internal Alpha)
 * Description: Internal engineering-alpha core plugin for the grocery-first WooCommerce product.
 *              Delivery checker, Shopping List, and future grocery-specific services.
 *              Not a public/final product name.
 * Version: 0.0.1-alpha
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Requires Plugins: woocommerce
 * Author: BhaivaTech
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_CORE_VERSION = '0.0.2-alpha';
const BHAIVATECH_STOREFRONT_CORE_FILE    = __FILE__;

// Legacy-compatible storefront modules remain presentation/application seams
// while the newer Delivery and Shopping List services use PSR-4 classes.
require_once __DIR__ . '/includes/product-workspace.php';
require_once __DIR__ . '/includes/mobile-shopping-nav.php';
require_once __DIR__ . '/includes/saved-products.php';
require_once __DIR__ . '/includes/serviceability.php';
require_once __DIR__ . '/includes/buy-again.php';

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'StorefrontCore\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$file     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

// ---------------------------------------------------------------------------
// Autoloader
// ---------------------------------------------------------------------------
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// ---------------------------------------------------------------------------
// Activation / Deactivation / Uninstall
// ---------------------------------------------------------------------------

register_activation_hook(
	__FILE__,
	static function (): void {
		( new StorefrontCore\ShoppingList\Installer() )->activate();
		StorefrontCore\Delivery\AdminNotice::on_activate();
		bhaivatech_storefront_register_buy_again_endpoint();
		flush_rewrite_rules();
	}
);

register_uninstall_hook( __FILE__, 'storefront_core_uninstall' );

register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);

function storefront_core_uninstall(): void {
	StorefrontCore\ShoppingList\Installer::uninstall();
}

/**
 * Enqueue the quantity-rail view module on customer-facing browse surfaces.
 *
 * WordPress normally discovers a block's viewScriptModule from block.json.
 * Product Collection can render the pattern in a context where that
 * discovery is deferred, so keep the small interaction layer available when
 * the rail is present. Cart and checkout remain owned by WooCommerce.
 *
 * @return void
 */
function storefront_core_enqueue_product_quick_add_assets(): void {
	if ( is_admin() || is_cart() || is_checkout() || ! function_exists( 'wp_enqueue_script_module' ) ) {
		return;
	}

	wp_enqueue_script_module(
		'storefront-core-product-quick-add',
		plugins_url( 'blocks/product-quick-add/view.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		[],
		BHAIVATECH_STOREFRONT_CORE_VERSION
	);
}

// ---------------------------------------------------------------------------
// GDPR Erasure
// ---------------------------------------------------------------------------

add_filter(
	'wp_privacy_personal_data_erasers',
	static function ( array $erasers ): array {
		$erasers['storefront-shopping-list'] = [
			'eraser_friendly_name' => __( 'Storefront Shopping List', 'bhaivatech-storefront-alpha' ),
			'callback'             => [ StorefrontCore\ShoppingList\Installer::class, 'gdpr_erase' ],
		];
		return $erasers;
	}
);

// ---------------------------------------------------------------------------
// Missing WooCommerce notice
// ---------------------------------------------------------------------------

function storefront_core_missing_woocommerce_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'BhaivaTech Storefront Core requires WooCommerce to be active.', 'bhaivatech-storefront-alpha' );
	echo '</p></div>';
}

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

function storefront_core_bootstrap(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'storefront_core_missing_woocommerce_notice' );
		return;
	}

	// Delivery services.
	$normalizer = new StorefrontCore\Delivery\PostcodeNormalizer();
	$checker    = new StorefrontCore\Delivery\ServiceabilityChecker();
	$handler    = new StorefrontCore\Delivery\RestHandler( $normalizer, $checker );
	$notice     = new StorefrontCore\Delivery\AdminNotice();

	add_action( 'rest_api_init', [ $handler, 'register' ] );
	$notice->register();

	// Existing grocery interaction modules.
	add_action( 'init', 'bhaivatech_storefront_register_product_workspace', 15 );
	add_action( 'init', 'bhaivatech_storefront_register_mobile_shopping_nav', 15 );
	add_action( 'init', 'bhaivatech_storefront_register_buy_again_endpoint', 15 );
	add_action( 'init', 'bhaivatech_storefront_register_buy_again_assets', 15 );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_saved_routes' );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_serviceability_route' );
	add_action( 'rest_api_init', 'bhaivatech_storefront_register_buy_again_routes' );
	add_action( 'wp_enqueue_scripts', 'bhaivatech_storefront_enqueue_buy_again_assets', 20 );
	add_action( 'wp_enqueue_scripts', 'storefront_core_enqueue_product_quick_add_assets', 25 );
	add_filter( 'woocommerce_get_query_vars', 'bhaivatech_storefront_buy_again_query_vars' );
	add_filter( 'woocommerce_account_menu_items', 'bhaivatech_storefront_buy_again_account_menu' );
	add_action( 'woocommerce_account_dashboard', 'bhaivatech_storefront_render_buy_again_dashboard_link', 20 );
	add_action( 'woocommerce_account_buy-again_endpoint', 'bhaivatech_storefront_render_buy_again_endpoint' );
	add_filter( 'the_content', 'bhaivatech_storefront_buy_again_account_content', 20 );

	// Shopping List services.
	$repository = new StorefrontCore\ShoppingList\ListRepository();
	$controller = new StorefrontCore\ShoppingList\RestController( $repository );

	add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

	require_once __DIR__ . '/includes/class-buy-again-service.php';
	require_once __DIR__ . '/includes/class-setup-wizard.php';
	require_once __DIR__ . '/includes/class-storefront-frontend.php';
	require_once __DIR__ . '/includes/class-system-status.php';
	( new BhaivaTech\Storefront\Storefront_Frontend( new BhaivaTech\Storefront\Buy_Again_Service() ) )->register();
	( new BhaivaTech\Storefront\Setup_Wizard() )->register();
	( new BhaivaTech\Storefront\System_Status() )->register();

	/**
	 * Register Custom Blocks (apiVersion 3 + Interactivity API)
	 */
	add_action(
		'init',
		static function (): void {
			if ( function_exists( 'register_block_type_from_metadata' ) ) {
				register_block_type_from_metadata( __DIR__ . '/blocks/delivery-checker' );
				register_block_type_from_metadata( __DIR__ . '/blocks/product-quick-add' );
			}
		}
	);

	/**
	 * Core loaded — future services (Buy Again, Setup) hook in here.
	 */
	do_action( 'storefront_core_loaded' );
}
add_action( 'plugins_loaded', 'storefront_core_bootstrap', 20 );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	StorefrontCore\Demo\Command::register();
}
