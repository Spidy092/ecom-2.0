# AisleFlow Mobile UI Audit — 2026-08-19

**Status:** Research + founder-device validation

## Why this audit exists

The first working Playground slice is deliberately simple and mobile-first. Before adding more features, this audit checks whether the current direction is actually aligned with current grocery UX evidence and identifies where the prototype is still behaving like generic WooCommerce.

## Evidence reviewed

### 2026 grocery UX research

- Baymard, *Online Grocery Ecommerce UX Research 2026* — 1,700+ total research hours, 70 new qualitative test sessions, and 1,200+ grocery-specific usability issues across the research catalog.
- Baymard, *Dynamically Update the Add to Cart Button to a Quantity Selector after Item Added* — validates the exact Add -> quantity-control pattern for high-item-count grocery baskets.
- Baymard, *Past Purchases on the Homepage* — validates prominent repeat-purchase access for grocery users.
- Baymard mobile/product-list research — mobile product lists must balance useful product information with scanability; sorting/filtering and product-list UI are common failure points.

### Current grocery products

- Blinkit currently blocks meaningful catalog shopping until location is selected and makes delivery context + search primary.
- bigbasket exposes search, shopping lists, Smart Basket/reorder and synchronized cart behavior as core shopping utilities.
- Instacart makes Buy It Again a first-class collection and also surfaces it across homepage, search/browse and saved-item flows.
- Kroger exposes Favorites / Sale Items / Past Purchases as direct cart-building paths.

### Current WooCommerce-theme market

- Bacola already offers location filtering, archive quantity boxes, AJAX search/cart, mobile bottom menu, side cart, recently viewed and many other grocery-theme features.
- GreenMart currently markets min/max/step quantity, AJAX search/cart, multiple mobile menus and a broad builder/plugin stack; its current changelog still includes WooCommerce-template compatibility fixes.
- Freshio remains Elementor-first with multiple demos and a plugin-heavy classic theme model.
- Therefore quantity controls, mobile bottom navigation, AJAX search and "modern/mobile" language are **commodity expectations**, not differentiation by themselves.

### Platform constraint

WooCommerce Store API is the correct shopper-facing seam for product and cart data. It already supports product collections/search plus cart add/update/remove operations. Grovia should continue to reconcile UI from WooCommerce responses instead of maintaining a second cart model.

## What the founder-device test proves

The current direction is worth keeping:

1. **The interface is understandable without instruction.** Product, price, Add/quantity, Basket Pulse and bottom navigation are obvious on a phone.
2. **The Add -> quantity transition is the right grocery interaction.** It avoids unnecessary product-page visits.
3. **Persistent basket state is valuable.** The dark Basket Pulse is easy to notice without opening a drawer.
4. **The bottom navigation is thumb-friendly and readable.** Text labels are currently better than icon-only navigation for alpha usability.
5. **Checkout should remain WooCommerce-owned.** The prototype reaches WooCommerce Checkout rather than inventing a custom payment flow.

## Important problems visible in the founder screenshots

### P0 — cart-state correctness before more polish

One screenshot shows product cards reporting `3 in cart` and `2 in cart` while Basket Pulse reports `2 items · US$8.80`.

That is a correctness failure, not a cosmetic issue. A grocery interface cannot show multiple cart truths.

Before the next feature, reproduce and eliminate any native-Woo/Grovia race or session/render mismatch. Acceptance rule: product-card quantity, mini-cart, Basket Pulse, Cart and Checkout must agree after every add/update/remove.

### P0 — remove duplicate cart affordances

The Shop screenshot shows native WooCommerce `X in cart` / `View cart` output at the same time as Grovia quantity state and Basket Pulse. This creates competing interaction languages.

When Grovia enhancement is healthy:
- one primary Add/quantity control per simple product;
- one persistent basket summary;
- no duplicate `View cart` link inside the product card area.

Native behavior remains the progressive-enhancement fallback only.

### P1 — product-list toolbar needs a mobile design

The default `Showing all 8 results` + native sorting select looks like unstyled desktop WooCommerce and consumes a valuable row.

For alpha:
- compact result context;
- one clear Sort/Filter entry point;
- no oversized native-select visual dominance;
- preserve native controls/fallback semantics underneath.

### P1 — product-card density is still too loose

Current cards are readable, but there is too much vertical whitespace for grocery scanning.

Refine toward:
- smaller but recognizable image;
- product name on 1–2 stable lines;
- pack/unit as separate secondary metadata rather than embedding everything into the title;
- price aligned predictably;
- Add/quantity control in a stable card footer;
- minimum 44px touch targets without making the entire control oversized.

Goal: more products per screen **without** losing readability.

### P1 — real demo media is necessary for design judgment

Placeholder image icons prevent meaningful visual-quality evaluation. Use redistributable, documented grocery demo assets before judging final card hierarchy, contrast or brand feel.

### P1 — location/serviceability should become real before decorative homepage work

2026 grocery research repeatedly shows fulfillment uncertainty becoming a late-stage failure. Blinkit also makes location a gating context before catalog shopping.

Grovia should not simply copy that implementation, but should make `Can you deliver to me?` an early, persistent answer. The current delivery block is therefore strategically correct; it should be functional before adding hero marketing sections.

### P1 — repeat shopping belongs near the top for returning customers

Baymard, Instacart, bigbasket and Kroger all reinforce the value of previous/frequent purchases.

For eligible logged-in customers, the home hierarchy should become:

```text
Delivery status
Search
Aisles
Buy Again / This Week
Fresh / everyday products
Basket state
```

For first-time users, replace the repeat shelf with everyday essentials rather than an empty personalization module.

### P2 — bottom navigation information architecture

Current prototype: `Home · Search · Aisles · Shop · Cart`.

Longer-term validated hypothesis: `Home · Search · Aisles · List · Cart` once Shopping List / Buy Again is real.

Do not add Account to the five prime shopping slots unless user testing proves it is more valuable than repeat-shopping access.

### P2 — checkout/demo cleanup

The current Playground shows WooCommerce's Coming Soon notice. That is fine for engineering but should be disabled in a polished demo environment so testers evaluate the product rather than store-visibility setup.

The checkout UI should be visually integrated with Grovia tokens while preserving WooCommerce Checkout Blocks behavior and accessibility.

## Updated UI principles for implementation

1. **Correctness before animation.** Every visible cart quantity/total must agree.
2. **One interaction language.** Avoid native + Grovia duplicate controls on the same surface.
3. **Shopping density is intentional.** Grocery cards should optimize repeated scanning, not screenshot whitespace.
4. **Location is context, not a popup gimmick.** Delivery certainty should persist through shopping.
5. **Search is a primary control.** Product-aware instant search is higher priority than decorative hero content.
6. **Repeat behavior is a first-class grocery job.** Buy Again/List should eventually outrank generic account/navigation features.
7. **Woo owns commerce truth.** Grovia owns the grocery interaction layer.
8. **No builder/plugin bloat as a design crutch.** Block-first remains a product advantage only if the resulting UX is genuinely easier.
9. **Mobile first, desktop intentionally adapted.** Do not merely stretch the mobile layout to desktop.
10. **Visual identity comes after task hierarchy is stable.** Do not hide unresolved UX behind colors, gradients, illustrations or animation.

## Recommended implementation order

### Gate 1 — stabilize the current shopping loop

- reproduce/fix cart-state mismatch;
- remove duplicate native `in cart` / `View cart` UI when enhanced;
- keep accessible fallback;
- add browser/E2E coverage for add -> increment -> second product -> remove -> Cart/Checkout agreement.

### Gate 2 — refine Shop/product-list UI

- mobile toolbar;
- tighter card density;
- separate unit/pack metadata;
- real licensed demo images;
- responsive desktop adaptation.

### Gate 3 — real delivery certainty

- postcode/serviceability interaction;
- persistent delivery state;
- no contradictory truth with WooCommerce Shipping Zones.

### Gate 4 — instant grocery search

- Store API product search;
- bounded results;
- debounce/cancel stale requests;
- direct Add/quantity from results;
- keyboard and screen-reader behavior;
- conventional search fallback.

### Gate 5 — Household Rhythm

- Shopping List;
- Buy Again;
- returning-customer home shelf;
- replace `Shop` bottom-nav slot with `List` only when useful.

### Gate 6 — commercial visual system

Only after the critical shopping loop is stable:
- final typography;
- real product photography system;
- brand accent/token variants;
- desktop hierarchy;
- polished Cart/Checkout presentation;
- visual regression testing.

## Success measurements

Do not accept "looks nice" as the only metric.

Measure on a realistic phone:
- time to first add;
- time/taps to build a 10-item basket across 3 aisles;
- quantity changes without product-page navigation;
- cart-state mismatch count = **0**;
- delivery certainty within <=2 deliberate interactions;
- number of products meaningfully scannable per viewport;
- repeat user can add 5 known items without opening individual product pages;
- keyboard task completion;
- screen-reader labels/status quality;
- layout at 200% zoom;
- JS/CSS/request cost for the shopping loop.

## Sources

- https://baymard.com/blog/online-grocery-ecommerce-ux-2026
- https://baymard.com/blog/grocery-add-to-cart-buttons
- https://baymard.com/blog/grocery-food-delivery-orders
- https://baymard.com/research/mcommerce-usability
- https://blinkit.com/
- https://www.bigbasket.com/about-us/
- https://docs.instacart.com/storefront/learn_about_your_storefront/shopping/buy_it_again/
- https://www.kroger.com/products/my-recent-purchases
- https://klbtheme.com/bacola/intro/
- https://themeforest.net/item/greenmart-organic-food-woocommerce-wordpress-theme/20754270
- https://themeforest.net/item/freshio-organic-food-store-wordpress-theme/28365085
- https://developer.woocommerce.com/docs/apis/store-api/
