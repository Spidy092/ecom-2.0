<?php
/**
 * Verify canonical Modern Grocery product-image ownership after repeated seeding.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before checking demo product assets.' );
}

$meta_key = '_bhaivatech_modern_grocery_asset_id';
$expected = array(
	'Alpha Milk'      => 'modern-grocery.product.milk',
	'Alpha Bread'     => 'modern-grocery.product.bread',
	'Alpha Tomato'    => 'modern-grocery.product.tomato',
	'Alpha Apple'     => 'modern-grocery.product.apple',
	'Alpha Lentils'   => 'modern-grocery.product.lentils',
	'Alpha Rice Pack' => 'modern-grocery.product.rice-pack',
);

$products_by_name = array();
foreach ( wc_get_products( array( 'limit' => -1 ) ) as $product ) {
	$name = $product->get_name();
	if ( isset( $expected[ $name ] ) ) {
		if ( isset( $products_by_name[ $name ] ) ) {
			WP_CLI::error( 'Duplicate deterministic product fixture exists: ' . $name );
		}
		$products_by_name[ $name ] = $product;
	}
}

$attachment_ids = array();
foreach ( $expected as $product_name => $asset_id ) {
	if ( ! isset( $products_by_name[ $product_name ] ) ) {
		WP_CLI::error( 'Missing deterministic product fixture: ' . $product_name );
	}

	$product       = $products_by_name[ $product_name ];
	$attachment_id = (int) $product->get_image_id();
	if ( $attachment_id <= 0 ) {
		WP_CLI::error( $product_name . ' has no canonical product image.' );
	}

	if ( $asset_id !== get_post_meta( $attachment_id, $meta_key, true ) ) {
		WP_CLI::error( $product_name . ' image is not owned by expected asset ID ' . $asset_id );
	}
	if ( 'image/webp' !== get_post_mime_type( $attachment_id ) ) {
		WP_CLI::error( $product_name . ' canonical image is not image/webp.' );
	}

	$image = wp_get_attachment_image_src( $attachment_id, 'full' );
	if ( ! is_array( $image ) || 960 !== (int) $image[1] || 960 !== (int) $image[2] ) {
		WP_CLI::error( $product_name . ' canonical image is not 960x960.' );
	}

	$owned = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => $meta_key,
			'meta_value'     => $asset_id,
		)
	);
	if ( 1 !== count( $owned ) || (int) $owned[0] !== $attachment_id ) {
		WP_CLI::error( 'Expected exactly one reusable Media attachment for ' . $asset_id );
	}

	if ( in_array( $attachment_id, $attachment_ids, true ) ) {
		WP_CLI::error( 'Canonical product fixtures unexpectedly share an attachment ID.' );
	}
	$attachment_ids[] = $attachment_id;
}

$rice = $products_by_name['Alpha Rice Pack'];
if ( ! $rice instanceof WC_Product_Variable ) {
	WP_CLI::error( 'Alpha Rice Pack must remain a variable product.' );
}
foreach ( $rice->get_children() as $variation_id ) {
	if ( '' !== (string) get_post_meta( (int) $variation_id, '_thumbnail_id', true ) ) {
		WP_CLI::error( 'Rice variations must not introduce package-size-specific image attachments.' );
	}
}

if ( 6 !== count( array_unique( $attachment_ids ) ) ) {
	WP_CLI::error( 'Expected six unique canonical demo product image attachments.' );
}

WP_CLI::success( 'Verified six unique, reusable 960x960 canonical product images after repeated fixture seeding.' );
