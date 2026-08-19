# BhaivaTech Storefront Core — Internal Engineering Alpha

This package contains grocery-storefront functionality that should survive a presentation/theme change.

## Current alpha boundary

Current issue-scoped slices include:

- Store API-backed product search/add/quantity/cart continuity;
- bounded typo recovery;
- Saved for later persistence and ownership handling;
- delivery serviceability checks;
- persistent mobile shopping navigation;
- adaptive department browsing;
- Store Setup & privacy-safe System Status.

The new setup/status slice is deliberately non-destructive. It provides preflight, compatibility context and a safe support export, but does not yet run the Modern Grocery starter import. The final importer remains gated by the commercial package/update provider and retry/idempotency contract in Issue #18.

## Commerce boundary

The shopper-facing commerce source of truth remains WooCommerce. Product discovery/cart work must use supported WooCommerce Store API/public APIs; customer-order work must use supported WooCommerce CRUD/query APIs.

The product is designed to coexist with extensions that use supported WordPress/WooCommerce integration points. WooCommerce is required; optional plugins/page builders are not automatically advertised as validated compatibility until tested.

Do not use WooCommerce internal APIs or direct order-table/post-table assumptions.
