# Grocery Search Gap — 2026-08-21

**Status:** Engineering-alpha product/research note
**Scope:** Next search increment after bounded Store API search and typo recovery

## Customer task

A returning grocery shopper searches for a familiar staple, recognizes it in the result list, and adds the amount they normally buy without leaving the basket-building workspace.

## Current baseline

The current alpha search already provides:

- bounded WooCommerce Store API queries;
- debounced and cancellable requests;
- typo recovery after an empty result set;
- product image, name, price and current cart quantity state;
- direct add for simple purchasable products;
- a conventional GET fallback.

The current search does not connect a matching result to the shopper's private purchase history. Buy Again exists as a separate authenticated account surface and already returns bounded product IDs and remembered quantities.

## Market benchmark

### WooCommerce Product Search

The official extension offers live search, product thumbnails, prices, direct add-to-cart, filters, search statistics, an indexer and relevance/search-weight controls. This is a strong commodity/search-platform baseline, but it is a separate extension and is not grocery-specific repeat-shopping context.

Source: https://woocommerce.com/products/woocommerce-product-search/ (accessed 2026-08-21)

### FiboSearch

The WordPress.org listing emphasizes autocomplete, title/description/SKU search, thumbnails, prices, mobile search, search history, quantity actions and typo tolerance. This validates that autocomplete and typo correction are crowded expectations, not a defensible Grovia moat.

Source: https://wordpress.org/plugins/ajax-search-for-woocommerce/ (accessed 2026-08-21)

### Beplus Fast Product Filter & Live Search

The WordPress.org listing combines live search, filters, typo correction, direct add-to-cart, caching and a standard GET fallback. It also explicitly markets accessibility and progressive enhancement. Grovia should not compete on another generic live-search/filter checklist.

Source: https://wordpress.org/plugins/beplus-fast-product-filter-live-search-for-woocommerce/ (accessed 2026-08-21)

### Grocery themes such as QuickMart and Gromark

Current grocery theme listings emphasize block patterns, smart filters, quick add, AJAX search, variation controls, one-click import, Elementor compatibility and multiple demos. These are useful market expectations, but they do not demonstrate a search experience that connects a private household purchase rhythm to the active result ledger.

Sources:
- https://woocommerce.com/products/quickmart/ (accessed 2026-08-21)
- https://gloriathemes.com/theme/gromark-grocery-store-food-woocommerce-wordpress-theme/ (accessed 2026-08-21)

## Commodity expectations

Do not treat these as differentiation:

- autocomplete/live search;
- typo correction;
- SKU/title search;
- thumbnails and prices in suggestions;
- AJAX filters;
- direct add-to-cart;
- mobile search;
- generic search analytics;
- a GET fallback.

## Observed gap and uniqueness thesis

The useful gap is not a new search algorithm. It is **repeat-aware basket search**: preserve WooCommerce's normal relevance, then add private, current-user-only context to a matching product result: `Bought before · 3 last time` and an explicit `Add 3 again` action. This joins the high-frequency grocery task to the existing Buy Again capability without exposing order data or creating a second commerce/search system.

The result must remain a product ledger. It must not reorder results, silently substitute a product, or auto-add a remembered quantity. Variable products keep `Choose options` because a parent-product match is not enough to safely reconstruct a variation choice.

## Alternatives considered

1. **Autocomplete dropdown:** familiar and useful, but directly overlaps mature WooCommerce search extensions and adds another interaction surface.
2. **External/relevance search service:** potentially stronger for large catalogs, but no catalog evidence justifies its V1 cost, privacy surface or dependency burden.
3. **Repeat-aware result context:** uses the existing private Buy Again boundary, adds no runtime dependency, preserves current Store API relevance and directly tests Grovia's grocery-specific repeat-shopping thesis.

**Selected:** repeat-aware result context.

## Measurable success criterion

In an authenticated repeat-shopper mission, a shopper can search for a known previously purchased simple product and add the remembered quantity with one result action, without opening the separate Buy Again surface. Measure time, deliberate interactions and wrong-quantity corrections against the current search flow. This is an alpha hypothesis, not a marketing claim.

## V1 fit and constraints

This belongs in V1 because repeat shopping is a protected V1 outcome, Buy Again is already implemented, and the work strengthens the existing search-to-basket vertical slice instead of broadening the product into a search platform.

- Load purchase history once per workspace and never per keystroke.
- Do not delay ordinary search results while history loads.
- Guests make no private history request.
- Use only authenticated current-user product IDs and bounded quantities.
- Keep current WooCommerce product stock, price, option and cart truth authoritative.
- Fail closed to ordinary search if history cannot load.
- Do not log order or customer payloads.

## Decision

Proceed with a small engineering-alpha implementation and authenticated browser coverage. Promote the behavior only after a human repeat-shopper test shows fewer interactions or less quantity correction than the separate Buy Again/search paths.
