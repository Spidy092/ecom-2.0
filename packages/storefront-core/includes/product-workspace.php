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
	$model_handle          = 'bhaivatech-storefront-product-workspace-model';
	$view_handle           = 'bhaivatech-storefront-product-workspace';
	$filter_model_handle   = 'bhaivatech-storefront-product-filters-model';
	$filter_view_handle    = 'bhaivatech-storefront-product-filters';
	$saved_model_handle    = 'bhaivatech-storefront-saved-products-model';
	$saved_view_handle     = 'bhaivatech-storefront-saved-products';
	$delivery_view_handle  = 'bhaivatech-storefront-delivery-serviceability';
	$browse_view_handle    = 'bhaivatech-storefront-department-browse';

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
		array( $model_handle, 'bhaivatech-storefront-buy-again-model' ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$filter_model_handle,
		plugins_url( 'assets/js/product-filters-model.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array(),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$filter_view_handle,
		plugins_url( 'assets/js/product-filters.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $view_handle, $filter_model_handle ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$saved_model_handle,
		plugins_url( 'assets/js/saved-products-model.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array(),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$saved_view_handle,
		plugins_url( 'assets/js/saved-products.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $view_handle, $saved_model_handle ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$delivery_view_handle,
		plugins_url( 'assets/js/delivery-serviceability.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $view_handle ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		$browse_view_handle,
		plugins_url( 'assets/js/department-browse.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $view_handle, $model_handle, $filter_view_handle ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	$shop_url              = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$is_logged_in          = is_user_logged_in();
	$serviceability_config = bhaivatech_storefront_serviceability_public_config();

	$config = array(
		'endpoints'      => array(
			'products'               => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
			'collectionData'         => esc_url_raw( rest_url( 'wc/store/v1/products/collection-data' ) ),
			'attributes'             => esc_url_raw( rest_url( 'wc/store/v1/products/attributes' ) ),
			'attributeTermsTemplate' => esc_url_raw( rest_url( 'wc/store/v1/products/attributes/__ATTRIBUTE_ID__/terms' ) ),
			'categories'             => esc_url_raw( rest_url( 'wc/store/v1/products/categories' ) ),
			'cart'                   => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
			'addItem'                => esc_url_raw( rest_url( 'wc/store/v1/cart/add-item' ) ),
			'updateItem'             => esc_url_raw( rest_url( 'wc/store/v1/cart/update-item' ) ),
			'removeItem'             => esc_url_raw( rest_url( 'wc/store/v1/cart/remove-item' ) ),
			'serviceability'         => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/serviceability' ) ),
		),
		'serviceability' => $serviceability_config,
		'saved'          => array(
			'loggedIn'        => $is_logged_in,
			'accountMax'      => BHAIVATECH_STOREFRONT_SAVED_MAX,
			'collection'      => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/saved-products' ) ),
			'productTemplate' => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/saved-products/__PRODUCT_ID__' ) ),
			'restNonce'       => $is_logged_in ? wp_create_nonce( 'wp_rest' ) : '',
		),
		'shopUrl'        => esc_url_raw( $shop_url ),
		'buyAgain'       => $is_logged_in ? esc_url_raw( rest_url( 'bhaivatech-storefront/v1/buy-again' ) ) : '',
		'buyAgainNonce'  => $is_logged_in ? wp_create_nonce( 'wp_rest' ) : '',
		'nonce'          => wp_create_nonce( 'wc_store_api' ),
		'messages'       => array(
			'requestFailed'                => __( 'Something went wrong. Try again.', 'bhaivatech-storefront-alpha' ),
			'cartUnavailable'              => __( 'Cart could not be loaded. Search is still available.', 'bhaivatech-storefront-alpha' ),
			'searching'                    => __( 'Searching groceries…', 'bhaivatech-storefront-alpha' ),
			'keepTyping'                   => __( 'Type at least 2 characters to search.', 'bhaivatech-storefront-alpha' ),
			'noResults'                    => __( 'No exact matches.', 'bhaivatech-storefront-alpha' ),
			'noResultsFor'                 => __( 'No products found for “%s”.', 'bhaivatech-storefront-alpha' ),
			'showAllResults'               => __( 'See all results for “%s”', 'bhaivatech-storefront-alpha' ),
			'didYouMean'                   => __( 'Did you mean “%s”?', 'bhaivatech-storefront-alpha' ),
			'searchSuggestion'             => __( 'Search %s', 'bhaivatech-storefront-alpha' ),
			'browseProducts'               => __( 'Browse products', 'bhaivatech-storefront-alpha' ),
			'browseLoadingDepartments'     => __( 'Loading departments…', 'bhaivatech-storefront-alpha' ),
			'browseChooseDepartment'       => __( 'Choose a department.', 'bhaivatech-storefront-alpha' ),
			'browseNoDepartments'          => __( 'No grocery departments are available yet.', 'bhaivatech-storefront-alpha' ),
			'browseDepartmentsUnavailable' => __( 'Departments could not be loaded. Browse the full shop instead.', 'bhaivatech-storefront-alpha' ),
			'browseLoadingProducts'        => __( 'Loading %s…', 'bhaivatech-storefront-alpha' ),
			'browseOneProductFound'        => __( '1 product in %s.', 'bhaivatech-storefront-alpha' ),
			'browseProductsFound'          => __( '%d products in %s.', 'bhaivatech-storefront-alpha' ),
			'browseEmptyDepartment'        => __( 'No products are available in %s right now.', 'bhaivatech-storefront-alpha' ),
			'browseProductsUnavailable'    => __( 'Products in %s could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
			'browseDepartments'            => __( 'Departments', 'bhaivatech-storefront-alpha' ),
			'filtersToggle'                => __( 'Filters', 'bhaivatech-storefront-alpha' ),
			'filtersToggleActive'          => __( 'Filters, %d active', 'bhaivatech-storefront-alpha' ),
			'filtersChooseContext'         => __( 'Search or choose a department to use filters.', 'bhaivatech-storefront-alpha' ),
			'filtersWaitingForResults'     => __( 'Waiting for search results…', 'bhaivatech-storefront-alpha' ),
			'filtersLoading'               => __( 'Loading filters…', 'bhaivatech-storefront-alpha' ),
			'filtersReady'                 => __( 'Filters ready.', 'bhaivatech-storefront-alpha' ),
			'filtersNoAdditional'          => __( 'Availability is the only filter for this selection.', 'bhaivatech-storefront-alpha' ),
			'filtersUnavailable'           => __( 'Filters could not be loaded. Shopping is still available.', 'bhaivatech-storefront-alpha' ),
			'filtersInStock'               => __( 'In stock only', 'bhaivatech-storefront-alpha' ),
			'filtersPrice'                 => __( 'Price', 'bhaivatech-storefront-alpha' ),
			'filtersMinPrice'              => __( 'Minimum price', 'bhaivatech-storefront-alpha' ),
			'filtersMaxPrice'              => __( 'Maximum price', 'bhaivatech-storefront-alpha' ),
			'filtersApply'                 => __( 'Apply filters', 'bhaivatech-storefront-alpha' ),
			'filtersClear'                 => __( 'Clear filters', 'bhaivatech-storefront-alpha' ),
			'filtersApplying'              => __( 'Applying filters…', 'bhaivatech-storefront-alpha' ),
			'filtersClearing'              => __( 'Clearing filters…', 'bhaivatech-storefront-alpha' ),
			'filtersProductsShown'         => __( 'Showing %d filtered products.', 'bhaivatech-storefront-alpha' ),
			'filtersCleared'               => __( 'Filters cleared. Showing %d products.', 'bhaivatech-storefront-alpha' ),
			'filtersNoResults'             => __( 'No products match these filters.', 'bhaivatech-storefront-alpha' ),
			'filtersApplyFailed'           => __( 'Filters could not be applied. Try again.', 'bhaivatech-storefront-alpha' ),
			'results'                      => __( '%d products found.', 'bhaivatech-storefront-alpha' ),
			'oneItem'                      => __( '1 item', 'bhaivatech-storefront-alpha' ),
			'manyItems'                    => __( '%d items', 'bhaivatech-storefront-alpha' ),
			'outOfStock'                   => __( 'Out of stock', 'bhaivatech-storefront-alpha' ),
			'unavailable'                  => __( 'Unavailable', 'bhaivatech-storefront-alpha' ),
			'chooseOptions'                => __( 'Choose options', 'bhaivatech-storefront-alpha' ),
			'add'                          => __( 'Add', 'bhaivatech-storefront-alpha' ),
			'addToCart'                    => __( 'Add to cart', 'bhaivatech-storefront-alpha' ),
			'boughtBefore'                => __( 'Bought before · %d last time', 'bhaivatech-storefront-alpha' ),
			'addAgainQuantity'             => __( 'Add %d again', 'bhaivatech-storefront-alpha' ),
			'quantityFor'                  => __( 'Quantity for %s', 'bhaivatech-storefront-alpha' ),
			'decrease'                     => __( 'Decrease %s quantity', 'bhaivatech-storefront-alpha' ),
			'increase'                     => __( 'Increase %s quantity', 'bhaivatech-storefront-alpha' ),
			'added'                        => __( 'Added to cart.', 'bhaivatech-storefront-alpha' ),
			'removed'                      => __( 'Removed from cart.', 'bhaivatech-storefront-alpha' ),
			'cartUpdated'                  => __( 'Cart updated.', 'bhaivatech-storefront-alpha' ),
			'saveForLater'                 => __( 'Save for later', 'bhaivatech-storefront-alpha' ),
			'saved'                        => __( 'Saved', 'bhaivatech-storefront-alpha' ),
			'saveProduct'                  => __( 'Save %s for later', 'bhaivatech-storefront-alpha' ),
			'removeFromSaved'              => __( 'Remove from Saved', 'bhaivatech-storefront-alpha' ),
			'removeSavedProduct'           => __( 'Remove %s from Saved', 'bhaivatech-storefront-alpha' ),
			'savedAdded'                   => __( 'Saved for later.', 'bhaivatech-storefront-alpha' ),
			'savedRemoved'                 => __( 'Removed from Saved.', 'bhaivatech-storefront-alpha' ),
			'savedLoading'                 => __( 'Loading Saved products…', 'bhaivatech-storefront-alpha' ),
			'savedEmpty'                   => __( 'No products saved yet.', 'bhaivatech-storefront-alpha' ),
			'savedUnavailable'             => __( 'Saved could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
			'savedUnavailableCount'        => __( '%d saved products are currently unavailable.', 'bhaivatech-storefront-alpha' ),
			'savedSessionOnly'             => __( 'Saved for this session only because browser storage is unavailable.', 'bhaivatech-storefront-alpha' ),
			'savedGuestLimit'              => __( 'You can save up to 50 products on this browser.', 'bhaivatech-storefront-alpha' ),
			'savedAccountScope'            => __( 'Saved to your account.', 'bhaivatech-storefront-alpha' ),
			'savedBrowserScope'            => __( 'Saved on this browser.', 'bhaivatech-storefront-alpha' ),
			'deliveryChecking'             => __( 'Checking delivery area…', 'bhaivatech-storefront-alpha' ),
			'deliveryServed'               => __( 'We serve this area.', 'bhaivatech-storefront-alpha' ),
			'deliveryServedDetail'         => __( 'Shipping options are confirmed at checkout.', 'bhaivatech-storefront-alpha' ),
			'deliveryNotServed'            => __( 'We do not currently serve this area.', 'bhaivatech-storefront-alpha' ),
			'deliveryNeedCountry'          => __( 'Choose a country or region to check delivery.', 'bhaivatech-storefront-alpha' ),
			'deliveryNeedState'            => __( 'Choose a state or region to check this area.', 'bhaivatech-storefront-alpha' ),
			'deliveryNeedPostcode'         => __( 'Enter a postcode to check delivery.', 'bhaivatech-storefront-alpha' ),
			'deliveryUnknown'              => __( 'We could not check this area right now. Try again.', 'bhaivatech-storefront-alpha' ),
			'deliveryCheck'                => __( 'Check area', 'bhaivatech-storefront-alpha' ),
			'deliveryChooseState'          => __( 'Choose state or region', 'bhaivatech-storefront-alpha' ),
		),
	);

	wp_add_inline_script(
		$view_handle,
		'window.BhaivaTechStorefrontConfig = ' . wp_json_encode( $config ) . ';',
		'before'
	);

	register_block_type( dirname( __DIR__ ) . '/blocks/product-workspace' );
}
