# Grovia V1 Architecture

## 1. Architectural intent

Grovia is a WordPress/WooCommerce product, not a replacement commerce platform. The architecture should extend stable platform seams and keep Grovia-specific behavior modular.

```text
Browser
  |
  v
WordPress Block Theme (grovia-theme)
  |           \
  |            -> WordPress blocks/theme.json/templates
  v
Grovia Core Plugin
  |
  +--> Shopping List
  +--> Buy Again adapter
  +--> Delivery availability
  +--> Setup/onboarding
  +--> Search/cart UX extensions
  |
  v
WooCommerce public APIs / Store API / hooks
  |
  +--> Products & variations
  +--> Cart
  +--> Customers
  +--> Orders
  +--> Checkout
  +--> Shipping / tax / payments
  |
  v
WordPress data/auth/settings layer
```

## 2. Ownership rules

### Theme owns

- templates, parts, patterns;
- visual design tokens;
- storefront composition;
- theme-level accessibility/presentation;
- styling supported WooCommerce blocks/components.

### Grovia Core owns

- functionality that must not vanish when presentation changes;
- user-scoped Shopping List;
- delivery rules/settings;
- Buy Again orchestration using WooCommerce history;
- setup wizard state/orchestration;
- Grovia-specific REST/AJAX handlers when platform APIs do not cover the requirement.

### WooCommerce owns

- commerce truth;
- product/variation inventory truth;
- cart/order lifecycle;
- customer orders;
- checkout;
- payment/shipping/tax integration contracts.

## 3. V1 module map

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
    │   ├── Search/
    │   ├── CartUx/
    │   ├── Setup/
    │   └── Admin/
    ├── assets/
    ├── languages/
    └── grovia-core.php
```

Names may evolve during implementation; the ownership boundaries should not.

## 4. Request flow examples

### Product search

```text
User types query
  -> debounce/cancel stale request
  -> bounded public product search endpoint
  -> validate normalized query
  -> WooCommerce product query
  -> minimal DTO/response
  -> render accessible results
  -> direct add where product needs no missing choices
```

### Shopping List

```text
Authenticated user action
  -> nonce/API CSRF control as applicable
  -> identity + ownership validation
  -> product/variation identifier validation
  -> ShoppingList service
  -> persistent user-scoped list
  -> response uses current WooCommerce product truth
```

### Buy Again

```text
Authenticated customer
  -> retrieve only orders accessible to that customer
  -> derive purchased product/variation IDs
  -> resolve current purchasability/stock/price
  -> show eligible products
  -> add selected items through WooCommerce cart APIs
```

### Delivery checker

```text
Postcode input
  -> normalize
  -> validate bounded format
  -> match configured delivery zones/postcodes
  -> return availability result
```

## 5. Extension policy

Create Grovia extension points only after at least one real internal use-case proves the seam. Do not publish speculative APIs that become permanent compatibility obligations.

Public hooks must be documented and versioned.

## 6. Database policy

Prefer existing WordPress/WooCommerce storage abstractions for V1. A custom table needs an ADR demonstrating:
- query/load requirement that existing storage cannot meet;
- schema/migration strategy;
- multisite behavior;
- cleanup/uninstall behavior;
- backup/restore implications.

## 7. Caching policy

Cache only derived/read-heavy data that can be safely invalidated. Never cache authorization decisions across users. Product price/stock is always revalidated at meaningful purchase boundaries.

## 8. Failure philosophy

Every asynchronous feature must have a safe fallback:
- search can fall back to normal search;
- add-to-cart failures show clear status and leave cart authoritative;
- delivery lookup failure does not pretend delivery is available;
- Shopping List failure does not affect cart/checkout;
- Buy Again skips unavailable products and explains why.

## 9. Third-party integrations

Integrations should be adapters around public plugin APIs, not copied vendor code or private internal calls. V1 supports the minimum needed to function with WooCommerce; broad compatibility packs are later work.

## 10. Architecture review triggers

Create/update an ADR when proposing:
- custom database table;
- mandatory runtime dependency;
- new external service;
- external search provider;
- telemetry;
- custom checkout/payment logic;
- breaking public hook/API;
- split from monorepo to multiple repos;
- significant build-tool/runtime framework addition.
