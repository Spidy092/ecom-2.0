<?php
/**
 * PHPUnit bootstrap for Storefront Core.
 *
 * Supports dual test execution:
 * 1. Fast isolated unit tests (using Brain\Monkey stubs).
 * 2. Full WordPress / WooCommerce integration tests (when WP_TESTS_DIR is set).
 *
 * @package StorefrontCore
 */

require_once __DIR__ . '/../vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( $_tests_dir && file_exists( $_tests_dir . '/includes/bootstrap.php' ) ) {
	// -----------------------------------------------------------------------
	// Mode A: Full WordPress Integration Environment
	// -----------------------------------------------------------------------
	require_once $_tests_dir . '/includes/functions.php';

	/**
	 * Manually load plugin & dependencies during WP test bootstrap.
	 */
	tests_add_filter(
		'muplugins_loaded',
		function() {
			// Ensure WooCommerce is loaded if available in test environment.
			if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
				require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			}
			require_once __DIR__ . '/../storefront-core.php';
		}
	);

	require $_tests_dir . '/includes/bootstrap.php';
} else {
	// -----------------------------------------------------------------------
	// Mode B: Fast Isolated Unit Test Environment (Brain\Monkey)
	// -----------------------------------------------------------------------
	\Brain\Monkey\setUp();
}
