<?php
declare(strict_types=1);

namespace StorefrontCore\Tests\Admin;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/' );
}

require_once __DIR__ . '/../../includes/class-setup-wizard.php';

/** Regression coverage for the setup-wizard hook contract. */
final class SetupWizardTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_register_attaches_each_setup_hook_once(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'admin_menu', \Mockery::type( 'array' ), 30 );
		Functions\expect( 'add_action' )
			->once()
			->with( 'admin_post_bhaivatech_storefront_setup', \Mockery::type( 'array' ) );

		$wizard = new \BhaivaTech\Storefront\Setup_Wizard();
		$wizard->register();

		$this->assertTrue( true );
	}
}
