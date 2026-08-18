# BhaivaTech Storefront Core — Internal Engineering Alpha

This package contains grocery-storefront functionality that should survive a presentation/theme change.

## Current alpha boundary

At this stage the plugin only bootstraps safely and verifies that WooCommerce is available. Feature work is added as issue-scoped vertical slices.

The shopper-facing commerce source of truth remains WooCommerce. Product discovery/cart work must use supported WooCommerce Store API/public APIs; customer-order work must use supported WooCommerce CRUD/query APIs.

Do not use WooCommerce internal APIs or direct order-table/post-table assumptions.
