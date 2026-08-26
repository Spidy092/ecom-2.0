# Grocery Quantity Rail - 2026-08-26

**Status:** Engineering-alpha product/research note  
**Scope:** Product-list purchase controls on the home and shop ledgers  
**Research mode:** Current ThemeForest feature listings and public grocery UX research; feature claims are not conversion results.

## Customer problem

A grocery shopper often needs several units of the same low-consideration item. Opening a product page or returning to the cart for every quantity change adds transitions and makes the list harder to scan.

## Competitors benchmarked

- [Bacola](https://themeforest.net/item/bacola-grocery-store-and-food-ecommerce-theme/32552148/) markets grocery-specific AJAX discovery and cart behavior, but its public listing is Elementor-oriented and not Gutenberg optimized.
- [GreenMart](https://themeforest.net/item/greenmart-organic-food-woocommerce-wordpress-theme/20754270/) lists min/max/step quantity, AJAX product search/cart, quick view and multiple mobile menu modes. Its breadth is useful, but it also brings several builder and plugin compatibility surfaces.
- [Organio](https://themeforest.net/item/organio-organic-food-store-wordpress/31597445/) lists quick view, compare, sticky menu and sticky mobile header alongside a large Elementor widget system.
- [Freshio](https://themeforest.net/item/freshio-organic-food-store-wordpress-theme/28365085/) positions mobile-first layouts, many demos and a large plugin/page-builder stack as its selling points.

## What they do well

- They establish familiar ecommerce expectations: visible category discovery, cart access, responsive product grids and direct purchase actions.
- GreenMart explicitly exposes quantity constraints, which is relevant to grocery stock and pack behavior.
- The mature products offer broad customization and many ready-made storefront compositions.

## Commodity expectations

Responsive grids, AJAX search, quick add, quick view, wishlist, sticky navigation and multiple header variants are now common marketplace checklist items. They are not a defensible Grovia moat by themselves.

## Observed gap

Marketplace themes often optimize for feature breadth and page-builder composition. The product-list action still needs to make the grocery quantity task obvious: show quantity controls for simple products, preserve a safe options path for variable products, and explain when a product cannot be reordered.

Baymard's public grocery finding recommends changing an Add to Cart action into a quantity picker after an item is added, because it is efficient for repeated grocery additions: [Grocery UX: Dynamically Update the Add to Cart Button](https://baymard.com/blog/grocery-add-to-cart-buttons). Its product-list research also treats scanning, filtering and product comparison as the path to purchase: [Product Lists UX](https://baymard.com/research/ecommerce-product-lists).

## Grovia uniqueness thesis

Grovia will use one quantity-aware purchase rail across the ledger: a bounded stepper for simple, purchasable, in-stock products; an explicit **Choose options** route for variable products; and a visible unavailable state when current WooCommerce truth blocks purchase. The control uses the existing Store API/interactivity implementation, so cart state remains authoritative and no new dependency is required.

## Alternative approaches considered

1. Keep the generic WooCommerce button everywhere. Lowest change cost, but quantity shopping remains a second-step task.
2. Add a page-builder-specific quick-view or modal. More visual affordance, but more transitions, focus complexity and dependency surface.
3. Use the existing Core quantity stepper in the ledger and render explicit safe fallbacks for variable/unavailable products. Best task value with the smallest maintained surface.

**Selected:** option 3.

## Measurable success criterion

On a 320-430px product ledger, a shopper can set a quantity and add a simple product without opening its product page, while variable and unavailable items remain understandable and keyboard reachable. Browser coverage must verify the remembered quantity request, the cart response feedback, the options route and unavailable messaging.

## Why this belongs in V1

Fast quantity shopping is a protected grocery V1 outcome. The change reuses an existing tested Core block, keeps WooCommerce's public Store API as the cart boundary, and avoids adding quick view, compare, builder widgets or recommendation logic.

## Performance, security and maintenance

- No new runtime dependency or remote asset.
- One small Interactivity view module handles quantity and Store API mutation.
- Product type, stock and purchasability are resolved through WooCommerce CRUD APIs.
- Fallback links are escaped at render time; no client-supplied order or customer data is involved.

**Sources/date:** ThemeForest listings and Baymard public research above, reviewed 2026-08-26.
