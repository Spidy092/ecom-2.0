<?php
/**
 * Add deterministic global product attributes for contextual filter E2E coverage.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding filter fixtures.' );
}

function bhaivatech_alpha_filter_product( string $name ): WC_Product {
	foreach ( wc_get_products( array( 'limit' => -1 ) ) as $product ) {
		if ( $product instanceof WC_Product && $product->get_name() === $name ) {
			return $product;
		}
	}
	WP_CLI::error( 'Missing filter fixture product: ' . $name );
}

function bhaivatech_alpha_filter_attribute( string $label, string $slug ): array {
	$attribute_id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $attribute_id ) {
		$attribute_id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'name',
				'has_archives' => false,
			)
		);
	}
	if ( is_wp_error( $attribute_id ) ) {
		WP_CLI::error( 'Could not create filter attribute ' . $label . ': ' . $attribute_id->get_error_message() );
	}

	$taxonomy = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . $taxonomy,
				array(
					'labels'                => array( 'name' => $label ),
					'hierarchical'          => false,
					'show_ui'               => false,
					'query_var'             => true,
					'rewrite'               => false,
					'update_count_callback' => '_update_post_term_count',
				)
			)
		);
	}
	return array( 'id' => (int) $attribute_id, 'taxonomy' => $taxonomy );
}

function bhaivatech_alpha_filter_term( string $taxonomy, string $name, string $slug ): int {
	$existing = term_exists( $slug, $taxonomy );
	if ( is_array( $existing ) && isset( $existing['term_id'] ) ) {
		return (int) $existing['term_id'];
	}
	if ( is_int( $existing ) ) {
		return $existing;
	}
	$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( 'Could not create filter term ' . $name . ': ' . $created->get_error_message() );
	}
	return (int) $created['term_id'];
}

function bhaivatech_alpha_assign_filter_attribute( WC_Product $product, int $attribute_id, string $taxonomy, int $term_id ): void {
	wp_set_object_terms( $product->get_id(), array( $term_id ), $taxonomy, false );
	$attribute = new WC_Product_Attribute();
	$attribute->set_id( $attribute_id );
	$attribute->set_name( $taxonomy );
	$attribute->set_options( array( $term_id ) );
	$attribute->set_visible( true );
	$attribute->set_variation( false );
	$attributes              = $product->get_attributes();
	$attributes[ $taxonomy ] = $attribute;
	$product->set_attributes( array_values( $attributes ) );
	$product->save();
}

$dietary = bhaivatech_alpha_filter_attribute( 'Dietary', 'dietary' );
$pack    = bhaivatech_alpha_filter_attribute( 'Pack', 'pack-filter' );
$organic = bhaivatech_alpha_filter_term( $dietary['taxonomy'], 'Organic', 'organic' );
$vegan   = bhaivatech_alpha_filter_term( $dietary['taxonomy'], 'Vegan', 'vegan' );
$single  = bhaivatech_alpha_filter_term( $pack['taxonomy'], 'Single', 'single' );
$family  = bhaivatech_alpha_filter_term( $pack['taxonomy'], 'Family', 'family' );

$assignments = array(
	'Alpha Tomato'  => array( $organic, $single ),
	'Alpha Apple'   => array( $vegan, $single ),
	'Alpha Milk'    => array( $organic, $single ),
	'Alpha Bread'   => array( $vegan, $family ),
	'Alpha Lentils' => array( $vegan, $family ),
);

foreach ( $assignments as $product_name => $term_ids ) {
	$product = bhaivatech_alpha_filter_product( $product_name );
	bhaivatech_alpha_assign_filter_attribute( $product, $dietary['id'], $dietary['taxonomy'], $term_ids[0] );
	bhaivatech_alpha_assign_filter_attribute( $product, $pack['id'], $pack['taxonomy'], $term_ids[1] );
	wc_delete_product_transients( $product->get_id() );
}

WP_CLI::success( 'Seeded contextual filter attributes: Dietary and Pack.' );
