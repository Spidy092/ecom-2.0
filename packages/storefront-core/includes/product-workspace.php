<?php
/**
 * Product workspace block registration.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the internal product-workspace block and its client assets.
 */
function bhaivatech_storefront_register_product_workspace(): void {
	$model_handle = 'bhaivatech-storefront-product-workspace-model';
	$view_handle  = 'bhaivatech-storefront-product-workspace';

	wp_register_script(
		$model_handle,
		plugins_url( 'assets/js/product-workspace-model.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array(),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$view_handle,
		plugins_url( 'assets/js/product-workspace.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $model_handle ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$is_logged_in = is_user_logged_in();

	$config = array(
		'endpoints' => array(
			'products'   => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
			'cart'       => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
			'addItem'    => esc_url_raw( rest_url( 'wc/store/v1/cart/add-item' ) ),
			'updateItem' => esc_url_raw( rest_url( 'wc/store/v1/cart/update-item' ) ),
			'removeItem' => esc_url_raw( rest_url( 'wc/store/v1/cart/remove-item' ) ),
		),
		'saved'     => array(
			'loggedIn'        => $is_logged_in,
			'collection'      => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/saved-products' ) ),
			'productTemplate' => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/saved-products/__PRODUCT_ID__' ) ),
			'restNonce'       => $is_logged_in ? wp_create_nonce( 'wp_rest' ) : '',
		),
		'shopUrl'   => esc_url_raw( $shop_url ),
		'nonce'     => wp_create_nonce( 'wc_store_api' ),
		'messages'  => array(
			'requestFailed'    => __( 'Something went wrong. Try again.', 'bhaivatech-storefront-alpha' ),
			'cartUnavailable'  => __( 'Cart could not be loaded. Search is still available.', 'bhaivatech-storefront-alpha' ),
			'searching'        => __( 'Searching groceries…', 'bhaivatech-storefront-alpha' ),
			'keepTyping'       => __( 'Type at least 2 characters to search.', 'bhaivatech-storefront-alpha' ),
			'noResults'        => __( 'No exact matches.', 'bhaivatech-storefront-alpha' ),
			'noResultsFor'     => __( 'No products found for “%s”.', 'bhaivatech-storefront-alpha' ),
			'didYouMean'       => __( 'Did you mean “%s”?', 'bhaivatech-storefront-alpha' ),
			'searchSuggestion' => __( 'Search %s', 'bhaivatech-storefront-alpha' ),
			'browseProducts'   => __( 'Browse products', 'bhaivatech-storefront-alpha' ),
			'results'          => __( '%d products found.', 'bhaivatech-storefront-alpha' ),
			'oneItem'          => __( '1 item', 'bhaivatech-storefront-alpha' ),
			'manyItems'        => __( '%d items', 'bhaivatech-storefront-alpha' ),
			'outOfStock'       => __( 'Out of stock', 'bhaivatech-storefront-alpha' ),
			'unavailable'      => __( 'Unavailable', 'bhaivatech-storefront-alpha' ),
			'chooseOptions'    => __( 'Choose options', 'bhaivatech-storefront-alpha' ),
			'add'              => __( 'Add', 'bhaivatech-storefront-alpha' ),
			'quantityFor'      => __( 'Quantity for %s', 'bhaivatech-storefront-alpha' ),
			'decrease'         => __( 'Decrease %s quantity', 'bhaivatech-storefront-alpha' ),
			'increase'         => __( 'Increase %s quantity', 'bhaivatech-storefront-alpha' ),
			'added'            => __( 'Added to cart.', 'bhaivatech-storefront-alpha' ),
			'removed'          => __( 'Removed from cart.', 'bhaivatech-storefront-alpha' ),
			'cartUpdated'      => __( 'Cart updated.', 'bhaivatech-storefront-alpha' ),
		),
	);

	wp_add_inline_script(
		$view_handle,
		'window.BhaivaTechStorefrontConfig = ' . wp_json_encode( $config ) . ';',
		'before'
	);

	register_block_type( dirname( __DIR__ ) . '/blocks/product-workspace' );
}
