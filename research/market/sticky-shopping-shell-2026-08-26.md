# Sticky Shopping Shell — 2026-08-26

**Status:** Engineering-alpha product/research note  
**Scope:** Persistent grocery discovery and cart access while scrolling  
**Research mode:** Controlled browser review of supplied competitor demos; observations are directional, not conversion or performance claims.

## Customer problem

A mobile grocery shopper scans a long product ledger, then needs to search for another staple, check delivery context, or confirm the basket without losing their place. If those controls disappear after the first viewport, the shopper must scroll back or open a second page.

## Competitors benchmarked

The supplied demos were reviewed in a normal browser on 2026-08-26:

- [Namm Organic](https://nammorganic.wpenginepowered.com/) — prominent offer/contact/header content followed by a large promotional and category-led homepage. The sampled header was not sticky.
- [Bagery](https://el.commonsupport.com/newwp/bagery/) — clear bakery navigation and product sections, but a long story/services/gallery sequence before and around commerce content. The sampled `.main-header` was in normal flow.
- [Groffer](https://groffer.modeltheme.com/?storefront=envato-elements) — category taxonomy, account/cart controls and promotional sections are easy to find at the top. The sampled header was static and the main navbar remained in normal flow.
- [Organio](https://demo.casethemes.net/organio/home-3/) — the existing mobile recon records multiple fixed/sticky elements and a large merchandising surface; dynamic positioning makes simple header comparisons unreliable.

These are interaction observations from demo configurations. They are not claims about the commercial products as a whole.

## What they do well

- Strong visual merchandising and immediate brand recognition.
- Familiar navigation, account and cart affordances.
- Category-led entry points that support browsing when a shopper is not searching for a specific item.

## Commodity expectations

Responsive headers, cart access, category navigation and occasional fixed surfaces are common ecommerce behavior. Sticky positioning alone is not a defensible market moat.

## Observed gap

The sampled demos prioritize hero/story/promotional composition or expose several independent fixed surfaces. They do not present one restrained, grocery-specific control system that keeps search and shopping context available while preserving the ledger and avoiding quick-view/compare clutter.

## Grovia uniqueness thesis

Grovia's signature is a **sticky shopping shell**: one canonical header/search surface stays reachable during product scanning; the existing Ink basket pulse and mobile shopping navigation provide cart confirmation and thumb-level movement; Cart and Checkout remain in native flow. The value is continuity of the grocery task, not decorative stickiness.

## Alternatives considered

1. **Sticky full header on every template:** maximizes reachability but consumes valuable checkout space and can make a tall mobile header feel heavy.
2. **Sticky compact search bar with scroll JavaScript:** could reduce occupied height after scrolling, but adds state, focus and reduced-motion complexity without evidence that V1 needs it.
3. **Keep only the existing bottom navigation and basket pulse:** lowest implementation cost, but search and delivery context still disappear on long product pages.

**Selected:** CSS-first sticky canonical header on customer-facing templates, excluded from Cart and Checkout. This uses supported theme presentation, adds no runtime dependency, and keeps state behavior in existing Core/WooCommerce contracts.

## Design-critic decision

**Decision: APPROVE with guardrails.**

- **Task advantage:** search and account/cart access remain available after scrolling.
- **Originality thesis:** the memorable element is a single grocery task rail, not a copied competitor header.
- **Strongest element:** continuity between search, ledger, basket pulse and mobile navigation.
- **Biggest UX risk:** a tall mobile header can reduce visible product density.
- **Accessibility risk:** sticky chrome can cover focused content or anchor targets; scroll padding and a browser scroll assertion are required.
- **Performance risk:** negligible CSS cost; avoid scroll listeners, backdrop effects and new dependencies.
- **Remove/simplify:** no additional sticky mini-cart, hero overlay, carousel or duplicated navigation.

## Measurable success criterion

In the fixed 10-item mobile grocery mission, the shopper can reach search or basket controls from a scrolled product list without returning to the top, while preserving the existing tap/time and accessibility baselines. The browser gate must confirm the canonical header remains visible after scroll at 320px, 390px and 430px widths.

## V1 fit and maintenance

This belongs in V1 because instant discovery, quantity shopping, delivery certainty and persistent cart feedback are protected V1 outcomes. The implementation is theme-owned CSS, keeps WooCommerce Cart/Checkout untouched, avoids JavaScript and new dependencies, and uses `scroll-padding-top`/`scroll-margin-top` to protect focus and anchor navigation.

**Sources/date:** supplied competitor demos above, reviewed 2026-08-26; existing [mobile competitor recon](../competitors/mobile-recon-2026-08-18.md); [Grovia design principles](../../docs/DESIGN-PRINCIPLES.md).
