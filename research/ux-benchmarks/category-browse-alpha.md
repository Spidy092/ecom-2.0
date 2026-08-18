# Grocery Category Browse — Alpha Decision

**Date:** 2026-08-19  
**Status:** Research decision — implementation may begin only from the boundary defined below.

## 1. Customer task

The browse experience is for a shopper who does **not** know an exact product name and wants to move quickly across familiar grocery departments.

Primary alpha task:

> Starting from Home, add one product each from Produce, Dairy and Pantry, then return to Search.

We care about:
- deliberate interactions;
- page/surface transitions;
- unnecessary scroll distance;
- whether Cart/quantity context is lost;
- narrow-screen overflow;
- keyboard completion.

This is different from the exact-product Search task. Browse should complement Search, not compete with it for primary attention.

## 2. Current platform seams

WooCommerce already owns category/catalog truth. The alpha must use public WooCommerce read paths rather than create a category database or custom admin system.

Useful public seams include:
- Woo Store API product-category collection (`/wc/store/v1/products/categories`);
- Woo Store API product collection queries scoped to product categories/taxonomies;
- Woo category hierarchy, counts, names, slugs, images/permalinks where available;
- core/Woo block-theme product collection and taxonomy patterns for the conventional Shop fallback.

The existing Storefront Core product workspace already owns the proven shopper cart path:

```text
Woo product data
  -> product card
  -> Add / inline quantity
  -> Woo Store API Cart
  -> authoritative cart state
```

Category Browse should feed products into that same shopping model rather than introduce another Add/Cart implementation.

## 3. Market/behavior observations

The current market already treats these as normal expectations:
- category/department navigation;
- mobile filters;
- direct Add/quantity on grocery-oriented listings;
- persistent Cart access;
- search as a high-priority grocery action.

A horizontal category strip by itself is therefore **not a differentiator**.

The product opportunity is the continuity of the task:

```text
Browse department
  -> see products
  -> Add / change quantity
  -> switch department
  -> keep Cart context
  -> return to Search
```

The interaction should feel like moving between grocery aisles, but we should not force every store into a visual rail when its category structure makes that worse.

## 4. Alternative A — conventional category landing

```text
Browse

Produce
Dairy
Bakery
Pantry
Drinks
Household
```

Tap -> category/products.

### Strengths
- immediately familiar;
- works with long names and many departments;
- simple keyboard/screen-reader model;
- easiest fallback when JavaScript is unavailable;
- low implementation/maintenance risk.

### Weaknesses
- often adds a page/surface transition before every department;
- repeated back-navigation can interrupt rapid multi-category grocery shopping;
- does not preserve the continuous-shopping feel we are testing.

### Alpha verdict

Keep as the **fallback/large-taxonomy presentation**, not the only experience.

## 5. Alternative B — always-on horizontal Aisle Rail

```text
Produce | Dairy | Bakery | Pantry | Drinks
                ↓
        products + inline quantity
```

### Strengths
- fast department switching;
- product/Cart context can stay on one surface;
- fits the grocery mental model better than a generic filter sidebar.

### Weaknesses
- weak when there are 12–25 top-level categories;
- long/localized category names create horizontal scrolling burden;
- hidden off-screen choices can reduce discoverability;
- nested child categories become messy quickly;
- risks becoming decorative novelty rather than task improvement.

### Alpha verdict

**Reject as a mandatory universal layout.**

Aisle Rail remains a useful presentation only for bounded top-level category sets.

## 6. Alternative C — Search-first with a few shortcuts

```text
Search groceries

Produce  Dairy  Pantry  Drinks
```

Full browsing continues on normal Woo Shop/category pages.

### Strengths
- smallest code surface;
- preserves Search as primary;
- easy to understand.

### Weaknesses
- weak for exploratory browsing and larger catalogs;
- shortcuts become arbitrary once the store has many important departments;
- repeated full-page navigation can break the rapid Add/quantity loop.

### Alpha verdict

Useful as a Home-page shortcut pattern, but insufficient as the complete Browse destination.

## 7. Recommended alpha — Adaptive Department Browse

Choose a reversible hybrid:

```text
Browse
  ↓
Top-level departments
  ↓
Selected department products
  ↓
inline Add / quantity
  ↓
change department without losing Cart context
```

### 7.1 Small top-level taxonomy

When the store has a **manageable top-level department set**, expose a compact scrollable department switcher above products.

Working alpha threshold to test: **2–8 top-level categories**.

Example:

```text
Produce | Dairy | Bakery | Pantry | Drinks

Fresh Tomato     ₹59     [+]
Bananas          ₹75     [-] 2 [+]
```

The threshold is a product heuristic, not a permanent API contract. Measure it before finalizing.

### 7.2 Larger top-level taxonomy

When top-level departments exceed the bounded rail threshold, do **not** create 20 tiny horizontal chips.

Use an explicit department chooser/list/grid:

```text
Browse departments

Produce        Dairy
Bakery         Pantry
Drinks         Household
Frozen         Personal Care
...
```

After selection, show the selected department clearly and provide a `Departments` control to switch.

This is intentionally more conventional because clarity is more valuable than forcing a signature pattern onto the wrong catalog.

### 7.3 Child categories

Do not recursively nest multiple horizontal rails.

For one selected top-level department:
- show direct children as a compact secondary list/chip group only when useful;
- keep `All <Department>` available;
- deeper hierarchy should use normal category navigation rather than an endlessly nested client UI.

### 7.4 Product continuity

Department changes should reuse the same product-card/Cart behavior already proven in engineering alpha:
- current product data from Woo;
- simple in-stock products can Add inline;
- quantity reconciles with authoritative Woo Cart;
- variable products remain `Choose options`;
- out-of-stock remains explicit;
- Saved state remains independent;
- Cart badge/navigation continue to use the existing cart event/state.

No second cart controller.

## 8. Navigation relationship

Mobile navigation currently uses:

```text
Home · Search · Browse · Cart · Account
```

Keep the label **Browse** during alpha.

Do not rename it `Categories` yet because Browse may include:
- departments;
- selected department products;
- child-category switching;
- later merchandising sections.

`Browse` becomes a real destination only when this slice is implemented. Until then it continues to point honestly to Woo Shop.

## 9. Accessibility requirements

The adaptive presentation must use one logical navigation model regardless of visual treatment.

Requirements:
- semantic heading for Browse/departments;
- department actions have visible text labels;
- selected department is exposed with a real state (`aria-current`, `aria-pressed`, or equivalent appropriate semantic), not color only;
- horizontal presentation must remain keyboard-scrollable without trapping focus;
- no ARIA `tablist` unless the interaction genuinely follows the tabs pattern;
- changing department announces the new result state once, without excessive live-region chatter;
- focus is preserved/restored sensibly when products rerender;
- minimum 44px touch targets;
- 200% zoom and narrow widths cannot hide the only route to an off-screen department.

## 10. Performance / request boundary

Alpha should remain bounded:
- request only the category levels needed for the current surface;
- do not recursively preload an entire deep taxonomy tree;
- product result page size stays bounded;
- abort stale product/category requests when the shopper changes department rapidly;
- do not fetch counts/images repeatedly if the public response already supplied them;
- no custom AJAX endpoint when Woo Store API provides the read contract;
- category images are optional presentation, never required to navigate.

## 11. Originality boundary

The product signature is **continuous grocery shopping across departments**, not the visual fact that chips scroll horizontally.

Do not copy:
- competitor category artwork;
- exact section order/layout;
- icon systems;
- color/shape expression;
- promotional/category-card compositions.

The UI should remain recognizable through its interaction system:

```text
Search
Delivery certainty
Department switching
Inline quantities
Saved
Persistent Cart
```

not decorative category cards.

## 12. Measurable alpha comparison

Before making an Aisle marketing claim, compare at least:

### Task
Add one product from Produce, one from Dairy and one from Pantry, then focus Search.

### Measure
- number of deliberate taps/clicks;
- number of full-page transitions;
- whether the shopper loses Cart/quantity context;
- scroll distance to switch department;
- keyboard completion;
- mobile overflow at 320/390/430px;
- behavior with 6, 12 and 25 top-level categories.

### Desired engineering-alpha outcome

For the bounded category case, department switching should require **no full-page transition** and should preserve Cart state.

This is an engineering target, not a public superiority claim.

## 13. Implementation boundary

The next engineering issue may implement only:
1. public top-level category loading;
2. adaptive department selector;
3. selected-category product loading through Woo public Store API;
4. existing product-card/cart behavior reuse;
5. optional direct child-category selector;
6. mobile-nav Browse destination update;
7. loading/empty/error/accessibility states;
8. deterministic real-Woo category fixtures and E2E.

Explicitly defer:
- mega menu;
- complex faceted filters;
- brand taxonomy UI;
- personalized departments;
- AI category ranking;
- custom category management;
- deep client-side taxonomy explorer;
- merchandising/page-builder system;
- claims that Aisle is faster than competitors.

## 14. Decision

**Proceed with Adaptive Department Browse for engineering alpha.**

Use an Aisle-style horizontal switcher only for a bounded top-level taxonomy. Use a conventional department chooser when the catalog is larger. In both cases, preserve one continuous product/Add/quantity/Cart surface.

This gives us a grocery-specific interaction experiment without sacrificing usability just to look different.
