# Modern Grocery V1 build note

**Status:** Implementation gate passed — first vertical slice and theme presentation wiring
**Date:** 2026-08-24
**Scope:** Theme shell, delivery checker, private Shopping List boundary, and UI/UX style delivery

## Customer problem

Repeat grocery shoppers need to confirm delivery, scan products, and keep a basket moving without being interrupted by promotional sections or a new page for every small action. Store owners need a credible storefront that does not require a page-builder bundle before the core shopping journey works.

## Evidence and benchmark

The current competitor and grocery-product research in `docs/MARKET-RESEARCH.md` and `research/design/critical-flow-directions.md` shows a consistent baseline: prominent search, delivery/location context, direct add actions, and repeat-shopping entry points. Those patterns are useful conventions, not a differentiator by themselves.

## Chosen approach

Modern Grocery opens with a **basket workspace**: delivery certainty and search appear before promotional content, the Aisle Rail is expressed through numbered category navigation, and product collections retain a consistent information hierarchy for pack, price, stock, and action state. The first implementation uses native WordPress/WooCommerce blocks plus a small Core enhancement for delivery and private list state.

## Alternatives considered

1. A promotional hero with several merchandising cards. Rejected for V1 because it delays the first grocery task and increases layout/image cost.
2. A custom JavaScript product grid and cart API. Rejected because WooCommerce already owns product, stock, price, and cart truth; duplicating it would increase compatibility and security cost.
3. A block-first shell with small progressive enhancements. Selected because it preserves conventional WooCommerce fallback behavior while making the grocery task hierarchy ownable and testable.

## Uniqueness thesis

Grovia's advantage is a coherent grocery task system, not a decorative theme: delivery is checked early, list/search/product surfaces share one scan pattern, and the theme remains useful without a mandatory builder or frontend framework.

## Measurement

The next browser acceptance slice should measure:

- delivery result understood before the first basket addition;
- no more than one full-page transition while adding a six-item basket;
- keyboard completion of delivery checking and mobile navigation;
- Shopping List reads/writes remain scoped to the authenticated customer;
- default theme assets remain small enough for a static/block-first storefront.

## Maintenance and security constraints

- Product/cart/checkout truth remains in WooCommerce.
- Delivery input is bounded and normalized at the REST boundary.
- Shopping List uses authenticated WordPress REST routes and user-scoped meta; no public list identifier is exposed.
- The theme ships no third-party runtime dependency or remote font.

## Theme presentation audit

The latest foundation contained the intended scoped CSS for the product
workspace, department browse, filters, and mobile navigation, but the theme
bootstrap no longer attached those styles to their Core-owned blocks. Several
template parts also used semantic `storefront-*` classes without a matching
presentation layer. This made the implementation visually incomplete even
though the interaction and markup seams existed.

The corrective decision is deliberately small: restore supported
`wp_enqueue_block_style()` registration, load the shared shell stylesheet, and
align the template-part layer to the existing token system. The result keeps
WooCommerce/Core responsible for behavior and truth while the theme owns
layout, density, focus states, mobile navigation, basket feedback, and account
navigation styling.

### Design-critic gate

- **Decision:** APPROVE with human/browser testing intentionally deferred by the
  current delivery instruction.
- **Task advantage:** Search, aisle context, product identity, price, and cart
  feedback remain visible in one low-transition grocery workspace.
- **Originality thesis:** The memorable signature is a numbered Aisle Rail and
  quiet Basket Pulse, not generic produce decoration or a promotional hero.
- **Biggest UX risk:** Product imagery and long names can still create uneven
  card density; the CSS clamps titles and keeps actions at touch-safe sizes.
- **Accessibility risk:** Fixed mobile navigation and basket feedback must keep
  focus visible and leave safe-area space; reduced-motion rules are included.
- **Performance risk:** Styles are attached to blocks conditionally, while the
  small shell stylesheet is shared and contains no runtime dependency.
