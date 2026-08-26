<?php
/**
 * Render callback for Product Quick Add block.
 *
 * Outputs a compact quantity stepper with:
 * - Accessible labels and live status region
 * - Interactivity API context for client-side state
 * - Stock quantity ceiling from WooCommerce product data
 * - data-state attribute for CSS visual states
 *
 * @package StorefrontCore
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

global $product;

$product_id = absint( $attributes['productId'] ?? 0 );
if ( ! $product_id && is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) {
	$product_id = absint( $product->get_id() );
}
if ( ! $product_id ) {
	return;
}

// Resolve current product truth from WooCommerce product data.
$stock_quantity  = 9999;
$wc_product      = wc_get_product( $product_id );
$render_fallback = ! empty( $attributes['renderFallback'] );
if ( ! $wc_product ) {
	return;
}

$is_simple      = is_callable( array( $wc_product, 'is_type' ) ) && $wc_product->is_type( 'simple' );
$is_purchasable = is_callable( array( $wc_product, 'is_purchasable' ) ) && $wc_product->is_purchasable();
$is_in_stock    = is_callable( array( $wc_product, 'is_in_stock' ) ) && $wc_product->is_in_stock();

// The ledger uses this same block for safe non-simple/unavailable states.
// Keep the historical early-return behavior when the fallback attribute is
// not requested so the block remains useful as a standalone quick-add.
if ( ! $is_simple || ! $is_purchasable || ! $is_in_stock ) {
	if ( ! $render_fallback ) {
		return;
	}

	$product_name = (string) $wc_product->get_name();
	if ( ! $is_purchasable || ! $is_in_stock ) {
		$availability_label = $is_in_stock
			? esc_html__( 'Unavailable', 'storefront-core' )
			: esc_html__( 'Out of stock', 'storefront-core' );
		printf(
			'<span class="storefront-quick-add-fallback storefront-quick-add-fallback--unavailable" role="status" aria-label="%s">%s</span>',
			esc_attr( $product_name . ': ' . wp_strip_all_tags( $availability_label ) ),
			$availability_label
		);
		return;
	}

	if ( ! $is_simple ) {
		$product_url = (string) $wc_product->get_permalink();
		if ( '' === $product_url ) {
			return;
		}

		printf(
			'<a class="storefront-quick-add-fallback storefront-quick-add-fallback--options" href="%s">%s</a>',
			esc_url( $product_url ),
			esc_html__( 'Choose options', 'storefront-core' )
		);
		return;
	}
}

if ( $wc_product->managing_stock() && $wc_product->get_stock_quantity() > 0 ) {
	$stock_quantity = (int) $wc_product->get_stock_quantity();
}

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class'      => 'storefront-quick-add-block',
		'data-state' => 'idle',
	]
);

$context = [
	'productId'     => (int) $product_id,
	'quantity'      => 1,
	'stockQuantity' => $stock_quantity,
	'isBusy'        => false,
	'added'         => false,
	'error'         => false,
];

$product_name = (string) $wc_product->get_name();
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="storefrontCore/quickAdd"
	<?php echo function_exists( 'wp_interactivity_data_wp_context' ) ? wp_interactivity_data_wp_context( $context ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	aria-label="<?php printf( /* translators: %s product name */ esc_attr__( 'Add %s to cart', 'storefront-core' ), esc_attr( $product_name ) ); ?>"
>
	<div class="storefront-quick-add-stepper">
		<button
			type="button"
			class="storefront-quick-add-btn storefront-quick-add-btn--minus"
			aria-label="<?php esc_attr_e( 'Decrease quantity', 'storefront-core' ); ?>"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="context.quantity <= 1 || context.isBusy"
		>&minus;</button>

		<span
			class="storefront-quick-add-qty"
			aria-label="<?php esc_attr_e( 'Quantity', 'storefront-core' ); ?>"
			data-wp-text="context.quantity"
		>1</span>

		<button
			type="button"
			class="storefront-quick-add-btn storefront-quick-add-btn--plus"
			aria-label="<?php esc_attr_e( 'Increase quantity', 'storefront-core' ); ?>"
			data-wp-on--click="actions.increment"
			data-wp-bind--disabled="context.isBusy || context.quantity >= context.stockQuantity"
		>&plus;</button>

		<button
			type="button"
			class="storefront-quick-add-submit"
			data-wp-on--click="actions.addToCart"
			data-wp-bind--disabled="context.isBusy"
		>
			<span data-wp-bind--hidden="context.added || context.error"><?php esc_html_e( 'Add', 'storefront-core' ); ?></span>
			<span data-wp-bind--hidden="!context.added"><?php esc_html_e( 'Added', 'storefront-core' ); ?></span>
			<span data-wp-bind--hidden="!context.error"><?php esc_html_e( 'Retry', 'storefront-core' ); ?></span>
		</button>

		<p class="storefront-quick-add-status" role="status" aria-live="polite" aria-atomic="true" data-level=""></p>
	</div>
</div>
