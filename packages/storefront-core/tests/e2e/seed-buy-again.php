<?php
/**
 * Seed deterministic customer orders for the Buy Again browser contract.
 *
 * This fixture deliberately uses WooCommerce CRUD APIs so it works with HPOS.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding Buy Again fixtures.' );
}

function bhaivatech_buy_again_ensure_account_page(): void {
	$page_id = absint( wc_get_page_id( 'myaccount' ) );
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_name'    => 'my-account',
				'post_content' => '[woocommerce_my_account]',
			)
		);
		update_option( 'woocommerce_myaccount_page_id', $page_id );
		flush_rewrite_rules();
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => 'My Account',
			'post_name'    => 'my-account',
			'post_content' => '[woocommerce_my_account]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		WP_CLI::error( 'Could not create deterministic My Account page.' );
	}

	update_option( 'woocommerce_myaccount_page_id', (int) $page_id );
	flush_rewrite_rules();
}

function bhaivatech_buy_again_user_id( string $login ): int {
	$user_id = username_exists( $login );
	if ( ! $user_id ) {
		WP_CLI::error( 'Expected seeded shopper is missing: ' . $login );
	}

	return (int) $user_id;
}

function bhaivatech_buy_again_ensure_empty_user(): int {
	$login   = 'alpha-buy-empty';
	$password = 'alpha-saved-pass';
	$user_id = username_exists( $login );
	if ( ! $user_id ) {
		$user_id = wp_create_user( $login, $password, $login . '@example.test' );
	}
	if ( is_wp_error( $user_id ) ) {
		WP_CLI::error( 'Could not create empty Buy Again shopper.' );
	}

	wp_set_password( $password, (int) $user_id );
	$user = new WP_User( (int) $user_id );
	$user->set_role( 'customer' );
	return (int) $user_id;
}

function bhaivatech_buy_again_product( string $name ): WC_Product {
	$products = wc_get_products( array( 'name' => $name, 'limit' => 1, 'return' => 'objects' ) );
	if ( empty( $products[0] ) ) {
		WP_CLI::error( 'Expected product is missing: ' . $name );
	}

	return $products[0];
}

function bhaivatech_buy_again_fixture_product( string $name, string $sku ): WC_Product_Simple {
	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_sku( $sku );
	$product->set_status( 'publish' );
	$product->set_regular_price( '35.00' );
	$product->set_stock_status( 'instock' );
	$product->save();
	return $product;
}

function bhaivatech_buy_again_delete_orders( int $user_id ): void {
	$orders = wc_get_orders( array( 'customer_id' => $user_id, 'limit' => -1, 'return' => 'objects' ) );
	foreach ( $orders as $order ) {
		if ( is_object( $order ) && method_exists( $order, 'delete' ) ) {
			$order->delete( true );
		}
	}
}

function bhaivatech_buy_again_order( int $user_id, string $status, array $products, ?string $date_created = null ): void {
	$order = wc_create_order( array( 'customer_id' => $user_id, 'created_via' => 'buy-again-e2e' ) );
	if ( is_wp_error( $order ) ) {
		WP_CLI::error( 'Could not create Buy Again fixture order.' );
	}

	if ( $date_created ) {
		$order->set_date_created( $date_created );
	}
	foreach ( $products as $product_and_quantity ) {
		$order->add_product( $product_and_quantity[0], $product_and_quantity[1] );
	}
	$order->calculate_totals();
	$order->save();
	$order->update_status( $status, 'Deterministic Buy Again fixture.' );
}

$shopper_a     = bhaivatech_buy_again_user_id( 'alpha-saved-a' );
$shopper_b     = bhaivatech_buy_again_user_id( 'alpha-saved-b' );
$shopper_empty = bhaivatech_buy_again_ensure_empty_user();
bhaivatech_buy_again_ensure_account_page();
bhaivatech_buy_again_delete_orders( $shopper_a );
bhaivatech_buy_again_delete_orders( $shopper_b );
bhaivatech_buy_again_delete_orders( $shopper_empty );

$apple       = bhaivatech_buy_again_product( 'Alpha Apple' );
$milk        = bhaivatech_buy_again_product( 'Alpha Milk' );
$tomato      = bhaivatech_buy_again_product( 'Alpha Tomato' );
$bread       = bhaivatech_buy_again_product( 'Alpha Bread' );
$rice        = bhaivatech_buy_again_product( 'Alpha Rice Pack' );
$rice_childs = $rice->get_children();
$rice_choice = ! empty( $rice_childs ) ? wc_get_product( $rice_childs[0] ) : null;
if ( ! $rice_choice ) {
	WP_CLI::error( 'Expected Alpha Rice variation is missing.' );
}

$pending   = bhaivatech_buy_again_fixture_product( 'Alpha Pending Only', 'alpha-pending-only' );
$cancelled = bhaivatech_buy_again_fixture_product( 'Alpha Cancelled Only', 'alpha-cancelled-only' );
$refunded  = bhaivatech_buy_again_fixture_product( 'Alpha Refunded Only', 'alpha-refunded-only' );
$failed    = bhaivatech_buy_again_fixture_product( 'Alpha Failed Only', 'alpha-failed-only' );

// The newest eligible order determines the remembered quantity for repeats.
bhaivatech_buy_again_order( $shopper_a, 'completed', array( array( $apple, 2 ), array( $tomato, 1 ), array( $rice_choice, 1 ) ), '2026-01-01 00:00:00' );
bhaivatech_buy_again_order( $shopper_a, 'processing', array( array( $apple, 1 ), array( $milk, 3 ) ), '2026-01-02 00:00:00' );

// These statuses must not leak into Buy Again.
bhaivatech_buy_again_order( $shopper_a, 'pending', array( array( $pending, 1 ) ) );
bhaivatech_buy_again_order( $shopper_a, 'cancelled', array( array( $cancelled, 1 ) ) );
bhaivatech_buy_again_order( $shopper_a, 'refunded', array( array( $refunded, 1 ) ) );
bhaivatech_buy_again_order( $shopper_a, 'failed', array( array( $failed, 1 ) ) );
bhaivatech_buy_again_order( $shopper_b, 'completed', array( array( $bread, 2 ) ) );

WP_CLI::success( 'Seeded isolated recent, variable, unavailable, and excluded-status Buy Again orders.' );
