<?php
/**
 * Seed deterministic WooCommerce product fixtures for engineering-alpha E2E.
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
$rice->set_stock_status( 'instock' );

$pack = new WC_Product_Attribute();
$pack->set_name( 'Pack' );
$pack->set_options( array( '1 kg', '5 kg' ) );
$pack->set_visible( true );
$pack->set_variation( true );
$rice->set_attributes( array( $pack ) );
$rice->save();

WP_CLI::success( 'Seeded Alpha Milk, Alpha Bread, Alpha Tomato and Alpha Rice Pack.' );
