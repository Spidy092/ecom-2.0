<?php
/**
 * Render callback for Delivery Checker block.
 *
 * @package StorefrontCore
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$heading     = $attributes['heading'] ?? __( 'Check Delivery Availability', 'storefront-core' );
$placeholder = $attributes['placeholder'] ?? __( 'Enter postcode (e.g. 560001)', 'storefront-core' );

$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'storefront-delivery-checker-block',
] );

$context = [
	'postcode' => '',
	'status'   => 'idle', // idle, loading, available, unavailable, error
	'message'  => '',
];

if ( function_exists( 'wp_interactivity_state' ) ) {
	wp_interactivity_state( 'storefrontCore/delivery', [
		'restUrl' => esc_url_raw( rest_url( 'storefront-core/v1/delivery/check' ) ),
	] );
}
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="storefrontCore/delivery"
	<?php echo function_exists( 'wp_interactivity_data_wp_context' ) ? wp_interactivity_data_wp_context( $context ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<h3 class="storefront-delivery-checker-block__title"><?php echo esc_html( $heading ); ?></h3>

	<form class="storefront-delivery-checker-block__form" data-wp-on--submit="actions.checkDelivery">
		<div class="storefront-delivery-checker-block__input-group">
			<input
				type="text"
				class="storefront-delivery-checker-block__input"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				data-wp-bind--value="context.postcode"
				data-wp-on--input="actions.updatePostcode"
				required
			/>
			<button type="submit" class="storefront-delivery-checker-block__button" data-wp-bind--disabled="state.isLoading">
				<span data-wp-text="state.buttonLabel"><?php esc_html_e( 'Check', 'storefront-core' ); ?></span>
			</button>
		</div>
	</form>

	<div
		class="storefront-delivery-checker-block__result"
		data-wp-bind--hidden="!context.message"
		data-wp-class--is-available="context.status === 'available'"
		data-wp-class--is-unavailable="context.status === 'unavailable'"
		data-wp-class--is-error="context.status === 'error'"
		data-wp-text="context.message"
	></div>
</div>
