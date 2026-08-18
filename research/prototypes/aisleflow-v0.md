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
└── app.js
```

Run it through any local static server, for example:

```bash
cd research/prototypes/aisleflow-v0
python3 -m http.server 8080
```

Then open `http://localhost:8080`.

No build step or external package is required.

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
- Complex/choice-required products are intentionally not modeled yet; production requirements should route those to a choice surface rather than pretending all products can quick-add.

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

The prototype displays:
- elapsed time;
- deliberate interactions;
- surface transitions;
- time to first Add.

This is deliberately crude. It is for comparative UX sessions, not production telemetry.

The user can reset the research run without reloading the page.

## 14. Fixed test mission

Use Issue #8.

Basket:

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
- repeat in returning mode using This Week for at least five items.

## 15. Target hypotheses — NOT claims

These are internal thresholds to test:
- first item added within 10 seconds after delivery context is understood;
- simple 10-product basket requires zero product-detail-page transitions;
- cart item count and total remain discoverable after first add;
- returning mode materially reduces search/browse actions;
- keyboard-only user can complete the core interaction;
- no required control depends on hover;
- core interaction remains understandable at narrow mobile widths and 200% zoom.

We must not market these as achieved until measured with real users and realistic builds.

## 16. Accessibility design notes

Current prototype intentionally includes:
- skip link;
- explicit form labels;
- native buttons/inputs;
- keyboard-focus styles;
- accessible quantity-control labels with product context;
- live status regions for delivery/cart changes;
- no hover-only actions;
- reduced-motion handling;
- touch-target-conscious controls;
- responsive layout that turns the product ledger into a compact mobile row rather than shrinking a desktop table.

Still requiring manual validation:
- screen-reader announcement verbosity for repeated quantity changes;
- focus behavior when Cart/List surfaces open;
- mobile browser safe-area testing;
- 200% zoom/reflow;
- horizontal Aisle Rail keyboard behavior;
- live-region behavior under rapid add/quantity updates.

## 17. Performance design notes

Prototype rules that should carry into engineering research:
- no framework needed for core interaction concept;
- no slider/animation package;
- no product images larger than recognition requires;
- same product component used across search/aisle/repeat/list surfaces;
- no forced modal/side-cart after add;
- bounded result sets will be required in the real Store API/search implementation;
- cart must remain server-authoritative in production even if UI optimistically updates.

## 18. What is intentionally missing

- final brand name/logo;
- final typefaces/colors;
- final photography/art direction;
- product-detail experience;
- variable-product choice surface;
- real delivery slots/logistics;
- payment/checkout implementation;
- WordPress/WooCommerce PHP;
- page-builder integration;
- promo banners/sliders;
- testimonials/about/blog layouts;
- multiple starter demos.

## 19. Current design recommendation

Proceed with **AisleFlow as the interaction hypothesis**, not as a locked visual theme.

What should survive into the next test:
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
- whether returning-user prioritization increases speed without confusing new shoppers;
- whether these benefits remain once connected to real WooCommerce latency/edge cases.

## 20. Production gate remains closed

Do **not** begin production WordPress implementation based only on this prototype.

Before the gate opens:
- Issue #2 needs hands-on competitor task counts enough for comparison;
- Issue #5 needs actual buyer/shopper validation;
- Issue #4 needs current WooCommerce block/Store API/HPOS architecture validation;
- Issue #8 needs prototype measurements;
- the PRD should be updated if those findings invalidate current assumptions.
