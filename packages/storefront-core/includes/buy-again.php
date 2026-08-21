<?php
/**
 * Buy Again account experience and private API.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ORDERS = 20;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS  = 50;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_LINES  = 250;
const BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_QTY    = 100;

/**
 * Register the supported WooCommerce My Account endpoint.
 */
function bhaivatech_storefront_register_buy_again_endpoint(): void {
	add_rewrite_endpoint( 'buy-again', EP_ROOT | EP_PAGES );
}

/**
 * Register the endpoint with WooCommerce's account query map.
 *
 * WooCommerce uses the map for account menu URLs and current-endpoint
 * detection. Keeping the key and URL slug identical makes this compatible
 * with both pretty and query-form endpoint routing.
 *
 * @param array<string, string> $vars Existing WooCommerce query vars.
 * @return array<string, string>
 */
function bhaivatech_storefront_buy_again_query_vars( array $vars ): array {
	$vars['buy-again'] = 'buy-again';
	return $vars;
}

/**
 * Add Buy Again to the WooCommerce account navigation.
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
 * Bound a remembered order quantity before it reaches the browser.
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
 * Product IDs are collected first, then resolved in bounded bulk queries so
 * order history never becomes an unbounded product lookup loop.
 *
 * @return WP_REST_Response
 */
function bhaivatech_storefront_buy_again_get(): WP_REST_Response {
	$user_id = get_current_user_id();
	$orders  = wc_get_orders(
		array(
			'customer_id' => $user_id,
			'status'      => array( 'processing', 'completed' ),
			'limit'       => BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ORDERS,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'return'      => 'objects',
		)
	);

	$line_items = array();
	$lookup_ids = array();
	$skipped    = 0;
	$line_count = 0;

	foreach ( $orders as $order ) {
		if ( ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			continue;
		}

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$line_count++;
			if ( $line_count > BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_LINES ) {
				$skipped++;
				continue;
			}

			$product_id   = absint( $item->get_product_id() );
			// The parent product is the safe card identity. Variable products
			// remain choice-required in the browser; no variation is selected.
			$lookup_id    = $product_id;
			$quantity     = absint( $item->get_quantity() );

			if ( ! $lookup_id || ! $quantity ) {
				$skipped++;
				continue;
			}

			if ( ! isset( $line_items[ $lookup_id ] ) ) {
				$line_items[ $lookup_id ] = array(
					'quantity' => bhaivatech_storefront_buy_again_quantity( $quantity ),
					'order_id' => $order->get_id(),
				);
				$lookup_ids[]              = $lookup_id;
				continue;
			}

			// Combine duplicate lines from the same order while preserving the
			// most recent order's quantity over older orders.
			if ( (int) $line_items[ $lookup_id ]['order_id'] === (int) $order->get_id() ) {
				$line_items[ $lookup_id ]['quantity'] = bhaivatech_storefront_buy_again_quantity(
					(int) $line_items[ $lookup_id ]['quantity'] + $quantity
				);
			}
		}
	}

	$lookup_ids = array_slice( array_values( array_unique( $lookup_ids ) ), 0, BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS );
	if ( ! $lookup_ids ) {
		$response = new WP_REST_Response(
			array(
				'items'        => array(),
				'skipped_count' => $skipped,
			)
		);
		$response->header( 'Cache-Control', 'no-store' );
		return $response;
	}

	$products = wc_get_products(
		array(
			'include' => $lookup_ids,
			'limit'   => count( $lookup_ids ),
			'return'  => 'objects',
		)
	);
	$product_map = array();
	$parent_ids  = array();

	foreach ( $products as $product ) {
		if ( ! is_object( $product ) ) {
			continue;
		}

		$product_map[ $product->get_id() ] = $product;
		$parent_id = $product->is_type( 'variation' ) ? absint( $product->get_parent_id() ) : $product->get_id();
		if ( $parent_id ) {
			$parent_ids[] = $parent_id;
		}
	}

	$parent_ids = array_slice( array_values( array_unique( $parent_ids ) ), 0, BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS );
	$parents = $parent_ids
		? wc_get_products(
			array(
				'include' => $parent_ids,
				'limit'   => count( $parent_ids ),
				'status'  => 'publish',
				'return'  => 'objects',
			)
		)
		: array();
	$parent_map = array();

	foreach ( $parents as $product ) {
		if ( is_object( $product ) ) {
			$parent_map[ $product->get_id() ] = $product;
		}
	}

	$items = array();
	foreach ( $line_items as $lookup_id => $line ) {
		$product = $product_map[ $lookup_id ] ?? null;
		if ( ! is_object( $product ) ) {
			$skipped++;
			continue;
		}

		$parent_id = $product->is_type( 'variation' ) ? absint( $product->get_parent_id() ) : $product->get_id();
		$current   = $parent_map[ $parent_id ] ?? null;
		if ( ! is_object( $current ) || ! $current->is_visible() ) {
			$skipped++;
			continue;
		}

		$items[ $parent_id ] = array(
			'product_id'       => $parent_id,
			'purchased_quantity' => (int) $line['quantity'],
		);
		if ( count( $items ) >= BHAIVATECH_STOREFRONT_BUY_AGAIN_MAX_ITEMS ) {
			break;
		}
	}

	$response = new WP_REST_Response(
		array(
			'items'         => array_values( $items ),
			'skipped_count' => $skipped,
		)
	);
	$response->header( 'Cache-Control', 'no-store' );
	return $response;
}

/**
 * Register the private Buy Again endpoint.
 */
function bhaivatech_storefront_register_buy_again_routes(): void {
	register_rest_route(
		'bhaivatech-storefront/v1',
		'/buy-again',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'bhaivatech_storefront_buy_again_get',
			'permission_callback' => 'bhaivatech_storefront_buy_again_permission',
		)
	);
}

/**
 * Register the small account-page client surface.
 */
function bhaivatech_storefront_register_buy_again_assets(): void {
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
		array( 'bhaivatech-storefront-product-workspace-model', 'bhaivatech-storefront-buy-again-model' ),
		BHAIVATECH_STOREFRONT_CORE_VERSION,
		true
	);
}

/**
 * Load Buy Again only for its My Account endpoint.
 */
function bhaivatech_storefront_enqueue_buy_again_assets(): void {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_wc_endpoint_url( 'buy-again' ) ) {
		return;
	}

	wp_enqueue_script( 'bhaivatech-storefront-buy-again' );
	wp_add_inline_script(
		'bhaivatech-storefront-buy-again',
		'window.BhaivaTechBuyAgainConfig = ' . wp_json_encode(
			array(
				'products'  => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
				'buyAgain' => esc_url_raw( rest_url( 'bhaivatech-storefront/v1/buy-again' ) ),
				'addItem'  => esc_url_raw( rest_url( 'wc/store/v1/cart/add-item' ) ),
				'cart'     => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
				'nonce'    => wp_create_nonce( 'wc_store_api' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'messages' => array(
					'loading'       => __( 'Loading recent purchases…', 'bhaivatech-storefront-alpha' ),
					'empty'         => __( 'Buy Again will appear after you have an eligible order.', 'bhaivatech-storefront-alpha' ),
					'unavailable'   => __( 'Buy Again could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
					'productsError' => __( 'Recent products could not be loaded. Try again.', 'bhaivatech-storefront-alpha' ),
					'retry'        => __( 'Try again', 'bhaivatech-storefront-alpha' ),
					'skipped'      => __( '%d recent products are no longer available.', 'bhaivatech-storefront-alpha' ),
					'boughtQuantity' => __( 'Bought %d last time', 'bhaivatech-storefront-alpha' ),
					'addAgain'     => __( 'Add again', 'bhaivatech-storefront-alpha' ),
					'adding'       => __( 'Adding %s…', 'bhaivatech-storefront-alpha' ),
					'added'        => __( 'Added %d × %s to your cart.', 'bhaivatech-storefront-alpha' ),
					'addFailed'    => __( '%s could not be added. Try again.', 'bhaivatech-storefront-alpha' ),
					'chooseOptions' => __( 'Choose options', 'bhaivatech-storefront-alpha' ),
					'outOfStock'   => __( 'Out of stock', 'bhaivatech-storefront-alpha' ),
					'unavailableProduct' => __( 'Unavailable', 'bhaivatech-storefront-alpha' ),
					'viewCart'     => __( 'View cart', 'bhaivatech-storefront-alpha' ),
				),
			)
		) . ';',
		'before'
	);
}

/**
 * Render the Buy Again endpoint content.
 */
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

/**
 * Keep the feature discoverable on stores whose account template does not
 * expose WooCommerce's classic navigation filter (for example, a customized
 * My Account template). The endpoint remains the canonical destination.
 */
function bhaivatech_storefront_render_buy_again_dashboard_link(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}

	echo bhaivatech_storefront_buy_again_dashboard_link_markup();
}

/**
 * Build the account dashboard link markup for classic and content-based
 * My Account templates.
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

/**
 * Add a discoverable link when a site uses a content-based account template.
 *
 * @param string $content Page content.
 * @return string
 */
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
