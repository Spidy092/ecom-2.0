# Grovia V1 Architecture

## 1. Architectural intent

Grovia is a WordPress/WooCommerce product, not a replacement commerce platform. The architecture should extend stable platform seams and keep Grovia-specific behavior modular.

```text
Browser / WordPress blocks
  |
  +--> public product/cart context
  |      |
  |      v
  |   WooCommerce Store API / Blocks
  |      |
  |      v
  |   WooCommerce product + cart truth
  |
  +--> Grovia private customer behavior
         |
         v
      Grovia Core authenticated REST/services
         |
         +--> Shopping List
         +--> Buy Again adapter
         +--> Delivery configuration/check
         +--> Setup/onboarding
         |
         v
      WooCommerce public CRUD APIs
         |
         +--> Orders / HPOS
         +--> Products / variations
         +--> Cart handoff
         |
         v
      WordPress auth/settings/data layer

Presentation:
WordPress Block Theme (grovia-theme)
  -> theme.json
  -> block templates / parts / patterns
  -> WooCommerce block styling/composition
```

The underlying rule is simple: **Grovia owns grocery UX; WooCommerce owns commerce truth.**

## 2. Ownership rules

### Theme owns

- templates, parts, patterns;
- visual design tokens;
- storefront composition;
- theme-level accessibility/presentation;
- styling/composition of supported WooCommerce blocks/components.

### Grovia Core owns

- functionality that must not vanish when presentation changes;
- user-scoped Shopping List;
- delivery serviceability rules/settings;
- Buy Again orchestration using WooCommerce history;
- setup wizard state/orchestration;
- Grovia-specific REST handlers when platform APIs do not cover a private/product-specific requirement;
- safe Store API extension data where a Woo resource needs Grovia-owned contextual data.

### WooCommerce owns

- commerce truth;
- product/variation inventory truth;
- current price/stock/purchasability;
- cart/order lifecycle;
- customer orders;
- Cart/Checkout Blocks;
- payment/shipping/tax integration contracts.

### WordPress owns

- authentication and user capabilities;
- REST routing/security primitives;
- theme/block system;
- settings/options primitives;
- internationalization;
- Interactivity API foundation where used by supported Woo blocks.

## 3. Validated WooCommerce platform seams — 2026-08-18

Research source: `research/technical/woocommerce-platform-2026.md`.

### Customer-facing product/cart data

Prefer the public WooCommerce Store API for:
- product collections/search data;
- product/category/attribute data;
- current shopper cart state;
- supported add/update/remove cart operations;
- checkout/cart context already owned by WooCommerce.

### Safe extension data

When Grovia needs extra non-sensitive contextual data on a Woo resource, prefer WooCommerce `ExtendSchema` / Store API extension mechanisms rather than a duplicate AJAX API.

Never place sensitive admin/customer data into public Store API extension payloads.

### Private Grovia customer data

Shopping List and similar Grovia-owned private state should use authenticated WordPress REST endpoints/services with strict permission callbacks and user ownership checks.

### Orders / Buy Again

Use WooCommerce CRUD/query APIs only:
- `wc_get_orders()` or another documented public order query API;
- `wc_get_order()` for individual orders;
- order object metadata methods for order metadata.

Never query/write `wp_posts` or `wp_postmeta` directly for order history. This is required for HPOS safety.

### Cart/Checkout

WooCommerce remains authoritative. Grovia may style/extend supported blocks and declare compatibility, but must not replace checkout or manually force malformed client cart state.

For extension-specific server-side cart changes within the Block experience, use supported Store API/cart extension update mechanisms.

### Internal/experimental APIs

Production Grovia code must not depend on:
- `Automattic\WooCommerce\Internal\*`;
- APIs/classes/hooks marked `@internal`.

Experimental Woo APIs require an ADR, feature gate, fallback, and upgrade/removal strategy before use.

## 4. V1 module map

```text
packages/
├── grovia-theme/
│   ├── style.css
│   ├── functions.php
│   ├── theme.json
│   ├── templates/
│   ├── parts/
│   ├── patterns/
│   └── assets/
│
└── grovia-core/
    ├── src/
    │   ├── Delivery/
    │   ├── ShoppingList/
    │   ├── BuyAgain/
    │   ├── Catalog/
    │   ├── CartUx/
    │   ├── StoreApi/
    │   ├── Rest/
    │   ├── Setup/
    │   └── Admin/
    ├── assets/
    ├── languages/
    └── grovia-core.php
```

Names may evolve during implementation; the ownership boundaries should not.

## 5. Request flow examples

### Product search

```text
User types query
  -> debounce/cancel stale request
  -> bounded WooCommerce Store API product request
  -> WooCommerce product truth
  -> minimal result model
  -> render accessible product ledger
  -> direct add only where product requires no missing choice
```

Do not introduce an external search service in V1 without measured need.

### Product add / quantity update

```text
Shopper action
  -> supported WooCommerce Store API/cart operation
  -> optional optimistic UI state
  -> authoritative server/cart response
  -> reconcile Product Ledger + Basket Pulse + Cart
  -> clear recovery state on conflict/network failure
```

The client must never become the permanent source of cart truth.

### Shopping List

```text
Authenticated user action
  -> authenticated Grovia REST route
  -> CSRF/nonce mechanism as appropriate
  -> identity + ownership validation
  -> product/variation identifier validation
  -> ShoppingList service
  -> persistent user-scoped list
  -> current WooCommerce product truth resolved when displayed/added
```

### Buy Again

```text
Authenticated customer
  -> server derives current customer identity
  -> WooCommerce CRUD query for that customer's eligible orders
  -> derive purchased product/variation IDs
  -> resolve current product/variation truth
  -> re-check purchasability/stock/required choices
  -> present eligible products
  -> add selected items through supported WooCommerce cart APIs
```

Never trust a browser-supplied customer ID/order ID as ownership evidence.

### Delivery checker

```text
Postcode input
  -> normalize + validate bounded format
  -> public serviceability handler
  -> consult privileged configured delivery source
  -> return only shopper-needed availability context
```

The final design must avoid contradictory truth between Grovia serviceability and WooCommerce Shipping Zones.

## 6. Block/theme direction

Grovia is block-first:
- `theme.json` for the core visual token system;
- block templates/parts/patterns for composition;
- Woo Product Collection/Filters and other supported blocks where they satisfy the requirement;
- Cart/Checkout Blocks remain Woo-owned;
- Elementor is optional future integration, never required for V1.

WooCommerce is moving new interactive blocks toward the WordPress Interactivity API. Grovia should understand and test that direction, but should not create custom Interactivity API blocks merely because the API is modern. Stable platform components are preferred when they satisfy the validated UX requirement.

## 7. Extension policy

Create Grovia extension points only after at least one real internal use case proves the seam. Do not publish speculative APIs that become permanent compatibility obligations.

Public hooks must be documented and versioned.

## 8. Database policy

Prefer existing WordPress/WooCommerce storage abstractions for V1.

A custom table needs an ADR demonstrating:
- query/load requirement that existing storage cannot meet;
- schema/migration strategy;
- multisite behavior;
- cleanup/uninstall behavior;
- backup/restore implications;
- privacy/export/erase implications where personal data is involved.

No custom order tables: WooCommerce HPOS owns order storage.

## 9. Caching policy

Cache only derived/read-heavy data that can be safely invalidated.

Never:
- cache authorization decisions across users;
- treat cached price/stock as purchase truth;
- duplicate entire WooCommerce order/product records into Grovia storage for convenience.

Current product price/stock/purchasability must be revalidated at meaningful purchase boundaries.

## 10. Failure philosophy

Every asynchronous feature must have a safe fallback:
- search can fall back to conventional Woo/WordPress search;
- add-to-cart failures show clear status and reconcile from authoritative cart state;
- delivery lookup failure does not pretend delivery is available;
- Shopping List failure does not affect cart/checkout;
- Buy Again skips unavailable/changed products and explains the condition;
- optional Grovia UI failure must not make core WooCommerce checkout unusable.

## 11. Third-party integrations

Integrations should be adapters around documented public plugin APIs, not copied vendor code or private internal calls. V1 supports the minimum needed to function with WooCommerce; broad compatibility packs are later work.

## 12. Architecture review triggers

Create/update an ADR when proposing:
- custom database table;
- mandatory runtime dependency;
- new external service;
- external search provider;
- telemetry;
- custom checkout/payment logic;
- Woo experimental API;
- breaking public hook/API;
- split from monorepo to multiple repos;
- significant build-tool/runtime framework addition;
- alternate delivery source of truth outside Woo Shipping Zones;
- support for an additional page builder.
