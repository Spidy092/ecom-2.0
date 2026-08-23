<?php
declare(strict_types=1);

namespace StorefrontCore\Tests\ShoppingList;

use PHPUnit\Framework\TestCase;
use StorefrontCore\ShoppingList\RestController;
use StorefrontCore\ShoppingList\ListRepository;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * PHPUnit test suite for ShoppingList RestController.
 */
class RestControllerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test require_auth returns false when user is not logged in.
	 */
	public function test_check_permission_unauthenticated(): void {
		Functions\expect( 'is_user_logged_in' )
			->once()
			->andReturn( false );

		$repo       = \Mockery::mock( ListRepository::class );
		$controller = new RestController( $repo );

		$result = $controller->require_auth();
		$this->assertFalse( $result );
	}

	/**
	 * Test require_auth returns true when user is logged in.
	 */
	public function test_check_permission_authenticated(): void {
		Functions\expect( 'is_user_logged_in' )
			->once()
			->andReturn( true );

		$repo       = \Mockery::mock( ListRepository::class );
		$controller = new RestController( $repo );

		$result = $controller->require_auth();
		$this->assertTrue( $result );
	}

	/**
	 * Test register_routes registers all 3 routes under storefront-core/v1.
	 */
	public function test_register_routes(): void {
		Functions\expect( 'register_rest_route' )
			->times( 3 )
			->with(
				'storefront-core/v1',
				\Mockery::type( 'string' ),
				\Mockery::type( 'array' )
			);

		$repo       = \Mockery::mock( ListRepository::class );
		$controller = new RestController( $repo );
		$controller->register_routes();

		$this->assertTrue( true );
	}
}
