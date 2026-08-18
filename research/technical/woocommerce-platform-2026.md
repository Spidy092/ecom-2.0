# WooCommerce Platform Architecture Research — 2026

**Status:** Current-platform research for V1 architecture  
**Date:** 2026-08-18  
**Issue:** #4  
**Source policy:** official WordPress/WooCommerce documentation, releases, and plugin metadata only.

## 1. Executive decision

Grovia should be built as a **block-first WordPress theme + WooCommerce extension plugin** that uses WooCommerce's public customer-facing Store API/Blocks interfaces for product/cart experiences and WooCommerce CRUD APIs for sensitive/customer/order data.

Do not build a parallel commerce engine. Do not use WooCommerce internal namespaces. Do not access order posts/postmeta directly. Do not mutate Cart/Checkout block client state manually.

## 2. Current platform snapshot

At this research snapshot:

- WooCommerce **11.0.0** is the current stable release, released 2026-08-04.
- WooCommerce 11.0.0's WordPress.org metadata requires WordPress 6.9+, is tested through WordPress 7.0.3, and permits PHP 7.4+.
- WordPress.org's current download page serves **WordPress 7.0.3** and recommends PHP 8.3+, MySQL 8.0+, or MariaDB 10.11+.
- WooCommerce's server guidance also recommends PHP 8.3+; its permissive minimum is not a sensible quality target for a new commercial product.

These are a dated snapshot, not permanent constants. CI/version declarations must be reviewed before every major product release.

Official sources:
- https://developer.woocommerce.com/releases/
- https://developer.woocommerce.com/2026/08/04/woocommerce-11-0/
- https://wordpress.org/plugins/woocommerce/
- https://wordpress.org/download/
- https://woocommerce.com/document/update-php-wordpress/

## 3. Decision: Store API for shopper-facing commerce context

### Official direction

WooCommerce's Store API is designed for customer-facing product, cart, and checkout functionality. It exposes product/catalog routes plus cart and checkout operations. Collection routes are paginated and product listing endpoints are explicitly available for frontend use.

WooCommerce documentation distinguishes this API from authenticated administrative APIs: sensitive information or data unrelated to the current shopper should not be exposed through Store API extension data.

### Grovia recommendation

Use the Store API for frontend functionality such as:
- product discovery/search result data;
- catalog/category product data;
- current shopper cart state;
- add/update/remove cart behavior using supported Store API paths;
- contextual extension data attached to product/cart resources when that data is safe to expose publicly.

Do not use the Store API to expose:
- another customer's order/history;
- private admin configuration;
- sensitive delivery configuration internals;
- license/account secrets;
- arbitrary order lookup by ID.

For Grovia-specific authenticated private data, use normal authenticated WordPress REST routes with strict permission callbacks and WooCommerce CRUD APIs where applicable.

Official source:
- https://developer.woocommerce.com/docs/apis/store-api/

## 4. Decision: Extend Store API rather than invent duplicate AJAX payloads

### Official direction

WooCommerce provides `ExtendSchema` APIs for adding namespaced extension data to Store API resources. Extensible resources include products, cart, cart items, and checkout. WooCommerce explicitly warns not to expose sensitive data because these customer-facing responses are public/contextual.

### Grovia recommendation

Where AisleFlow needs extra safe product/cart metadata:
- use `woocommerce_store_api_register_endpoint_data()` / supported `ExtendSchema` interfaces;
- namespace all Grovia data;
- keep callbacks bounded and fast because product endpoint callbacks may run repeatedly across collections;
- return only the data the component genuinely needs;
- avoid duplicating core price, stock, variation, or cart truth.

Potential examples:
- a safe delivery-eligibility presentation flag associated with current context, if the final architecture proves it belongs on an existing resource;
- a lightweight Grovia UI hint that is derived server-side and contains no private configuration.

Do not create duplicate custom AJAX endpoints simply to return WooCommerce data the Store API already provides.

Official sources:
- https://developer.woocommerce.com/docs/apis/store-api/extending-store-api/
- https://developer.woocommerce.com/docs/apis/store-api/extending-store-api/available-endpoints-to-extend
- https://developer.woocommerce.com/docs/apis/store-api/extending-store-api/extend-store-api-add-data/

## 5. Decision: server-authoritative cart updates

### Official direction

For extension actions that alter server-side cart state during Cart/Checkout, WooCommerce provides `woocommerce_store_api_register_update_callback()` plus client-side `extensionCartUpdate()`. Woo explicitly says extensions should not manually replace/update Cart/Checkout block client state because malformed state can break the shopping flow.

### Grovia recommendation

AisleFlow can use optimistic visual feedback only as temporary UI, but the WooCommerce server/session remains authoritative.

Rules:
- add/update/remove requests use supported Store API behavior;
- reconcile UI with returned authoritative cart data;
- if Grovia has an extension-specific server-side cart operation, use the supported cart-extension update interface rather than mutating Woo block stores by hand;
- on network/conflict failure, recover from authoritative cart state and communicate the error clearly.

Official source:
- https://developer.woocommerce.com/docs/apis/store-api/extending-store-api/extend-store-api-update-cart/

## 6. Decision: Cart and Checkout remain WooCommerce-owned

### Official direction

Cart and Checkout Blocks have explicit extension mechanisms and compatibility declarations. WooCommerce favors explicit interfaces such as Block extensions, Slot/Fills, inner blocks, and Store API interfaces rather than assuming every legacy PHP hook can alter block markup.

### Grovia recommendation

Grovia should:
- style and compose WooCommerce Cart/Checkout Blocks;
- declare compatibility when Grovia Core genuinely supports the block experience;
- use documented extension interfaces only where a Grovia feature must appear in cart/checkout;
- avoid replacing checkout with a Grovia template/application;
- never build payment processing into the theme.

Before relying on a legacy hook in Cart/Checkout, verify it against WooCommerce's current hook-alternatives documentation.

Official sources:
- https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/
- https://developer.woocommerce.com/docs/block-development/reference/hooks/hook-alternatives/

## 7. Decision: HPOS-safe Buy Again and order access

### Official direction

HPOS changes the physical storage of orders. WooCommerce explicitly instructs extension developers to stop using direct WordPress post/postmeta APIs for order data and instead use WooCommerce CRUD APIs such as `wc_get_order()` and order object metadata methods. Direct post-table access can return stale data or write to the wrong datastore under HPOS.

### Grovia recommendation

Buy Again must:
- retrieve orders through WooCommerce public CRUD/query APIs (`wc_get_orders()` / appropriate customer-facing APIs);
- retrieve individual orders through `wc_get_order()` when required;
- authorize ownership using the authenticated customer and WooCommerce semantics, not a client-supplied customer/order ID;
- read product/variation identifiers from authorized order items;
- re-resolve current products/variations through WooCommerce before presenting/adding them;
- revalidate current purchasability, price, stock and required choices;
- never query `wp_posts` / `wp_postmeta` for order history.

If metadata is added to an order, use order object metadata methods and save through WooCommerce CRUD.

Official source:
- https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/recipe-book/

## 8. Decision: never consume WooCommerce internal APIs

### Official direction

WooCommerce states that classes in `Automattic\WooCommerce\Internal` and code marked `@internal` are for core use only and do not carry backwards-compatibility guarantees.

### Grovia recommendation

Add a static/code-review rule:
- production Grovia code may not import/use `Automattic\WooCommerce\Internal\*`;
- production Grovia code may not deliberately call methods/classes/hooks marked `@internal`;
- experimental APIs require an explicit ADR, feature flag, fallback, and upgrade plan before they can be considered.

This is especially important because WooCommerce 11.0 removed/deprecated older product-editor/block APIs and added compatibility shims for some third-party breakage.

Official sources:
- https://developer.woocommerce.com/docs/extensions/getting-started-extensions/
- https://developer.woocommerce.com/docs/extensions/core-concepts/
- https://developer.woocommerce.com/docs/extensions/best-practices-extensions/extension-development-best-practices/
- https://wordpress.org/plugins/woocommerce/

## 9. Decision: Block-first catalog and theme architecture

### Official direction

WooCommerce's Product Collection and Product Filters are part of the modern block catalog experience. WooCommerce is also moving new interactive shopping blocks toward WordPress's Interactivity API; Product Collection and Product Filters already use it, while other product/cart blocks have been moving in that direction.

### Grovia recommendation

The Grovia theme should:
- use `theme.json`, block templates, patterns, and WooCommerce-supported blocks as its default composition system;
- prefer Product Collection/Filters and public block APIs over legacy shortcode/template recreation when they satisfy the requirement;
- design CSS/tokens around block output rather than coupling the product to Elementor-generated DOM;
- understand the WordPress Interactivity API because it is relevant to WooCommerce's direction, but **not** prematurely rewrite every Grovia interaction as a custom Interactivity API block merely for novelty;
- avoid experimental Woo interfaces for V1 unless a stable public alternative cannot meet a validated requirement.

Official sources:
- https://developer.woocommerce.com/docs/block-development/extensible-blocks/product-collection-block/
- https://developer.woocommerce.com/2025/10/30/understanding-the-interactivity-api-driven-future-for-woocommerce-blocks/

## 10. Product search architecture

### V1 recommendation

Start with WooCommerce/Store API catalog data and bounded result sets.

Requirements:
- debounce/cancel stale client search requests;
- never request an unbounded product catalog;
- return/display only the fields needed by the current search surface;
- keep pack/unit/variation truth based on WooCommerce product objects/data;
- measure catalog behavior with realistic data before introducing a dedicated search service;
- preserve a conventional search fallback if enhanced JS fails.

A dedicated external search service is an optimization option for later scale, not a V1 dependency.

## 11. Shopping List architecture

Shopping List is Grovia-owned private customer functionality rather than WooCommerce cart/order truth.

Recommended boundary:
- authenticated WordPress REST route(s) owned by Grovia Core;
- strict permission callback/current-user ownership checks;
- store product/variation identifiers, not copied prices/stock descriptions;
- resolve current product truth via WooCommerce when rendering/adding;
- no order data needs to be exposed through this route;
- exact persistence (user meta vs custom table) remains a later decision based on expected query/load requirements.

Guest lists remain deferred unless research proves they are necessary.

## 12. Delivery availability architecture

V1 serviceability is Grovia-owned store configuration, not a full shipping/logistics engine.

Recommended split:
- admin configuration protected by privileged authenticated WordPress APIs/settings;
- public shopper check exposes only a yes/no/contextual serviceability result required by the UI;
- do not leak the full internal zone/postcode configuration unnecessarily;
- bound/normalize postcode input;
- separate this UX signal from WooCommerce's actual shipping-rate calculation so the storefront never implies that a prototype serviceability result is a final checkout promise unless the underlying shipping rules are synchronized.

The exact relationship with WooCommerce Shipping Zones must be researched during implementation design; avoid creating two contradictory sources of delivery truth.

## 13. Variable products / quick add

AisleFlow V0 currently proves interaction on simple products only.

Production rule:
- simple purchasable product -> direct Add/quantity is allowed;
- product requiring a choice -> do not silently choose a variation;
- use WooCommerce variation/product data and a validated compact choice surface or route to product detail;
- do not preload huge variation payloads for every product row without measurement;
- Issue #9 owns the specific variable-product interaction decision.

## 14. Tentative support matrix

Do **not** confuse WooCommerce's lowest technically permitted versions with Grovia's supported quality floor.

### Upstream current snapshot

- WordPress current download: 7.0.3
- WooCommerce current stable: 11.0.0
- WooCommerce 11.0 metadata: WP 6.9+, PHP 7.4+
- WordPress/Woo recommendations: PHP 8.3+

### Proposed Grovia V1 engineering matrix

**Development/recommended environment**
- PHP 8.3+
- WordPress latest stable
- WooCommerce latest stable

**Candidate customer minimum (not yet final)**
- PHP 8.1 or 8.2 floor to be decided after hosting-market compatibility research
- WordPress 6.9+ initially plausible because WooCommerce 11.0 requires it
- WooCommerce 11.0+ initially plausible for a brand-new product

Why not declare PHP 7.4 simply because Woo permits it:
- PHP 7.4 is long EOL;
- supporting old runtimes increases code/test/security burden;
- Grovia is a new commercial product, not a legacy plugin with an installed base to preserve.

Before paid beta, validate actual target-customer hosting support and lock one minimum version in plugin/theme headers and CI.

## 15. Compatibility CI recommendations

At implementation time CI should include:
- latest stable WordPress + latest stable WooCommerce + PHP 8.3;
- latest stable WordPress + latest stable WooCommerce + newest supported PHP;
- declared minimum WordPress/Woo/PHP combination;
- upcoming WordPress/Woo pre-release compatibility as a non-blocking/nightly signal where practical;
- HPOS enabled;
- Cart/Checkout Blocks enabled/default;
- block theme active;
- classic/legacy behavior tested only where we explicitly claim support.

WooCommerce recommends its Quality Insights Toolkit (QIT) for testing extensions across WooCommerce, WordPress, PHP, and other extension combinations; evaluate QIT as an additional compatibility signal when the packages exist.

## 16. Architecture decisions this research resolves

### Resolved

1. Block-first remains correct.
2. Store API is the primary frontend commerce-data seam.
3. ExtendSchema is preferred for safe contextual extension data on Store API resources.
4. Cart state remains WooCommerce/server authoritative.
5. Cart/Checkout Blocks remain Woo-owned; Grovia extends/styles them.
6. Buy Again uses Woo CRUD and must be HPOS safe.
7. Woo internal namespaces/@internal are forbidden.
8. Shopping List/private customer features use authenticated Grovia-owned routes, not public Store API schema.
9. External search, custom tables and custom checkout are not V1 defaults.
10. Modern PHP support is a deliberate product quality choice, not dictated by Woo's old minimum.

### Still open

1. Exact PHP minimum after hosting/customer research.
2. Exact Shopping List persistence.
3. Whether delivery serviceability wraps Woo Shipping Zones or maintains a synchronized Grovia configuration.
4. Variable-product quick-add choice surface.
5. Exact block composition for AisleFlow Product Ledger after prototype/user testing.
6. Whether any Interactivity API custom block is needed versus existing Woo blocks + light enhancement.

## 17. Pre-implementation checklist

Before Codex writes a production Woo integration:

- identify the exact public Woo API/block/CRUD seam;
- verify it is not `@internal`/experimental unless explicitly approved;
- state whether endpoint data is public/session-contextual or private/authenticated;
- define authorization/nonce behavior;
- define HPOS implications;
- define block Cart/Checkout compatibility implications;
- define fallback/error behavior;
- add an integration test that proves the public seam rather than Woo internals.

## 18. Conclusion

The current WooCommerce direction strongly supports our original strategy: **build less custom commerce infrastructure, lean on supported Woo/WordPress foundations, and spend Grovia's engineering budget on the grocery-specific interaction advantage.**

That means AisleFlow can be distinctive in UX while remaining conservative underneath: public Store API + Blocks + Woo CRUD + strict Grovia-owned private endpoints only where the platform does not already own the data.
