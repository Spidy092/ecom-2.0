<?php
/**
 * Engineering-alpha theme bootstrap.
 *
 * @package BhaivaTechGroceryAlpha
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register only presentation-level theme support.
 */
function bhaivatech_grocery_alpha_setup(): void {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'bhaivatech_grocery_alpha_setup' );

/**
 * Attach theme-owned presentation styles to Storefront Core blocks.
 */
function bhaivatech_grocery_alpha_register_block_styles(): void {
	wp_enqueue_block_style(
		'bhaivatech-storefront/product-workspace',
		array(
			'handle' => 'bhaivatech-grocery-alpha-product-workspace',
			'src'    => get_theme_file_uri( 'assets/css/product-workspace.css' ),
			'path'   => get_theme_file_path( 'assets/css/product-workspace.css' ),
			'ver'    => wp_get_theme()->get( 'Version' ),
		)
	);

	wp_enqueue_block_style(
		'bhaivatech-storefront/product-workspace',
		array(
			'handle' => 'bhaivatech-grocery-alpha-department-browse',
			'src'    => get_theme_file_uri( 'assets/css/department-browse.css' ),
			'path'   => get_theme_file_path( 'assets/css/department-browse.css' ),
			'ver'    => wp_get_theme()->get( 'Version' ),
		)
	);

	wp_enqueue_block_style(
		'bhaivatech-storefront/product-workspace',
		array(
			'handle' => 'bhaivatech-grocery-alpha-product-filters',
			'src'    => get_theme_file_uri( 'assets/css/product-filters.css' ),
			'path'   => get_theme_file_path( 'assets/css/product-filters.css' ),
			'ver'    => wp_get_theme()->get( 'Version' ),
		)
	);

	wp_enqueue_block_style(
		'bhaivatech-storefront/mobile-shopping-nav',
		array(
			'handle' => 'bhaivatech-grocery-alpha-mobile-shopping-nav',
			'src'    => get_theme_file_uri( 'assets/css/mobile-shopping-nav.css' ),
			'path'   => get_theme_file_path( 'assets/css/mobile-shopping-nav.css' ),
			'ver'    => wp_get_theme()->get( 'Version' ),
		)
	);
}
add_action( 'init', 'bhaivatech_grocery_alpha_register_block_styles' );
