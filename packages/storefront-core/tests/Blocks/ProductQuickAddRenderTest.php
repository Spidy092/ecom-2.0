<?php
declare( strict_types=1 );

namespace StorefrontCore\Tests\Blocks;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/' );
}

/**
 * Unit tests for the product-quick-add block render callback.
 *
 * Validates that render.php outputs the correct structure, data-wp-context,
 * accessibility attributes, and stock-quantity integration.
 */
final class ProductQuickAddRenderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test render returns empty when productId is 0 and no global product.
	 */
	public function test_render_returns_empty_for_missing_product(): void {
		global $product;
		$product = null;

		$output = $this->invoke_render( [ 'productId' => 0 ] );

		$this->assertNull( $output, 'Should return null/empty when no product ID is available.' );
	}

	/**
	 * Test render returns empty when product is not purchasable.
	 */
	public function test_render_returns_empty_for_non_purchasable_product(): void {
		$mock_product = $this->create_mock_product( 42, 'Test Item', true, false, false, 0 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 42 )
			->andReturn( $mock_product );

		$output = $this->invoke_render( [ 'productId' => 42 ] );

		$this->assertNull( $output, 'Should return null when product is not purchasable.' );
	}

	/**
	 * Test render returns empty when product is out of stock.
	 */
	public function test_render_returns_empty_for_out_of_stock_product(): void {
		$mock_product = $this->create_mock_product( 42, 'Test Item', false, true, false, 0 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 42 )
			->andReturn( $mock_product );

		$output = $this->invoke_render( [ 'productId' => 42 ] );

		$this->assertNull( $output, 'Should return null when product is out of stock.' );
	}

	/**
	 * Test render outputs correct wrapper attributes.
	 */
	public function test_render_outputs_wrapper_attributes(): void {
		$mock_product = $this->create_mock_product( 42, 'Organic Milk', true, true, true, 15 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 42 )
			->andReturn( $mock_product );

		Functions\expect( 'get_block_wrapper_attributes' )
			->once()
			->andReturnUsing( function ( array $attrs ) {
				$parts = [];
				foreach ( $attrs as $key => $value ) {
					$parts[] = $key . '="' . $value . '"';
				}
				return implode( ' ', $parts );
			} );

		Functions\expect( 'wp_interactivity_data_wp_context' )
			->once()
			->andReturnUsing( function ( array $context ) {
				return 'data-wp-context=\'' . json_encode( $context ) . '\'';
			} );

		Functions\stubs( [
			'esc_attr_e' => function ( $text ) { echo $text; },
			'esc_html_e' => function ( $text ) { echo $text; },
			'esc_attr__' => function ( $text ) { return $text; },
			'esc_attr'   => function ( $text ) { return $text; },
			'__'         => function ( $text ) { return $text; },
		] );

		$output = $this->invoke_render( [ 'productId' => 42 ] );

		$this->assertNotNull( $output );
		$this->assertStringContainsString( 'data-state="idle"', $output );
		$this->assertStringContainsString( 'storefront-quick-add-block', $output );
		$this->assertStringContainsString( 'data-wp-interactive="storefrontCore/quickAdd"', $output );
		$this->assertStringContainsString( 'aria-label="Add Organic Milk to cart"', $output );
	}

	/**
	 * Test render outputs correct stock quantity in context.
	 */
	public function test_render_passes_stock_quantity_in_context(): void {
		$mock_product = $this->create_mock_product( 7, 'Rice 1kg', true, true, true, 25 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 7 )
			->andReturn( $mock_product );

		Functions\expect( 'get_block_wrapper_attributes' )
			->once()
			->andReturn( 'class="storefront-quick-add-block" data-state="idle"' );

		$captured_context = null;
		Functions\expect( 'wp_interactivity_data_wp_context' )
			->once()
			->andReturnUsing( function ( array $context ) use ( &$captured_context ) {
				$captured_context = $context;
				return 'data-wp-context=\'' . json_encode( $context ) . '\'';
			} );

		Functions\stubs( [
			'esc_attr_e' => function ( $text ) { echo $text; },
			'esc_html_e' => function ( $text ) { echo $text; },
			'esc_attr__' => function ( $text ) { return $text; },
			'esc_attr'   => function ( $text ) { return $text; },
			'__'         => function ( $text ) { return $text; },
		] );

		$this->invoke_render( [ 'productId' => 7 ] );

		$this->assertNotNull( $captured_context );
		$this->assertSame( 7, $captured_context['productId'] );
		$this->assertSame( 1, $captured_context['quantity'] );
		$this->assertSame( 25, $captured_context['stockQuantity'] );
		$this->assertFalse( $captured_context['isBusy'] );
		$this->assertFalse( $captured_context['added'] );
		$this->assertFalse( $captured_context['error'] );
	}

	/**
	 * Test render uses 9999 as stock ceiling when product does not manage stock.
	 */
	public function test_render_uses_unlimited_stock_when_unmanaged(): void {
		$mock_product = $this->create_mock_product( 9, 'Bananas', true, true, false, 0 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 9 )
			->andReturn( $mock_product );

		Functions\expect( 'get_block_wrapper_attributes' )
			->once()
			->andReturn( 'class="storefront-quick-add-block" data-state="idle"' );

		$captured_context = null;
		Functions\expect( 'wp_interactivity_data_wp_context' )
			->once()
			->andReturnUsing( function ( array $context ) use ( &$captured_context ) {
				$captured_context = $context;
				return 'data-wp-context=\'' . json_encode( $context ) . '\'';
			} );

		Functions\stubs( [
			'esc_attr_e' => function ( $text ) { echo $text; },
			'esc_html_e' => function ( $text ) { echo $text; },
			'esc_attr__' => function ( $text ) { return $text; },
			'esc_attr'   => function ( $text ) { return $text; },
			'__'         => function ( $text ) { return $text; },
		] );

		$this->invoke_render( [ 'productId' => 9 ] );

		$this->assertNotNull( $captured_context );
		$this->assertSame( 9999, $captured_context['stockQuantity'] );
	}

	/**
	 * Test render includes accessible stepper controls.
	 */
	public function test_render_includes_accessible_controls(): void {
		$mock_product = $this->create_mock_product( 5, 'Eggs', true, true, false, 0 );

		Functions\expect( 'wc_get_product' )
			->once()
			->with( 5 )
			->andReturn( $mock_product );

		Functions\expect( 'get_block_wrapper_attributes' )
			->once()
			->andReturn( 'class="storefront-quick-add-block" data-state="idle"' );

		Functions\expect( 'wp_interactivity_data_wp_context' )
			->once()
			->andReturn( 'data-wp-context="{}"' );

		Functions\stubs( [
			'esc_attr_e' => function ( $text ) { echo $text; },
			'esc_html_e' => function ( $text ) { echo $text; },
			'esc_attr__' => function ( $text ) { return $text; },
			'esc_attr'   => function ( $text ) { return $text; },
			'__'         => function ( $text ) { return $text; },
		] );

		$output = $this->invoke_render( [ 'productId' => 5 ] );

		$this->assertNotNull( $output );
		// Accessible labels for stepper buttons.
		$this->assertStringContainsString( 'Decrease quantity', $output );
		$this->assertStringContainsString( 'Increase quantity', $output );
		// Quantity display with label.
		$this->assertStringContainsString( 'aria-label="Quantity"', $output );
		// Status live region.
		$this->assertStringContainsString( 'role="status"', $output );
		$this->assertStringContainsString( 'aria-live="polite"', $output );
		// Submit button labels.
		$this->assertStringContainsString( 'Add', $output );
		$this->assertStringContainsString( 'Added', $output );
		$this->assertStringContainsString( 'Retry', $output );
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Execute the render.php callback with provided attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return string|null Rendered HTML or null if block returned early.
	 */
	private function invoke_render( array $attributes ): ?string {
		$content = '';
		$block   = null;

		// Stub function_exists for wp_interactivity_data_wp_context.
		if ( ! function_exists( 'wp_interactivity_data_wp_context' ) ) {
			// Already stubbed by Brain\Monkey.
		}

		ob_start();
		$result = include __DIR__ . '/../../blocks/product-quick-add/render.php';
		$output = ob_get_clean();

		// render.php uses return; for early exit which evaluates to 1 from include.
		if ( '' === $output && 1 === $result ) {
			return null;
		}

		return $output ?: null;
	}

	/**
	 * Create a mock WooCommerce product.
	 *
	 * @param int    $id              Product ID.
	 * @param string $name            Product name.
	 * @param bool   $in_stock        Whether product is in stock.
	 * @param bool   $purchasable     Whether product is purchasable.
	 * @param bool   $managing_stock  Whether stock is managed.
	 * @param int    $stock_quantity  Stock quantity (when managed).
	 * @return object
	 */
	private function create_mock_product(
		int $id,
		string $name,
		bool $in_stock,
		bool $purchasable,
		bool $managing_stock,
		int $stock_quantity
	): object {
		$product = \Mockery::mock( 'WC_Product' );
		$product->shouldReceive( 'get_id' )->andReturn( $id );
		$product->shouldReceive( 'get_name' )->andReturn( $name );
		$product->shouldReceive( 'is_in_stock' )->andReturn( $in_stock );
		$product->shouldReceive( 'is_purchasable' )->andReturn( $purchasable );
		$product->shouldReceive( 'managing_stock' )->andReturn( $managing_stock );
		$product->shouldReceive( 'get_stock_quantity' )->andReturn( $stock_quantity );

		return $product;
	}
}
