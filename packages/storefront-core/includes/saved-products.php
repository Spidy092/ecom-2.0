<?php
/**
 * Saved-for-later persistence for authenticated shoppers.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_SAVED_META_KEY = '_bhaivatech_storefront_saved_product_ids';
const BHAIVATECH_STOREFRONT_SAVED_MAX      = 100;

/**
 * Normalize a Saved ID list into unique, positive product IDs with a hard cap.
 *
 * @param mixed $ids Candidate value.
 * @return int[]
 */
function bhaivatech_storefront_normalize_saved_product_ids( $ids ): array {
	if ( ! is_array( $ids ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $ids as $id ) {
		$product_id = absint( $id );
		if ( ! $product_id || isset( $normalized[ $product_id ] ) ) {
			continue;
		}

		$normalized[ $product_id ] = $product_id;

		if ( count( $normalized ) >= BHAIVATECH_STOREFRONT_SAVED_MAX ) {
			break;
		}
	}

	return array_values( $normalized );
}

/**
 * Saved supports published parent products only. Variations are selected later
 * through WooCommerce's product/variation flow and are never persisted here.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function bhaivatech_storefront_saved_product_is_valid( int $product_id ): bool {
	if ( $product_id <= 0 || 'product' !== get_post_type( $product_id ) ) {
		return false;
	}

	if ( 'publish' !== get_post_status( $product_id ) ) {
		return false;
	}

	return (bool) wc_get_product( $product_id );
}

/**
 * Read Saved IDs for one user. Caller decides the user identity; REST callbacks
 * always pass the current authenticated WordPress user.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function bhaivatech_storefront_get_saved_product_ids( int $user_id ): array {
	if ( $user_id <= 0 ) {
		return array();
	}

	return bhaivatech_storefront_normalize_saved_product_ids(
		get_user_meta( $user_id, BHAIVATECH_STOREFRONT_SAVED_META_KEY, true )
	);
}

/**
 * Persist a normalized Saved ID list.
 *
 * @param int   $user_id User ID.
 * @param int[] $ids Product IDs.
 * @return int[] Persisted IDs.
 */
function bhaivatech_storefront_set_saved_product_ids( int $user_id, array $ids ): array {
	$normalized = bhaivatech_storefront_normalize_saved_product_ids( $ids );
	update_user_meta( $user_id, BHAIVATECH_STOREFRONT_SAVED_META_KEY, $normalized );
	return $normalized;
}

/**
 * Require the current authenticated WordPress user for all Saved API access.
 * A REST nonce protects cookie-authenticated requests from CSRF; authorization
 * still comes from the current WordPress identity rather than the nonce itself.
 *
 * @return true|WP_Error
 */
function bhaivatech_storefront_saved_permission() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'bhaivatech_saved_auth_required',
			__( 'Sign in to use account Saved items.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return true;
}

/**
 * Return only the minimum account Saved state required by the storefront.
 *
 * @return WP_REST_Response
 */
function bhaivatech_storefront_saved_get(): WP_REST_Response {
	return rest_ensure_response(
		array( 'ids' => bhaivatech_storefront_get_saved_product_ids( get_current_user_id() ) )
	);
}

/**
 * Add one parent product to the current shopper's Saved list.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function bhaivatech_storefront_saved_add( WP_REST_Request $request ) {
	$product_id = absint( $request['product_id'] );

	if ( ! bhaivatech_storefront_saved_product_is_valid( $product_id ) ) {
		return new WP_Error(
			'bhaivatech_saved_invalid_product',
			__( 'That product cannot be saved.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => 400 )
		);
	}

	$user_id = get_current_user_id();
	$ids     = bhaivatech_storefront_get_saved_product_ids( $user_id );

	if ( in_array( $product_id, $ids, true ) ) {
		return rest_ensure_response( array( 'ids' => $ids ) );
	}

	if ( count( $ids ) >= BHAIVATECH_STOREFRONT_SAVED_MAX ) {
		return new WP_Error(
			'bhaivatech_saved_limit_reached',
			sprintf(
				/* translators: %d: maximum number of saved products. */
				__( 'You can save up to %d products.', 'bhaivatech-storefront-alpha' ),
				BHAIVATECH_STOREFRONT_SAVED_MAX
			),
			array( 'status' => 409 )
		);
	}

	$ids[] = $product_id;

	return rest_ensure_response(
		array( 'ids' => bhaivatech_storefront_set_saved_product_ids( $user_id, $ids ) )
	);
}

/**
 * Remove one product from the current shopper's Saved list.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function bhaivatech_storefront_saved_remove( WP_REST_Request $request ): WP_REST_Response {
	$product_id = absint( $request['product_id'] );
	$user_id    = get_current_user_id();
	$ids        = array_values(
		array_filter(
			bhaivatech_storefront_get_saved_product_ids( $user_id ),
			static function ( int $saved_id ) use ( $product_id ): bool {
				return $saved_id !== $product_id;
			}
		)
	);

	return rest_ensure_response(
		array( 'ids' => bhaivatech_storefront_set_saved_product_ids( $user_id, $ids ) )
	);
}

/**
 * Register private account Saved endpoints.
 */
function bhaivatech_storefront_register_saved_routes(): void {
	register_rest_route(
		'bhaivatech-storefront/v1',
		'/saved-products',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'bhaivatech_storefront_saved_get',
			'permission_callback' => 'bhaivatech_storefront_saved_permission',
		)
	);

	register_rest_route(
		'bhaivatech-storefront/v1',
		'/saved-products/(?P<product_id>\d+)',
		array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'bhaivatech_storefront_saved_add',
				'permission_callback' => 'bhaivatech_storefront_saved_permission',
				'args'                => array(
					'product_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => 'bhaivatech_storefront_saved_remove',
				'permission_callback' => 'bhaivatech_storefront_saved_permission',
				'args'                => array(
					'product_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			),
		)
	);
}
