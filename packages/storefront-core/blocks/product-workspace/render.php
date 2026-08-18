<?php
/**
 * Server-rendered shell for the grocery product workspace.
 *
 * WooCommerce remains authoritative for all product/cart state; JavaScript
 * progressively enhances this shell through public APIs.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_script( 'bhaivatech-storefront-saved-products' );
wp_enqueue_script( 'bhaivatech-storefront-delivery-serviceability' );

$cart_url              = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
$search_id             = wp_unique_id( 'bt-product-search-' );
$saved_panel_id        = wp_unique_id( 'bt-saved-panel-' );
$delivery_country_id   = wp_unique_id( 'bt-delivery-country-' );
$delivery_state_id     = wp_unique_id( 'bt-delivery-state-' );
$delivery_state_text_id = wp_unique_id( 'bt-delivery-state-text-' );
$delivery_postcode_id  = wp_unique_id( 'bt-delivery-postcode-' );
$delivery_config       = bhaivatech_storefront_serviceability_public_config();
$delivery_countries    = $delivery_config['countries'] ?? array();
$single_country        = $delivery_config['singleCountry'] ?? '';
$single_country_label  = '';

foreach ( $delivery_countries as $delivery_country ) {
	if ( $single_country === ( $delivery_country['code'] ?? '' ) ) {
		$single_country_label = (string) ( $delivery_country['label'] ?? $single_country );
		break;
	}
}
?>
<section class="bt-product-workspace" data-bt-product-workspace>
	<section class="bt-delivery-check" aria-labelledby="<?php echo esc_attr( $delivery_postcode_id ); ?>-heading" data-bt-delivery>
		<form class="bt-delivery-check__form" data-bt-serviceability novalidate>
			<div class="bt-delivery-check__heading-row">
				<strong id="<?php echo esc_attr( $delivery_postcode_id ); ?>-heading">
					<?php esc_html_e( 'Delivery area', 'bhaivatech-storefront-alpha' ); ?>
				</strong>
				<?php if ( '' !== $single_country ) : ?>
					<span class="bt-delivery-check__country-context">
						<?php echo esc_html( $single_country_label ?: $single_country ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div class="bt-delivery-check__fields">
				<?php if ( count( $delivery_countries ) > 1 ) : ?>
					<div class="bt-delivery-check__field" data-bt-delivery-country-field>
						<label for="<?php echo esc_attr( $delivery_country_id ); ?>">
							<?php esc_html_e( 'Country or region', 'bhaivatech-storefront-alpha' ); ?>
						</label>
						<select id="<?php echo esc_attr( $delivery_country_id ); ?>" data-bt-delivery-country>
							<option value=""><?php esc_html_e( 'Choose country or region', 'bhaivatech-storefront-alpha' ); ?></option>
							<?php foreach ( $delivery_countries as $delivery_country ) : ?>
								<option value="<?php echo esc_attr( $delivery_country['code'] ?? '' ); ?>">
									<?php echo esc_html( $delivery_country['label'] ?? $delivery_country['code'] ?? '' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else : ?>
					<input type="hidden" value="<?php echo esc_attr( $single_country ); ?>" data-bt-delivery-country />
				<?php endif; ?>

				<div class="bt-delivery-check__field" hidden data-bt-delivery-state-select-field>
					<label for="<?php echo esc_attr( $delivery_state_id ); ?>">
						<?php esc_html_e( 'State or region', 'bhaivatech-storefront-alpha' ); ?>
					</label>
					<select id="<?php echo esc_attr( $delivery_state_id ); ?>" data-bt-delivery-state-select disabled>
						<option value=""><?php esc_html_e( 'Choose state or region', 'bhaivatech-storefront-alpha' ); ?></option>
					</select>
				</div>

				<div class="bt-delivery-check__field" hidden data-bt-delivery-state-input-field>
					<label for="<?php echo esc_attr( $delivery_state_text_id ); ?>">
						<?php esc_html_e( 'State or region', 'bhaivatech-storefront-alpha' ); ?>
					</label>
					<input
						id="<?php echo esc_attr( $delivery_state_text_id ); ?>"
						type="text"
						maxlength="100"
						autocomplete="address-level1"
						data-bt-delivery-state-input
						disabled
					/>
				</div>

				<div class="bt-delivery-check__field bt-delivery-check__postcode-field">
					<label for="<?php echo esc_attr( $delivery_postcode_id ); ?>">
						<?php esc_html_e( 'Postcode', 'bhaivatech-storefront-alpha' ); ?>
					</label>
					<input
						id="<?php echo esc_attr( $delivery_postcode_id ); ?>"
						type="text"
						maxlength="32"
						autocomplete="postal-code"
						inputmode="text"
						required
						data-bt-delivery-postcode
					/>
				</div>

				<button type="submit" class="bt-delivery-check__submit" data-bt-delivery-submit>
					<?php esc_html_e( 'Check area', 'bhaivatech-storefront-alpha' ); ?>
				</button>
			</div>

			<p class="bt-delivery-check__result" role="status" aria-live="polite" aria-atomic="true" data-bt-delivery-result></p>
		</form>
	</section>

	<div class="bt-product-workspace__search">
		<label for="<?php echo esc_attr( $search_id ); ?>">
			<?php esc_html_e( 'Search groceries', 'bhaivatech-storefront-alpha' ); ?>
		</label>
		<input
			id="<?php echo esc_attr( $search_id ); ?>"
			class="bt-product-workspace__input"
			type="search"
			autocomplete="off"
			placeholder="<?php echo esc_attr_x( 'Milk, rice, tomatoes…', 'grocery product search placeholder', 'bhaivatech-storefront-alpha' ); ?>"
			data-bt-search
		/>
		<p class="bt-product-workspace__hint">
			<?php esc_html_e( 'Type at least 2 characters. Results are limited to keep shopping fast.', 'bhaivatech-storefront-alpha' ); ?>
		</p>
	</div>

	<p class="bt-product-workspace__status" role="status" aria-live="polite" aria-atomic="true" data-bt-status>
		<?php esc_html_e( 'Ready to search.', 'bhaivatech-storefront-alpha' ); ?>
	</p>

	<div class="bt-product-workspace__saved-summary">
		<button
			type="button"
			class="bt-product-workspace__saved-toggle"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $saved_panel_id ); ?>"
			data-bt-saved-toggle
		>
			<span><?php esc_html_e( 'Saved', 'bhaivatech-storefront-alpha' ); ?></span>
			<span aria-hidden="true">·</span>
			<span data-bt-saved-count>0</span>
		</button>
	</div>

	<section
		id="<?php echo esc_attr( $saved_panel_id ); ?>"
		class="bt-saved-panel"
		hidden
		aria-labelledby="<?php echo esc_attr( $saved_panel_id ); ?>-title"
		data-bt-saved-panel
	>
		<div class="bt-saved-panel__header">
			<div>
				<h2 id="<?php echo esc_attr( $saved_panel_id ); ?>-title">
					<?php esc_html_e( 'Saved for later', 'bhaivatech-storefront-alpha' ); ?>
				</h2>
				<p data-bt-saved-scope></p>
			</div>
			<button type="button" data-bt-saved-close>
				<?php esc_html_e( 'Close Saved', 'bhaivatech-storefront-alpha' ); ?>
			</button>
		</div>
		<div class="bt-saved-panel__products" data-bt-saved-products></div>
	</section>

	<div class="bt-product-workspace__results" data-bt-results></div>

	<div class="bt-product-workspace__cart" aria-label="<?php echo esc_attr_x( 'Current cart summary', 'accessibility label', 'bhaivatech-storefront-alpha' ); ?>">
		<div>
			<strong data-bt-cart-count><?php esc_html_e( '0 items', 'bhaivatech-storefront-alpha' ); ?></strong>
			<span data-bt-cart-total></span>
		</div>
		<a class="bt-product-workspace__cart-link" href="<?php echo esc_url( $cart_url ); ?>">
			<?php esc_html_e( 'View cart', 'bhaivatech-storefront-alpha' ); ?>
		</a>
	</div>
</section>
