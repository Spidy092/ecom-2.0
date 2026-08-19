# AisleFlow Foundation Prototype

**Status:** implementation prototype for review, not V1 production completion  
**Date:** 2026-08-19

## Why this exists

This slice turns the already-researched AisleFlow direction into a real WordPress/WooCommerce surface without pretending the unvalidated interactions are finished.

It follows the current thesis documented in `research/ux-benchmarks/grocery-critical-flow.md` and `research/design/critical-flow-directions.md`:

> Build the basket, not the theme demo.

## Problem being tested

A grocery shopper should reach useful shopping controls immediately instead of moving through a large marketing hero, decorative category blocks, and several product-detail page transitions.

## What this slice implements

- a real block theme package under `packages/grovia-theme/`;
- a real Grovia Core plugin bootstrap under `packages/grovia-core/`;
- mobile-first shopping-workspace hierarchy;
- delivery context placed before search;
- prominent search;
- Aisle Rail navigation;
- compact grocery product shelf using WooCommerce-owned product/cart behavior;
- a shopping-focused fixed mobile navigation prototype;
- default system fonts and no third-party frontend framework, slider, or page-builder runtime.

## What is intentionally not faked

This slice does **not** claim these features are implemented yet:

- inline add -> quantity stepper reconciliation;
- Basket Pulse / persistent authoritative cart total;
- postcode serviceability checking;
- Shopping List;
- Buy Again / This Week;
- setup wizard;
- production demo imagery or final brand identity.

Those require their own public WooCommerce/Grovia service seams, authorization rules, tests, failure states, accessibility review, and user validation.

## Why this is materially different from the benchmark

The differentiator is structural, not decorative:

1. no full-screen hero before shopping;
2. shopping context -> search -> aisles -> products is the first path;
3. categories act as store structure rather than a decorative icon grid;
4. card chrome is intentionally reduced instead of stacking wishlist/compare/quick-view utilities;
5. the runtime baseline remains WordPress + WooCommerce + Grovia Theme + Grovia Core.

## Review criteria

Before promoting this beyond prototype status, test on mobile:

- Is the first useful shopping action obvious without scrolling through marketing content?
- Can a shopper reach search and products quickly?
- Does the Aisle Rail clarify navigation rather than add another layer?
- Are touch targets, focus states and reflow usable?
- Does the 2-column mobile shelf feel dense but readable?
- Does the absence of a hero make the product feel purposeful rather than unfinished?

## Next vertical slice

Implement one real interaction end-to-end:

**simple product Add -> quantity state -> WooCommerce-authoritative cart reconciliation -> accessible status feedback.**

Do not add Shopping List, Buy Again, delivery rules, or setup-wizard code in that same slice.
