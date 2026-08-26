# BhaivaTech Storefront Core — Internal Engineering Alpha

This package contains grocery-storefront functionality that should survive a presentation/theme change.

## Current alpha boundary

The first vertical slice now provides a public delivery checker, an authenticated custom-table Shopping List, Buy Again, cart feedback, and a capability-protected setup wizard. Feature work remains issue-scoped vertical slices.

The shopper-facing commerce source of truth remains WooCommerce. Product discovery/cart work must use supported WooCommerce Store API/public APIs; customer-order work must use supported WooCommerce CRUD/query APIs.

When a bounded search reaches its result cap, the workspace exposes a conventional WooCommerce search link with the same query instead of silently hiding additional matches.

## Search slice

The product workspace uses a bounded WooCommerce Store API search (`12` results, `80` characters maximum) with debounced and cancellable requests. Search results keep the same product-card and inline quantity interaction as department browsing. A typo recovery request is only made after an empty result set and never silently replaces the shopper's query.

The search control is also a conventional `GET` form targeting the WooCommerce shop URL (`s` query parameter). JavaScript intercepts valid submissions for the enhanced flow; browsers without JavaScript, or after an endpoint failure, retain a normal search path.

Authenticated shoppers also receive repeat-aware context from the private Buy Again endpoint. A matching product can show the bounded remembered quantity and an explicit `Add N again` action without changing WooCommerce search relevance. History loads once, never blocks ordinary search, makes no guest request, and fails closed to normal search. Products requiring options retain `Choose options`.

The market/product rationale is recorded in `research/market/search-gap-2026-08-21.md`.

Do not use WooCommerce internal APIs or direct order-table/post-table assumptions.

## Public seams in this slice

- `storefront-core/v1/delivery/check?postcode=...` — bounded, public read-only serviceability result backed by WooCommerce Shipping Zones.
- `storefront-core/v1/shopping-list` and `/items` — authenticated list read/write/delete operations scoped to the current WordPress user.
- `bhaivatech-storefront/v1/buy-again` — authenticated, current-user-scoped product IDs and remembered quantities derived from the latest 20 `processing`/`completed` orders through `wc_get_orders()`; `storefront-core/v1/buy-again` remains a compatibility alias.
- `[bhaivatech_delivery_checker]` — progressive-enhancement shortcode used by the block theme.
- `[bhaivatech_shopping_list]` — private list view for a customer page; standard WooCommerce product-card add-to-cart markup receives a secondary Save to list action for signed-in customers.
- `[bhaivatech_buy_again]` — private repeat-purchase view derived through `wc_get_orders()` and current product truth; it never accepts an order ID from the client.
- `buy-again` — WooCommerce My Account endpoint with quantity memory, direct Store API cart add for safe simple products, explicit variable-product choices, unavailable states, and retry-safe failure handling.
- `[bhaivatech_cart_feedback]` — compact Store API-backed cart count/total surface that reconciles after common WooCommerce cart events.
- WooCommerce > Storefront setup — capability-protected three-step merchant setup for store basics and delivery areas; WooCommerce tax/shipping/payment configuration remains separate and authoritative.

Delivery configuration belongs in WooCommerce Shipping Zones; the checker does not maintain a second postcode database. Shopping List entries are stored in the `storefront_shopping_list` custom table, while current product truth is resolved by WooCommerce when entries are read.

## Disposable Grovia demo

When WP-CLI is available in a local, development, or staging environment, the
core plugin registers a namespaced seeder:

```bash
wp grovia seed-demo
wp grovia seed-demo --reset
```

The command creates only marked Grovia demo departments, products, pages,
media, and store options. `--reset` removes only those marked fixtures; it
does not delete unrelated products, pages, orders, users, or media. The seed
uses WooCommerce CRUD APIs for catalog data and WordPress APIs for pages,
options, and attachments so the fixture remains compatible with HPOS.
