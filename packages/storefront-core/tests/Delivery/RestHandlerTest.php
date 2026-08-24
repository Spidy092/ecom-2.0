<?php
declare(strict_types=1);

namespace StorefrontCore\Tests\Delivery;

use PHPUnit\Framework\TestCase;
use StorefrontCore\Delivery\RestHandler;
use StorefrontCore\Delivery\PostcodeNormalizer;
use StorefrontCore\Delivery\ServiceabilityChecker;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * PHPUnit test suite for Delivery RestHandler matching exact class signatures.
 */
class RestHandlerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test register() registers route under storefront-core/v1/delivery/check.
	 */
	public function test_register(): void {
		Functions\expect( 'register_rest_route' )
			->once()
			->with(
				'storefront-core/v1',
				'/delivery/check',
				Mockery::type( 'array' )
			);

		$normalizer = new PostcodeNormalizer();
		$checker    = new ServiceabilityChecker( 'IN' );
		$handler    = new RestHandler( $normalizer, $checker );
		$handler->register();

		$this->assertTrue( true );
	}

	/**
	 * Test handle() returns WP_Error when postcode is invalid.
	 */
	public function test_handle_invalid_postcode(): void {
		if ( ! class_exists( 'WP_REST_Request' ) ) {
			eval( 'class WP_REST_Request { public function get_param($k) { return "12!"; } }' );
		}
		if ( ! class_exists( 'WP_Error' ) ) {
			eval( 'class WP_Error { public $code; public $message; public $data; public function __construct($c, $m, $d=[]) { $this->code=$c; $this->message=$m; $this->data=$d; } }' );
		}

		Functions\stubs( [
			'__' => function( $text ) { return $text; },
		] );

		$normalizer = new PostcodeNormalizer();
		$checker    = new ServiceabilityChecker( 'IN' );
		$handler    = new RestHandler( $normalizer, $checker );

		$request  = new \WP_REST_Request();
		$response = $handler->handle( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertSame( 'storefront_invalid_postcode', $response->code );
	}
}
