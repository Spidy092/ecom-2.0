<?php
/**
 * Coarse delivery-area serviceability backed by WooCommerce shipping zones.
 *
 * This endpoint answers geographic service area only. WooCommerce checkout
 * remains authoritative for actual rates, fees and cart-specific availability.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_SERVICEABILITY_POSTCODE_MAX = 32;
const BHAIVATECH_STOREFRONT_SERVICEABILITY_STATE_MAX    = 100;

/**
 * Validate a bounded scalar REST field without imposing a country-specific
 * postcode grammar.
 *
 * @param mixed $value Candidate value.
 * @param int   $max Maximum UTF-8 byte length accepted.
 * @return bool
 */
function bhaivatech_storefront_serviceability_is_bounded_scalar( $value, int $max ): bool {
	if ( null === $value || '' === $value ) {
		return true;
	}

	if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
		return false;
	}

	return strlen( (string) $value ) <= $max;
}

/**
 * Normalize destination fields using the same broad rules Woo uses for zone
 * matching.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function bhaivatech_storefront_serviceability_clean_text( $value ): string {
	return trim( wc_clean( (string) $value ) );
}

/**
 * Return WooCommerce shipping countries.
 *
 * @return array<string,string>
 */
function bhaivatech_storefront_serviceability_shipping_countries(): array {
	if ( ! WC()->countries ) {
		return array();
	}

	return WC()->countries->get_shipping_countries();
}

/**
 * Determine whether configured shipping zones make state necessary for a
 * correct match in the selected country.
 *
 * @param string $country ISO country code.
 * @return bool
 */
function bhaivatech_storefront_serviceability_country_requires_state( string $country ): bool {
	$country = strtoupper( $country );
	$prefix  = $country . ':';

	foreach ( WC_Shipping_Zones::get_shipping_zones() as $zone ) {
		foreach ( $zone->get_zone_locations() as $location ) {
			if (
				'state' === $location->type &&
				str_starts_with( strtoupper( (string) $location->code ), $prefix )
			) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Is a supplied state code valid when WooCommerce has a canonical state list?
 *
 * Some countries use free-form region text, so an empty/false Woo state list
 * is not treated as a validation failure.
 *
 * @param string $country Country code.
 * @param string $state State code.
 * @return bool
 */
function bhaivatech_storefront_serviceability_state_is_valid( string $country, string $state ): bool {
	if ( '' === $state || ! WC()->countries ) {
		return true;
	}

	$states = WC()->countries->get_states( $country );

	if ( ! is_array( $states ) || array() === $states ) {
		return true;
	}

	return isset( $states[ $state ] );
}

/**
 * Default classification for whether one enabled shipping method represents
 * delivery rather than pickup.
 *
 * Third-party integrations can override the result with the documented filter
 * instead of requiring a hardcoded courier-plugin list in Storefront Core.
 *
 * @param WC_Shipping_Method $method Shipping method.
 * @param WC_Shipping_Zone   $zone Matched zone.
 * @param array              $package Minimal destination package.
 * @return bool
 */
function bhaivatech_storefront_serviceability_method_counts_as_delivery( WC_Shipping_Method $method, WC_Shipping_Zone $zone, array $package ): bool {
	$pickup_ids  = array( 'local_pickup', 'legacy_local_pickup' );
	$is_pickup   = in_array( (string) $method->id, $pickup_ids, true ) || $method->supports( 'local-pickup' );
	$is_delivery = ! $is_pickup;

	/**
	 * Filters whether an enabled method should count as delivery for the coarse
	 * pre-shopping serviceability check.
	 *
	 * This does not change WooCommerce checkout/rate behavior.
	 *
	 * @param bool               $is_delivery Default classification.
	 * @param WC_Shipping_Method $method Shipping method instance.
	 * @param WC_Shipping_Zone   $zone Matched zone.
	 * @param array              $package Minimal destination package.
	 */
	return (bool) apply_filters(
		'bhaivatech_storefront_serviceability_method_counts_as_delivery',
		$is_delivery,
		$method,
		$zone,
		$package
	);
}

/**
 * Does the matched zone have at least one enabled delivery-capable method?
 *
 * Deliberately do not call method::is_available(). Cart/coupon/minimum-order
 * conditions belong to checkout, not coarse geographic serviceability.
 *
 * @param WC_Shipping_Zone $zone Matched zone.
 * @param array            $package Minimal destination package.
 * @return bool
 */
function bhaivatech_storefront_serviceability_zone_is_served( WC_Shipping_Zone $zone, array $package ): bool {
	foreach ( $zone->get_shipping_methods( true ) as $method ) {
		if (
			$method instanceof WC_Shipping_Method &&
			bhaivatech_storefront_serviceability_method_counts_as_delivery( $method, $zone, $package )
		) {
			return true;
		}
	}

	return false;
}

/**
 * Evaluate coarse serviceability without persisting destination input.
 *
 * @param mixed $country Raw country.
 * @param mixed $state Raw state.
 * @param mixed $postcode Raw postcode.
 * @return array{status:string,required?:string[]}
 */
function bhaivatech_storefront_evaluate_serviceability( $country, $state, $postcode ): array {
	$shipping_countries = bhaivatech_storefront_serviceability_shipping_countries();
	$country            = strtoupper( bhaivatech_storefront_serviceability_clean_text( $country ) );
	$state              = strtoupper( bhaivatech_storefront_serviceability_clean_text( $state ) );
	$postcode           = wc_normalize_postcode( bhaivatech_storefront_serviceability_clean_text( $postcode ) );

	if ( array() === $shipping_countries ) {
		return array( 'status' => 'not_served' );
	}

	if ( '' === $postcode ) {
		return array(
			'status'   => 'needs_more_location',
			'required' => array( 'postcode' ),
		);
	}

	if ( '' === $country ) {
		if ( 1 === count( $shipping_countries ) ) {
			$country = (string) array_key_first( $shipping_countries );
		} else {
			return array(
				'status'   => 'needs_more_location',
				'required' => array( 'country' ),
			);
		}
	}

	if ( ! isset( $shipping_countries[ $country ] ) ) {
		return array( 'status' => 'not_served' );
	}

	if ( ! bhaivatech_storefront_serviceability_state_is_valid( $country, $state ) ) {
		return array(
			'status'   => 'needs_more_location',
			'required' => array( 'state' ),
		);
	}

	if ( '' === $state && bhaivatech_storefront_serviceability_country_requires_state( $country ) ) {
		return array(
			'status'   => 'needs_more_location',
			'required' => array( 'state' ),
		);
	}

	$package = array(
		'destination' => array(
			'country'  => $country,
			'state'    => $state,
			'postcode' => $postcode,
		),
	);

	try {
		$zone = wc_get_shipping_zone( $package );

		if ( ! $zone instanceof WC_Shipping_Zone ) {
			return array( 'status' => 'unknown' );
		}

		return array(
			'status' => bhaivatech_storefront_serviceability_zone_is_served( $zone, $package )
				? 'served'
				: 'not_served',
		);
	} catch ( Throwable $error ) {
		// Serviceability is advisory. Never expose internal zone/method failures
		// or configuration details to a public request.
		return array( 'status' => 'unknown' );
	}
}

/**
 * Public stateless serviceability request.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function bhaivatech_storefront_serviceability_rest( WP_REST_Request $request ) {
	$country  = $request->get_param( 'country' );
	$state    = $request->get_param( 'state' );
	$postcode = $request->get_param( 'postcode' );

	if ( ! bhaivatech_storefront_serviceability_is_bounded_scalar( $country, 2 ) ) {
		return new WP_Error(
			'bhaivatech_serviceability_invalid_country',
			__( 'Country must be a two-letter shipping country code.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => 400 )
		);
	}

	if ( ! bhaivatech_storefront_serviceability_is_bounded_scalar( $state, BHAIVATECH_STOREFRONT_SERVICEABILITY_STATE_MAX ) ) {
		return new WP_Error(
			'bhaivatech_serviceability_invalid_state',
			__( 'State is too long.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => 400 )
		);
	}

	if ( ! bhaivatech_storefront_serviceability_is_bounded_scalar( $postcode, BHAIVATECH_STOREFRONT_SERVICEABILITY_POSTCODE_MAX ) ) {
		return new WP_Error(
			'bhaivatech_serviceability_invalid_postcode',
			__( 'Postcode is too long.', 'bhaivatech-storefront-alpha' ),
			array( 'status' => 400 )
		);
	}

	return rest_ensure_response(
		bhaivatech_storefront_evaluate_serviceability( $country, $state, $postcode )
	);
}

/**
 * Register the public read-only-in-effect serviceability endpoint.
 */
function bhaivatech_storefront_register_serviceability_route(): void {
	register_rest_route(
		'bhaivatech-storefront/v1',
		'/serviceability',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'bhaivatech_storefront_serviceability_rest',
			'permission_callback' => '__return_true',
		)
	);
}
