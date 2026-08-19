<?php
/**
 * Buyer-onboarding contract assertions for the Store Setup admin experience.
 */

defined( 'ABSPATH' ) || exit;

$onboarding_file = dirname( __DIR__, 2 ) . '/includes/buyer-onboarding.php';
$source          = file_get_contents( $onboarding_file );

if ( false === $source ) {
	WP_CLI::error( 'Could not read buyer-onboarding.php.' );
}

if ( ! function_exists( 'bhaivatech_storefront_render_buyer_onboarding_page' ) ) {
	WP_CLI::error( 'Buyer onboarding render callback is not loaded by Storefront Core.' );
}

$admin = get_user_by( 'login', 'alpha-setup-admin' );
if ( ! $admin ) {
	WP_CLI::error( 'Buyer onboarding contract requires the seeded setup administrator.' );
}

wp_set_current_user( (int) $admin->ID );

ob_start();
bhaivatech_storefront_render_buyer_onboarding_page();
$html = (string) ob_get_clean();
$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

$required_visible_fragments = array(
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
	'This guide does not certify legal, tax, payment, shipping or business compliance.',
);

foreach ( $required_visible_fragments as $fragment ) {
	if ( false === strpos( $text, $fragment ) ) {
		WP_CLI::error( 'Missing rendered buyer-onboarding contract fragment: ' . $fragment );
	}
}

$required_link_fragments = array(
	'/wp-admin/site-editor.php',
	'post_type=product',
	'page=wc-settings',
	'page=bhaivatech-storefront-setup',
);

foreach ( $required_link_fragments as $fragment ) {
	if ( false === strpos( $html, $fragment ) ) {
		WP_CLI::error( 'Missing buyer-onboarding navigation target: ' . $fragment );
	}
}

if ( false !== stripos( $html, '<form' ) || false !== stripos( $html, '<input' ) ) {
	WP_CLI::error( 'Buyer onboarding must remain navigation-only and may not introduce a persistence form.' );
}

$forbidden_visible_fragments = array(
	'Import Modern Grocery',
	'Install Elementor',
	'100% ready to launch',
	'automatically configured payments',
);

foreach ( $forbidden_visible_fragments as $fragment ) {
	if ( false !== strpos( $text, $fragment ) ) {
		WP_CLI::error( 'Unsafe onboarding claim/action found: ' . $fragment );
	}
}

$forbidden_mutation_calls = array(
	'update_option(',
	'update_post_meta(',
	'wp_insert_post(',
	'wp_update_post(',
	'wp_delete_post(',
	'set_theme_mod(',
	'wp_set_object_terms(',
);

foreach ( $forbidden_mutation_calls as $call ) {
	if ( false !== strpos( $source, $call ) ) {
		WP_CLI::error( 'Buyer onboarding introduced a forbidden content/settings mutation call: ' . $call );
	}
}

WP_CLI::success( 'Verified rendered buyer onboarding remains guided, non-destructive and WordPress/WooCommerce-native.' );
