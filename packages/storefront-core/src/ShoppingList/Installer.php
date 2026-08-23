<?php
/**
 * Shopping List database installer.
 *
 * Creates and updates the shopping list custom table via dbDelta().
 * Registers uninstall and GDPR erasure hooks.
 *
 * Why custom table (not user_meta):
 *  - YITH Wishlist and TI Wishlist both use custom tables because serialized
 *    arrays in user_meta are opaque to the DB engine and degrade at scale.
 *  - Clean rows allow efficient batch product-truth resolution at render time.
 *  - Migrating from user_meta to a table later would break customer list data.
 *
 * @package StorefrontCore\ShoppingList
 */

declare( strict_types=1 );

namespace StorefrontCore\ShoppingList;

/**
 * Manages the {prefix}storefront_shopping_list table lifecycle.
 */
final class Installer {

	/** Current schema version. Bump when altering the table schema. */
	private const SCHEMA_VERSION     = 1;
	private const SCHEMA_VERSION_KEY = 'storefront_core_shopping_list_db_version';
	private const TABLE_SUFFIX       = 'storefront_shopping_list';

	/**
	 * Run on plugin activation.
	 *
	 * Called via register_activation_hook() in the plugin bootstrap.
	 */
	public function activate(): void {
		$this->maybe_create_or_upgrade_table();
	}

	/**
	 * Create or upgrade the shopping list table.
	 *
	 * Safe to call multiple times — dbDelta() handles idempotency.
	 */
	public function maybe_create_or_upgrade_table(): void {
		global $wpdb;

		$installed = (int) get_option( self::SCHEMA_VERSION_KEY, 0 );
		if ( $installed >= self::SCHEMA_VERSION ) {
			return;
		}

		$table          = $wpdb->prefix . self::TABLE_SUFFIX;
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Schema:
		 *  id            — auto-increment surrogate PK.
		 *  user_id       — WordPress user ID (scoped per blog in multisite via $wpdb->prefix).
		 *  product_id    — WooCommerce product ID.
		 *  variation_id  — WooCommerce variation ID; 0 for simple/non-variable products.
		 *  added_at      — UTC timestamp of when the item was saved.
		 *
		 * Indexes:
		 *  UNIQUE KEY user_product — prevents duplicates; makes add idempotent (INSERT IGNORE).
		 *  KEY user_id             — fast per-user list retrieval.
		 */
		$sql = "CREATE TABLE {$table} (
			id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id       BIGINT(20) UNSIGNED NOT NULL,
			product_id    BIGINT(20) UNSIGNED NOT NULL,
			variation_id  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			added_at      DATETIME            NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_product (user_id, product_id, variation_id),
			KEY user_id (user_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::SCHEMA_VERSION_KEY, self::SCHEMA_VERSION, false );
	}

	/**
	 * Drop the table and remove options on plugin uninstall.
	 *
	 * Called from the static uninstall hook registered in the plugin bootstrap.
	 * Only runs if "Delete data on uninstall" is explicitly configured.
	 */
	public static function uninstall(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				'DROP TABLE IF EXISTS %i',
				$wpdb->prefix . self::TABLE_SUFFIX
			)
		);

		delete_option( self::SCHEMA_VERSION_KEY );
	}

	/**
	 * Erase a user's shopping list data on GDPR erase request.
	 *
	 * Hooked to `wp_privacy_personal_data_erasers` in the plugin bootstrap.
	 *
	 * @param string $email  User's email address.
	 * @param int    $page   Paged erasure (not needed for single-query erase).
	 * @return array{items_removed: bool, items_retained: bool, messages: string[], done: bool}
	 */
	public static function gdpr_erase( string $email, int $page = 1 ): array {
		global $wpdb;

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return [
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => [],
				'done'           => true,
			];
		}

		$table   = $wpdb->prefix . self::TABLE_SUFFIX;
		$deleted = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			[ 'user_id' => $user->ID ],
			[ '%d' ]
		);

		return [
			'items_removed'  => (bool) $deleted,
			'items_retained' => false,
			'messages'       => $deleted ? [ __( 'Shopping list data erased.', 'bhaivatech-storefront-alpha' ) ] : [],
			'done'           => true,
		];
	}
}
