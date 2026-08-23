<?php
/**
 * Render callback for Product Quick Add block.
 *
 * @package StorefrontCore
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

global $product;

$product_id = $attributes['productId'] ?? ( $product ? $product->get_id() : 0 );
if ( ! $product_id ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'storefront-quick-add-block',
] );

$context = [
	'productId' => $product_id,
	'quantity'  => 1,
	'isBusy'    => false,
	'added'     => false,
];
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="storefrontCore/quickAdd"
	<?php echo function_exists( 'wp_interactivity_data_wp_context' ) ? wp_interactivity_data_wp_context( $context ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<div class="storefront-quick-add-stepper">
		<button
			type="button"
			class="storefront-quick-add-btn storefront-quick-add-btn--minus"
			aria-label="<?php esc_attr_e( 'Decrease quantity', 'storefront-core' ); ?>"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="context.quantity <= 1 || context.isBusy"
		>-</button>

		<span class="storefront-quick-add-qty" data-wp-text="context.quantity">1</span>

		<button
			type="button"
			class="storefront-quick-add-btn storefront-quick-add-btn--plus"
			aria-label="<?php esc_attr_e( 'Increase quantity', 'storefront-core' ); ?>"
			data-wp-on--click="actions.increment"
			data-wp-bind--disabled="context.isBusy"
		>+</button>

		<button
			type="button"
			class="storefront-quick-add-submit"
			data-wp-on--click="actions.addToCart"
			data-wp-bind--disabled="context.isBusy"
		>
			<span data-wp-bind--hidden="context.added"><?php esc_html_e( 'Add', 'storefront-core' ); ?></span>
			<span data-wp-bind--hidden="!context.added"><?php esc_html_e( '✓ Added', 'storefront-core' ); ?></span>
		</button>
	</div>
</div>
