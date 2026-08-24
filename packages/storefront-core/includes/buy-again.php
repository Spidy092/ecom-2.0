<?php
/**
 * HPOS-safe Buy Again account experience and private API.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ORDERS = 20;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS  = 50;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_LINES  = 250;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_QTY    = 100;

/** Register the supported WooCommerce My Account endpoint. */
function bhaivatech_storefront_register_buy_again_endpoint(): void {
	add_rewrite_endpoint( 'buy-again', EP_ROOT | EP_PAGES );
}

/**
 * Register the endpoint with WooCommerce's account query map.
 *
 * @param array<string, string> $vars Existing query variables.
 * @return array<string, string>
 */
function bhaivatech_storefront_buy_again_query_vars( array $vars ): array {
	$vars['buy-again'] = 'buy-again';
	return $vars;
}

/**
 * Add Buy Again to the account navigation.
 *
 * @param array<string, string> $items Existing account links.
 * @return array<string, string>
 */
function bhaivatech_storefront_buy_again_account_menu( array $items ): array {
	$updated = array();

	foreach ( $items as $key => $label ) {
		$updated[ $key ] = $label;
		if ( 'orders' === $key ) {
			$updated['buy-again'] = __( 'Buy Again', 'bhaivatech-storefront-alpha' );
		}
	}

	if ( ! isset( $updated['buy-again'] ) ) {
		$updated['buy-again'] = __( 'Buy Again', 'bhaivatech-storefront-alpha' );
	}

	return $updated;
}

/**
 * Require the current authenticated customer for Buy Again history.
 *
 * @return true|WP_Error
 */
function bhaivatech_storefront_buy_again_permission() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'bhaivatech_buy_again_auth_required',
			__( 'Sign in to use Buy Again.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return true;
}

/**
 * Bound a remembered quantity before it reaches the browser.
 *
 * @param mixed $quantity Candidate quantity.
 * @return int
 */
function bhaivatech_storefront_buy_again_quantity( $quantity ): int {
	return max( 1, min( BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_QTY, absint( $quantity ) ) );
}

/**
 * Read recent eligible order line items for the current customer.
 *
 * Identity is derived exclusively from get_current_user_id(). Order and
 * customer identifiers supplied by a browser are intentionally ignored.
 * WooCommerce CRUD/query APIs are used so this remains HPOS-compatible.
 *
 * @return WP_REST_Response
 */
function bhaivatech_storefront_buy_again_get(): WP_REST_Response {
	$orders = wc_get_orders(
		array(
			'customer_id' => get_current_user_id(),
			'status'      => array( 'processing', 'completed' ),
			'limit'       => BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ORDERS,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'return'      => 'objects',
		)
	);

	$line_items = array();
	$skipped    = 0;
	$line_count = 0;

	foreach ( is_array( $orders ) ? $orders : array() as $order ) {
		if ( ! is_object( $order ) || ! is_callable( array( $order, 'get_items' ) ) ) {
			continue;
		}

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$line_count++;
			if ( $line_count > BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_LINES ) {
				$skipped++;
				continue;
			}

			$product_id = is_callable( array( $item, 'get_product_id' ) ) ? absint( $item->get_product_id() ) : 0;
			$quantity   = is_callable( array( $item, 'get_quantity' ) ) ? absint( $item->get_quantity() ) : 0;

			if ( ! $product_id || ! $quantity ) {
				$skipped++;
				continue;
			}

			$order_id = is_callable( array( $order, 'get_id' ) ) ? absint( $order->get_id() ) : 0;
			if ( ! isset( $line_items[ $product_id ] ) ) {
				if ( count( $line_items ) >= BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS ) {
					$skipped++;
					continue;
				}

				$line_items[ $product_id ] = array(
					'quantity' => bhaivatech_storefront_buy_again_quantity( $quantity ),
					'order_id' => $order_id,
				);
				continue;
			}

			// Multiple lines for the same product in the newest order are one
			// remembered quantity. Older orders never replace the newest one.
			if ( (int) $line_items[ $product_id ]['order_id'] === $order_id ) {
				$line_items[ $product_id ]['quantity'] = bhaivatech_storefront_buy_again_quantity(
					(int) $line_items[ $product_id ]['quantity'] + $quantity
				);
			}
		}
	}

	$ids = array_keys( $line_items );
	if ( empty( $ids ) ) {
		$response = new WP_REST_Response(
			array(
				'items'         => array(),
				'skipped_count' => $skipped,
			)
		);
		$response->header( 'Cache-Control', 'no-store' );
		return $response;
	}

	$products = wc_get_products(
		array(
			'include' => $ids,
			'limit'   => count( $ids ),
			'status'  => 'publish',
			'return'  => 'objects',
		)
	);
	$product_map = array();
	foreach ( is_array( $products ) ? $products : array() as $product ) {
		if ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) {
			$product_map[ absint( $product->get_id() ) ] = $product;
		}
	}

	$items = array();
	foreach ( $line_items as $product_id => $line ) {
		$product = $product_map[ $product_id ] ?? null;
		if ( ! is_object( $product ) || ! is_callable( array( $product, 'is_visible' ) ) || ! $product->is_visible() ) {
			$skipped++;
			continue;
		}

		$items[] = array(
			'product_id'        => absint( $product_id ),
			'purchased_quantity' => (int) $line['quantity'],
		);
	}

	$response = new WP_REST_Response(
		array(
			'items'         => $items,
			'skipped_count' => $skipped,
		)
	);
	$response->header( 'Cache-Control', 'no-store' );
	return $response;
}

/** Register the private Buy Again route under the canonical and legacy namespaces. */
function bhaivatech_storefront_register_buy_again_routes(): void {
	$route = array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'bhaivatech_storefront_buy_again_get',
		'permission_callback' => 'bhaivatech_storefront_buy_again_permission',
		'args'                => array(),
	);

	register_rest_route( 'bhaivatech-storefront/v1', '/buy-again', $route );
	register_rest_route( 'storefront-core/v1', '/buy-again', $route );
}

/** Register the account-only scripts and their configuration. */
function bhaivatech_storefront_register_buy_again_assets(): void {
	$model_handle = 'bhaivatech-storefront-product-workspace-model';
	if ( ! wp_script_is( $model_handle, 'registered' ) ) {
		wp_register_script(
			$model_handle,
			plugins_url( 'assets/js/product-workspace-model.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
			array(),
			BHAIVATECH_STOREFRONT_CORE_VERSION,
			true
		);
	}

	wp_register_script(
		'bhaivatech-storefront-buy-again-model',
		plugins_url( 'assets/js/buy-again-model.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array(),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);

	wp_register_script(
		'bhaivatech-storefront-buy-again',
		plugins_url( 'assets/js/buy-again.js', BHAIVATECH_STOREFRONT_CORE_FILE ),
		array( $model_handle, 'bhaivatech-storefront-buy-again-model' ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);
}

/** Load Buy Again only for its account endpoint. */
function bhaivatech_storefront_enqueue_buy_again_assets(): void {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_wc_endpoint_url( 'buy-again' ) ) {
		return;
	}

	wp_enqueue_script( 'bhaivatech-storefront-buy-again' );
	wp_add_inline_script(
		'bhaivatech-storefront-buy-again',
		'window.BhaivaTechBuyAgainConfig = ' . wp_json_encode(
			array(
				'products' => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
				'buyAgain' => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/buy-again' ) ),
				'addItem' => esc_url_raw( rest_url( 'wc/store/v1/cart/add-item' ) ),
				'cart' => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
				'nonce' => wp_create_nonce( 'wc_store_api' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'messages' => array(
					'loading' => __( 'Loading recent purchases…', 'bhaivatech-storefront-alpha' ),
					'empty' => __( 'Buy Again will appear after you have an eligible order.', 'bhaivatech-storefront-alpha' ),
					'unavailable' => __( 'Buy Again could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
					'productsError' => __( 'Recent products could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
					'retry' => __( 'Try again', 'bhaivatech-storefront-alpha' ),
					'skipped' => __( '%d recent products are no longer available.', 'bhaivatech-storefront-alpha' ),
					'boughtQuantity' => __( 'Bought %d last time', 'bhaivatech-storefront-alpha' ),
					'addAgain' => __( 'Add again', 'bhaivatech-storefront-alpha' ),
					'adding' => __( 'Adding %s…', 'bhaivatech-storefront-alpha' ),
					'added' => __( 'Added %d × %s to your cart.', 'bhaivatech-storefront-alpha' ),
					'addFailed' => __( '%s could not be added. Try again.', 'bhaivatech-storefront-alpha' ),
					'chooseOptions' => __( 'Choose options', 'bhaivatech-storefront-alpha' ),
					'outOfStock' => __( 'Out of stock', 'bhaivatech-storefront-alpha' ),
					'unavailableProduct' => __( 'Unavailable', 'bhaivatech-storefront-alpha' ),
				),
			)
		) . ';',
		'before'
	);
}

/** Render the dedicated Buy Again account surface. */
function bhaivatech_storefront_render_buy_again_endpoint(): void {
	$heading_id = wp_unique_id( 'bt-buy-again-heading-' );
	?>
	<section class="bt-buy-again" data-bt-buy-again aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="bt-buy-again__heading">
			<p class="bt-buy-again__eyebrow"><?php esc_html_e( 'Repeat shopping', 'bhaivatech-storefront-alpha' ); ?></p>
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Buy Again', 'bhaivatech-storefront-alpha' ); ?></h2>
			<p><?php esc_html_e( 'Bring familiar grocery staples back to your cart without starting over.', 'bhaivatech-storefront-alpha' ); ?></p>
		</div>
		<p class="bt-buy-again__status" data-bt-buy-again-status role="status" aria-live="polite" aria-atomic="true"></p>
		<div class="bt-buy-again__results" data-bt-buy-again-results></div>
		<p class="bt-buy-again__cart-link"><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'bhaivatech-storefront-alpha' ); ?></a></p>
	</section>
	<?php
}

/** Add a dashboard fallback link for customized account templates. */
function bhaivatech_storefront_render_buy_again_dashboard_link(): void {
	if ( is_user_logged_in() ) {
		echo bhaivatech_storefront_buy_again_dashboard_link_markup();
	}
}

/**
 * Build a dashboard fallback link.
 *
 * @return string
 */
function bhaivatech_storefront_buy_again_dashboard_link_markup(): string {
	return sprintf(
		'<p class="bt-buy-again__dashboard-link" data-bt-buy-again-link><a href="%s">%s</a></p>',
		esc_url( wc_get_endpoint_url( 'buy-again' ) ),
		esc_html__( 'Buy Again', 'bhaivatech-storefront-alpha' )
	);
}

/** Add a discoverable link to content-based My Account pages. */
function bhaivatech_storefront_buy_again_account_content( string $content ): string {
	if (
		! is_user_logged_in()
		|| ! function_exists( 'is_account_page' )
		|| ! is_account_page()
		|| ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'buy-again' ) )
		|| false !== strpos( $content, 'data-bt-buy-again-link' )
	) {
		return $content;
	}

	return $content . bhaivatech_storefront_buy_again_dashboard_link_markup();
}
