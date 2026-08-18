# AisleFlow V0 — Low-Fidelity Interaction Prototype

**Status:** Interactive research prototype; not production WordPress code  
**Date:** 2026-08-18  
**Issue:** #7  
**Concept:** Rapid Basket + Aisle Rail + Household Rhythm

## 1. Purpose

AisleFlow V0 tests one product thesis:

> A grocery storefront should help a person build a basket continuously, rather than repeatedly interrupting shopping with page transitions, promotional sections, quick-view layers, and generic theme chrome.

This prototype intentionally uses vanilla HTML/CSS/JavaScript. It is not the future implementation stack. It exists to validate interaction hierarchy before WordPress/WooCommerce engineering begins.

## 2. What makes this prototype non-generic

The prototype does **not** begin with a hero, slider, brand story, testimonials, or promotional carousel.

The first customer decisions are:
1. can the store deliver to me?
2. what do I need?
3. which aisle helps me find it?
4. how many do I need?
5. what is currently in my basket?

The distinctive interaction system is:

- **Delivery certainty** — serviceability is visible before the basket grows.
- **Aisle Rail** — numbered store aisles create a stable shopping structure rather than generic category chips scattered across a long homepage.
- **Product Ledger** — compact rows prioritize name, pack/unit, price, stock and quantity state.
- **Basket Pulse** — cart feedback is persistent and lightweight; the shopper does not need a side-cart modal after every add.
- **Household Rhythm** — returning mode makes recent/frequent items a primary shopping path.
- **Shopping-focused mobile dock** — Home, Search, Aisles, List, Cart; Account does not consume one of the five primary shopping positions.

## 3. Prototype artifact

Files:

```text
research/prototypes/aisleflow-v0/
├── index.html
├── styles.css
├── app.js
├── a11y-normalize.js
├── browser_smoke.py
├── fixed_mission.py
└── requirements-dev.txt
```

Run it through any local static server, for example:

```bash
cd research/prototypes/aisleflow-v0
python3 -m http.server 8080
```

Then open `http://localhost:8080`.

The storefront prototype itself has no framework/build dependency. Playwright is used only by the research/CI automation.

## 4. State map

```text
FIRST-TIME HOME
  |
  +--> delivery check
  |
  +--> search ------------------------+
  |                                   |
  +--> aisle rail                     |
       |                               |
       v                               v
    PRODUCT LEDGER <------------ SEARCHED LEDGER
       |
       +--> Add -> Quantity
       |          |
       |          +--> Basket Pulse
       |
       +--> Save -> Shopping List
       |
       +--> Cart dock -> CART

RETURNING HOME
  |
  +--> This Week / Buy Again
  |        |
  |        +--> inline Add/Quantity
  |
  +--> Search / Aisles
  |
  +--> Shopping List
  |
  +--> Cart
```

No simple-product detail page is required for the tested basket-building mission.

## 5. First-time mobile wireframe

```text
┌───────────────────────────────────┐
│ Delivery certainty                │
│ 560001                      Check │
│ ✓ Delivery available today       │
├───────────────────────────────────┤
│ What goes in the basket?          │
│ [ Search milk, rice, tomatoes… ]  │
├───────────────────────────────────┤
│ 00 All  01 Produce  02 Dairy →    │
├───────────────────────────────────┤
│ 01 PRODUCE                        │
│                                   │
│ 🍅 Fresh Tomato        1 kg       │
│ ₹42                       [ Add ]  │
│                                   │
│ 🍌 Banana Robusta      6 pcs      │
│ ₹55                    [−] 2 [+]  │
│                                   │
│ 🍎 Royal Gala Apple    4 pcs      │
│ ₹180                      [ Add ]  │
├───────────────────────────────────┤
│ Banana Robusta ×2                 │
│ 7 items · ₹842        View basket │
├───────────────────────────────────┤
│ Home Search Aisles List Cart(7)   │
└───────────────────────────────────┘
```

## 6. Returning mobile wireframe

```text
┌───────────────────────────────────┐
│ Delivering to 560001 today        │
├───────────────────────────────────┤
│ [ Search groceries… ]             │
├───────────────────────────────────┤
│ This week                         │
│ From your recent orders           │
│                                   │
│ 🥛 Milk 1 L            [−] 2 [+]  │
│ 🥚 Eggs 6 pcs             [ Add ]  │
│ 🍞 Bread 400 g            [ Add ]  │
│ 🍚 Rice 5 kg              [ Add ]  │
├───────────────────────────────────┤
│ Continue through Aisles           │
│ 01 Produce 02 Dairy 03 Bakery →   │
├───────────────────────────────────┤
│ Home Search Aisles List Cart      │
└───────────────────────────────────┘
```

## 7. Product interaction states

### Before add

```text
Tomato
1 kg · Produce
₹42                         [ Add ]
                             Save
```

### After add

```text
Tomato
1 kg · Produce
₹42                     [−] 2 [+]
                             Saved
```

### Rules

- `Add` becomes quantity state immediately.
- Decrementing from `1` to `0` removes the line from the basket.
- Pack/unit never disappears after add.
- Save/List is visually secondary to basket quantity.
- Low-stock status exists but does not compete with Add.
- Choice-required products do not silently select a variant; they open a compact chooser or product detail depending on complexity.

## 8. Delivery behavior

Prototype-only postcode rules:
- `560001`, `560034`, `560038`, `560102` -> available;
- other values -> unavailable.

These are interaction fixtures, not real logistics data.

The key test is not the postcode database. It is whether a shopper understands serviceability **before** investing in the basket.

## 9. Aisle Rail behavior

Aisles:

```text
00 All
01 Produce
02 Dairy
03 Bakery
04 Staples
05 Snacks
06 Household
```

The numbers are meaningful because they encode a real ordered store structure. They are not decorative section numbers.

Selecting an aisle:
- clears search;
- updates the product ledger;
- changes the contextual aisle marker/description;
- keeps basket state intact.

## 10. Search behavior

Search filters the same product ledger rather than switching to a visually unrelated search-result template.

This preserves interaction memory:
- same product row;
- same Add/Quantity behavior;
- same basket feedback;
- same saved state.

The `/` key focuses search for keyboard testing.

## 11. Basket Pulse

After the first cart add, a persistent basket bar shows:

```text
Last changed product × quantity
N items · ₹total                     View basket
```

The important rule is **continuous understanding, not continuous interruption**.

The prototype deliberately avoids auto-opening a mini-cart/side-cart after every action.

## 12. Shopping List

One saved-list surface exists in V0.

Reasons:
- grocery repetition makes saved household items more relevant than product comparison;
- one list is sufficient to validate the interaction;
- multiple named lists remain outside V1 until user evidence supports them.

## 13. Built-in research instrumentation

The facilitator controls are collapsed by default so they do not consume the shopper's first viewport. They expose:
- elapsed time;
- deliberate interactions;
- surface transitions;
- time to first Add;
- first-time / returning mode switch;
- reset.

This is deliberately crude. It is for comparative UX sessions, not production telemetry.

## 14. Fixed test mission

Issue #8 uses this basket:

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
- remove one product completely;
- check delivery once;
- open cart;
- repeat a separate returning-mode reference mission using This Week for at least five items.

## 15. Automated lower-bound measurement — 2026-08-18

A deterministic Playwright run now protects the core interaction count from accidental regression.

**Environment:** Chromium through Playwright 1.62.0, 390×844 viewport.  
**Method:** automation knows the exact selectors and auto-scrolls; therefore these are **minimum mechanical interaction counts, not human usability results**.

### First-time fixed 10-product mission

Measured:
- **17 deliberate interactions**;
- **2 surfaces**;
- **0 simple-product detail transitions**;
- delivery checked once;
- basket reached 14 items before the required removal;
- final basket after removing Toor Dal: **13 items / ₹1,332**.

Mechanical count:

```text
1 delivery check
+ 14 add/increment actions
+ 1 remove action
+ 1 open-cart action
= 17 deliberate interactions
```

### Returning-household reference mission

Measured:
- **11 deliberate interactions**;
- **3 surfaces**;
- five repeat products added from This Week / Buy Again;
- three additional products added from the main ledger;
- one repeated item quantity changed;
- Shopping List inspected;
- Cart opened;
- **0 simple-product detail transitions**.

The returning scenario is intentionally not identical to the first-time basket, so `11 vs 17` is **not** a valid claim that returning shopping is 35% faster. It is only evidence that the repeat-shopping surface can complete a useful reference task with a small action count.

CI artifact source: GitHub Actions workflow `Prototype fixed grocery mission`, run #4 on 2026-08-18.

## 16. Mobile hierarchy correction discovered by research automation

The first recon pass found our own search field around **791 px from the top** in a 390×844 viewport. The cause was not customer value: facilitator controls plus an oversized delivery statement consumed the opening screen.

We changed the prototype so:
- facilitator controls are collapsed;
- delivery becomes a compact serviceability row rather than a marketing hero;
- search follows delivery immediately.

The subsequent control measurement placed search around **400 px from the top**, approximately halving the distance to the primary shopping action.

This is an internal lab observation, not a conversion claim.

## 17. Target hypotheses — NOT claims

These remain internal thresholds to test with real people:
- first item added within 10 seconds after delivery context is understood;
- simple 10-product basket requires zero product-detail-page transitions;
- cart item count and total remain discoverable after first add;
- returning mode materially reduces search/browse effort for repeat shopping;
- keyboard-only user can complete the core interaction;
- no required control depends on hover;
- core interaction remains understandable at narrow mobile widths and 200% zoom.

Only the zero-simple-product-detail mechanical path has been proven by automation. Human comprehension/speed hypotheses remain open.

## 18. Accessibility design and automated checks

Current prototype intentionally includes:
- skip link;
- explicit form labels;
- native buttons/inputs;
- keyboard-focus styles;
- accessible quantity-control labels with product context;
- controlled live status regions for delivery/cart changes;
- no hover-only actions;
- reduced-motion handling;
- touch-target-conscious controls;
- responsive product ledger;
- category navigation modeled as navigation/filter buttons rather than ARIA tabs;
- focus preservation when dynamic product controls re-render;
- Cart/List focus-return behavior covered by smoke tests.

Headless Chromium CI currently passes the automated accessibility invariants added for the prototype.

Still requiring real/manual validation:
- screen-reader announcement quality/verbosity;
- VoiceOver/TalkBack/NVDA behavior;
- 200% zoom/reflow;
- mobile browser safe areas;
- touch behavior on real devices;
- horizontal Aisle Rail comprehension;
- live-region behavior during human rapid shopping.

A green automated smoke test must never be described as WCAG conformance.

## 19. Competitor reconnaissance notes

A read-only mobile Playwright harness now records a consistent structural sample without bypassing site protections.

Current automation environment results:
- WoodMart grocery demo: usable storefront response;
- Organio: bot/challenge response in some runs and therefore not blindly interpreted;
- Bacola: blocked with HTTP 403;
- Freshio: blocked with HTTP 403;
- GreenMart Fresh Food & Grocery: blocked with HTTP 403.

We record those as access limitations rather than attempting bot-protection bypasses.

The WoodMart sample is important because it demonstrates that a mature competitor can surface product quantity controls effectively in the first mobile viewport despite a much broader interface/runtime. We should treat that as a strong benchmark, not assume specialization automatically makes us better.

## 20. Performance design notes

Prototype rules that should carry into engineering research:
- no framework needed for core interaction concept;
- no slider/animation package;
- no product images larger than recognition requires;
- same product component used across search/aisle/repeat/list surfaces;
- no forced modal/side-cart after add;
- bounded result sets will be required in the real Store API/search implementation;
- cart must remain server-authoritative in production even if UI optimistically updates.

## 21. What is intentionally missing

- final brand name/logo;
- final typefaces/colors;
- final photography/art direction;
- full product-detail experience;
- production variable-product chooser;
- real delivery slots/logistics;
- payment/checkout implementation;
- WordPress/WooCommerce PHP;
- page-builder integration;
- promo banners/sliders;
- testimonials/about/blog layouts;
- multiple starter demos.

## 22. Current design recommendation

Proceed with **AisleFlow as the interaction hypothesis**, not as a locked visual theme or public brand.

What should survive into real-user testing:
1. serviceability before basket investment;
2. search as a first-class action;
3. Aisle Rail as stable navigation;
4. ledger-like product scanning;
5. inline quantity state;
6. Basket Pulse;
7. returning-user Household Rhythm;
8. shopping-focused mobile dock.

What remains unproven:
- whether Aisle Rail is genuinely easier than a conventional category drawer;
- whether the product ledger feels premium enough for store owners while staying dense;
- whether Basket Pulse is useful or visually noisy over longer sessions;
- whether returning-user prioritization increases real human speed without confusing new shoppers;
- whether shoppers value early serviceability enough to justify the vertical space versus showing products even earlier;
- whether these benefits remain once connected to real WooCommerce latency/edge cases.

## 23. Production gate remains closed

Do **not** begin production WordPress implementation based only on automation.

Completed technical/research gates:
- WooCommerce block / Store API / HPOS architecture research;
- automated accessibility invariants;
- fixed mechanical basket mission;
- variable-product quick-add product rule;
- repeatable competitor reconnaissance tooling.

Still required before production implementation:
- Issue #5: actual buyer/store-owner/shopper validation;
- Issue #8: human task measurements (time, hesitation, comprehension, keyboard/manual findings);
- final synthesis must confirm or change the current PRD based on those observations.
