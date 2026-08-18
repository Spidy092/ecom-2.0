# Critical-Flow Design Directions

**Status:** Design research — no production implementation  
**Date:** 2026-08-18  
**Issue:** #6  
**Inputs:** Issue #2 competitor benchmark + current real grocery storefront patterns

## 1. Design premise

Marketplace themes are only one reference class. To build a better grocery product we also need to study real grocery-shopping behavior.

Current public grocery products reinforce three important behaviors:

- **Blinkit** puts delivery status/location, search and cart at the top of the shopping experience before category browsing.
- **BigBasket** exposes direct Add actions and a “My Smart Basket” concept alongside daily grocery categories.
- **Instacart** makes “Buy it again” a first-class collection and surfaces previously purchased items across header, homepage, search/browse and saved-list workflows.

These patterns validate our hypotheses around early serviceability, direct basket building and repeat shopping. They do **not** give us permission to copy the visual design of those products.

## 2. Non-negotiable design constraints

Every direction below must:
- remain grocery-first on mobile;
- make simple-product add/quantity state primary;
- make delivery/serviceability understandable early;
- keep cart state visible without forcing a cart page visit;
- support realistic pack/unit information;
- avoid hover-only essential behavior;
- avoid a full-screen generic organic hero as the opening experience;
- avoid wishlist/compare/quick-view clutter around the primary action;
- remain feasible without a large mandatory frontend framework;
- be visually original rather than an imitation of Bacola, Blinkit, Instacart, WoodMart, Organio or Freshio.

## 3. Direction A — Rapid Basket

### Concept

> The store behaves like a fast basket-building console for normal people: search, scan, tap quantity, keep moving.

### Best for

High-frequency shoppers who know roughly what they need and want to assemble a basket quickly.

### Signature interaction

**Basket Pulse** — after every add/quantity update, a lightweight persistent strip confirms the changed item and total without opening a side-cart modal.

Example:

```text
Added: Amul Taaza 1L   ×2      Basket ₹684
                         Undo   View basket
```

The strip should collapse into the persistent cart/navigation region rather than permanently consuming extra space.

### Home information hierarchy

```text
┌──────────────────────────────┐
│ Delivering to 560001  Today  │
│ [ Search groceries...      ] │
├──────────────────────────────┤
│ Frequent: Milk  Eggs  Rice → │
├──────────────────────────────┤
│ Buy again                    │
│ [Milk][-1+] [Eggs][ Add ] →  │
├──────────────────────────────┤
│ Fresh today                  │
│ [product] [product]          │
│ [product] [product]          │
│                              │
├──────────────────────────────┤
│ Home Search Aisles List Cart │
└──────────────────────────────┘
```

No promotional hero before search.

### Search / Shop

```text
┌──────────────────────────────┐
│ ← [ milk                 × ] │
│ Dairy  Plant-based  Offers   │
├──────────────────────────────┤
│ Amul Taaza       1 L         │
│ ₹68              [−] 2 [+]   │
│ Delivery today               │
├──────────────────────────────┤
│ Nandini Toned    500 ml      │
│ ₹29              [ Add ]     │
├──────────────────────────────┤
│ Oat Milk         1 L         │
│ ₹249             [ Add ]     │
└──────────────────────────────┘
```

Search is vertically efficient. Product images support recognition but do not dominate the row.

### Product-card state

```text
Before                         After
┌──────────────┐               ┌──────────────┐
│ image        │               │ image        │
│ Tomato       │               │ Tomato       │
│ 1 kg         │               │ 1 kg         │
│ ₹42   [Add]  │       →       │ ₹42 [−]2[+]  │
└──────────────┘               └──────────────┘
```

Secondary “save” action can be available from a contextual affordance/product detail, not competing beside Add.

### Cart feedback

Cart state is persistent but quiet:

```text
7 items   ₹842                     View basket
```

On quantity update it becomes Basket Pulse briefly, then returns to compact state.

### Mobile navigation

```text
Home | Search | Aisles | List | Cart(7)
```

Account remains inside Home/profile header or a secondary menu so the five prime thumb slots stay shopping-focused.

### Visual concept

- utility-first, crisp information hierarchy;
- typography carries most structure;
- food photography small but high quality;
- avoid default rounded-card overload;
- one brand accent is used primarily for state/action, not background decoration;
- product list/grid uses subtle shelf/ledger alignment to make units and prices scan quickly.

### Accessibility concerns

- quantity buttons need clear accessible names including product context;
- Basket Pulse needs non-intrusive live announcement behavior;
- horizontal category chips must not become an inaccessible scroll trap;
- sticky elements must leave enough viewport for content and browser safe areas.

### Performance implications

Good. Small interactive modules, no requirement for decorative slider/motion frameworks. Search/quantity state still requires careful request cancellation and cart synchronization.

### What it deliberately avoids

- Bacola-style dense promotional homepage sections;
- Organio-style long storytelling homepage;
- Freshio quick-view/compare/wishlist-heavy card chrome;
- Blinkit visual styling and exact layout.

### Measurable advantages

- fewest page transitions in 10-item basket task;
- shortest time to first item added;
- cart state visible 100% of the shopping session once non-empty.

### Main risk

Can look overly functional/commodity if visual identity is not distinctive enough.

---

## 4. Direction B — Aisle Rail

### Concept

> Turn a grocery store's mental model — walking aisles — into a calm digital navigation system without copying a supermarket shelf visually.

### Best for

Shoppers browsing categories and store owners who want a more ownable premium identity than generic app UI.

### Signature interaction

**Aisle Rail** — a persistent category rail that shows where the shopper is in the store and lets them jump between major grocery groups without returning to menus.

On mobile it is a compact horizontal/vertical hybrid; on desktop it can become a narrow left rail.

### Home information hierarchy

```text
┌──────────────────────────────┐
│ 560001 ✓   [ Search... ]     │
├──────────────────────────────┤
│ AISLE RAIL                   │
│ Produce ─ Dairy ─ Pantry ─→  │
├──────────────────────────────┤
│ Produce                      │
│ In season this week          │
│ [product] [product]          │
│ [product] [product]          │
├──────────────────────────────┤
│ Dairy                        │
│ [product] [product]          │
├──────────────────────────────┤
│ 6 items • ₹640    Basket →   │
└──────────────────────────────┘
```

There is no separate “Shop by category” decorative icon section; category structure **is the page structure**.

### Search / Shop

Search can preserve the rail as a category context:

```text
Search: “bread”

All  Bakery  Gluten-free  Snacks
──────────── active aisle ───────

Product results
```

When browsing, moving across the rail updates the product section while preserving basket state.

### Product card

Cards use a recognizable **shelf label** information hierarchy without literally imitating supermarket price tags:

```text
┌──────────────┐
│ image        │
│ Tomato       │
│ 1 kg         │
│              │
│ ₹42     Add  │
└──────────────┘
```

Pack/unit and price align predictably across the grid.

### Cart feedback

The Aisle Rail and basket state should not compete. Basket becomes a fixed compact edge/bar after first add.

### Mobile navigation

```text
Home | Search | Aisles | List | Cart
```

Tapping Aisles opens an accessible index of the same rail structure rather than a separate mega menu design language.

### Visual concept

- strong typographic aisle names;
- distinct sectional rhythm instead of card-within-card UI;
- subtle rules/alignment create “market order” without literal shelves;
- product photography and unit labels create the food identity;
- palette can be more neutral/urban/premium and need not rely on green.

### Accessibility concerns

- rail must have a non-gesture alternative;
- selected aisle state cannot rely on color alone;
- anchor/jump behavior needs focus management and reduced-motion handling;
- headings must preserve a meaningful document outline.

### Performance implications

Very good if implemented with native navigation/anchor/filter primitives. Risk arises only if we turn the rail into heavy animated scroll logic.

### What it deliberately avoids

- generic circular category-icon grids;
- mega-menu overload;
- leaf/vegetable illustration as brand identity;
- quick-view-heavy product cards.

### Measurable advantages

- category switch without returning to a menu/home page;
- user can explain “where they are” in the store after navigating;
- lower interaction cost for multi-category basket tasks.

### Main risk

Aisle metaphor can become gimmicky if it adds navigation complexity rather than removing it.

---

## 5. Direction C — Household Rhythm

### Concept

> The store remembers the rhythm of everyday life: what you often buy becomes the easiest thing to buy again.

### Best for

Returning grocery customers and stores where repeat orders drive meaningful revenue.

### Signature interaction

**This Week** — a living repeat-purchase shelf assembled from Buy Again + saved items, always customer-controlled.

It is not an opaque AI recommendation engine. The user understands why items are there: purchased before or explicitly saved.

### Returning-user home

```text
┌──────────────────────────────┐
│ Delivery 560001 ✓ Today      │
│ [ Search groceries...      ] │
├──────────────────────────────┤
│ This week                    │
│ From your recent shopping    │
│                              │
│ Milk        [−]2[+]          │
│ Eggs        [ Add ]          │
│ Bananas     [−]1[+]          │
│ Rice 5kg    [ Add ]          │
│                              │
│ [ Add selected to basket ]   │
├──────────────────────────────┤
│ Explore aisles               │
│ Produce Dairy Pantry ...     │
├──────────────────────────────┤
│ Home Search Aisles List Cart │
└──────────────────────────────┘
```

### First-time-user home

“This week” does not appear empty and awkwardly dominate. It becomes:

```text
Start your basket
Popular everyday essentials
```

Once the user has order history, the hierarchy shifts progressively.

### Search / Shop

Previously purchased products receive a restrained “Bought before” cue, not a promotional badge explosion.

Search results can rank the user's known product higher when the query strongly matches, while remaining transparent and avoiding price manipulation.

### Shopping List

V1 supports one simple list:

```text
Weekly list
☑ Milk        1
☑ Eggs        2
☐ Rice        1
☑ Tomatoes    1

Add selected (4)
```

The interaction should allow users to edit before committing to cart.

### Cart feedback

When adding from This Week/List, cart groups the action as a single coherent operation but preserves individual success/failure states for unavailable items.

### Mobile navigation

```text
Home | Search | Aisles | List | Cart
```

“List” becomes strategically important and is not hidden inside Account.

### Visual concept

- calmer, more personal than “quick commerce” styling;
- temporal language (“This week”, “Last bought”, “Usually”) used sparingly and truthfully;
- no fake AI sparkle iconography;
- list rows and product cards share one design grammar.

### Accessibility concerns

- explain why repeat items appear;
- batch-add must report partial failures clearly;
- order-history-derived content is private and must never leak between users;
- list checkboxes/quantity controls need clear labels and states.

### Performance implications

Moderate. Requires efficient order-history/product-current-state queries and likely caching strategy. Must avoid N+1 order/product lookups.

### What it deliberately avoids

- generic “recommended for you” black-box personalization;
- comparison features irrelevant to repeat groceries;
- over-marketing previous purchases.

### Measurable advantages

- returning user can add 5 known products without opening product pages;
- reduced time from home to meaningful basket for repeat customers;
- percentage of repeat basket created from This Week/Buy Again becomes a product metric after opt-in analytics/business design is decided.

### Main risk

Less impressive for first-time visitors and demo reviewers unless first-visit behavior is equally strong.

---

## 6. Design-critic scoring

Scale: 1 weak -> 5 exceptional. These are **research scores**, to be challenged by real prototype tests.

| Dimension | Rapid Basket | Aisle Rail | Household Rhythm |
| --- | ---: | ---: | ---: |
| Grocery task speed | 5 | 4 | 5 returning / 3 new |
| Originality vs theme market | 4 | 5 | 5 |
| Mobile ergonomics | 5 | 4 | 4 |
| Scanability | 5 | 5 | 4 |
| Accessibility feasibility | 4 | 4 | 4 |
| Performance feasibility | 5 | 5 | 4 |
| Store-owner commercial appeal | 5 | 4 | 5 |
| Brand-system potential | 4 | 5 | 5 |
| **Overall provisional** | **37/40** | **36/40** | **36/40*** |

`*` Household Rhythm score assumes a returning shopper; first-time experience must be designed separately.

## 7. Recommended direction — not a simple winner

Do **not** select one direction wholesale.

The strongest V1 hypothesis is a disciplined hybrid:

### Core interaction = Rapid Basket

Use Rapid Basket as the underlying task model:
- search-first;
- inline quantity;
- persistent cart;
- minimal page transitions.

### Structural signature = Aisle Rail

Use the Aisle Rail concept as the distinctive navigation/visual grammar:
- categories become store structure;
- avoid generic category-icon grids/mega menus;
- provides brandable rhythm without decorative clutter.

### Returning-customer layer = Household Rhythm

When eligible, insert This Week / Buy Again into the Rapid Basket home hierarchy and List navigation.

This creates a coherent concept:

> **A fast digital grocery aisle that remembers what your household buys.**

Working internal name for the UX system:

## **AisleFlow**

This is an interaction-system codename, not a public product name/trademark decision.

## 8. AisleFlow mobile skeleton

```text
┌────────────────────────────────┐
│ Delivering to 560001 ✓  Today  │
│ [ Search groceries...        ] │
├────────────────────────────────┤
│ Produce — Dairy — Pantry —→    │  ← Aisle Rail
├────────────────────────────────┤
│ This week                      │  ← only when useful
│ Milk        ₹68   [−] 2 [+]    │
│ Eggs        ₹89   [ Add ]      │
│ Bread       ₹45   [ Add ]      │
├────────────────────────────────┤
│ Produce                        │
│ [product]       [product]      │
│ unit • price    unit • price   │
│ [ Add ]         [−]1[+]        │
│                                │
│ [product]       [product]      │
├────────────────────────────────┤
│ 7 items • ₹842     View basket │  ← persistent state
├────────────────────────────────┤
│ Home Search Aisles List Cart   │
└────────────────────────────────┘
```

## 9. What makes AisleFlow meaningfully different

Not colors. Not icons. Not animations.

The differences are structural:

1. **No hero tax** — shopping begins immediately.
2. **Aisles are navigation and page structure**, not a decorative category section.
3. **Product cards become quantity controls** after first add.
4. **Cart is a continuous state**, not a destination the shopper must repeatedly open.
5. **Delivery status is store context**, not a late checkout surprise.
6. **Repeat shopping becomes part of the home/search architecture** for eligible customers.
7. **Secondary ecommerce utilities are demoted** so primary grocery actions remain visually obvious.

## 10. Prototype acceptance criteria

Before WordPress production code, create a clickable or browser prototype and test:

### First-time basket task

- select location/serviceability;
- add 10 simple grocery items from at least 3 aisles;
- change quantities on 3;
- open basket.

Record:
- total interactions;
- page transitions;
- time;
- errors/hesitation;
- whether user noticed cart total/location state.

### Returning-user task

- add 5 previous/saved items;
- add 3 new items from different aisles;
- remove one repeat item;
- continue to basket.

### Accessibility

- keyboard-only completion on desktop web;
- screen-reader names for search/nav/quantity/cart states;
- 200% zoom/reflow check;
- reduced motion;
- touch targets/mobile safe areas.

### Performance concept gate

Prototype architecture must not require:
- full-page slider library;
- animation framework for core interaction;
- giant icon/font bundle;
- all product data loaded up front;
- global scripts for unused modules.

## 11. Decision status

**Recommendation: PROCEED TO LOW-FIDELITY AisleFlow prototype, not production theme code.**

Do not lock final color/typography/brand until Issue #3 naming work advances. Do not claim UX superiority publicly until Issue #2 hands-on benchmark and Issue #5 user validation produce evidence.

## 12. External reference observations — retrieved 2026-08-18

- Blinkit category/home pages: https://blinkit.com/ and https://blinkit.com/categories
- BigBasket storefront: https://www.bigbasket.com/
- Instacart Buy It Again documentation: https://docs.instacart.com/storefront/learn_about_your_storefront/shopping/buy_it_again
- Instacart saved/list help: https://www.instacart.com/help/section/2893565984

These sources informed behavioral research only. Grovia/AisleFlow must not copy their proprietary visual expression, code or assets.
