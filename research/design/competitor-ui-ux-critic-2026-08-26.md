# Grovia competitor UI/UX critic — 2026-08-26

## Design read

This is a redesign of a grocery storefront for mobile-first, trust-sensitive shoppers. The visual language should feel editorial and calm, with commercial polish, a single copper accent, and useful motion only where it confirms cart state.

## Baseline score before the hero update

| Dimension | Score (1–5) | Evidence |
| --- | ---: | --- |
| Task clarity | 4 | Search, delivery check, aisles, ledger, and cart are explicit. |
| Grocery specificity | 5 | Units, stock truth, quantity rail, Buy Again, and aisle language are product-specific. |
| Mobile ergonomics | 4 | Sticky shell, bottom nav, one-column ledger, and 48px actions; first viewport was too text-heavy. |
| Scanability/density | 4 | Ledger rows preserve image → title → unit → price → action order. |
| Information hierarchy | 3 | The previous intro delayed product imagery and direct shopping actions. |
| Accessibility | 4 | Semantic headings, labels, live cart feedback, focus states, and reduced-motion rules are present. |
| Interaction feedback | 4 | Store API add, quantity state, and basket pulse are visible. |
| Perceived trust/quality | 3 | Functional but visually less merchandised than paid competitors. |
| Originality | 5 | Calm basket/ledger concept is distinct from builder-heavy demo themes. |
| Performance feasibility | 5 | Native blocks, conditional assets, local WebP images, no new runtime dependency. |

## Decision: APPROVE with the selected split basket brief

### Task advantage

The shopper can choose a primary action (Shop or delivery check) from the first screen, then keep using the same search → aisle → ledger flow. A simple product can be added without opening its product page; variable and unavailable products explain the safe alternative.

### Originality thesis

Grovia's signature is a basket brief: product imagery and operational facts frame a ledger-like shopping tool. It deliberately avoids the competitor pattern of a carousel-led homepage, page-builder dependency, or decorative organic motifs as the main identity.

### Strongest element

The quantity-aware ledger action is more useful for repeat grocery shopping than a static product-card button. The new hero makes that interaction legible before the shopper scrolls.

### Biggest UX risk

The split hero could push the first product rows below the fold on small phones if copy or image height grows. The mobile rule therefore stacks the visual after the CTA and caps its height.

### Accessibility risk

The two-image composition must remain supplementary: meaningful alt text is present, links have visible focus, and no information is conveyed by image position or color alone.

### Performance risk

Hero imagery adds two local requests. The primary image is eager with explicit dimensions; the secondary image is lazy, WebP, and reused from the demo manifest. No remote font, CDN, or animation library is introduced.

### What to remove or simplify

Do not add sliders, autoplay video, testimonial bands, fake scarcity, duplicated mobile navigation, or a second quick-view system merely to match feature lists from the benchmark themes.

### What must be tested

- 320px, 390px, 430px, and desktop layout with no horizontal overflow.
- Heading order, link/button focus, alt text, 200% reflow, and reduced motion.
- One-tap Shop and delivery actions from the hero.
- Existing direct-add, quantity bounds, variable-product routing, unavailable states, Buy Again, Cart, and Checkout behavior.
