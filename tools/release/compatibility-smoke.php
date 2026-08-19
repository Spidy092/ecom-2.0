<?php
/**
 * Runtime assertions for an exact WordPress/WooCommerce/PHP compatibility row.
 *
 * Usage with WP-CLI:
 * wp eval-file compatibility-smoke.php <wp> <woo> <php-major.minor>
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $args[0], $args[1], $args[2] ) ) {
	WP_CLI::error( 'Expected WordPress, WooCommerce and PHP versions.' );
}

$expected_wp  = (string) $args[0];
$expected_woo = (string) $args[1];
$expected_php = (string) $args[2];

global $wp_version;

if ( $expected_wp !== (string) $wp_version ) {
	WP_CLI::error( sprintf( 'WordPress version mismatch: expected %s, got %s.', $expected_wp, $wp_version ) );
}

if ( ! defined( 'WC_VERSION' ) || $expected_woo !== (string) WC_VERSION ) {
	WP_CLI::error( sprintf( 'WooCommerce version mismatch: expected %s, got %s.', $expected_woo, defined( 'WC_VERSION' ) ? WC_VERSION : 'not loaded' ) );
}

$actual_php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
if ( $expected_php !== $actual_php ) {
	WP_CLI::error( sprintf( 'PHP version mismatch: expected %s, got %s.', $expected_php, $actual_php ) );
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

if ( ! is_plugin_active( 'storefront-core/storefront-core.php' ) ) {
	WP_CLI::error( 'Storefront Core is not active.' );
}

$theme = wp_get_theme();
if ( 'storefront-theme' !== $theme->get_stylesheet() ) {
	WP_CLI::error( 'Storefront Theme is not active.' );
}

if ( ! class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' ) ) {
	WP_CLI::error( 'WooCommerce OrderUtil is unavailable; cannot verify HPOS.' );
}

if ( ! \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
	WP_CLI::error( 'HPOS is not enabled in the clean compatibility environment.' );
}

$server = rest_get_server();
$routes = $server->get_routes();

foreach ( array( '/wc/store/v1/products', '/bhaivatech-storefront/v1/serviceability' ) as $required_route ) {
	if ( ! isset( $routes[ $required_route ] ) ) {
		WP_CLI::error( 'Required REST route is missing: ' . $required_route );
	}
}

$store_request  = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
$store_response = rest_do_request( $store_request );
if ( $store_response->get_status() >= 400 ) {
	WP_CLI::error( 'Woo Store API products request failed with HTTP ' . $store_response->get_status() . '.' );
}

$core_request  = new WP_REST_Request( 'POST', '/bhaivatech-storefront/v1/serviceability' );
$core_response = rest_do_request( $core_request );
if ( $core_response->get_status() < 200 || $core_response->get_status() >= 500 ) {
	WP_CLI::error( 'Core serviceability request returned unexpected HTTP ' . $core_response->get_status() . '.' );
}

WP_CLI::success(
	sprintf(
		'Compatibility row passed: WordPress %s / WooCommerce %s / PHP %s; HPOS + Store API + Core REST verified.',
		$expected_wp,
		$expected_woo,
		$expected_php
	)
);
