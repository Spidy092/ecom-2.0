<?php
/**
 * Seed deterministic WooCommerce shipping-zone fixtures for serviceability E2E.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before seeding serviceability fixtures.' );
}

// Limit shipping countries so missing-country and unsupported-country behavior
// is deterministic and independent from the runner/store defaults.
update_option( 'woocommerce_ship_to_countries', 'specific' );
update_option( 'woocommerce_specific_ship_to_countries', array( 'IN', 'US', 'GB' ) );

// Remove all explicit zones and their method-instance settings.
foreach ( WC_Shipping_Zones::get_shipping_zones() as $zone ) {
	WC_Shipping_Zones::delete_zone( $zone->get_id() );
}

// Reset the built-in zone 0 (Locations not covered by other zones).
$zone_zero = new WC_Shipping_Zone( 0 );
foreach ( $zone_zero->get_shipping_methods( false ) as $method ) {
	$zone_zero->delete_shipping_method( (int) $method->instance_id );
}
$zone_zero->add_shipping_method( 'flat_rate' );

/**
 * Create one explicit shipping zone.
 *
 * @param string                          $name Zone name.
 * @param int                             $order Zone order.
 * @param array<int,array{code:string,type:string}> $locations Locations.
 * @param string                          $method_id Shipping method ID.
 * @return WC_Shipping_Zone
 */
function bhaivatech_seed_shipping_zone( string $name, int $order, array $locations, string $method_id ): WC_Shipping_Zone {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( $name );
	$zone->set_zone_order( $order );

	foreach ( $locations as $location ) {
		$zone->add_location( $location['code'], $location['type'] );
	}

	$zone->save();
	$instance_id = $zone->add_shipping_method( $method_id );

	if ( ! $instance_id ) {
		WP_CLI::error( sprintf( 'Could not add %s to zone %s.', $method_id, $name ) );
	}

	return $zone;
}

bhaivatech_seed_shipping_zone(
	'Alpha India Delivery',
	1,
	array(
		array( 'code' => 'IN', 'type' => 'country' ),
		array( 'code' => '560001...560099', 'type' => 'postcode' ),
	),
	'flat_rate'
);

bhaivatech_seed_shipping_zone(
	'Alpha California Delivery',
	2,
	array(
		array( 'code' => 'US:CA', 'type' => 'state' ),
	),
	'flat_rate'
);

bhaivatech_seed_shipping_zone(
	'Alpha UK Pickup Only',
	3,
	array(
		array( 'code' => 'GB', 'type' => 'country' ),
	),
	'local_pickup'
);

WC_Cache_Helper::get_transient_version( 'shipping', true );

WP_CLI::success( 'Seeded IN range delivery, US:CA delivery, GB pickup-only, and zone-0 flat-rate fallback.' );
