<?php
/**
 * Plugin Name: Grovia Core Prototype
 * Plugin URI: https://github.com/Spidy092/ecom-2.0
 * Description: Grocery-specific functionality foundation for the ecom-2.0 V1 prototype.
 * Version: 0.2.0
 * Requires Plugins: woocommerce
 * Text Domain: grovia-core
 *
 * @package GroviaCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GROVIA_CORE_VERSION' ) ) {
	define( 'GROVIA_CORE_VERSION', '0.2.0' );
}

/**
 * Load translations after plugins are available.
 */
function grovia_core_load_textdomain() {
	load_plugin_textdomain( 'grovia-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'grovia_core_load_textdomain' );

/**
 * Show a clear admin notice if WooCommerce is unavailable.
 *
 * The prototype deliberately does not register fallback commerce behavior.
 */
function grovia_core_missing_woocommerce_notice() {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php echo esc_html__( 'Grovia Core requires WooCommerce. Install and activate WooCommerce before using Grovia storefront functionality.', 'grovia-core' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'grovia_core_missing_woocommerce_notice' );

/**
 * Load the progressive cart interaction only on product-discovery surfaces.
 *
 * WooCommerce remains authoritative for cart state. The client receives a
 * Store API nonce and reconciles from the full cart response after every
 * mutation instead of maintaining an independent basket model.
 */
function grovia_core_enqueue_cart_ux() {
	if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$should_load = is_front_page() || is_shop() || is_product_taxonomy() || is_search();

	if ( ! $should_load ) {
		return;
	}

	wp_enqueue_script(
		'grovia-cart-ux',
		plugins_url( 'assets/js/cart-ux.js', __FILE__ ),
		array(),
		GROVIA_CORE_VERSION,
		true
	);

	wp_localize_script(
		'grovia-cart-ux',
		'GroviaCartUx',
		array(
			'cartEndpoint' => untrailingslashit( rest_url( 'wc/store/v1/cart' ) ),
			'nonce'        => wp_create_nonce( 'wc_store_api' ),
			'cartUrl'      => wc_get_cart_url(),
			'strings'      => array(
				'add'            => __( 'Add', 'grovia-core' ),
				'added'          => __( 'Added', 'grovia-core' ),
				'updated'        => __( 'Updated', 'grovia-core' ),
				'removed'        => __( 'Removed', 'grovia-core' ),
				'viewBasket'     => __( 'View basket', 'grovia-core' ),
				'item'           => __( 'item', 'grovia-core' ),
				'items'          => __( 'items', 'grovia-core' ),
				'increase'       => __( 'Increase quantity for', 'grovia-core' ),
				'decrease'       => __( 'Decrease quantity for', 'grovia-core' ),
				'genericError'   => __( 'Basket update failed. Please try again.', 'grovia-core' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'grovia_core_enqueue_cart_ux', 20 );
