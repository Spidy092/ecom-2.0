# Storefront Design System v0.1

**Status:** engineering-alpha visual system for moderated testing  
**Scope:** one Modern Grocery storefront only  
**Issue:** #38

This system turns the proven grocery interaction model into one coherent commercial-looking storefront without copying a marketplace theme or grocery app. It is intentionally small: one visual grammar, one action hierarchy, and one responsive behavior model.

## 1. Product expression

Working visual direction: **Market Ledger**.

The storefront should feel like a modern, well-run neighborhood market: ordered, fast to scan, warm without looking rustic, and premium without decorative excess.

The signature is not a leaf, gradient, hero illustration, glass card, or animation. The signature is the **shopping ledger**:

- strong alignment of product image, product identity, unit/price and quantity state;
- thin shelf-like rules instead of cards nested inside cards;
- a warm paper canvas with dark ink typography;
- copper/orange reserved for actions and selected state;
- cart state remains visually anchored while the shopper moves through departments.

If the logo is removed, the product should still be recognizable from this interaction hierarchy.

## 2. Color tokens

| Role | Token | Value | Use |
| --- | --- | --- | --- |
| Canvas | `paper` | `#FCFBF7` | page background |
| Primary text | `ink` | `#17201F` | body/headings/cart surface |
| Surface | `surface` | `#FFFFFF` | inputs, focused product surfaces |
| Quiet surface | `soft` | `#F3F0E9` | image wells, selected/utility backgrounds |
| Secondary text | `muted` | `#596463` | hints, metadata |
| Divider | `line` | `#D9DEDB` | rules/borders |
| Primary action | `copper` | `#9A3412` | Add, selected shopping state, key CTA |
| Strong action | `copper-dark` | `#7C2D12` | hover/active intent |
| Success | `success` | `#146C43` | served/in-stock confirmation where needed |
| Danger | `danger` | `#A32D2D` | unavailable/error state |
| Information | `info` | `#155E75` | neutral system information |

Contrast checks for the alpha palette:

- Ink on Paper: ~16.1:1.
- Muted on Paper: ~5.9:1.
- Copper on Paper: ~7.1:1.
- White on Copper: ~7.3:1.

Meaning must never rely on color alone.

## 3. Typography

No remote font dependency in alpha.

Primary family: system UI stack (`system-ui`, `-apple-system`, `BlinkMacSystemFont`, `Segoe UI`, sans-serif).

Type scale:

- `xs`: 0.78–0.82rem — auxiliary metadata only.
- `sm`: 0.875rem — hints/status/unit labels.
- `body`: 1rem — controls and product information.
- `lead`: 1.125rem — important totals/section introductions.
- `h3`: ~1.25rem — component section heading.
- `h2`: ~1.55rem — storefront section heading.
- `h1`: ~2rem fluid — page/marketing title only.

Shopping UI favors weight/spacing over large type. Prices and quantities use tabular numerals.

## 4. Spacing and geometry

Base spacing sequence:

`4, 8, 12, 16, 24, 32, 48, 64px`.

Use 12–16px internal spacing for high-frequency shopping components. Use 24–32px between major sections. Large editorial spacing is allowed outside the active shopping workspace but may not push Search/Browse below a promotional hero.

Radius:

- `sm` 8px — compact buttons/inputs.
- `md` 12px — images/search surfaces.
- `lg` 16px — floating cart/navigation only.
- `pill` 999px — department filters/badges only.

Do not round every container.

## 5. Layout

WordPress content width: 760px.  
Wide storefront width: 1240px.

Responsive behavior is one system, not separate mobile/desktop products:

- **320px:** single-column ledger, delivery fields stack, 48px minimum nav targets, no horizontal document overflow.
- **390px / 430px:** primary alpha target; product rows stay compact, search and Add are thumb-first, department rail can overflow horizontally without hiding the only navigation route.
- **521–700px:** compact tablet; delivery fields can use two columns, mobile shopping nav remains active.
- **>700px:** desktop; mobile nav disappears, product workspace can use multi-column product surfaces while preserving the same information/action order.
- **>1000px:** wider gutters only; do not invent a different shopping model.

## 6. Action hierarchy

### Primary

`Add`, successful checkout-facing actions, and the current department selection use Copper.

### Quantity state

Once a simple product is in Cart, `Add` becomes a compact `− quantity +` controller in the same visual location. Quantity state must be more prominent than Save.

### Secondary

Saved, Browse full shop, Choose options, close/back and utility controls use quiet/outlined treatment.

### Destructive/unavailable

Out of stock and errors use explicit text plus semantic state. Never represent failure with red alone.

## 7. Product card / ledger

Information order is fixed:

1. image/identity;
2. title;
3. unit/pack or relevant variation context;
4. price;
5. availability;
6. Add/quantity;
7. Save/secondary action.

Mobile defaults to a ledger row rather than a decorative tile. Desktop may place ledger rows into a grid, but the internal order stays identical.

States to support:

- default;
- Add available;
- quantity active;
- Saved;
- variable/Choose options;
- out of stock;
- unavailable;
- busy/loading/error.

## 8. Search and recovery

Search is the strongest form control in the workspace: 52–56px high with clear label, surface background and Copper focus treatment.

Typo recovery is a quiet assistance row, not a warning box. The suggested search is primary within that row; `Browse products` remains secondary.

Empty search is not styled like an error.

## 9. Delivery checker

Delivery is store context, not a marketing card.

Use a subtle warm surface/rule and compact fields. Served/not-served messages retain explicit wording and a small semantic marker/rule. Do not show invented ETA, fee, free-shipping or delivery guarantee.

## 10. Departments

The Aisle/department system is the primary structural signature.

- 2–8 top-level departments: horizontal rail with pill-like controls.
- selected department: filled Copper/white state plus `aria-pressed`.
- large taxonomy: grid chooser using the same department control grammar.
- selected state cannot rely on color only; weight/border/state remain visible.

The rail may scroll; the page itself may not overflow horizontally.

## 11. Saved

Saved is deliberately quieter than Cart. The disclosure should read like a utility, not a second purchase container. The expanded Saved surface uses the same ledger rows as active product results, with removal visually demoted.

## 12. Cart and mobile navigation

Cart is the strongest persistent surface after the first add.

- use Ink as the anchored cart surface;
- show count and total with tabular numerals;
- `View cart` uses a high-contrast action;
- sticky/fixed surfaces must not cover focused content.

Mobile navigation uses the Paper surface. Current destination uses Copper plus weight/shape rather than underline alone. Cart badge uses the same action color.

## 13. Focus and motion

Focus ring: 3px Copper with at least 3px offset where geometry permits. Fixed navigation may use inset focus so it is not clipped.

Motion budget:

- only state confirmation, disclosure and scroll/focus movement;
- typical transition 120–180ms;
- no parallax, autoplay carousel, decorative entrance animation or spring physics in the shopping workspace;
- `prefers-reduced-motion: reduce` removes non-essential motion and smooth scrolling.

## 14. Loading / empty / error

Loading must preserve layout where practical. Avoid adding a heavy skeleton framework in V0.1.

- loading: retain component geometry and use status copy/busy state;
- empty: plain explanation + useful next action;
- recoverable error: explicit failure text + retry/fallback route;
- protocol/transport failure must never look like a legitimate empty department.

## 15. Promotional/editorial content

Promotional content is allowed after the shopping entry point, not before it.

Rules:

- no full-screen hero before Search/department access;
- no more than one dominant campaign message in the first viewport;
- editorial cards cannot visually outweigh product purchase controls;
- store imagery should support product/store identity rather than become generic organic stock decoration.

## 16. Implementation contract

`theme.json` owns global palette, typography, spacing and default page styles. Theme CSS owns Storefront Core presentation. Storefront Core remains responsible for behavior/data/state and should not learn brand colors or decorative layout rules.

The first implementation pass must not change REST/cart/security semantics.

## 17. Validation gate

Before Issue #38 is considered complete:

- test 320, 390 and 430px with the real Woo fixtures;
- test desktop without changing the shopping mental model;
- verify keyboard focus through Search → department → Add/quantity → Saved → Cart;
- verify color contrast and reduced-motion behavior;
- moderate at least 3 target-user sessions for primary-action recognition;
- capture screenshots only after the behavior remains green;
- do not publish conversion/speed claims from visual preference alone.
