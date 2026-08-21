# Grovia V1 Visual Directions — 2026-08-21

**Status:** visual exploration only; no production token freeze.

**Constraint:** preserve the validated grocery interaction architecture. These directions change presentation, not the meaning of Search, Aisles, Add/quantity, serviceability, Basket Pulse, List/Buy Again, Cart or Checkout.

## Design brief

**Subject:** a local grocery/supermarket storefront where shoppers build 10–30 item baskets on mobile.

**Primary shopper job:** move from delivery certainty to a trustworthy basket quickly without opening unnecessary pages.

**Primary buyer job:** see a storefront that looks premium and distinctive enough to buy, while still feeling maintainable and editable inside modern WordPress.

**Non-negotiables:**

- no green + cream + leaf-logo shorthand;
- no giant generic hero before discovery;
- no glassmorphism/card pile;
- no unfamiliar commerce controls invented only for novelty;
- no visual treatment that hides pack/unit/price/quantity information;
- no animation that slows the basket task;
- no font/runtime choice without redistributable licensing and performance review;
- product photography is part of the final design evaluation, not an afterthought.

---

# Direction A — Shelf Signal

## Thesis

Make the storefront feel like the clearest, best-designed physical supermarket shelf translated to a phone: strong aisle signage, shelf-edge price/pack information, large merchandise photography, and one obvious purchase state.

This is the recommended direction to prototype first because it makes the **shopping instrument itself** the visual identity.

## Palette

- **Stock White** `#F7F8F5` — background; slightly cool, not cream.
- **Aisle Ink** `#141918` — body/headings.
- **Signal Yellow** `#F4C542` — aisle/service attention, used sparingly.
- **Tomato Red** `#D94A34` — primary purchase/action accent.
- **Rail Gray** `#D8DEDA` — dividers/shelf rails.
- **Fresh Status** `#147A55` — success/serviceability only.

Color rule: red is not used for errors in the same treatment as purchase actions; destructive/error states use a distinct deeper danger token and icon/text state.

## Type roles

Candidate families to license-check before implementation:

- **Display / aisle labels:** Archivo SemiCondensed or another open condensed grotesk with strong numeral clarity.
- **Body / product titles:** Source Sans 3 or a similarly readable humanist sans.
- **Utility / pack-price-data:** IBM Plex Mono or a tabular-numeral utility face used only for small shelf-label data, never long body copy.

Performance fallback: system sans with tabular numerals if bundled font cost is not justified.

## Layout concept

```text
┌──────────────────────────────┐
│ Deliver to 560001  ✓ Served  │  compact service rail
├──────────────────────────────┤
│ Search milk, rice, bananas…  │
├──────────────────────────────┤
│ Produce  Dairy  Pantry  ...  │  aisle signal rail
├──────────────┬───────────────┤
│ [PHOTO]      │ [PHOTO]       │
│ Milk         │ Eggs          │
│ 1 L          │ 6 pcs         │
│ $3.20        │ $4.40         │
│ [  ADD  ]    │ [ −  2  + ]   │
├──────────────┴───────────────┤  shelf-edge rule
│ 5 items · $18.70      Basket │
├──────────────────────────────┤
│ Home Search Aisles List Cart │
└──────────────────────────────┘
```

## Signature

**Shelf-edge state rail:** price/pack/purchase state always resolves against a crisp horizontal rail, so scanning a long two-column grid feels like reading real shelf labels. After Add, the action morphs into quantity without moving the information hierarchy.

## Why it can attract buyers

- looks recognizably grocery without leaf/organic clichés;
- photographs well for marketplace screenshots;
- creates a visible story around fast basket building;
- works with discount/status labels without adding card clutter;
- the signature survives logo removal.

## Risks

- can become too utilitarian/discount-supermarket if typography and photography are cheap;
- yellow/red can become visually loud if used beyond signals;
- condensed type requires accessibility testing at small sizes.

## Accessibility/performance

- purchase controls retain >=44 px practical targets;
- never rely on yellow/red alone for state;
- utility font limited to pack/price metadata;
- avoid animated shelf movements; only morph the Add/quantity control with reduced-motion support;
- no background textures/images required.

---

# Direction B — Kitchen List

## Thesis

Build the visual identity around the repeat-shopping behavior that makes grocery different: a polished household shopping list merged with the product shelf.

This direction is quieter than typical marketplace demos but can make **Shopping List / Buy Again** feel native to the whole storefront instead of bolted onto Account.

## Palette

- **List White** `#FFFFFF`
- **Graphite** `#202523`
- **Notebook Blue** `#2857B8`
- **Citrus** `#E9B949`
- **Mist** `#EEF2F2`
- **Fresh Status** `#117A57`

No paper texture, torn edges or fake handwriting.

## Type roles

Candidate families to license-check:

- **Display / section names:** Manrope or a similarly compact geometric sans with restrained personality.
- **Body / products:** Atkinson Hyperlegible Next / Source Sans 3 class of highly legible sans.
- **Utility:** same body family with tabular numerals; avoid a third font if performance does not justify it.

## Layout concept

```text
┌──────────────────────────────┐
│ 560001  ✓ We serve this area │
│ [ Search groceries...      ] │
│                              │
│ THIS WEEK                    │
│ □ Milk      1 L      [Add]   │
│ □ Eggs      6 pcs    [Add]   │
│ □ Bread     400 g    [Add]   │
│                              │
│ AISLES                        │
│ Produce  Dairy  Pantry  ...  │
│                              │
│ [photo] Milk   [photo] Eggs  │
│ $3.20  − 2 +   $4.40  Add    │
│                              │
│ 5 items · $18.70      Basket │
└──────────────────────────────┘
```

## Signature

**This Week strip:** returning shoppers see a compact repeat-shopping list integrated before the shelf; selected items animate into the same authoritative basket state used everywhere else.

## Why it can attract buyers

- gives the commercial demo a strong “weekly grocery” story competitors rarely lead with;
- makes repeat shopping visibly different from a generic wishlist;
- plain, calm UI can appeal to local stores that do not want an aggressively promotional marketplace look.

## Risks

- first-time shoppers must not see a mostly empty repeat-shopping surface;
- can look like a productivity app rather than a grocery store if photography is underused;
- checkbox/list language must not duplicate Save/Saved semantics confusingly.

## Accessibility/performance

- checklist controls require explicit product-specific labels;
- no handwritten-font gimmick;
- selected/list state cannot be communicated only through checkmarks/color;
- uses fewer decorative assets than most competitor demos.

---

# Direction C — Market Transit

## Thesis

Treat grocery shopping as movement through a market: delivery context, aisle context and basket progress are the main visual signals. Use bold wayfinding rather than organic decoration.

This direction is the most visually energetic and could produce the strongest marketplace screenshot, but it carries the highest risk of becoming app-like or overly branded.

## Palette

- **Porcelain** `#F6F7F4`
- **Midnight** `#111827`
- **Route Blue** `#2447D8`
- **Mandarin** `#F36A2D`
- **Platform Gray** `#DCE1E7`
- **Fresh Status** `#0C7A58`

## Type roles

Candidate families to license-check:

- **Display / wayfinding:** Barlow Condensed or another signage-inspired sans.
- **Body / products:** Public Sans / Source Sans 3 class of neutral, highly legible sans.
- **Utility / totals:** body family with tabular figures rather than a decorative data font.

## Layout concept

```text
┌──────────────────────────────┐
│ DELIVERY → 560001 ✓          │
├──────────────────────────────┤
│ [ Search groceries...      ] │
│                              │
│ AISLE 01 Produce             │
│ 02 Dairy  03 Pantry  04 Home │
│                              │
│ [PHOTO]          [PHOTO]     │
│ Bananas          Whole Milk  │
│ 1 kg · $2.80     1 L · $3.20 │
│ [ − 2 + ]        [ Add ]     │
│                              │
│ BASKET → 5 items · $18.70    │
│ Home Search Aisles List Cart │
└──────────────────────────────┘
```

## Signature

**Wayfinding spine:** a consistent route-like visual line links serviceability → aisle → basket state, helping the shopper understand where they are without introducing a wizard or extra page transitions.

## Why it can attract buyers

- visually stronger than the current prototype;
- clearly not a traditional green organic theme;
- supports delivery/category context naturally;
- marketplace screenshots can communicate the product story in one frame.

## Risks

- can resemble transport/fintech/quick-commerce apps if imagery and copy are not grocery-specific;
- numbered aisles become decoration if actual category structure does not map cleanly;
- excessive blue/orange signal use may compete with product photography.

## Accessibility/performance

- route line is decorative only; semantics remain proper headings/nav/controls;
- no information depends on line position or color;
- motion limited to state transitions; no scrolling route animation;
- strong high-contrast typography but must be checked for long translations.

---

# Comparative design critique

| Criterion | Shelf Signal | Kitchen List | Market Transit |
| --- | ---: | ---: | ---: |
| Grocery specificity | 5 | 5 | 4.5 |
| Marketplace visual impact | 4.5 | 3.5 | 5 |
| Multi-item shopping clarity | 5 | 4.5 | 4.5 |
| Repeat-shopping differentiation | 4 | 5 | 3.5 |
| Originality without unfamiliar controls | 5 | 4.5 | 4.5 |
| Accessibility risk | Low–medium | Low | Medium |
| Performance cost | Low | Low | Low |
| Risk of generic theme look | Low | Low–medium | Low |
| Risk of generic AI look | Low if palette restrained | Low | Medium if over-styled |

## Recommendation

Prototype **Shelf Signal first**.

Reason:

- strongest connection between visual identity and the already-validated Add/quantity/basket interaction;
- high product density without looking unfinished;
- strong marketplace screenshot potential;
- does not require new product features to look distinctive;
- works for grocery, organic, farm produce and local supermarket stores without forcing all of them into green/leaf branding.

Use **Kitchen List** as the main alternative because it may outperform Shelf Signal for the repeat-shopping thesis.

Keep **Market Transit** as an intentional high-energy challenger, not the default.

## Decision gate before implementation

For each direction, create the same three mobile frames at 390×844 using identical realistic content:

1. first-time Home/Search + serviceability;
2. product shelf after Milk ×2 and Eggs ×1;
3. returning-shopper This Week / Buy Again state.

Judge blind/logo-removed frames against:

- immediate grocery recognition;
- premium perception;
- product scanning speed;
- quantity-state clarity;
- basket clarity;
- visual memorability;
- accessibility risk;
- estimated frontend cost.

Do not implement a direction into production merely because one static screenshot looks attractive. The selected visual system must survive the complete 10-item mission.
