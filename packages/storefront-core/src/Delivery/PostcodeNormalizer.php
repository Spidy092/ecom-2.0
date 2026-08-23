<?php
/**
 * Postcode normalizer.
 *
 * Pure value class — no WordPress dependencies; safe to unit-test without stubs.
 *
 * @package StorefrontCore\Delivery
 */

declare( strict_types=1 );

namespace StorefrontCore\Delivery;

/**
 * Normalizes raw postcode input before any lookup.
 *
 * Rules applied:
 * - Strip leading/trailing whitespace.
 * - Collapse internal whitespace (e.g. "SW1A 2AA" → "SW1A2AA").
 * - Uppercase (postcode matching is case-insensitive in WooCommerce).
 * - Reject if longer than MAX_LENGTH characters after normalization.
 * - Reject if it contains characters outside [A-Z0-9-] after normalization.
 */
final class PostcodeNormalizer {

	private const MAX_LENGTH = 10;
	private const PATTERN    = '/^[A-Z0-9\-]{1,10}$/';

	/**
	 * Normalize a raw postcode string.
	 *
	 * @param string $raw Raw postcode from user input.
	 * @return string|null Normalized postcode, or null if invalid.
	 */
	public function normalize( string $raw ): ?string {
		// Remove all whitespace (leading, trailing, internal).
		$normalized = preg_replace( '/\s+/', '', $raw );
		if ( null === $normalized ) {
			return null;
		}

		$normalized = strtoupper( $normalized );

		if ( strlen( $normalized ) > self::MAX_LENGTH ) {
			return null;
		}

		if ( ! preg_match( self::PATTERN, $normalized ) ) {
			return null;
		}

		return $normalized;
	}
}
