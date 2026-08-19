<?php
/**
 * Expand the deterministic category fixture from 4 to 9 non-empty top-level departments.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding department overflow.' );
}

$bread_id = (int) wc_get_product_id_by_sku( 'alpha-bread-e2e' );
if ( $bread_id <= 0 || ! wc_get_product( $bread_id ) instanceof WC_Product ) {
	WP_CLI::error( 'Alpha Bread fixture is required before department overflow.' );
}

$extra_departments = array(
	'Frozen'        => 'alpha-frozen',
	'Drinks'        => 'alpha-drinks',
	'Household'     => 'alpha-household',
	'Personal Care' => 'alpha-personal-care',
	'Snacks'        => 'alpha-snacks',
);

$term_ids = array();
foreach ( $extra_departments as $name => $slug ) {
	$term = term_exists( $slug, 'product_cat' );
	if ( is_array( $term ) && isset( $term['term_id'] ) ) {
		$term_ids[] = (int) $term['term_id'];
		continue;
	}

	$created = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( 'Could not create overflow department ' . $name . ': ' . $created->get_error_message() );
	}
	$term_ids[] = (int) $created['term_id'];
}

wp_set_object_terms( $bread_id, $term_ids, 'product_cat', true );
wp_update_term_count( $term_ids, 'product_cat' );
WP_CLI::success( 'Expanded grocery fixture to nine non-empty top-level departments using Alpha Bread.' );
