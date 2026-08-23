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

const BHAIVATECH_STOREFRONT_CORE_VERSION = '0.0.1-alpha';

spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'StorefrontCore\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
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
	}
);

register_uninstall_hook( __FILE__, 'storefront_core_uninstall' );

function storefront_core_uninstall(): void {
	StorefrontCore\ShoppingList\Installer::uninstall();
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

	// Shopping List services.
	$repository = new StorefrontCore\ShoppingList\ListRepository();
	$controller = new StorefrontCore\ShoppingList\RestController( $repository );

	add_action( 'rest_api_init', [ $controller, 'register_routes' ] );

	require_once __DIR__ . '/includes/class-buy-again-service.php';
	require_once __DIR__ . '/includes/class-setup-wizard.php';
	require_once __DIR__ . '/includes/class-storefront-frontend.php';
	( new BhaivaTech\Storefront\Storefront_Frontend( new BhaivaTech\Storefront\Buy_Again_Service() ) )->register();
	( new BhaivaTech\Storefront\Setup_Wizard() )->register();

	/**
	 * Register Custom Blocks (apiVersion 3 + Interactivity API)
	 */
	add_action( 'init', static function (): void {
		if ( function_exists( 'register_block_type_from_metadata' ) ) {
			register_block_type_from_metadata( __DIR__ . '/blocks/delivery-checker' );
			register_block_type_from_metadata( __DIR__ . '/blocks/product-quick-add' );
		}
	} );

	/**
	 * Core loaded — future services (Buy Again, Setup) hook in here.
	 */
	do_action( 'storefront_core_loaded' );
}
add_action( 'plugins_loaded', 'storefront_core_bootstrap', 20 );
