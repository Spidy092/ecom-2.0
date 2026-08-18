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

$cart_url       = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
$search_id      = wp_unique_id( 'bt-product-search-' );
$saved_panel_id = wp_unique_id( 'bt-saved-panel-' );
?>
<section class="bt-product-workspace" data-bt-product-workspace>
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
