# Buy Again V1 research note

**Date:** 2026-08-21
**Status:** implementation gate for the V1 Buy Again vertical slice

## Customer problem

A repeat grocery shopper should be able to recover a familiar item from a recent order without searching the catalog again or rebuilding an entire order. The shopper needs current stock, price, and product-choice truth because grocery availability changes between purchases.

## Competitors benchmarked

- WooCommerce core My Account: the native **Order again** action recreates a completed order, which is useful for full-basket repetition but is not an efficient single-item grocery recovery surface.
- WooCommerce Repeat Order and Buy Again: adds a dedicated My Account Buy Again area and supports configurable eligible order statuses, but is a broad extension surface with table/configuration concepts beyond Grovia V1.
- Buy Again for WooCommerce: adds My Account lists, quantity memory, filters, reminders, and multiple reorder entry points; those are commodity expectations rather than a V1 moat.

Sources reviewed 2026-08-21:

- https://woocommerce.com/document/the-my-account-page/
- https://woocommerce.com/document/repeat-order-and-buy-again/
- https://woocommerce.com/document/buy-again/

## Observed gap and uniqueness thesis

Grovia will ship one focused account surface: recent eligible products, deduplicated by current parent product, with remembered quantity and direct add for safe simple products. Variable or changed products remain explicit (“Choose options” or unavailable) instead of silently selecting stale purchase data.

The advantage is grocery-task speed plus truthful current commerce state, not a large reorder administration system.

## Alternatives considered

1. Whole-order replay: defer to WooCommerce’s native Order again behavior; too coarse for a shopper replacing one or two staples.
2. All-history reorder table: defer; unbounded history and filters increase query, privacy, and maintenance cost.
3. Recent per-product Buy Again: selected for V1 because it is bounded, mobile-scannable, and directly supports repeat grocery tasks.

## Measurable success criterion

In the controlled browser task, a logged-in shopper can add one previously purchased simple product from My Account in at most two primary interactions, with no catalog search and no product-page navigation. The endpoint remains bounded to 20 orders, 50 unique products, and one current-product resolution pass.

## Scope and risks

V1 includes logged-in customers, processing/completed orders, direct add for simple purchasable products, and safe states for variable/unavailable products. It excludes guest history, reminders, bulk reorder, recommendations, and a custom persistence table.

The protected boundary is purchase history. The server derives the current user, uses WooCommerce CRUD/query APIs, returns only product IDs and remembered quantities, and does not expose order/customer identifiers or raw order data.
