<?php
/**
 * Title: Shopping List Widget
 * Slug: storefront/shopping-list-widget
 * Categories: storefront-grocery
 * Description: Personal shopping list panel. Requires authenticated user and storefront-core plugin.
 */
?>
<!-- wp:html -->
<div class="storefront-shopping-list" id="storefront-shopping-list" aria-label="<?php esc_attr_e( 'Shopping list', 'bhaivatech-grocery-alpha' ); ?>">

	<div class="storefront-shopping-list__header">
		<h2 class="storefront-shopping-list__title"><?php esc_html_e( 'Your list', 'bhaivatech-grocery-alpha' ); ?></h2>
	</div>

	<div
		class="storefront-shopping-list__body"
		id="storefront-shopping-list-body"
		aria-live="polite"
		aria-atomic="false"
	>
		<!-- Populated by storefront-interactions.js when user is logged in -->
		<p class="storefront-shopping-list__loading" aria-live="polite">
			<?php esc_html_e( 'Loading your list…', 'bhaivatech-grocery-alpha' ); ?>
		</p>
	</div>

	<div class="storefront-shopping-list__footer" id="storefront-shopping-list-footer" hidden>
		<button
			type="button"
			class="storefront-shopping-list__add-all wp-element-button"
			id="storefront-shopping-list-add-all"
			aria-label="<?php esc_attr_e( 'Add all available list items to basket', 'bhaivatech-grocery-alpha' ); ?>"
		>
			<?php esc_html_e( 'Add to basket', 'bhaivatech-grocery-alpha' ); ?>
		</button>
	</div>

	<template id="storefront-list-item-tpl">
		<div class="storefront-shopping-list__item" data-product-id="" data-variation-id="">
			<label class="storefront-shopping-list__item-label">
				<input type="checkbox" class="storefront-shopping-list__item-check" aria-label="" />
				<span class="storefront-shopping-list__item-name"></span>
			</label>
			<button
				type="button"
				class="storefront-shopping-list__item-remove"
				aria-label=""
			>
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
					<path d="M4 4L12 12M12 4L4 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
				</svg>
			</button>
		</div>
	</template>

</div>
<!-- /wp:html -->
