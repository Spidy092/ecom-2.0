<?php
/**
 * Shopping List REST controller.
 *
 * Namespace:  storefront-core/v1
 * Base route: /shopping-list
 *
 * Security:
 *  - All routes require is_user_logged_in().
 *  - User identity is always derived from get_current_user_id() — never from
 *    the request body or query string.
 *  - State-changing routes (POST, DELETE) require the standard WP REST nonce
 *    in X-WP-Nonce header (verified by the REST infrastructure automatically).
 *  - Product IDs are validated to exist in WooCommerce before insertion.
 *
 * @package StorefrontCore\ShoppingList
 */

declare( strict_types=1 );

namespace StorefrontCore\ShoppingList;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Shopping List REST controller.
 */
final class RestController {

	private ListRepository $repository;
	private string $namespace = 'storefront-core/v1';
	private string $rest_base = 'shopping-list';

	public function __construct( ListRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// GET /storefront-core/v1/shopping-list — retrieve current user's list.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'require_auth' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		// POST /storefront-core/v1/shopping-list/items — add item.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/items',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'require_auth' ],
					'args'                => [
						'product_id'   => [
							'type'     => 'integer',
							'required' => true,
							'minimum'  => 1,
						],
						'variation_id' => [
							'type'    => 'integer',
							'default' => 0,
							'minimum' => 0,
						],
					],
				],
			]
		);

		// DELETE /storefront-core/v1/shopping-list/items/{product_id} — remove item.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/items/(?P<product_id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'require_auth' ],
					'args'                => [
						'product_id'   => [
							'type'    => 'integer',
							'minimum' => 1,
						],
						'variation_id' => [
							'type'    => 'integer',
							'default' => 0,
							'minimum' => 0,
						],
					],
				],
			]
		);
	}

	/**
	 * Permission callback — all list routes require an authenticated user.
	 */
	public function require_auth(): bool {
		return is_user_logged_in();
	}

	/**
	 * GET /shopping-list — return list with current WooCommerce product truth resolved.
	 */
	public function get_items( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id(); // Server-derived — never client-supplied.
		$rows    = $this->repository->get( $user_id );
		$items   = [];

		foreach ( $rows as $row ) {
			$product = wc_get_product( $row['product_id'] );

			// Resolve current product truth from WooCommerce.
			if ( ! $product ) {
				$items[] = [
					'product_id'   => $row['product_id'],
					'variation_id' => $row['variation_id'],
					'name'         => null,
					'available'    => false,
					'removed'      => true,
					'added_at'     => $row['added_at'],
				];
				continue;
			}

			$purchasable = $product->is_purchasable() && $product->is_in_stock();

			$items[] = [
				'product_id'   => $row['product_id'],
				'variation_id' => $row['variation_id'],
				'name'         => $product->get_name(),
				'available'    => $purchasable,
				'removed'      => false,
				'added_at'     => $row['added_at'],
			];
		}

		return new WP_REST_Response( [ 'items' => $items ], 200 );
	}

	/**
	 * POST /shopping-list/items — add a product to the list.
	 */
	public function create_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id      = get_current_user_id();
		$product_id   = (int) $request->get_param( 'product_id' );
		$variation_id = (int) $request->get_param( 'variation_id' );

		// Validate product exists in WooCommerce.
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return new WP_Error(
				'storefront_invalid_product',
				__( 'Product not found.', 'bhaivatech-storefront-alpha' ),
				[ 'status' => 400 ]
			);
		}

		try {
			$this->repository->add( $user_id, $product_id, $variation_id );
		} catch ( \OverflowException $e ) {
			return new WP_Error(
				'storefront_list_full',
				$e->getMessage(),
				[ 'status' => 422 ]
			);
		}

		return new WP_REST_Response( [ 'added' => true ], 201 );
	}

	/**
	 * DELETE /shopping-list/items/{product_id} — remove an item.
	 */
	public function delete_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id      = get_current_user_id();
		$product_id   = (int) $request->get_param( 'product_id' );
		$variation_id = (int) ( $request->get_param( 'variation_id' ) ?? 0 );

		$removed = $this->repository->remove( $user_id, $product_id, $variation_id );

		return new WP_REST_Response( [ 'removed' => $removed ], 200 );
	}

	/**
	 * Schema for list items.
	 */
	public function get_public_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'storefront-shopping-list',
			'type'       => 'object',
			'properties' => [
				'items' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'product_id'   => [ 'type' => 'integer' ],
							'variation_id' => [ 'type' => 'integer' ],
							'name'         => [ 'type' => [ 'string', 'null' ] ],
							'available'    => [ 'type' => 'boolean' ],
							'removed'      => [ 'type' => 'boolean' ],
							'added_at'     => [ 'type' => 'string', 'format' => 'date-time' ],
						],
					],
				],
			],
		];
	}
}
