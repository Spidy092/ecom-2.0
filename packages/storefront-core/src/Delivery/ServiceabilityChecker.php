<?php
/**
 * Serviceability checker.
 *
 * Delegates postcode matching to WooCommerce Shipping Zones via the public
 * WC_Shipping_Zones::get_zone_matching_package() API. This is the single
 * source of delivery truth — no parallel postcode list is maintained.
 *
 * @package StorefrontCore\Delivery
 */

declare( strict_types=1 );

namespace StorefrontCore\Delivery;

/**
 * Checks whether a normalized postcode is serviceable.
 *
 * WooCommerce zone matching supports:
 *  - Exact postcodes (e.g. "560001")
 *  - Wildcards (e.g. "560*")
 *  - Ranges (e.g. "560001...560099")
 *
 * Zone ID 0 = "Rest of the World" (no configured named zone).
 * We treat zone 0 as "unavailable" because most grocery stores that have not
 * explicitly configured delivery to an area should not imply availability.
 *
 * Edge case: if a store owner adds postcodes to the Rest of World zone, this
 * checker will show them as unavailable. The Admin notice class informs owners
 * to use named zones instead.
 */
final class ServiceabilityChecker {

	/**
	 * Store base country code used when querying WooCommerce zones.
	 * Retrieved from WooCommerce settings at query time.
	 */
	private string $country;

	public function __construct( string $country = '' ) {
		$this->country = $country;
	}

	/**
	 * Check whether a normalized postcode falls within a configured WC Shipping Zone.
	 *
	 * @param string $normalized_postcode Normalized postcode from PostcodeNormalizer.
	 * @return bool True = delivery available.
	 */
	public function is_available( string $normalized_postcode ): bool {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return false;
		}

		$country = $this->country ?: $this->store_base_country();

		/**
		 * WC_Shipping_Zones::get_zone_matching_package() is a public, stable
		 * WooCommerce API. It handles wildcards and ranges natively.
		 *
		 * @see WC_Shipping_Zones::get_zone_matching_package()
		 */
		$zone = \WC_Shipping_Zones::get_zone_matching_package(
			[
				'destination' => [
					'country'  => $country,
					'state'    => '',
					'postcode' => $normalized_postcode,
				],
			]
		);

		// Zone ID 0 = Rest of World (no match) → unavailable.
		return $zone instanceof \WC_Shipping_Zone && $zone->get_id() > 0;
	}

	/**
	 * Retrieve the store base country from WooCommerce settings.
	 *
	 * @return string ISO 3166-1 alpha-2 country code.
	 */
	private function store_base_country(): string {
		if ( function_exists( 'WC' ) && WC()->countries instanceof \WC_Countries ) {
			return WC()->countries->get_base_country();
		}
		return 'IN'; // Safe fallback for India-first demo; does not affect zone matching logic.
	}
}
