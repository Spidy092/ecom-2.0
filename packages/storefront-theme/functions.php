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
