<?php
declare(strict_types=1);

namespace StorefrontCore\Tests\Delivery;

use PHPUnit\Framework\TestCase;
use StorefrontCore\Delivery\ServiceabilityChecker;
use Brain\Monkey;

/**
 * Unit test suite for ServiceabilityChecker matching exact method signatures.
 */
class ServiceabilityCheckerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test is_available returns false when WC_Shipping_Zones class is absent.
	 */
	public function test_is_available_returns_false_without_woocommerce(): void {
		$checker = new ServiceabilityChecker( 'IN' );
		$result  = $checker->is_available( '560001' );

		$this->assertFalse( $result );
	}

	/**
	 * Test is_available returns true when zone ID > 0.
	 */
	public function test_is_available_returns_true_when_zone_matches(): void {
		if ( ! class_exists( 'WC_Shipping_Zone' ) ) {
			eval( 'class WC_Shipping_Zone { private $id; public function __construct($id) { $this->id = $id; } public function get_id() { return $this->id; } }' );
		}

		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			eval( 'class WC_Shipping_Zones { public static function get_zone_matching_package($pkg) { return new WC_Shipping_Zone(1); } }' );
		}

		$checker = new ServiceabilityChecker( 'IN' );
		$result  = $checker->is_available( '560001' );

		$this->assertTrue( $result );
	}
}
