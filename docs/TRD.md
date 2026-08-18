# Technical Requirements Document — Grovia V1

**Status:** Draft, platform seams validated 2026-08-18  
**Date:** 2026-08-18

## 1. Technical objective

Build a commercially maintainable WordPress/WooCommerce product with a small attack surface, clear ownership boundaries, strong mobile performance, and a release process suitable for recurring updates.

The architecture must not depend on a proprietary page builder for core storefront behavior.

Current platform research: `research/technical/woocommerce-platform-2026.md`.

## 2. Product components

### `grovia-theme`

Owns presentation and theme integration:
- `theme.json` design tokens and global styles;
- templates and template parts;
- block patterns;
- WooCommerce block presentation/styling integration;
- assets directly related to presentation;
- accessibility-minded markup around theme-owned UI.

It must not become the permanent home for reusable application/business functionality.

### `grovia-core`

Owns Grovia-specific functionality that should survive a theme change or is not purely presentation:
- Shopping List behavior;
- Buy Again orchestration;
- delivery-availability rules/configuration;
- setup/onboarding orchestration;
- lightweight storefront enhancements where WooCommerce's standard blocks/API do not meet the validated grocery UX;
- Store API extension data/callbacks where appropriate;
- authenticated Grovia REST routes for private customer/product-specific state;
- admin/system-status integration specific to Grovia.

### WooCommerce

WooCommerce remains authoritative for:
- products;
- product variations;
- current price/stock/purchasability;
- cart/order domain behavior;
- customers/accounts;
- taxes/shipping/payment integrations;
- Cart/Checkout Blocks;
- checkout/order lifecycle.

Grovia must not fork or duplicate WooCommerce's core commerce model.

### WordPress

WordPress remains authoritative for:
- authentication/user capabilities;
- settings and options APIs;
- HTTP/REST foundations;
- block/theme system;
- Interactivity API foundation where applicable;
- internationalization;
- cron/scheduling primitives where needed;
- standard escaping/sanitization APIs.

## 3. Repository strategy

V1 may begin as a monorepo while the product boundaries stabilize:

```text
/
├── AGENTS.md
├── docs/
├── research/
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

## 5. PHP and platform requirements

### Current upstream snapshot (2026-08-18)

- WooCommerce stable: 11.0.0.
- WooCommerce 11.0 metadata: WordPress 6.9+, PHP 7.4+, tested through WordPress 7.0.3.
- WordPress current download: 7.0.3.
- WordPress and WooCommerce recommend PHP 8.3+.

### Grovia engineering target

- develop and optimize on PHP 8.3+;
- test against current stable WordPress and WooCommerce;
- do not use PHP 7.4 as the default Grovia quality target merely because WooCommerce permits it;
- exact customer minimum remains open until target-host/customer compatibility research is complete;
- once locked, use only language features supported by the declared minimum;
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
- ensure user-scoped authorization for every read/write;
- expose it through authenticated Grovia-owned API/service boundaries, not public Store API extension data.

Guest shopping lists are deferred unless implementation/testing evidence shows a low-risk cookie/session approach is necessary for product-market fit.

Exact persistence (user meta vs custom table) remains open; a custom table requires an ADR and demonstrated query/scale need.

### Buy Again

Do not create a duplicate purchase-history database.

Requirements:
- derive eligible products from WooCommerce orders the authenticated customer is authorized to access;
- use WooCommerce public CRUD/query APIs (`wc_get_orders()`, `wc_get_order()`, order object methods as appropriate);
- never use direct `wp_posts` / `wp_postmeta` access for order data;
- remain compatible with HPOS;
- resolve current product/variation truth before presenting or adding;
- cache only when justified and invalidate conservatively.

### Delivery availability

V1 supports configured postcodes/zones mapped to serviceability. Do not model fleet logistics.

Requirements:
- settings are capability-protected;
- public result exposes only shopper-needed availability context;
- postcode input is normalized and bounded;
- final design must define how serviceability relates to WooCommerce Shipping Zones so two contradictory delivery truths cannot emerge.

## 7. HTTP / REST / Store API boundaries

### Shopper-facing WooCommerce data

Prefer supported WooCommerce Store API for:
- product/catalog data;
- category/attribute context;
- current shopper cart state;
- supported cart mutations;
- WooCommerce-owned checkout/cart context.

### Store API extension data

Use WooCommerce `ExtendSchema` / public extension functions when Grovia needs safe namespaced contextual data on product/cart resources.

Rules:
- do not expose sensitive/private settings or another user's data;
- callbacks must be bounded and fast;
- never duplicate core price/stock/cart truth.

### Private Grovia data

Use authenticated WordPress REST routes/services for private state such as Shopping List, with explicit permission callbacks and user/resource ownership checks.

### State-changing requests

- use the CSRF/nonce mechanism appropriate to the selected WordPress/Woo Store API route plus authorization where needed;
- nonces do not replace capabilities/ownership;
- endpoints return structured error codes/messages;
- no endpoint accepts arbitrary SQL fragments, template paths, filenames, URLs, callbacks, class names, or executable content from the client.

### Cart state

The WooCommerce server/session is authoritative.

Grovia may show optimistic UI while a request is pending, but must reconcile from the Woo response. For extension-specific Cart/Checkout processing, use supported Woo cart-extension update mechanisms rather than manually forcing client block state.

## 8. Search

V1 search must remain compatible with standard WooCommerce product data.

Initial implementation:
- use/buildupon WooCommerce Store API product/catalog capabilities;
- use bounded result sets/pagination;
- cancel/debounce stale search requests;
- request/render only needed fields/context;
- avoid introducing an external search service before realistic catalog measurements demonstrate a need;
- preserve conventional search fallback when enhanced JavaScript fails.

External search is a later scaling option, not a V1 requirement.

## 9. WooCommerce API stability rule

Production Grovia code must not depend on:
- `Automattic\WooCommerce\Internal\*`;
- classes/methods/hooks marked `@internal`.

Experimental Woo APIs require a documented ADR, feature gate, fallback and upgrade/removal strategy before use.

Integration tests should prove behavior through public WooCommerce seams rather than private implementation details.

## 10. Cart and Checkout Blocks

WooCommerce owns Cart and Checkout.

Grovia requirements:
- style/compose supported Cart/Checkout Blocks;
- declare compatibility only after validation;
- use documented Woo block/Store API extension interfaces;
- verify legacy hooks against Woo's current hook-alternatives docs before relying on them;
- do not replace checkout;
- do not build a custom payment gateway in V1.

## 11. Frontend JavaScript / Interactivity

Principles:
- progressively enhance platform behavior;
- split by feature/page where practical;
- no global frontend framework solely for small interactions;
- asynchronous state exposes loading/success/error states to assistive technologies where relevant;
- avoid DOM polling when platform events/APIs are available;
- cancel/debounce search requests where appropriate;
- prevent race conditions between rapid quantity changes and cart state;
- reconcile all cart state with WooCommerce.

WooCommerce is moving new interactive blocks toward WordPress Interactivity API. Grovia should be compatible with that direction and use stable Woo components where possible, but must not create custom Interactivity API blocks simply for novelty.

## 12. CSS / design system

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

## 13. Accessibility acceptance requirements

For Grovia-owned interactive UI:
- keyboard operable;
- logical tab order;
- visible focus;
- semantic button/link behavior;
- labels/names for icon-only actions;
- live status for asynchronous cart/search changes when necessary without excessive announcement noise;
- no color-only status communication;
- reduced-motion support;
- touch targets appropriate for mobile shopping;
- focus management for drawers/dialogs/surfaces;
- no inaccessible custom select replacement without a compelling need;
- 200% zoom/reflow testing on critical grocery flows.

## 14. Performance budgets

Initial engineering budgets; values can be refined after prototypes:
- load only feature JS/CSS when needed;
- avoid blocking third-party assets in the default demo;
- image dimensions/responsive sources must be explicit;
- no autoplay hero video in V1 default demo;
- no mandatory slider library;
- search endpoints use bounded result counts;
- prevent N+1 product/order queries in Shopping List and Buy Again;
- Store API extension callbacks used across product collections must be cheap/bounded;
- do not preload all variation data for every product card without measured justification.

The controlled demo should target 90+ Lighthouse categories and preferably 95+ where realistic, but marketing may only publish reproducible results with device/network/hosting/test conditions.

## 15. Security requirements

`docs/SECURITY.md` is normative. A known high-severity vulnerability, missing authorization boundary, unsanitized dangerous sink, unsupported vulnerable dependency, private data exposed through public Store API schema, or direct unsafe order storage access blocks release.

## 16. Testing strategy

### Unit tests

Use for isolated rules such as delivery matching and small transformations.

### Integration tests

Prioritize behavior through WordPress/WooCommerce public seams:
- Shopping List ownership and state;
- Buy Again authorization/product eligibility under HPOS;
- Store API product/cart integration;
- cart quantity synchronization/reconciliation;
- setup settings;
- endpoint validation;
- variable product choice rules once defined.

### Browser/E2E tests

Critical journeys:
- search -> add -> adjust quantity;
- delivery availability;
- cart -> checkout handoff;
- Shopping List;
- Buy Again;
- mobile navigation;
- setup wizard;
- network/error recovery for basket changes.

### Accessibility tests

Automated checks plus manual keyboard/screen-reader/zoom spot testing on critical interactions.

## 17. CI quality gates

Pull requests should run, as the codebase becomes available:
- PHP syntax checks;
- WordPress Coding Standards/PHPCS;
- PHP static analysis (configured to a realistic baseline);
- rule/search preventing production use of Woo `Internal` namespace where practical;
- JavaScript lint/type checks if applicable;
- unit/integration tests;
- HPOS-enabled integration tests;
- Cart/Checkout Blocks compatibility tests where Grovia integrates;
- build/package validation;
- dependency/vulnerability scanning;
- selected browser smoke tests.

Compatibility matrix should include:
- declared minimum WP/Woo/PHP combination;
- current stable WP/Woo on PHP 8.3;
- current stable WP/Woo on newest supported PHP;
- upcoming WordPress/Woo pre-release as a non-blocking/nightly signal where practical.

Evaluate WooCommerce QIT as an additional compatibility signal once implementation packages exist.

Release tags should additionally produce deterministic ZIP artifacts and verify package contents.

## 18. Packaging

Commercial release artifacts should be generated, never manually assembled:
- `grovia.zip`;
- `grovia-core.zip`;
- checksums;
- changelog/release metadata.

Exclude development dependencies, local config, secrets, tests not required by customers, and unrelated build sources from customer ZIPs.

## 19. Observability and privacy

V1 should not collect usage telemetry by default.

If telemetry is later proposed:
- opt-in only unless legal/product review explicitly changes the policy;
- document fields, purpose, retention, and processor;
- collect the minimum data needed;
- never collect product/customer/order personal data merely for product analytics.

## 20. Compatibility policy

Before paid beta, lock:
- minimum/supported PHP versions;
- WordPress minimum/current target(s);
- WooCommerce minimum/current target(s);
- Chrome, Firefox, Safari, Edge support policy;
- mobile viewport/device test baseline.

Current platform snapshots in research documents are not permanent support promises. Every compatibility claim must be backed by CI/manual validation, not marketplace metadata optimism.

## 21. Definition of done for a feature

A feature is done only when:
- research/uniqueness gate is satisfied for market-facing work;
- acceptance behavior works;
- authorization/input/output handling is correct;
- tests cover the agreed public WordPress/WooCommerce seam;
- HPOS/Blocks implications are handled where relevant;
- accessibility behavior is reviewed;
- assets/load/query costs are justified;
- failure/empty/loading states exist;
- documentation is updated;
- it passes available CI gates;
- it does not silently expand V1 scope.
