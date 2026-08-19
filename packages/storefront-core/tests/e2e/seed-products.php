<?php
/**
 * Seed deterministic WooCommerce product, department and shopper fixtures.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding fixtures.' );
}

if ( ! defined( 'BHAIVATECH_ALPHA_DEMO_ASSET_META_KEY' ) ) {
	define( 'BHAIVATECH_ALPHA_DEMO_ASSET_META_KEY', '_bhaivatech_modern_grocery_asset_id' );
}

/**
 * Return the package-local Modern Grocery asset manifest.
 *
 * The release/provenance manifest lives outside the customer package. This
 * smaller runtime map ships with Core so starter/demo code never needs a
 * network request or a repository-only file to resolve canonical assets.
 *
 * @return array<string,array<string,string>> Assets keyed by stable asset ID.
 */
function bhaivatech_alpha_demo_assets(): array {
	static $assets = null;

	if ( is_array( $assets ) ) {
		return $assets;
	}

	$manifest_path = dirname( __DIR__, 2 ) . '/starter-assets/modern-grocery/manifest.json';
	if ( ! is_file( $manifest_path ) ) {
		WP_CLI::error( 'Modern Grocery starter asset manifest is missing.' );
	}

	$decoded = json_decode( (string) file_get_contents( $manifest_path ), true );
	if ( ! is_array( $decoded ) || 1 !== ( $decoded['schema'] ?? null ) || 'modern-grocery' !== ( $decoded['starter'] ?? null ) ) {
		WP_CLI::error( 'Modern Grocery starter asset manifest is invalid.' );
	}

	$items = $decoded['assets'] ?? null;
	if ( ! is_array( $items ) ) {
		WP_CLI::error( 'Modern Grocery starter asset manifest has no asset list.' );
	}

	$assets = array();
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			WP_CLI::error( 'Modern Grocery starter asset entry is invalid.' );
		}

		$id      = isset( $item['id'] ) ? (string) $item['id'] : '';
		$file    = isset( $item['file'] ) ? (string) $item['file'] : '';
		$fixture = isset( $item['fixture'] ) ? (string) $item['fixture'] : '';
		$alt     = isset( $item['alt'] ) ? (string) $item['alt'] : '';

		if ( '' === $id || '' === $file || '' === $fixture || '' === $alt || isset( $assets[ $id ] ) ) {
			WP_CLI::error( 'Modern Grocery starter asset entry is incomplete or duplicated.' );
		}

		$assets[ $id ] = array(
			'id'      => $id,
			'file'    => $file,
			'fixture' => $fixture,
			'alt'     => $alt,
		);
	}

	return $assets;
}

/**
 * Resolve or create one canonical demo media attachment by stable asset ID.
 *
 * Never use title/filename matching as ownership. The stable private post meta
 * allows repeated setup/fixture runs to reuse the same attachment without
 * touching customer-owned media.
 *
 * @param string $asset_id Stable Modern Grocery asset ID.
 * @return int Attachment ID.
 */
function bhaivatech_alpha_demo_attachment( string $asset_id ): int {
	$assets = bhaivatech_alpha_demo_assets();
	if ( ! isset( $assets[ $asset_id ] ) ) {
		WP_CLI::error( 'Unknown Modern Grocery demo asset: ' . $asset_id );
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => BHAIVATECH_ALPHA_DEMO_ASSET_META_KEY,
			'meta_value'     => $asset_id,
		)
	);

	if ( count( $existing ) > 1 ) {
		WP_CLI::error( 'Duplicate Modern Grocery demo attachments exist for ' . $asset_id );
	}
	if ( 1 === count( $existing ) ) {
		return (int) $existing[0];
	}

	$asset       = $assets[ $asset_id ];
	$source_path = dirname( __DIR__, 2 ) . '/starter-assets/modern-grocery/' . $asset['file'];
	if ( ! is_file( $source_path ) ) {
		WP_CLI::error( 'Canonical demo image is missing for ' . $asset_id );
	}

	$bytes = file_get_contents( $source_path );
	if ( false === $bytes ) {
		WP_CLI::error( 'Could not read canonical demo image for ' . $asset_id );
	}

	$uploaded = wp_upload_bits( 'modern-grocery-' . $asset['file'], null, $bytes );
	if ( ! empty( $uploaded['error'] ) ) {
		WP_CLI::error( 'Could not copy canonical demo image into Media Library: ' . $uploaded['error'] );
	}

	$filetype = wp_check_filetype( basename( $uploaded['file'] ), null );
	if ( empty( $filetype['type'] ) || 'image/webp' !== $filetype['type'] ) {
		WP_CLI::error( 'Canonical demo image has an unexpected MIME type for ' . $asset_id );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $asset['fixture'] . ' demo image',
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$uploaded['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::error( 'Could not create demo image attachment for ' . $asset_id . ': ' . $attachment_id->get_error_message() );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $uploaded['file'] );
	if ( is_array( $metadata ) ) {
		wp_update_attachment_metadata( (int) $attachment_id, $metadata );
	}

	update_post_meta( (int) $attachment_id, BHAIVATECH_ALPHA_DEMO_ASSET_META_KEY, $asset_id );
	update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $asset['alt'] );

	return (int) $attachment_id;
}

/**
 * Attach one canonical image to a WooCommerce product object.
 *
 * @param WC_Product $product Product being seeded.
 * @param string     $asset_id Stable Modern Grocery asset ID.
 * @return void
 */
function bhaivatech_alpha_assign_demo_image( WC_Product $product, string $asset_id ): void {
	$product->set_image_id( bhaivatech_alpha_demo_attachment( $asset_id ) );
}

/**
 * Assert each canonical demo asset has exactly one Media Library attachment.
 *
 * @return void
 */
function bhaivatech_alpha_assert_demo_asset_uniqueness(): void {
	foreach ( array_keys( bhaivatech_alpha_demo_assets() ) as $asset_id ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => BHAIVATECH_ALPHA_DEMO_ASSET_META_KEY,
				'meta_value'     => $asset_id,
			)
		);
		if ( 1 !== count( $ids ) ) {
			WP_CLI::error( 'Expected exactly one canonical demo attachment for ' . $asset_id );
		}
	}
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
bhaivatech_alpha_assign_demo_image( $milk, 'modern-grocery.product.milk' );
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
bhaivatech_alpha_assign_demo_image( $bread, 'modern-grocery.product.bread' );
$bread->save();
wp_set_object_terms( $bread->get_id(), array( $bakery ), 'product_cat' );

$tomato = new WC_Product_Simple();
$tomato->set_name( 'Alpha Tomato' );
$tomato->set_status( 'publish' );
$tomato->set_regular_price( '59.00' );
$tomato->set_manage_stock( true );
$tomato->set_stock_quantity( 0 );
$tomato->set_stock_status( 'outofstock' );
bhaivatech_alpha_assign_demo_image( $tomato, 'modern-grocery.product.tomato' );
$tomato->save();
wp_set_object_terms( $tomato->get_id(), array( $produce ), 'product_cat' );

$apple = new WC_Product_Simple();
$apple->set_name( 'Alpha Apple' );
$apple->set_status( 'publish' );
$apple->set_regular_price( '80.00' );
$apple->set_manage_stock( true );
$apple->set_stock_quantity( 30 );
$apple->set_stock_status( 'instock' );
bhaivatech_alpha_assign_demo_image( $apple, 'modern-grocery.product.apple' );
$apple->save();
wp_set_object_terms( $apple->get_id(), array( $produce, $leafy ), 'product_cat' );

$lentils = new WC_Product_Simple();
$lentils->set_name( 'Alpha Lentils' );
$lentils->set_status( 'publish' );
$lentils->set_regular_price( '120.00' );
$lentils->set_manage_stock( true );
$lentils->set_stock_quantity( 20 );
$lentils->set_stock_status( 'instock' );
bhaivatech_alpha_assign_demo_image( $lentils, 'modern-grocery.product.lentils' );
$lentils->save();
wp_set_object_terms( $lentils->get_id(), array( $pantry ), 'product_cat' );

$rice = new WC_Product_Variable();
$rice->set_name( 'Alpha Rice Pack' );
$rice->set_status( 'publish' );
bhaivatech_alpha_assign_demo_image( $rice, 'modern-grocery.product.rice-pack' );

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

bhaivatech_alpha_assert_demo_asset_uniqueness();

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

WP_CLI::success( 'Seeded grocery products with six reusable canonical demo images, four top-level departments, one child department, and isolated Saved shoppers.' );
