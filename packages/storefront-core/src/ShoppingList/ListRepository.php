<?php
/**
 * Shopping List repository.
 *
 * All DB access goes through $wpdb->prepare() — no raw interpolation.
 * User identity always comes from server context, never from client input.
 *
 * @package StorefrontCore\ShoppingList
 */

declare( strict_types=1 );

namespace StorefrontCore\ShoppingList;

/**
 * CRUD for the storefront_shopping_list table.
 */
class ListRepository {

	/** Max items per user list. Prevents unbounded growth. */
	public const MAX_ITEMS = 200;

	private string $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'storefront_shopping_list';
	}

	/**
	 * Retrieve all items for a user.
	 *
	 * Does NOT return price/stock — those must be resolved from WooCommerce at render time.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array<int, array{product_id: int, variation_id: int, added_at: string}>
	 */
	public function get( int $user_id ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT product_id, variation_id, added_at FROM %i WHERE user_id = %d ORDER BY added_at DESC',
				$this->table,
				$user_id
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return [];
		}

		return array_map(
			static function ( array $row ): array {
				return [
					'product_id'   => (int) $row['product_id'],
					'variation_id' => (int) $row['variation_id'],
					'added_at'     => $row['added_at'],
				];
			},
			$rows
		);
	}

	/**
	 * Add an item to the list. Idempotent — duplicate adds (same user+product+variation) are silently ignored.
	 *
	 * @param int $user_id      WordPress user ID.
	 * @param int $product_id   WooCommerce product ID.
	 * @param int $variation_id WooCommerce variation ID; 0 for simple products.
	 * @return bool True if item was inserted, false if duplicate or error.
	 * @throws \OverflowException If the list is at MAX_ITEMS.
	 */
	public function add( int $user_id, int $product_id, int $variation_id = 0 ): bool {
		if ( $this->count( $user_id ) >= self::MAX_ITEMS ) {
			throw new \OverflowException(
				sprintf( 'Shopping list is full (max %d items).', self::MAX_ITEMS )
			);
		}

		global $wpdb;

		// INSERT IGNORE relies on UNIQUE KEY (user_id, product_id, variation_id).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->query(
			$wpdb->prepare(
				'INSERT IGNORE INTO %i (user_id, product_id, variation_id, added_at) VALUES (%d, %d, %d, %s)',
				$this->table,
				$user_id,
				$product_id,
				$variation_id,
				current_time( 'mysql', true ) // UTC.
			)
		);

		return 1 === $inserted;
	}

	/**
	 * Remove a specific item from the list.
	 *
	 * @param int $user_id      WordPress user ID.
	 * @param int $product_id   WooCommerce product ID.
	 * @param int $variation_id WooCommerce variation ID; 0 for simple products.
	 * @return bool True if a row was deleted.
	 */
	public function remove( int $user_id, int $product_id, int $variation_id = 0 ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete(
			$this->table,
			[
				'user_id'      => $user_id,
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
			],
			[ '%d', '%d', '%d' ]
		);

		return false !== $deleted && $deleted > 0;
	}

	/**
	 * Remove all items for a user. Used by uninstall/GDPR erase.
	 *
	 * @param int $user_id WordPress user ID.
	 */
	public function clear( int $user_id ): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->table,
			[ 'user_id' => $user_id ],
			[ '%d' ]
		);
	}

	/**
	 * Count how many items the user has saved.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return int
	 */
	public function count( int $user_id ): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE user_id = %d',
				$this->table,
				$user_id
			)
		);
	}
}
