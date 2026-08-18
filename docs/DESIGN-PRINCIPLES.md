# Grovia UI/UX & Design Principles

**Purpose:** produce a premium, original ecommerce product — not a reskinned marketplace template.

## 1. Design starts from grocery behavior

Every visual decision must support a real grocery task:
- scan many items quickly;
- understand unit/pack/price quickly;
- add/change quantity quickly;
- know whether an item is unavailable;
- know where/when delivery is possible;
- see cart state without losing shopping context;
- return to habitual purchases quickly.

A decorative element that weakens these tasks is a defect, even if it makes a screenshot look more impressive.

## 2. No generic organic-theme visual shorthand

Do not default to:
- leaf logos;
- endless green gradients;
- cream backgrounds simply because the store is organic;
- hand-drawn vegetable decorations everywhere;
- giant centered hero + three statistic cards + icon row because it is a common template formula;
- random blobs/waves/overlapping circles;
- excessive rounded cards;
- gratuitous glassmorphism;
- stock photography that looks interchangeable with ten other organic themes.

The final visual identity should be derived from the selected store concept and should remain recognizable if screenshots are shown without the Grovia logo.

## 3. One memorable signature, disciplined surrounding UI

Each starter site should have one defensible visual/product signature rather than 20 competing effects.

For Modern Grocery, the signature should likely emerge from **shopping interaction** (search/category/quantity/cart/delivery system) rather than decorative illustration.

## 4. Mobile is designed first

Critical flows are wireframed and usability-tested at narrow widths before desktop polish.

Mobile requirements:
- thumb-reachable primary actions;
- persistent navigation without covering content;
- product cards scannable at realistic grocery density;
- fast quantity controls;
- no tiny hit targets;
- filter/search drawers with proper focus management;
- checkout unaffected by theme chrome;
- safe-area handling where needed.

## 5. Information hierarchy beats decoration

On a grocery product card, prioritize:
1. product identity/image;
2. title;
3. unit/pack/variant;
4. current price and important discount context;
5. stock/availability when relevant;
6. add/quantity action.

Badges, ratings, promotional copy and secondary metadata must not overwhelm purchase-critical information.

## 6. Shopping density is a feature

Grocery customers may scan dozens of products. The UI should balance:
- enough whitespace to remain clear;
- enough density to avoid endless scrolling;
- stable card heights where possible;
- obvious quantity state;
- consistent unit/pack placement;
- predictable touch targets.

Do not design product cards only for oversized desktop screenshots.

## 7. Interaction language is consistent

An action should keep the same name/state throughout the journey.

Examples:
- `Add` -> quantity control after success;
- `Save to list` -> `Saved` with a clear reversal;
- `Check delivery` -> specific available/unavailable result;
- loading and error states explain what happened without vague messages.

## 8. Accessibility is visual quality

A premium design includes:
- obvious keyboard focus;
- sufficient contrast;
- controls identifiable without color alone;
- motion that respects reduced-motion preference;
- form labels that survive placeholders;
- clear error placement;
- dialog/drawer focus containment and return;
- semantic controls rather than clickable decorative containers.

## 9. Performance is interaction design

Do not design an interaction we can only deliver by loading a large frontend stack on every page.

Before approving motion/search/card systems, understand:
- JS required;
- requests triggered;
- layout instability;
- image cost;
- font cost;
- cacheability;
- slow-network behavior.

The design must still communicate state on slower devices/connections.

## 10. Originality review before implementation

For each critical screen:
1. benchmark at least three relevant competitors;
2. document similarities that are unavoidable conventions;
3. identify distinctive competitor expressions we must not copy;
4. propose at least two alternative Grovia directions;
5. select one using task value, originality, accessibility and performance;
6. run a "logo removal" critique — if it becomes anonymous/generic, revise.

## 11. Prototype before framework

For the five initial screens:
- Home;
- Search/Shop;
- product card/quantity state;
- Cart/cart feedback;
- Mobile navigation;

create interaction prototypes before committing implementation architecture to a visual system.

## 12. Design success metrics

Use concrete measures when practical:
- time to add a 10-item grocery basket;
- taps/clicks;
- unnecessary page transitions;
- time to discover delivery availability;
- time for a new store owner to reach a credible demo storefront;
- keyboard task completion;
- accessibility audit failures;
- mobile layout shifts;
- frontend asset/request budget;
- user confusion/help requests during testing.

## 13. Copy principles

- plain language;
- active verbs;
- labels describe customer-recognizable actions;
- avoid exaggerated marketing inside the product UI;
- errors state what failed and what can be done;
- empty states lead to a useful action;
- no fake urgency/social-proof demo content.

## 14. Theme customizability without chaos

Customers need a coherent system, not thousands of unrelated toggles.

V1 customization should prioritize:
- brand color system;
- typography system;
- logo/identity;
- layout density within tested ranges;
- header/navigation choices that preserve usability;
- product-card variants only when they solve distinct use cases.

Do not expose settings simply because the underlying CSS property exists.

## 15. Design quality gate

A V1 UI change should not merge until the reviewer can answer **yes** to:
- Does it solve a documented customer task?
- Is it clearly better than the benchmarked alternatives in at least one meaningful dimension?
- Is the expression original rather than copied?
- Does it work first-class on mobile?
- Is it keyboard/accessibility aware?
- Is its performance cost justified?
- Does it fit one coherent visual system?
- Would we still choose it if it were not being judged by a marketplace screenshot?
