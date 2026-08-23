# BhaivaTech Storefront Core — Internal Engineering Alpha

This package contains grocery-storefront functionality that should survive a presentation/theme change.

## Current alpha boundary

The first vertical slice now provides a public delivery checker, an authenticated custom-table Shopping List, Buy Again, cart feedback, and a capability-protected setup wizard. Feature work remains issue-scoped vertical slices.

The shopper-facing commerce source of truth remains WooCommerce. Product discovery/cart work must use supported WooCommerce Store API/public APIs; customer-order work must use supported WooCommerce CRUD/query APIs.

Do not use WooCommerce internal APIs or direct order-table/post-table assumptions.

## Public seams in this slice

- `storefront-core/v1/delivery/check?postcode=...` — bounded, public read-only serviceability result backed by WooCommerce Shipping Zones.
- `storefront-core/v1/shopping-list` and `/items` — authenticated list read/write/delete operations scoped to the current WordPress user.
- `storefront-core/v1/buy-again` — authenticated product IDs derived from paid orders through `wc_get_orders()`.
- `[bhaivatech_delivery_checker]` — progressive-enhancement shortcode used by the block theme.
- `[bhaivatech_shopping_list]` — private list view for a customer page; standard WooCommerce product-card add-to-cart markup receives a secondary Save to list action for signed-in customers.
- `[bhaivatech_buy_again]` — private repeat-purchase view derived through `wc_get_orders()` and current product truth; it never accepts an order ID from the client.
- `[bhaivatech_cart_feedback]` — compact Store API-backed cart count/total surface that reconciles after common WooCommerce cart events.
- WooCommerce > Storefront setup — capability-protected three-step merchant setup for store basics and delivery areas; WooCommerce tax/shipping/payment configuration remains separate and authoritative.

Delivery configuration belongs in WooCommerce Shipping Zones; the checker does not maintain a second postcode database. Shopping List entries are stored in the `storefront_shopping_list` custom table, while current product truth is resolved by WooCommerce when entries are read.
