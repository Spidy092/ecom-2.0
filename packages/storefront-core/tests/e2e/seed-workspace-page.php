<?php
/** Seed the current Core product-workspace page for browser contracts. */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding the workspace page.' );
}

$page = get_page_by_path( 'alpha-workspace', OBJECT, 'page' );

$post = array(
	'post_title'   => 'Alpha Grocery Workspace',
	'post_name'    => 'alpha-workspace',
	'post_content' => '<!-- wp:bhaivatech-storefront/product-workspace /-->',
	'post_status'  => 'publish',
	'post_type'    => 'page',
);

if ( $page ) {
	$post['ID'] = $page->ID;
	$page_id    = wp_update_post( $post, true );
} else {
	$page_id = wp_insert_post( $post, true );
}

if ( is_wp_error( $page_id ) ) {
	WP_CLI::error( 'Could not create the deterministic workspace page.' );
}

update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();

// wp-env may keep its front controller on query permalinks even after the
// rewrite option is updated. index.php is the stable public WordPress seam in
// both configurations, so the browser gate does not depend on Apache rewrite
// behavior.
echo esc_url_raw( home_url( '/index.php?pagename=alpha-workspace' ) );
