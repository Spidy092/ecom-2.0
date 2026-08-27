<?php
/**
 * Title: Product Ledger Row
 * Slug: storefront/product-ledger-row
 * Categories: storefront-grocery
 * Description: Compact product row for grocery scanning — image, title, unit/pack, price, add-to-cart. Used inside Product Collection template.
 * Block Types: woocommerce/product-template
 */
?>
<!-- wp:group {"className":"storefront-ledger-row","tagName":"div","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group storefront-ledger-row">

	<!-- Product image -->
	<!-- wp:woocommerce/product-image {"className":"storefront-ledger-row__image","imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"showProductLink":true,"saleBadgeAlign":"left","width":80,"height":80} /-->

	<!-- Title + pack/unit + price group -->
	<!-- wp:group {"className":"storefront-ledger-row__info","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"},"style":{"layout":{"selfStretch":"fill"}}} -->
	<div class="wp-block-group storefront-ledger-row__info">
		<!-- wp:post-title {"textAlign":"left","level":3,"isLink":true,"className":"storefront-ledger-row__title","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
		<!-- wp:storefront/product-unit {"className":"storefront-ledger-row__unit"} /-->
		<!-- wp:woocommerce/product-price {"className":"storefront-ledger-row__price"} /-->
	</div>
	<!-- /wp:group -->

	<!-- Add to cart action -->
	<!-- wp:group {"className":"storefront-ledger-row__action","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
	<div class="wp-block-group storefront-ledger-row__action">
		<!-- wp:storefront-core/product-quick-add {"renderFallback":true} /-->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
