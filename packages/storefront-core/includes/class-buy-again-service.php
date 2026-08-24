<?php
/**
 * HPOS-safe repeat-purchase lookup.
 *
 * @package BhaivaTechStorefrontCore
 */

namespace BhaivaTech\Storefront;

defined( 'ABSPATH' ) || exit;

/**
 * Derives currently purchasable products from the authenticated customer's orders.
 */
final class Buy_Again_Service {
	private const ORDER_LIMIT   = 20;
	private const PRODUCT_LIMIT = 50;

	/**
	 * Get the current customer's eligible repeat products.
	 *
	 * @param int $customer_id Authenticated WordPress customer ID.
	 * @return array<int, object>
	 */
	public function get_products_for_customer( int $customer_id ): array {
		if ( $customer_id <= 0 || ! function_exists( 'wc_get_orders' ) || ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$orders = wc_get_orders(
			array(
				'customer_id' => $customer_id,
				// Buy Again intentionally excludes pending, cancelled, failed and
				// refunded orders; only settled/current fulfilment states qualify.
				'status'      => array( 'processing', 'completed' ),
				'limit'       => self::ORDER_LIMIT,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'return'      => 'objects',
			)
		);

		$products = array();
		$seen     = array();
		foreach ( is_array( $orders ) ? $orders : array() as $order ) {
			if ( ! is_object( $order ) || ! is_callable( array( $order, 'get_items' ) ) ) {
				continue;
			}

			foreach ( $order->get_items( 'line_item' ) as $item ) {
				if ( ! is_object( $item ) ) {
					continue;
				}

				// The parent product is the safe repeat-card identity. Variable
				// products still require options in the browser.
				$product_id = is_callable( array( $item, 'get_product_id' ) ) ? absint( $item->get_product_id() ) : 0;

				if ( $product_id <= 0 || isset( $seen[ $product_id ] ) ) {
					continue;
				}

				$product = wc_get_product( $product_id );
				if ( ! $product || ! is_callable( array( $product, 'is_visible' ) ) || ! $product->is_visible() || 'publish' !== $product->get_status() ) {
					continue;
				}

				$seen[ $product_id ] = true;
				$products[]          = $product;
				if ( count( $products ) >= self::PRODUCT_LIMIT ) {
					break 2;
				}
			}
		}

		return $products;
	}
}
