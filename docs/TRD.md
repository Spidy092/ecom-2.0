# Technical Requirements Document — Grovia V1

**Status:** Draft  
**Date:** 2026-08-18

## 1. Technical objective

Build a commercially maintainable WordPress/WooCommerce product with a small attack surface, clear ownership boundaries, strong mobile performance, and a release process suitable for recurring updates.

The architecture must not depend on a proprietary page builder for core storefront behavior.

## 2. Product components

### `grovia-theme`

Owns presentation and theme integration:
- `theme.json` design tokens and global styles;
- templates and template parts;
- block patterns;
- WooCommerce presentation/styling integration;
- assets directly related to presentation;
- accessibility-minded markup around theme-owned UI.

It must not become the permanent home for reusable application/business functionality.

### `grovia-core`

Owns Grovia-specific functionality that should survive a theme change or is not purely presentation:
- Shopping List behavior;
- Buy Again orchestration/UX endpoints;
- delivery-availability rules/configuration;
- setup/onboarding orchestration;
- lightweight storefront search enhancements where core WooCommerce behavior is insufficient;
- cart/quantity interaction helpers where an extension is required;
- admin/system-status integration specific to Grovia.

### WooCommerce

WooCommerce remains authoritative for:
- products;
- product variations;
- cart/order domain behavior;
- customers/accounts;
- taxes/shipping/payment integrations;
- checkout/order lifecycle.

Grovia must not fork or duplicate WooCommerce's core commerce model.

### WordPress

WordPress remains authoritative for:
- authentication/user capabilities;
- settings and options APIs;
- HTTP/REST foundations;
- block/theme system;
- internationalization;
- cron/scheduling primitives where needed;
- standard escaping/sanitization APIs.

## 3. Repository strategy

V1 may begin as a monorepo while the product boundaries stabilize:

```text
/
├── AGENTS.md
├── docs/
├── packages/
│   ├── grovia-theme/
│   └── grovia-core/
├── tools/
├── tests/
└── .github/workflows/
```

Separate repositories should be created only when independent release/security/ownership needs justify the operational overhead.

## 4. Minimum runtime dependency philosophy

Required runtime dependencies should remain:
- WordPress;
- WooCommerce;
- Grovia Theme;
- Grovia Core.

Do not require Elementor, Slider Revolution, a generic addon bundle, wishlist plugin, filter plugin, or mega-menu plugin to make the core demo function.

A dependency proposal must document:
1. why WordPress/WooCommerce cannot reasonably provide the capability;
2. package/license;
3. update owner;
4. security impact;
5. frontend cost;
6. exit/migration path.

## 5. PHP requirements

Exact minimum PHP version is an open decision until compatibility research is finalized. Once locked:
- use only language features supported by the declared minimum;
- follow WordPress coding conventions unless a documented project ADR explicitly differs;
- avoid global mutable state where possible;
- isolate services behind small interfaces;
- never access superglobals deep inside domain services; normalize/validate at boundaries.

## 6. Data model

### Shopping List

Preferred V1 direction:
- one list per authenticated customer;
- store product/variation identifiers and minimal metadata needed to preserve intent;
- do not duplicate product price/stock truth;
- recalculate availability/price from WooCommerce at render/add-to-cart time;
- ensure user-scoped authorization for every read/write.

Guest shopping lists are deferred unless implementation/testing evidence shows a low-risk cookie/session approach is necessary for product-market fit.

### Buy Again

Do not create a duplicate purchase-history database. Derive eligible products from WooCommerce orders the authenticated customer is authorized to access. Cache only when justified and invalidate conservatively.

### Delivery availability

V1 supports configured postcodes/zones, mapped to store-defined availability. Do not model fleet logistics. Settings must be validated and administration capability-protected.

## 7. HTTP / REST / AJAX boundaries

Where asynchronous behavior is required:
- prefer supported WordPress/WooCommerce REST or Store API extension patterns;
- state-changing requests require CSRF protections appropriate to the selected API pattern plus authorization/capability validation;
- endpoints must return structured error codes/messages;
- rate-sensitive public endpoints such as search must be designed to avoid expensive unbounded queries;
- no endpoint may accept arbitrary SQL fragments, template paths, filenames, URLs, callbacks, class names, or executable content from the client.

## 8. Search

V1 search must remain compatible with standard WooCommerce product data.

Initial implementation should avoid introducing an external search service. Optimize the built-in path first through bounded queries, caching where safe, and reduced payloads. External search is a later scaling option, not a V1 requirement.

## 9. Frontend JavaScript

Principles:
- progressively enhance platform behavior;
- split by feature/page where practical;
- no global framework solely for small interactions;
- asynchronous state must expose loading/success/error states to assistive technologies where relevant;
- avoid DOM polling when platform events/hooks are available;
- cancel/debounce search requests where appropriate;
- prevent race conditions between rapid quantity changes and cart state.

## 10. CSS / design system

Use `theme.json` and WordPress primitives wherever appropriate.

Establish tokens for:
- color;
- typography;
- spacing;
- radii;
- borders;
- shadows;
- motion;
- responsive layout constraints.

Avoid per-demo token drift. The starter site should be a composition of one coherent design system.

## 11. Accessibility acceptance requirements

For Grovia-owned interactive UI:
- keyboard operable;
- logical tab order;
- visible focus;
- semantic button/link behavior;
- labels/names for icon-only actions;
- live status for asynchronous cart/search changes when necessary;
- no color-only status communication;
- reduced-motion support;
- touch targets appropriate for mobile shopping;
- focus management for drawers/dialogs;
- no inaccessible custom select replacement without a compelling need.

## 12. Performance budgets

Initial engineering budgets; values can be refined after prototypes:
- load only feature JS/CSS when needed;
- avoid blocking third-party assets in the default demo;
- image dimensions/responsive sources must be explicit;
- no autoplay hero video in V1 default demo;
- no mandatory slider library;
- search endpoints must use bounded result counts;
- prevent N+1 product/order queries in Shopping List and Buy Again.

The controlled demo should target 90+ Lighthouse categories and preferably 95+ where realistic, but marketing may only publish reproducible results with device/network/hosting/test conditions.

## 13. Security requirements

`docs/SECURITY.md` is normative. A known high-severity vulnerability, missing authorization boundary, unsanitized dangerous sink, or unsupported vulnerable dependency blocks release.

## 14. Testing strategy

### Unit tests

Use for isolated rules such as delivery matching and small transformations.

### Integration tests

Prioritize behavior through WordPress/WooCommerce public seams:
- Shopping List ownership and state;
- Buy Again authorization/product eligibility;
- cart quantity synchronization;
- setup settings;
- endpoint validation.

### Browser/E2E tests

Critical journeys:
- search -> add -> adjust quantity;
- delivery availability;
- cart -> checkout handoff;
- Shopping List;
- Buy Again;
- mobile navigation;
- setup wizard.

### Accessibility tests

Automated checks plus manual keyboard/screen-reader spot testing on critical interactions.

## 15. CI quality gates

Pull requests should run, as the codebase becomes available:
- PHP syntax checks;
- WordPress Coding Standards/PHPCS;
- PHP static analysis (configured to a realistic baseline);
- JavaScript lint/type checks if applicable;
- unit/integration tests;
- build/package validation;
- dependency/vulnerability scanning;
- selected browser smoke tests.

Release tags should additionally produce deterministic ZIP artifacts and verify the package contents.

## 16. Packaging

Commercial release artifacts should be generated, never manually assembled:
- `grovia.zip`;
- `grovia-core.zip`;
- checksums;
- changelog/release metadata.

Exclude development dependencies, local config, secrets, tests not required by customers, and unrelated build sources from customer ZIPs.

## 17. Observability and privacy

V1 should not collect usage telemetry by default.

If telemetry is later proposed:
- opt-in only unless legal/product review explicitly changes the policy;
- document fields, purpose, retention, and processor;
- collect the minimum data needed;
- never collect product/customer/order personal data merely for product analytics.

## 18. Compatibility policy

Before V1 implementation, lock:
- minimum/supported PHP versions;
- WordPress current and previous supported target(s);
- WooCommerce current and previous supported target(s);
- Chrome, Firefox, Safari, Edge support policy;
- mobile viewport/device test baseline.

Every compatibility claim must be backed by CI/manual validation, not listing-page optimism.

## 19. Definition of done for a feature

A feature is done only when:
- acceptance behavior works;
- authorization/input/output handling is correct;
- tests cover the agreed public seam;
- accessibility behavior is reviewed;
- assets load only where necessary;
- failure/empty/loading states exist;
- documentation is updated;
- it passes available CI gates;
- it does not silently expand V1 scope.
