# Contextual grocery filters — engineering alpha

Date: 2026-08-19

## Decision

Build a bounded contextual filter layer on top of the existing shared Product Workspace.

The alpha supports only:

1. **Availability** — an `In stock only` control backed by WooCommerce Store API `stock_status`.
2. **Price** — minimum and maximum price backed by Store API `min_price` / `max_price` in currency minor units.
3. **Global product attributes** — a bounded set of WooCommerce global attributes backed by Store API `attributes` queries.

Category remains owned by Adaptive Department Browse. Filters do not introduce a second category UI.

## Source of truth

Use WooCommerce Store API only:

- `/wc/store/v1/products` for filtered products;
- `/wc/store/v1/products/collection-data` for contextual price range, stock counts and attribute counts;
- `/wc/store/v1/products/attributes` for global attribute definitions;
- `/wc/store/v1/products/attributes/:id/terms` for terms.

No custom product-filter REST API, duplicate product index, custom facet database, or Cart controller.

## Context model

Filters apply to one active product context at a time:

- Search query, or
- selected top-level department.

Changing Search/Browse context resets active filters for the alpha. This avoids hidden cross-context state and makes the behavior easy to understand and test.

## Attribute bound

The alpha may expose at most **4** global product attributes in the filter UI. The cap is an engineering heuristic to keep requests/UI bounded, not a public product promise.

Only attributes with non-zero terms in the active context are shown.

## UX

- A visible `Filters` control sits adjacent to the shared product result surface.
- The panel is progressively enhanced and keyboard reachable.
- `Apply filters` executes one bounded product request for the active context.
- `Clear filters` restores the unfiltered active context.
- Active filter count is visible on the Filters control.
- Empty results keep the current context and filters visible so shoppers can relax them.
- Loading/error state is announced through the existing shared product status region.

## Commercial-stack integration

The initial implementation was proven independently in #43 / PR #49. Issue #67 owns bringing the same bounded behavior onto the later Market Ledger / packaging / native-customization / Store Setup / onboarding branch line without regressing those systems.

## Explicitly out of scope

- ratings;
- sort controls;
- brands as a separate taxonomy UI;
- sale-only filtering;
- tags;
- saved/persisted filter presets;
- URL synchronization/deep-linking;
- merchant filter configuration;
- recursive category filters;
- AI/personalized filters;
- a custom search/filter index.

## Gate

Do not ship until the existing Search, Cart, Saved, mobile nav, Adaptive Department Browse, visual-system/customization, Delivery, package/provenance and Store Setup/onboarding regressions remain green in pinned WordPress + WooCommerce + Chrome, plus real-browser tasks for availability, price, attributes, clear/reset and narrow screens.
