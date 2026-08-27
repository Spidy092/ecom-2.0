# Grovia Fresh Dispatch — Stitch Design System

This is the visual source of truth for the Grovia Modern Grocery storefront. It
is written so a future Stitch session can generate screens that remain faithful
to the implemented WordPress/WooCommerce experience. The Stitch MCP is not
connected in this workspace; this document does not add a runtime dependency.

## 1. Visual theme and atmosphere

Fresh Dispatch is a confident, editorial grocery interface: **Daily App
Balanced** density (6/10), **Offset Asymmetric** variance (7/10), and
**Static Restrained** motion (2/10). It should feel like a well-run market
dispatch board rather than an organic-theme template.

The signature is the shopping sequence itself: search, numbered aisles, product
identity, unit/price, quantity, and cart feedback. The display type and tomato
action color create recognition, but they never compete with grocery tasks.

## 2. Color palette and roles

- **Paper Canvas** `#FFFDF8` — page and reading background.
- **Aubergine Ink** `#1E1424` — header, hero field, footer, primary text on light surfaces.
- **Clean Surface** `#FFFFFF` — search fields and product image wells.
- **Apricot Tint** `#F6D8C8` — product board and warm section surface; this is a surface tint, not a second accent.
- **Muted Ink** `#665D68` — metadata, helper copy, and quiet navigation.
- **Structural Rule** `rgb(30 20 36 / 22%)` — shelf rules and low-emphasis borders.
- **Tomato Action** `#FF4B2B` — the single accent for Add, focus, active aisle state, and primary CTA.

Never introduce a second accent, neon gradient, purple glow, or pure black.
Contrast must remain explicit; status cannot be communicated by color alone.

## 3. Typography rules

- **Display:** compressed sans with a tight track (implementation uses `Impact`,
  `Haettenschweiler`, and `Arial Narrow` fallbacks); uppercase only for short
  editorial headings and aisle numerals.
- **Body:** readable system sans with relaxed leading and a maximum line length
  of roughly 65 characters. Do not load a remote font at runtime.
- **Numbers:** tabular numerals; use a mono face for quantities, prices, and
  aisle indices when density increases.
- **Scale:** use `clamp()` for display headings; never allow a hero word to
  become a multi-screen text wall.

Do not use Inter, generic serif faces, decorative script, or all-caps body copy.

## 4. Hero and header

The header is a dark, two-level dispatch shell:

1. slim delivery context line;
2. Grovia mark, account/cart actions, and navigation;
3. full-width search field with one tomato search button and a delivery action.

The hero is an asymmetric split composition. Numbered aisle links occupy a
quiet left rail, the short headline and one primary CTA occupy the copy zone,
and the original market-crate image occupies a dedicated visual zone. Text and
image never overlap. Search and aisle access remain available before any
promotional story.

Use one primary CTA: **Shop the market**. Avoid sliders, autoplay, scroll
prompts, filler labels, or a second competing hero action.

## 5. Product ledger components

The product board uses a gapless rule grid, not nested cards. Every product keeps
this order:

1. image;
2. product title;
3. unit or pack context;
4. current price;
5. availability or product truth;
6. quantity rail and Add action;
7. secondary action only when it is useful.

Simple purchasable in-stock products show a tomato Add button and `− 1 +`
quantity rail. Variable products show **Choose options** and never silently
select a variation. Out-of-stock or unavailable products state the reason and
do not show a misleading Add action.

Product image wells are square or gently cropped, never broken-image placeholders.
Use real dimensions and responsive sources. Loading preserves card geometry;
errors are inline and recoverable.

## 6. Controls and states

- **Buttons:** flat, tactile, minimum 44px target, 2px or less radius, tomato
  fill only for the primary action. Active state may translate by 1px; no glow.
- **Inputs:** label above the field, helper/error text below, visible tomato
  focus ring with adequate offset, no floating labels.
- **Cart feedback:** a compact anchored confirmation reports the added item and
  current count without blocking the next action.
- **Empty states:** explain what to do next (`Browse the market`, `Open Buy
  Again`) rather than showing an unexplained blank panel.
- **Errors:** name the failed operation and provide Retry; Account, Cart, and
  Checkout remain usable.

## 7. Layout and responsive behavior

- Contain content at approximately 1280px with generous dark-to-paper section
  transitions.
- Use CSS Grid for product boards and asymmetric hero structure; do not use
  percentage math hacks.
- Desktop: four product columns with shared rules.
- Tablet: two columns only when the card remains fully actionable.
- Mobile (`<= 767px`): one column, compact image height, thumb-reachable
  quantity rail, and the persistent five-item shopping navigation.
- Never create document-level horizontal scroll. The aisle rail may scroll
  inside its own bounded region.
- Reserve bottom padding so fixed navigation cannot cover focused content.

## 8. Motion and performance

Motion is restrained in the active shopping workspace. Use short transitions on
focus, add confirmation, and disclosure only. Respect
`prefers-reduced-motion: reduce`. Animate only `transform` and `opacity`; do
not add parallax, autoplay, perpetual shimmer, or a heavy animation library.

Images are local, original, and responsive. No remote fonts, competitor assets,
runtime CDN, Elementor dependency, or new frontend framework is permitted.

## 9. Anti-patterns — never do these

- No copied competitor layout, wording, screenshots, or distinctive assets.
- No leaf-logo shorthand, green-gradient organic theme, or generic hero formula.
- No emojis, Inter, generic serif fonts, pure black, neon gradients, or glow shadows.
- No three-equal-card marketing row, nested card stacks, giant centered hero, or
  fake testimonials.
- No filler copy such as “Scroll to explore” or “Seamless shopping.”
- No hidden product titles, tiny quantity controls, misleading availability, or
  broken image links.
- No client-side order/customer identifiers and no guest purchase history.

## 10. Validation bar

A generated screen is acceptable only if a shopper can search, choose an aisle,
understand a product's unit/price/availability, add a simple item, and see cart
feedback without leaving the shopping context. Check 320px, 390px, 430px, and
desktop widths; keyboard focus, contrast, reduced motion, 200% reflow, and fixed
navigation coverage must remain correct.
