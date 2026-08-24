<?php
/**
 * Delivery REST handler.
 *
 * Registers GET /storefront-core/v1/delivery/check.
 *
 * Security:
 * - Public endpoint (no authentication required).
 * - Input normalized + validated by PostcodeNormalizer.
 * - Returns only boolean available/unavailable context — no internal config leaked.
 * - Stateless — no session state read or written.
 *
 * @package StorefrontCore\Delivery
 */

declare( strict_types=1 );

namespace StorefrontCore\Delivery;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Delivery availability REST handler.
 */
final class RestHandler {

	private PostcodeNormalizer $normalizer;
	private ServiceabilityChecker $checker;

	public function __construct( PostcodeNormalizer $normalizer, ServiceabilityChecker $checker ) {
		$this->normalizer = $normalizer;
		$this->checker    = $checker;
	}

	/**
	 * Register the REST route on rest_api_init.
	 */
	public function register(): void {
		register_rest_route(
			'storefront-core/v1',
			'/delivery/check',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => '__return_true', // Public — no user data returned.
				'args'                => [
					'postcode' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( string $value ): bool {
							// Additional early-rejection: reject obviously too-long strings
							// before normalization to avoid needless work.
							return strlen( trim( $value ) ) <= 20;
						},
					],
				],
			]
		);
	}

	/**
	 * Handle GET /storefront-core/v1/delivery/check?postcode=…
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$raw      = (string) $request->get_param( 'postcode' );
		$postcode = $this->normalizer->normalize( $raw );

		if ( null === $postcode ) {
			return new WP_Error(
				'storefront_invalid_postcode',
				__( 'Invalid postcode format.', 'bhaivatech-storefront-alpha' ),
				[ 'status' => 400 ]
			);
		}

		$available = $this->checker->is_available( $postcode );

		return new WP_REST_Response(
			[ 'available' => $available ],
			200
		);
	}
}
