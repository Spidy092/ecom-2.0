<?php
/**
 * Grovia theme bootstrap.
 *
 * @package Grovia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme support.
 */
function grovia_theme_setup() {
	load_theme_textdomain( 'grovia', get_template_directory() . '/languages' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_editor_style( 'assets/css/storefront.css' );
}
add_action( 'after_setup_theme', 'grovia_theme_setup' );

/**
 * Load the small storefront stylesheet.
 */
function grovia_theme_enqueue_assets() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'grovia-storefront',
		get_theme_file_uri( 'assets/css/storefront.css' ),
		array(),
		$theme->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'grovia_theme_enqueue_assets' );
