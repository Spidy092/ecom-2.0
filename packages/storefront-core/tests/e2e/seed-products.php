<?php
/**
 * Seed deterministic WooCommerce product and shopper fixtures for alpha E2E.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding fixtures.' );
}

$existing = wc_get_products(
	array(
		'limit'  => -1,
		'return' => 'ids',
	)
);

foreach ( $existing as $product_id ) {
	wp_delete_post( $product_id, true );
}

$milk = new WC_Product_Simple();
$milk->set_name( 'Alpha Milk' );
$milk->set_status( 'publish' );
$milk->set_regular_price( '68.00' );
$milk->set_manage_stock( true );
$milk->set_stock_quantity( 50 );
$milk->set_stock_status( 'instock' );
$milk->save();

$bread = new WC_Product_Simple();
$bread->set_name( 'Alpha Bread' );
$bread->set_status( 'publish' );
$bread->set_regular_price( '45.00' );
$bread->set_manage_stock( true );
$bread->set_stock_quantity( 25 );
$bread->set_stock_status( 'instock' );
$bread->save();

$tomato = new WC_Product_Simple();
$tomato->set_name( 'Alpha Tomato' );
$tomato->set_status( 'publish' );
$tomato->set_regular_price( '59.00' );
$tomato->set_manage_stock( true );
$tomato->set_stock_quantity( 0 );
$tomato->set_stock_status( 'outofstock' );
$tomato->save();

$rice = new WC_Product_Variable();
$rice->set_name( 'Alpha Rice Pack' );
$rice->set_status( 'publish' );

$pack = new WC_Product_Attribute();
$pack->set_name( 'Pack' );
$pack->set_options( array( '1 kg', '5 kg' ) );
$pack->set_visible( true );
$pack->set_variation( true );
$rice->set_attributes( array( $pack ) );
$rice->save();

$rice_variations = array(
	'1 kg' => '199.00',
	'5 kg' => '799.00',
);

foreach ( $rice_variations as $pack_value => $price ) {
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $rice->get_id() );
	$variation->set_attributes( array( 'pack' => $pack_value ) );
	$variation->set_regular_price( $price );
	$variation->set_manage_stock( true );
	$variation->set_stock_quantity( 10 );
	$variation->set_stock_status( 'instock' );
	$variation->save();
}

WC_Product_Variable::sync( $rice->get_id() );
wc_delete_product_transients( $rice->get_id() );

$shopper_password = 'alpha-saved-pass';
foreach ( array( 'alpha-saved-a', 'alpha-saved-b' ) as $login ) {
	$user_id = username_exists( $login );

	if ( ! $user_id ) {
		$user_id = wp_create_user( $login, $shopper_password, $login . '@example.test' );
	}

	if ( is_wp_error( $user_id ) ) {
		WP_CLI::error( 'Could not create Saved E2E shopper ' . $login . '.' );
	}

	wp_set_password( $shopper_password, (int) $user_id );
	$user = new WP_User( (int) $user_id );
	$user->set_role( 'customer' );
	delete_user_meta( (int) $user_id, BHAIVATECH_STOREFRONT_SAVED_META_KEY );
}

WP_CLI::success( 'Seeded products plus isolated Saved shoppers alpha-saved-a and alpha-saved-b.' );
