<?php
/**
 * Seed deterministic, non-branded storefront shell content for visual E2E.
 *
 * The real product keeps Site Title / Tagline editable through native
 * WordPress blocks. This fixture only prevents repository/default WordPress
 * text from biasing screenshot review as "unfinished".
 */

defined( 'ABSPATH' ) || exit;

update_option( 'blogname', 'Modern Grocery' );
update_option( 'blogdescription', 'Everyday groceries, clearly organized.' );

$sample_page = get_page_by_path( 'sample-page' );
if ( $sample_page instanceof WP_Post ) {
	wp_delete_post( $sample_page->ID, true );
}

WP_CLI::success( 'Seeded neutral Modern Grocery demo shell identity.' );
