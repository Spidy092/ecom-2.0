<?php
/**
 * Seed deterministic WooCommerce product, department and shopper fixtures.
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

/**
 * Return a stable product category term ID.
 *
 * @param string $name Category name.
 * @param string $slug Category slug.
 * @param int    $parent Parent term ID.
 * @return int
 */
function bhaivatech_alpha_category( string $name, string $slug, int $parent = 0 ): int {
	$existing_term = term_exists( $slug, 'product_cat' );
	if ( is_array( $existing_term ) && isset( $existing_term['term_id'] ) ) {
		return (int) $existing_term['term_id'];
	}
	if ( is_int( $existing_term ) ) {
		return $existing_term;
	}

	$created = wp_insert_term(
		$name,
		'product_cat',
		array(
			'slug'   => $slug,
			'parent' => $parent,
		)
	);

	if ( is_wp_error( $created ) ) {
		WP_CLI::error( 'Could not create department ' . $name . ': ' . $created->get_error_message() );
	}

	return (int) $created['term_id'];
}

$produce = bhaivatech_alpha_category( 'Produce', 'alpha-produce' );
$dairy   = bhaivatech_alpha_category( 'Dairy', 'alpha-dairy' );
$pantry  = bhaivatech_alpha_category( 'Pantry', 'alpha-pantry' );
$bakery  = bhaivatech_alpha_category( 'Bakery', 'alpha-bakery' );
$leafy   = bhaivatech_alpha_category( 'Leafy Greens', 'alpha-leafy-greens', $produce );

$milk = new WC_Product_Simple();
$milk->set_name( 'Alpha Milk' );
$milk->set_status( 'publish' );
$milk->set_regular_price( '68.00' );
$milk->set_manage_stock( true );
$milk->set_stock_quantity( 50 );
$milk->set_stock_status( 'instock' );
$milk->save();
wp_set_object_terms( $milk->get_id(), array( $dairy ), 'product_cat' );

$bread = new WC_Product_Simple();
$bread->set_name( 'Alpha Bread' );
$bread->set_sku( 'alpha-bread-e2e' );
$bread->set_status( 'publish' );
$bread->set_regular_price( '45.00' );
$bread->set_manage_stock( true );
$bread->set_stock_quantity( 25 );
$bread->set_stock_status( 'instock' );
$bread->save();
wp_set_object_terms( $bread->get_id(), array( $bakery ), 'product_cat' );

$tomato = new WC_Product_Simple();
$tomato->set_name( 'Alpha Tomato' );
$tomato->set_status( 'publish' );
$tomato->set_regular_price( '59.00' );
$tomato->set_manage_stock( true );
$tomato->set_stock_quantity( 0 );
$tomato->set_stock_status( 'outofstock' );
$tomato->save();
wp_set_object_terms( $tomato->get_id(), array( $produce ), 'product_cat' );

$apple = new WC_Product_Simple();
$apple->set_name( 'Alpha Apple' );
$apple->set_status( 'publish' );
$apple->set_regular_price( '80.00' );
$apple->set_manage_stock( true );
$apple->set_stock_quantity( 30 );
$apple->set_stock_status( 'instock' );
$apple->save();
wp_set_object_terms( $apple->get_id(), array( $produce, $leafy ), 'product_cat' );

$lentils = new WC_Product_Simple();
$lentils->set_name( 'Alpha Lentils' );
$lentils->set_status( 'publish' );
$lentils->set_regular_price( '120.00' );
$lentils->set_manage_stock( true );
$lentils->set_stock_quantity( 20 );
$lentils->set_stock_status( 'instock' );
$lentils->save();
wp_set_object_terms( $lentils->get_id(), array( $pantry ), 'product_cat' );

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
wp_set_object_terms( $rice->get_id(), array( $pantry ), 'product_cat' );

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

WP_CLI::success( 'Seeded grocery products, four top-level departments, one child department, and isolated Saved shoppers.' );
