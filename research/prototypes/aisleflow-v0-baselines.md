# AisleFlow V0 — Automated Interaction Baselines

**Date:** 2026-08-18  
**Viewport:** 390 × 844 CSS px  
**Issue:** #8  
**Workflow:** `Prototype fixed grocery mission`  
**Validated run:** `32148399201`  
**Artifact:** `aisleflow-fixed-mission-4`  
**Artifact digest:** `sha256:8bd03008940191b1f8209f85cbd280b0c140ea50936199e47b6f7c15e51e3558`

## Important interpretation rule

These are **scripted deterministic lower bounds**, not usability results.

Playwright knows the exact selectors, auto-scrolls, does not hesitate, does not misunderstand labels, and does not make normal human errors. Therefore:

- do not use these numbers in marketing;
- do not compare them directly to a competitor unless the competitor is tested with an equivalent deterministic mission;
- do not claim the returning scenario is “X% faster” because it is a different task;
- use them primarily as regression budgets for our own interaction architecture.

## Baseline A — first-time fixed 10-product mission

Mission quantities before the required removal:

```text
Amul Taaza Milk 1 L       ×2
Farm Eggs 6 pcs           ×2
Whole Wheat Bread 400 g   ×1
Sona Masoori Rice 5 kg    ×1
Toor Dal 1 kg             ×1
Fortune Sunflower Oil 1 L ×1
Fresh Tomato 1 kg         ×2
Banana Robusta 6 pcs      ×1
Bingo Potato Chips 90 g   ×2
Surf Excel Matic 1 kg     ×1
```

Then:
- remove Toor Dal entirely;
- read current basket count/total from persistent basket state;
- open Cart.

Validated lower bound:

| Metric | Result |
| --- | ---: |
| Deliberate interactions | **17** |
| Surface count | **2** |
| Simple-product detail transitions | **0** |
| Delivery checks | 1 |
| Final quantity count after removal | 13 |
| Final subtotal in prototype data | ₹1,332 |

Interaction accounting:

```text
1 delivery check
+ 14 Add/increment actions to reach requested quantities
+ 1 removal
+ 1 Cart open
= 17 deliberate interactions
```

This is now a regression budget. If a future design makes the same deterministic task require materially more actions, the PR must explain what additional customer value justifies the cost.

## Baseline B — returning household reference mission

Scenario:
- facilitator switches prototype to Returning mode and resets the research meter;
- add five known repeat products from `This Week / Buy Again`;
- add three different products from the main ledger;
- increase one repeated product;
- open Shopping List;
- continue to Cart.

Validated lower bound:

| Metric | Result |
| --- | ---: |
| Deliberate interactions | **11** |
| Surface count | **3** |
| Repeat products added | 5 |
| New products added | 3 |
| Simple-product detail transitions | **0** |

The returning mission is intentionally different from Baseline A. Its purpose is to protect the repeat-shopping architecture, not to prove a percentage speed improvement.

## Other automated evidence currently green

The prototype also has a separate headless Chromium accessibility smoke suite covering:
- focus preservation after dynamic re-render;
- Aisle Rail semantics;
- search focus shortcut;
- centralized search/basket announcements;
- Shopping List/Cart focus handling;
- textual delivery state;
- target-height guard;
- reduced-motion behavior.

This still does not replace manual keyboard, screen-reader, 200% zoom or real-device testing.

## Product implications

1. **Zero simple-product detail transitions remains a protected grocery UX goal.**
2. Persistent basket state must stay understandable before Cart is opened.
3. A product-card redesign should not add extra confirmation steps merely for visual novelty.
4. Variable products remain governed by the separate quick-add rule: compact choice when safe, product detail when choices are complex; never silently choose a variant.
5. Human testing should compare actual hesitation/error/search behavior against this theoretical lower bound.

## Next evidence required

Issue #8 remains open for:
- real/manual AisleFlow mission runs;
- equivalent competitor task observations where accessible;
- comparison of first-time vs returning human behavior.

Issue #5 remains the source of real shopper/buyer evidence.
