<?php
/**
 * Unit tests for ListRepository.
 *
 * Uses Mockery to stub $wpdb so no database is required.
 *
 * @package StorefrontCore\Tests\ShoppingList
 */

declare( strict_types=1 );

namespace StorefrontCore\Tests\ShoppingList;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use StorefrontCore\ShoppingList\ListRepository;

final class ListRepositoryTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	private function makeWpdb( string $prefix = 'wp_' ): object {
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = $prefix;
		return $wpdb;
	}

	/** @test */
	public function it_returns_empty_array_when_no_rows(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'PREPARED_SQL' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( [] );

		$GLOBALS['wpdb'] = $wpdb;

		Functions\when( 'current_time' )->justReturn( '2026-08-23 00:00:00' );

		$repo  = new ListRepository();
		$items = $repo->get( 1 );

		$this->assertSame( [], $items );
	}

	/** @test */
	public function it_maps_db_rows_to_typed_arrays(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'PREPARED_SQL' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( [
			[ 'product_id' => '42', 'variation_id' => '0', 'added_at' => '2026-08-01 10:00:00' ],
		] );

		$GLOBALS['wpdb'] = $wpdb;

		$repo  = new ListRepository();
		$items = $repo->get( 7 );

		$this->assertCount( 1, $items );
		$this->assertSame( 42, $items[0]['product_id'] );
		$this->assertSame( 0, $items[0]['variation_id'] );
	}

	/** @test */
	public function add_returns_true_on_new_insert(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'PREPARED_SQL' );
		// count() query.
		$wpdb->shouldReceive( 'get_var' )->andReturn( '5' );
		// INSERT IGNORE.
		$wpdb->shouldReceive( 'query' )->andReturn( 1 );

		$GLOBALS['wpdb'] = $wpdb;

		Functions\when( 'current_time' )->justReturn( '2026-08-23 00:00:00' );

		$repo   = new ListRepository();
		$result = $repo->add( 1, 42, 0 );

		$this->assertTrue( $result );
	}

	/** @test */
	public function add_returns_false_on_duplicate(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'PREPARED_SQL' );
		$wpdb->shouldReceive( 'get_var' )->andReturn( '5' );
		// INSERT IGNORE — 0 rows affected means duplicate.
		$wpdb->shouldReceive( 'query' )->andReturn( 0 );

		$GLOBALS['wpdb'] = $wpdb;

		Functions\when( 'current_time' )->justReturn( '2026-08-23 00:00:00' );

		$repo   = new ListRepository();
		$result = $repo->add( 1, 42, 0 );

		$this->assertFalse( $result );
	}

	/** @test */
	public function add_throws_overflow_exception_at_max_items(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'PREPARED_SQL' );
		$wpdb->shouldReceive( 'get_var' )->andReturn( (string) ListRepository::MAX_ITEMS );

		$GLOBALS['wpdb'] = $wpdb;

		$this->expectException( \OverflowException::class );

		$repo = new ListRepository();
		$repo->add( 1, 99, 0 );
	}

	/** @test */
	public function remove_returns_true_when_row_deleted(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'delete' )->andReturn( 1 );

		$GLOBALS['wpdb'] = $wpdb;

		$repo   = new ListRepository();
		$result = $repo->remove( 1, 42, 0 );

		$this->assertTrue( $result );
	}

	/** @test */
	public function remove_returns_false_when_row_not_found(): void {
		$wpdb = $this->makeWpdb();
		$wpdb->shouldReceive( 'delete' )->andReturn( 0 );

		$GLOBALS['wpdb'] = $wpdb;

		$repo   = new ListRepository();
		$result = $repo->remove( 1, 999, 0 );

		$this->assertFalse( $result );
	}

	/** @test */
	public function get_is_scoped_to_the_requesting_user(): void {
		$wpdb = $this->makeWpdb();

		$capturedSql = null;
		$wpdb->shouldReceive( 'prepare' )
			->andReturnUsing( function ( string $sql, ...$args ) use ( &$capturedSql ): string {
				$capturedSql = $sql;
				return 'PREPARED';
			} );
		$wpdb->shouldReceive( 'get_results' )->andReturn( [] );

		$GLOBALS['wpdb'] = $wpdb;

		$repo = new ListRepository();
		$repo->get( 7 );

		// The SQL template must reference the user_id placeholder.
		$this->assertStringContainsString( 'user_id', $capturedSql );
	}
}
