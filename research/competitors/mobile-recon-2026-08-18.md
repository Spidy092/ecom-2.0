# Mobile Competitor Recon — Reviewed Findings

**Run date:** 2026-08-18  
**Viewport:** 390 × 844 CSS px  
**Playwright:** 1.62.0 / headless Chromium  
**Issue:** #11  
**Reviewed artifact:** `mobile-competitor-recon-11`  
**Artifact digest:** `sha256:17f5fb769f2888d20abc57bd9d6f6ac42fb1e56ae43f81255912af8703822a44`

## 1. Research limits

This is one controlled browser reconnaissance run. It is **not** a Core Web Vitals test, conversion study, security rating, or proof that one theme is faster/better.

The harness records structural/discoverability signals and browser resource observations. Hosting, caching, geolocation, anti-bot systems, consent layers, demo configuration and third-party services can change results.

Do not use these numbers in marketing.

## 2. Target status

| Target | Result | UX metrics interpretable? | Reason |
| --- | --- | --- | --- |
| AisleFlow V0 control | 200 / OK | Yes | Local research prototype |
| Bacola | 403 / Blocked | No | Challenge page in automated environment |
| Freshio | 403 / Blocked | No | Challenge page in automated environment |
| Organio | 200 / OK | Yes | Storefront rendered in this run |
| GreenMart Fresh | 403 / Blocked | No | Challenge page in automated environment |
| WoodMart Grocery | 200 / OK | Yes | Grocery archive rendered |

No attempt was made to bypass blocked targets. Their challenge-page DOM/resource metrics are discarded.

## 3. Reviewed structural observations

| Signal | AisleFlow V0 | Organio | WoodMart Grocery |
| --- | ---: | ---: | ---: |
| DOM elements | 384 | 3,364 | 2,437 |
| Visible controls | 45 | 271 | 156 |
| Search inputs | 1 | 1 | 2 |
| First search top position | ~400 px | unreliable negative position from dynamic/sticky layout | 0 px |
| Delivery/location affordance detected | 1 | 0 | 0 |
| Add affordances | 14 | 17 | 12 |
| Quantity affordances initially visible | 0 | 0 | 36 |
| Wishlist affordances | 0 | 17 | 2 |
| Compare affordances | 0 | 17 | 2 |
| Quick-view affordances | 0 | 17 | 0 |
| Visible fixed/sticky elements | 3 | 3 | 7 |
| Script elements | 2 | 94 | 104 |
| Stylesheet links | 2 | 52 | 88 |
| Resource requests observed | 0* | 186 | 212 |
| Document height | 2,433 px | 10,624 px | 2,777 px |

`*` AisleFlow is loaded as a local `file://` control, so its zero network-resource count is **not directly comparable** with remote production demos.

## 4. Screenshot review

### AisleFlow V0

The first iteration had a research/marketing problem: the primary search input began around **791 px** from the top in the same mobile viewport because the facilitator HUD and a large delivery statement consumed most of the screen.

After evidence-driven revision:
- research controls are collapsed for facilitators;
- the oversized delivery statement was replaced by a compact serviceability step;
- delivery input begins around 228 px;
- search begins around **400 px**;
- search, delivery and the beginning of aisle navigation all appear within the first mobile viewport;
- the automated accessibility smoke suite still passes after the change.

This is a concrete example of the research gate changing the product rather than merely documenting competitors.

### WoodMart Grocery

The sampled mobile archive gets into actual products quickly. Product imagery, wishlist controls and inline quantity/add controls are visible in the first viewport after the category/archive header.

Important lesson: WoodMart has a large structural/resource surface in this lab observation, but its **shopping interaction density is strong**. Grovia/AisleFlow cannot claim a UX advantage merely from having fewer scripts or controls. We must outperform on the complete grocery task: delivery certainty, search, multi-item adding, repeat shopping, clarity and maintainability.

### Organio

The sampled mobile homepage exposes a much larger document and control surface, with repeated wishlist/compare/quick-view affordances. The first viewport is dominated by branded/header/merchandising presentation rather than a dense multi-item grocery workflow.

The detected negative search position is not reliable enough to interpret because the page uses dynamic/sticky positioning. Do not use it as evidence that search is hidden or broken.

## 5. Defensible product implications

### Keep

- **Early delivery certainty** remains differentiated in the successfully measured set: the AisleFlow control exposes an explicit postcode/serviceability action; the sampled Organio/WoodMart pages did not expose a comparable location control in this run.
- **No wishlist/compare/quick-view clutter around V1 product cards** remains a sound grocery-first decision.
- **Small default product surface** remains valuable for maintainability/performance, but is not itself a shopper UX win.

### Improve / validate

- Product discovery must remain above the fold; no large marketing hero before search.
- Aisle Rail needs human validation because it consumes vertical space before the first product.
- WoodMart's first-viewport quantity density is a serious benchmark. Our simple-product flow must prove equal or better task efficiency in the fixed 10-item mission.
- The first-time delivery step must not become friction for returning shoppers; remembered serviceability should collapse into a compact status.
- AisleFlow currently shows zero quantity controls before the first Add by design. After Add, quantity controls replace the command. This should be measured against WoodMart's always-visible quantity controls rather than assumed superior.

## 6. What we still cannot conclude

We cannot yet claim:
- fastest grocery theme;
- fewer taps than Bacola/Freshio/GreenMart;
- higher conversion;
- better accessibility than competitors;
- better Core Web Vitals;
- lower real-world transfer size;
- better shopper preference.

Those require the fixed task benchmark and real participants.

## 7. Next evidence

1. Issue #8 — run the fixed 10-item mission on AisleFlow and reachable competitors.
2. Issue #5 — real grocery shopper + WooCommerce buyer/store-owner sessions.
3. Issue #10 — manual keyboard, 200% zoom and screen-reader checks.
4. Re-run Bacola/Freshio/GreenMart manually in a normal browser where available; do not automate anti-bot bypasses.

## 8. Working UX target after this review

> **Get a shopper from serviceability certainty to the first basket action immediately, then let them build a multi-item basket without losing context.**

That is a stronger and more testable target than “clean modern grocery theme.”
