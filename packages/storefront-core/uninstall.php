<?php
/**
 * Clean up all Storefront Core data on explicit plugin uninstall.
 *
 * This file runs ONLY when the plugin is deleted through the WordPress admin.
 * It removes:
 * - Custom database table (shopping list)
 * - All plugin-owned options
 * - All plugin-owned transients
 * - Flushes rewrite rules (buy-again endpoint)
 *
 * Security: WordPress calls this file directly; WP_UNINSTALL_PLUGIN is
 * defined only in that context. No capability check is needed here because
 * WordPress already verifies delete_plugins capability before invoking.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// ---------------------------------------------------------------------------
// Autoloader for namespaced classes used during cleanup.
// ---------------------------------------------------------------------------

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'StorefrontCore\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}
		$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

// ---------------------------------------------------------------------------
// 1. Drop the Shopping List custom table and schema version option.
// ---------------------------------------------------------------------------

if ( class_exists( 'StorefrontCore\\ShoppingList\\Installer' ) ) {
	\StorefrontCore\ShoppingList\Installer::uninstall();
}

// ---------------------------------------------------------------------------
// 2. Remove all plugin-owned options.
// ---------------------------------------------------------------------------

$storefront_core_options = array(
	'bhaivatech_storefront_setup',               // Setup wizard state.
	'bhaivatech_storefront_delivery_postcodes',  // Legacy postcode option (pre D-027).
	'storefront_core_shopping_list_db_version',  // Table schema version (also cleaned by Installer but defensive).
);

foreach ( $storefront_core_options as $option_key ) {
	delete_option( $option_key );
}

// ---------------------------------------------------------------------------
// 3. Remove all plugin-owned transients.
// ---------------------------------------------------------------------------

$storefront_core_transients = array(
	'storefront_delivery_notice_shown',  // Delivery notice dismissal state.
);

foreach ( $storefront_core_transients as $transient_key ) {
	delete_transient( $transient_key );
}

// ---------------------------------------------------------------------------
// 4. Flush rewrite rules to remove the buy-again endpoint registration.
// ---------------------------------------------------------------------------

flush_rewrite_rules();

// ---------------------------------------------------------------------------
// 5. Multisite support: repeat cleanup for each site if network-wide.
// ---------------------------------------------------------------------------

if ( is_multisite() ) {
	// Get all sites and clean up per-site data.
	$sites = get_sites(
		array(
			'number' => 0,
			'fields' => 'ids',
		)
	);

	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );

		// Drop table per site (prefix changes).
		if ( class_exists( 'StorefrontCore\\ShoppingList\\Installer' ) ) {
			\StorefrontCore\ShoppingList\Installer::uninstall();
		}

		// Clean per-site options.
		foreach ( $storefront_core_options as $option_key ) {
			delete_option( $option_key );
		}

		// Clean per-site transients.
		foreach ( $storefront_core_transients as $transient_key ) {
			delete_transient( $transient_key );
		}

		restore_current_blog();
	}
}
