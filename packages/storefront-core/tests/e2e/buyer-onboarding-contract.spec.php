<?php
/**
 * Buyer-onboarding contract assertions for the Store Setup admin experience.
 */

defined( 'ABSPATH' ) || exit;

$setup_file = dirname( __DIR__, 2 ) . '/includes/setup-status.php';
$source     = file_get_contents( $setup_file );

if ( false === $source ) {
	WP_CLI::error( 'Could not read setup-status.php.' );
}

$required_fragments = array(
	'Personalize your store',
	'Add or change logo and store name',
	'Choose a visual style',
	'Adjust colors and typography',
	'Edit header, navigation and footer',
	'Replace demo products and images',
	'Review launch readiness',
	'Appearance → Editor',
	'WooCommerce → Products',
	'No settings or customer content are changed from these onboarding links.',
);

foreach ( $required_fragments as $fragment ) {
	if ( false === strpos( $source, $fragment ) ) {
		WP_CLI::error( 'Missing buyer-onboarding contract fragment: ' . $fragment );
	}
}

$forbidden_fragments = array(
	'Import Modern Grocery',
	'Install Elementor',
	'100% ready to launch',
	'automatically configured payments',
);

foreach ( $forbidden_fragments as $fragment ) {
	if ( false !== strpos( $source, $fragment ) ) {
		WP_CLI::error( 'Unsafe onboarding claim/action found: ' . $fragment );
	}
}

WP_CLI::success( 'Verified buyer onboarding remains guided, non-destructive and WordPress/WooCommerce-native.' );
