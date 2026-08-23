<?php
/**
 * Title: Delivery Checker
 * Slug: storefront/delivery-checker
 * Categories: storefront-grocery
 * Description: Compact postcode delivery availability checker. Queries WooCommerce Shipping Zones via the Core REST API.
 * Viewport Width: 800
 */
?>
<!-- wp:html -->
<div class="storefront-delivery-checker" id="storefront-delivery-checker">
	<form
		class="storefront-delivery-form"
		id="storefront-delivery-form"
		novalidate
		aria-label="<?php esc_attr_e( 'Check delivery availability', 'bhaivatech-grocery-alpha' ); ?>"
	>
		<div class="storefront-delivery-form__row">
			<label for="storefront-postcode-input" class="storefront-delivery-form__label">
				<?php esc_html_e( 'Delivering to', 'bhaivatech-grocery-alpha' ); ?>
			</label>
			<input
				type="text"
				id="storefront-postcode-input"
				name="postcode"
				class="storefront-delivery-form__input"
				autocomplete="postal-code"
				maxlength="10"
				placeholder="<?php esc_attr_e( 'Enter postcode', 'bhaivatech-grocery-alpha' ); ?>"
				aria-required="true"
				aria-describedby="storefront-delivery-result"
				inputmode="text"
			/>
			<button
				type="submit"
				class="storefront-delivery-form__submit wp-element-button"
				aria-label="<?php esc_attr_e( 'Check delivery availability', 'bhaivatech-grocery-alpha' ); ?>"
			>
				<?php esc_html_e( 'Check', 'bhaivatech-grocery-alpha' ); ?>
			</button>
		</div>
		<div
			id="storefront-delivery-result"
			class="storefront-delivery-form__result"
			role="status"
			aria-live="polite"
			aria-atomic="true"
		></div>
	</form>
</div>
<!-- /wp:html -->
