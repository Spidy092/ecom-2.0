# Buy Again V1 research note

**Date:** 2026-08-24  
**Status:** implementation gate for the V1 Buy Again vertical slice

## Customer problem

A repeat grocery shopper should recover a familiar product from a recent order without searching again or rebuilding an entire order. Current stock, price, and product-choice truth must still win because grocery availability changes between purchases.

## Competitors benchmarked

- WooCommerce core My Account: native **Order again** recreates a completed order, useful for full-basket repetition but coarse for replacing one grocery staple.
- WooCommerce Repeat Order and Buy Again: provides a dedicated My Account list and configurable statuses, but adds broader configuration than V1 needs.
- Buy Again for WooCommerce: adds lists, quantity memory, filters, reminders, and multiple entry points; these are expectations rather than a durable Grovia moat.

Sources reviewed:

- https://woocommerce.com/document/the-my-account-page/
- https://woocommerce.com/document/repeat-order-and-buy-again/
- https://woocommerce.com/document/buy-again/

## Observed gap and uniqueness thesis

Grovia uses one bounded account surface: recent eligible products, deduplicated by current parent product, remembered quantity, and direct add only where a simple product is safely purchasable. Variable or changed products remain explicit (`Choose options` or unavailable) instead of silently selecting stale purchase data.

The advantage is grocery-task speed plus truthful current commerce state, not a large reorder administration system.

## Alternatives considered

1. Whole-order replay: defer to WooCommerce’s native behavior; too coarse for a shopper replacing one or two staples.
2. All-history reorder table: defer; unbounded history and filters increase query, privacy, and maintenance cost.
3. Recent per-product Buy Again: selected for V1 because it is bounded, mobile-scannable, and supports repeat grocery tasks directly.

## Measurable success criterion

In the controlled browser task, a logged-in shopper can add one previously purchased simple product from My Account in at most two primary interactions, with no catalog search or product-page navigation. The endpoint remains bounded to 20 orders, 50 unique products, and one current-product resolution pass.

## Scope and risks

V1 includes logged-in customers, processing/completed orders, direct add for simple purchasable products, remembered quantities, and safe variable/unavailable states. It excludes guest history, reminders, bulk reorder, recommendations, and a separate purchase-history table.

The protected boundary is purchase history. The server derives the current user, uses WooCommerce CRUD/query APIs, returns only product IDs and remembered quantities, and never exposes order/customer identifiers or raw order data.
