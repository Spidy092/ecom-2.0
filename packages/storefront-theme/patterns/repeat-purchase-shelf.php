<?php
/**
 * Title: Repeat Purchase Shelf
 * Slug: storefront/repeat-purchase-shelf
 * Categories: storefront-grocery
 * Description: "This Week" section for returning customers — Buy Again items from recent orders. Populated by Core plugin when user has order history.
 */
?>
<!-- wp:html -->
<section class="storefront-repeat-shelf" id="storefront-repeat-shelf" aria-labelledby="storefront-repeat-shelf-heading" hidden>

	<header class="storefront-repeat-shelf__header">
		<h2 class="storefront-repeat-shelf__heading" id="storefront-repeat-shelf-heading">
			<?php esc_html_e( 'This week', 'bhaivatech-grocery-alpha' ); ?>
		</h2>
		<p class="storefront-repeat-shelf__sub">
			<?php esc_html_e( 'From your recent shopping', 'bhaivatech-grocery-alpha' ); ?>
		</p>
	</header>

	<ul class="storefront-repeat-shelf__list" id="storefront-repeat-shelf-list" role="list" aria-live="polite" aria-atomic="false">
		<!-- Populated by storefront-interactions.js via Buy Again REST endpoint (future) -->
	</ul>

	<template id="storefront-repeat-item-tpl">
		<li class="storefront-repeat-shelf__item storefront-ledger-row" data-product-id="" data-variation-id="">
			<img class="storefront-repeat-shelf__image storefront-ledger-row__image" src="" alt="" width="80" height="80" loading="lazy" />
			<div class="storefront-repeat-shelf__info storefront-ledger-row__info">
				<span class="storefront-repeat-shelf__name storefront-ledger-row__title"></span>
				<span class="storefront-repeat-shelf__price storefront-ledger-row__price"></span>
			</div>
			<div class="storefront-repeat-shelf__action storefront-ledger-row__action">
				<button type="button" class="storefront-repeat-shelf__add wp-element-button storefront-add-btn">
					<?php esc_html_e( 'Add', 'bhaivatech-grocery-alpha' ); ?>
				</button>
			</div>
		</li>
	</template>

</section>
<!-- /wp:html -->
