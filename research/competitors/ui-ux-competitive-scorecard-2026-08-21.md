# Grocery WooCommerce UI/UX Competitive Scorecard — 2026-08-21

**Purpose:** decide how Grovia should look and behave before visual polish becomes implementation debt.

**Status:** decision-support research, not a marketing ranking.

## 1. Evidence boundary

This scorecard combines:

- the controlled 390×844 mobile reconnaissance already recorded in `research/competitors/mobile-recon-2026-08-18.md`;
- founder real-device Playground screenshots from the current prototype cycle;
- current public marketplace/product pages and vendor demos reviewed on 2026-08-21;
- the current canonical theme tokens/style variations on `agent/grovia-compatibility-matrix`;
- the product and design constraints in `docs/PRD.md` and `docs/DESIGN-PRINCIPLES.md`.

Important limits:

- Bacola, Freshio and GreenMart blocked the earlier automated reconnaissance, so no anti-bot bypass is attempted and no fake timing comparison is reported.
- Public feature claims are not treated as proof of usability, accessibility, performance or security.
- Scores below are product/design judgement for prioritization, not objective market measurements.
- A controlled 10-item task benchmark and real participant preference testing remain required before any claim such as “best” or “fastest”.

## 2. Current competitor reality

### WoodMart Grocery — breadth/trust + dense shopping benchmark

Current live grocery demo strengths:

- product search and category selection are visually prominent;
- product grid reaches merchandise quickly;
- quantity controls are visible directly on cards;
- wishlist/compare/quick-view capabilities are integrated;
- mature visual system and large customization surface;
- very high market trust compared with a new product.

Weakness/opportunity for Grovia:

- large feature/configuration surface;
- earlier controlled recon observed a much larger DOM/script/style/resource surface than the local AisleFlow research control;
- grocery workflow is one use case inside a multipurpose product, so repeat-shopping and serviceability can be more focused in Grovia.

### Bacola — primary grocery feature benchmark

Current strengths:

- grocery-specific visual positioning;
- quantity field on product boxes;
- AJAX search/filter/add/remove;
- location filtering;
- mobile bottom menu;
- side cart, wishlist, quick view and many buyer-visible commerce features;
- setup/demo tooling and substantial documentation.

Weakness/opportunity for Grovia:

- many individual features are already commodity, so Grovia cannot differentiate by copying the checklist;
- Elementor-centric positioning leaves room for a polished block-first grocery product;
- Grovia can aim for a smaller, more coherent default shopping surface.

### GreenMart — mature customization/feature benchmark

Current strengths:

- 8+ organic niches;
- Elementor + WPBakery + theme options;
- AJAX search/cart;
- min/max/step quantity;
- mobile menus;
- one-click import;
- years of market presence and reviews.

Weakness/opportunity for Grovia:

- multiple builders/frameworks create a larger compatibility obligation;
- current changelog continues to include Woo template updates and builder fixes;
- broad organic-theme visual language is easier to substitute than a distinctive grocery workflow.

### Nest — hyperlocal/delivery benchmark

Current strengths:

- GPS location filtering;
- distance-based delivery charges;
- mobile positioning;
- multivendor integrations;
- WhatsApp ordering and broad commerce feature set.

Weakness/opportunity for Grovia:

- “location” or “delivery” alone is no longer differentiation;
- Grovia must be more precise: early serviceability certainty tied to WooCommerce configuration, without claiming a delivery time/rate that cannot be proven;
- broader multivendor/logistics scope increases configuration and maintenance surface that Grovia deliberately excludes from V1.

### Grogin — newer grocery challenger benchmark

Current strengths:

- established vendor reputation;
- grocery-specific presentation;
- Elementor/Elementor Pro compatibility;
- theme setup flow;
- broad WooCommerce compatibility claims;
- hundreds of existing sales/reviews, which Grovia does not yet have.

Weakness/opportunity for Grovia:

- public listing is not Gutenberg optimized;
- broad feature competition remains easy to copy;
- Grovia can position around block editing, shopping speed and maintainability instead of another feature bundle.

## 3. Buyer-facing scorecard

Scale: 1 = weak/unproven, 3 = competitive baseline, 5 = market-leading/very strong for the evaluated dimension.

| Dimension | WoodMart Grocery | Bacola | GreenMart | Nest | Grogin | Grovia now | Grovia V1 target |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| Marketplace first impression | 5 | 4.5 | 4 | 4 | 4 | 2.5 | 4.5 |
| Grocery-specific shopping focus | 3.5 | 4.5 | 3.5 | 4 | 4 | 4.5 | 5 |
| Mobile product density | 5 | 4 | 4 | 4 | 4 | 4 | 4.5 |
| Search/discovery prominence | 5 | 4.5 | 4 | 4 | 4 | 4 | 5 |
| Listing-page quantity workflow | 5 | 5 | 4 | 4 | 4 | 4* | 5 |
| Delivery/serviceability clarity | 2.5 | 4 | 2.5 | 5 | 3 | 4 concept | 5 |
| Repeat-shopping focus | 3 | 3 | 2.5 | 2.5 | 2.5 | 3 concept | 5 |
| Filter/sort polish | 5 | 4.5 | 4 | 4 | 4 | 2.5 | 4.5 |
| Visual distinctiveness | 4.5 | 4 | 3.5 | 3.5 | 3.5 | 2.5 | 5 |
| Customization breadth | 5 | 4.5 | 5 | 4 | 4 | 2.5 | 3.5 |
| Block/native WordPress alignment | 4.5 | 2 | 2 | 2 | 2 | 5 | 5 |
| Low mandatory dependency surface | 3 | 2.5 | 2 | 2 | 2.5 | 5 | 5 |
| Market trust/reviews | 5 | 4.5 | 4.5 | 4 | 3.5 | 1 | 2 initially |

`*` The prototype has the desired Add → quantity interaction, but founder testing exposed competing cart truths in the parallel PR #79 implementation. The canonical stack must prove one authoritative Woo cart before this can be scored as release-ready.

## 4. Where Grovia is genuinely strong

### A. Product thesis is more focused than most theme checklists

The strongest reason to buy cannot be “AJAX”, “quantity controls”, “mobile menu” or “location”. Competitors already have them.

The strongest V1 promise is the integrated loop:

> Know whether the store serves me → find groceries quickly → add/change quantities without losing context → always trust the basket → reuse habitual purchases → checkout through WooCommerce.

That is a product system, not a checkbox.

### B. Block-first architecture is a real strategic opening

ThemeForest grocery results remain heavily Elementor-oriented while current WooCommerce Store Editing increasingly rewards block themes. Grovia should keep Elementor optional rather than make it the runtime foundation.

### C. Mobile grocery behavior is being designed before desktop decoration

The prototype already demonstrates understandable product → price → Add/quantity → Basket Pulse → mobile navigation behavior. That simplicity should be protected.

### D. Security/compatibility are designed as product-quality gates

The repository already treats authorization, CSRF, XSS, data boundaries, dependency risk and exact platform compatibility as release work. Buyers may not see this first, but agencies, marketplace reviewers and long-term customers will experience the consequences.

## 5. Where Grovia is currently weak

### A. Visual quality does not yet sell the product

The current Playground prototype communicates interaction, but placeholder imagery and unfinished default Woo residues make it look like engineering work rather than a premium commercial product.

A buyer will compare screenshots before reading architecture notes. This is the largest immediate commercial design gap.

### B. The current canonical palette risks becoming another AI/template default

Current base tokens use warm `Paper`/`Soft` surfaces with a `Copper` accent. The installed `frontend-design` review explicitly warns that warm cream + terracotta/copper-like accent treatments are now common generic AI design defaults.

This does **not** mean the palette is automatically wrong. It means color cannot be our signature and the system needs a stronger subject-specific rationale.

### C. `Fresh Grove` conflicts with our own originality rule

The current `Fresh Grove` style variation uses pale green surfaces, green accent and serif headings. That is very close to the exact generic “organic theme” shorthand prohibited in `docs/DESIGN-PRINCIPLES.md`.

Decision: **do not ship Fresh Grove unchanged.** Redesign or remove it before V1 visual freeze.

### D. Stock WooCommerce UI residue breaks coherence

Founder screenshots exposed native `Showing all results`, browser-like `Default sorting`, duplicate `X in cart`, `View cart` and custom Basket Pulse/quantity UI at the same time.

Decision: when Grovia enhancement is healthy, the shopper should see one coherent interaction language. Native fallback must remain available when enhancement fails, but duplicate healthy-state controls should not compete visually.

### E. Trust is almost zero compared with mature products

No amount of visual polish can create 3,000 reviews. Grovia must earn trust through the first external customer, then 10, then 100, alongside transparent support/update/security practice.

## 6. Design decisions to keep

1. **No giant generic marketing hero before grocery discovery.** Search/serviceability/category/product entry must dominate the first task viewport.
2. **Two-column dense mobile grocery cards where width allows.** Density is part of the product value.
3. **Add → `− quantity +` after authoritative cart success.** Keep one interaction instead of separate Add + quantity + View Cart clutter.
4. **Persistent but non-obstructive Basket Pulse.** It must never disagree with WooCommerce and must not cover checkout/focused controls.
5. **Mobile bottom navigation.** Keep labels visible; do not make icons the only meaning.
6. **Serviceability before basket investment.** Collapse to a compact status for returning/known-location states.
7. **No wishlist/compare/quick-view overload by default.** Saved/Shopping List should have a grocery-specific role rather than generic wishlist cloning.
8. **One Modern Grocery visual system first.** No demo-count race.

## 7. Decisions/mistakes to correct

1. **Parallel cart engines:** stop. Port good UI ideas onto the canonical `storefront-core` implementation after cart-authority regression proves it.
2. **Visual polish too late:** infrastructure is strong, but placeholder/demo visual quality now needs equal priority.
3. **Fresh Grove visual shorthand:** redesign/remove before release.
4. **Generic token naming (`Copper`) leaking into semantic meaning:** move toward semantic design tokens so a visual style can change without commerce components assuming a particular color story.
5. **Native + enhanced duplicate controls:** make progressive enhancement visually atomic.
6. **Do not add more V1 features to compensate for unfinished presentation.** Finish the core journey and visual system first.

## 8. V1 visual competitiveness gate

A visual release candidate should not pass until all are true:

- [ ] no placeholder product imagery in the commercial demo;
- [ ] product photography/assets have documented redistribution rights;
- [ ] 320 / 390 / 430 px mobile screenshots show no horizontal overflow;
- [ ] search, serviceability and a meaningful path to products are available within the first mobile viewport without a giant hero tax;
- [ ] product title, pack/unit, price and Add/quantity state form one predictable card rhythm;
- [ ] Add/quantity controls maintain at least 44 px practical touch targets;
- [ ] native WooCommerce fallback exists, but healthy enhanced state has no duplicate purchase controls;
- [ ] Basket Pulse never covers the last actionable content and is suppressed/reworked on checkout where appropriate;
- [ ] Shop Filter/Sort is a deliberate mobile surface rather than raw default Woo archive chrome;
- [ ] empty/loading/error/unavailable states are designed, not browser/default-theme leftovers;
- [ ] the design still looks recognizably Grovia after the logo is removed;
- [ ] the selected direction does not reduce to green + cream + serif + leaf imagery;
- [ ] the visual signature is tied to grocery shopping behavior, not decoration;
- [ ] keyboard focus, reduced motion and 200% zoom survive visual polish;
- [ ] the same fixed 10-item mission is measured on Grovia and at least three reachable competitor demos;
- [ ] at least five relevant external users/store builders provide real preference/comprehension evidence before strong commercial UX claims.

## 9. Recommended signature direction

Do **not** invent an unfamiliar shopping control just to look unique.

Working signature:

> **The live market shelf** — a dense, calm product surface where serviceability, search/aisles, product quantity state and basket state visibly behave as one continuous shopping instrument.

The memorable part should be what happens:

- compact serviceability status;
- search/aisle context stays understandable;
- Add transforms cleanly into quantity;
- basket truth is persistent;
- repeat purchases can re-enter the same shelf without a separate “account dashboard journey”.

Visual identity should support that instrument with disciplined typography, product imagery, spacing and motion—not compete with it.

## 10. Next design work

### Track A — correctness

- finish #83 cart-authority regression on canonical stack;
- remove parallel cart-state implementation risk;
- ensure Product Workspace, bottom Cart badge, Store API, Cart and Checkout agree.

### Track B — visual system

Produce **three** subject-specific visual directions for the same mobile home/search/product shelf without changing UX architecture. Each direction must define:

- 4–6 color tokens with rationale;
- display/body/utility typography roles;
- image treatment;
- card geometry/density;
- icon language;
- one signature visual behavior;
- dark/light or alternate-style strategy only if justified;
- accessibility/performance consequences.

Reject any direction that could be relabeled as a generic organic template.

### Track C — controlled competitor task benchmark

Mission:

> confirm delivery where available; find/add 10 common groceries across 3+ categories; change 3 quantities; remove 1 item; understand basket count/total; reach Cart.

Record:

- time to first add;
- total task time;
- deliberate taps;
- page transitions;
- search/category errors;
- cart corrections;
- time to serviceability certainty;
- layout/obstruction failures;
- what each competitor does better than Grovia.

## 11. Current position

Grovia is **not yet a top market product**. It is a pre-market challenger with stronger product focus and architecture than its current screenshots communicate.

If launched today, mature competitors win first impression, breadth, customization and trust.

The plausible V1 winning territory is narrower:

> **premium mobile grocery task UX + block-first maintainability + disciplined commerce/security architecture.**

The next milestone is not “more features”. It is to make that advantage visible, measurable and trustworthy.

## Sources reviewed 2026-08-21

- Existing controlled recon: `research/competitors/mobile-recon-2026-08-18.md`
- https://woodmart.xtemos.com/demo-grocery/demo/grocery/
- https://themeforest.net/item/bacola-grocery-store-and-food-ecommerce-theme/32552148
- https://klbtheme.com/bacola/intro/
- https://themeforest.net/item/greenmart-organic-food-woocommerce-wordpress-theme/20754270
- https://mobile.thembay.com/greenmart/
- https://themeforest.net/item/nest-multipurpose-woocommerce-wordpress-theme/37772027
- https://themeforest.net/item/grogin-grocery-store-woocommerce-wordpress-theme/50297090
- https://themeforest.net/category/wordpress/ecommerce/woocommerce?term=grocery
- `docs/PRD.md`
- `docs/DESIGN-PRINCIPLES.md`
- `docs/SECURITY.md`
- `packages/storefront-theme/theme.json`
- `packages/storefront-theme/styles/fresh-grove.json`
- `packages/storefront-theme/styles/minimal-market.json`
