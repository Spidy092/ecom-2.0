<?php
/**
 * Remove Grovia Core settings and user-scoped list data on explicit uninstall.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'StorefrontCore\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}
		$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

if ( class_exists( 'StorefrontCore\\ShoppingList\\Installer' ) ) {
	\StorefrontCore\ShoppingList\Installer::uninstall();
}

delete_option( 'bhaivatech_storefront_setup' );
delete_option( 'bhaivatech_storefront_delivery_postcodes' );
delete_transient( 'storefront_delivery_notice_shown' );
