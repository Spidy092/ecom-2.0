# Modern Grocery Visual System V1

**Status:** Engineering-alpha visual system  
**Issue:** #38  
**Date:** 2026-08-20  
**Public brand:** unresolved; AisleFlow/Grovia remain internal codenames.

## 1. Design thesis

Modern Grocery should look like a **well-organized contemporary market**, not a generic organic-theme landing page and not a clone of a quick-commerce app.

The signature comes from shopping structure:

```text
location context
→ search
→ aisles
→ dense product ledger
→ Add becomes quantity
→ one persistent Basket Pulse
```

The visual system therefore emphasizes typography, alignment, product recognition, pack/price scanability and state clarity. Decoration is subordinate to shopping.

## 2. Visual character

Working character:

- **ordered, not sterile**;
- **warm, not beige-everywhere**;
- **fast, not frantic**;
- **premium through restraint**, not gradients/glass/oversized cards;
- **food-led**, with photography carrying future richness;
- **urban-neutral**, avoiding the default green/leaf organic identity.

### Logo-removal test

A screenshot without the store logo should still be recognizable through:
- thin shelf/ledger rules;
- strong pack/price rhythm;
- restrained burnt-orange commerce accent;
- one floating dark Basket Pulse;
- aisle navigation integrated into the shopping hierarchy.

## 3. Color roles

Canonical V1 palette:

| Token | Value | Role |
|---|---:|---|
| Ink | `#161A1D` | primary text, strong rules, dark Basket Pulse |
| Paper | `#FFFDFC` | main store canvas |
| Soft | `#F5F3EE` | contextual surfaces only |
| Line | `#D9D6CF` | separators, field/card boundaries |
| Muted | `#5E5A54` | secondary text |
| Accent | `#C2410C` | Add/action/current-shopping state |
| Success | `#176B45` | confirmed service/availability |
| Warning | `#8A5300` | caution/recoverable attention |

Rules:
- Accent is an **action/state color**, not a large decorative background.
- Success is reserved for truthful positive state, never generic branding.
- Status must never rely on color alone.
- Paper remains dominant so real grocery photography can supply category color later.

## 4. Typography

V1 uses the system UI stack only. No remote font request and no bundled webfont are required.

```text
-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif
```

Scale:

| Role | Size |
|---|---|
| micro/meta | `0.75rem` |
| small/supporting | `0.875rem` |
| body/control | `1rem` |
| product/compact heading | `1rem`–`1.0625rem` |
| section heading | `clamp(1.25rem, 3vw, 1.55rem)` |
| store statement | `clamp(1.9rem, 6vw, 3.2rem)` max |

Rules:
- product titles: maximum two stable visual lines where practical;
- pack/unit is secondary to title but remains visible before Add;
- prices use tabular numerals where supported;
- avoid ultra-light weights;
- uppercase is limited to compact context labels, never body copy.

## 5. Spacing and layout

Base rhythm derives from `4px / 8px` increments.

```text
2XS  0.375rem
XS   0.5rem
S    0.75rem
M    1rem
L    1.5rem
XL   2rem
2XL  3rem
```

### Widths

- readable content: ~`760px`;
- wide shopping canvas: `1240px` max;
- mobile horizontal page inset: `14–16px`;
- desktop inset: `24–32px`.

### Breakpoints

These are implementation breakpoints, not device-brand assumptions:

- `<= 479px`: compact mobile grocery grid;
- `480–759px`: wide mobile / small tablet;
- `760–1023px`: tablet/compact desktop, bottom nav removed;
- `>= 1024px`: desktop market layout;
- `>= 1240px`: capped wide canvas.

## 6. Shape, border, elevation

The system deliberately avoids rounded-card overload.

- product cards: **no floating card shadow**;
- product cards: top rule / restrained border rather than container-on-container styling;
- primary controls: radius `10–12px`;
- chips/aisles: pill radius is acceptable because they are compact selectors;
- contextual delivery surface: radius `12px`, one border, no shadow;
- Basket Pulse: radius `12px`, one meaningful floating shadow;
- modal/drawer surfaces if later introduced may use stronger elevation; normal shopping content may not.

## 7. Product-card contract

Information order:

```text
image
product name
pack / unit
price / important price context
availability if necessary
Add OR quantity control
```

### Default

- image supports recognition but does not dominate mobile height;
- card content aligns to a shared baseline;
- action remains at the card footer;
- no wishlist/compare/quick-view cluster around Add.

### Added

`Add` becomes the quantity control in the same physical action zone.

```text
[ − ]   2   [ + ]
```

No second `X in cart` or `View cart` language is visible when Grovia enhancement is healthy.

### Loading

- action remains in place;
- controls become temporarily unavailable;
- opacity change is subtle;
- no full-card spinner or layout shift.

### Out of stock / unavailable

- price/product information remains readable;
- action zone contains explicit text;
- status is not color-only.

### Variable product

- never silently choose a pack/variation;
- use an explicit `Choose options` state until a future compact-choice interaction is validated.

## 8. Search

Search is a primary shopping control, not a decorative header field.

- located before product merchandising;
- minimum target height `48px`;
- strong ink boundary rather than shadow;
- placeholder examples are grocery-specific but concise;
- focus ring is visibly separate from the border;
- search state/results must not shift Basket Pulse or mobile navigation unexpectedly.

## 9. Aisles

Aisle navigation is structural, not an icon-grid decoration.

- horizontal native overflow on compact mobile when department count is small;
- text labels remain visible;
- active/current state uses border/weight plus color, not color alone;
- no food emoji/icon requirement;
- desktop can occupy a stronger horizontal band or future narrow side rail, but uses the same labels/state model.

## 10. Delivery context

Delivery certainty belongs above shopping, but it must not become a hero.

- one compact contextual surface;
- truthful availability only when backed by Woo/Grovia serviceability logic;
- until functional on a branch, do not display a fake green success state;
- delivery price/time is not inferred from postcode serviceability alone.

## 11. Basket Pulse

Basket Pulse is the only intentionally floating commerce surface in the default storefront.

Mobile:

```text
5 items · $18.40                 View basket
```

- dark Ink background;
- readable Paper text;
- positioned above bottom navigation and safe area;
- after an add/update, a short live status appears then clears;
- summary itself remains stable;
- all quantities/totals come from the authoritative Woo Store API cart.

Desktop:
- compact lower-right utility;
- never stretches full screen;
- does not become a side-cart replacement.

## 12. Mobile bottom navigation

- text labels remain in alpha; icons are not required for comprehension;
- minimum target height 44px;
- safe-area aware;
- white/Paper background with one top rule;
- no heavy shadow competing with Basket Pulse;
- current destination receives a non-color-only state when reliable.

## 13. Header / footer

Header:
- sticky but visually light;
- store identity left, cart/account utility right;
- no oversized mega-header before search;
- no promotional ticker by default.

Footer:
- simple store identity / help / policies in later commercial pass;
- remove engineering/prototype copy from the customer-facing demo;
- mobile bottom-nav clearance remains protected.

## 14. Motion

Default transitions should be `120–180ms` and limited to state recognition.

Allowed:
- Add → quantity state;
- subtle focus/pressed transition;
- Basket Pulse status appearance;
- disclosure expansion when it does not move focus unpredictably.

Avoid:
- entrance animation for every product card;
- scroll-jacking aisle motion;
- parallax;
- autoplay hero/video;
- decorative bouncing cart controls.

`prefers-reduced-motion: reduce` removes nonessential motion.

## 15. Loading / empty / error patterns

Loading:
- keep the eventual geometry where practical;
- text/status explains long-running actions;
- no whole-page loader for one cart mutation.

Empty:
- describe what is empty;
- offer one relevant recovery path (Search/Browse/etc.).

Error:
- localize the error to the failed action;
- do not destroy the last known good cart/product state;
- use plain language and retry path when safe.

## 16. Mobile acceptance frames

### 320px
- two-column product grid only if title/action remain usable; otherwise one dense row/list fallback is allowed;
- no document horizontal overflow;
- 44px action targets;
- Basket Pulse and bottom nav do not cover focused controls.

### 390px
- canonical V1 mobile reference;
- delivery/search/aisles remain above product merchandising;
- two products can be scanned together where realistic media exists;
- Add/quantity footer stays stable.

### 430px
- no arbitrary extra chrome merely because width is available;
- retain the same information hierarchy.

## 17. Desktop adaptation

Desktop is **not** a second product.

- header gains breathing room, not extra promotional rows;
- wide canvas supports four product columns where cards remain readable;
- aisle structure may widen but preserves its interaction semantics;
- Basket Pulse moves to a compact lower-right position;
- mobile bottom navigation disappears;
- shopping starts near the top instead of inserting a desktop hero.

## 18. Performance budget implications

This visual system requires:
- no frontend design framework;
- no slider library;
- no animation library;
- no icon pack;
- no remote font;
- CSS + existing small interaction modules only.

Future real demo images must follow the separate provenance/file-size gate.

## 19. Visual regression / review checklist

Every visual-system PR should capture or test:
- 320 / 390 / 430 mobile widths;
- 1024+ desktop;
- empty cart and non-empty Basket Pulse;
- product Add and quantity states;
- keyboard focus;
- reduced motion;
- out-of-stock/variable states when those fixtures exist;
- no duplicate native/Grovia cart UI;
- no engineering-placeholder/customer-facing copy.

## 20. Explicitly deferred

- final public brand/logo;
- custom webfont;
- multiple demo aesthetics;
- decorative hero/slider;
- dark mode;
- icon-only mobile nav;
- animation system;
- final photography batch (tracked separately);
- claim that this visual system improves conversion or speed without buyer/shopper evidence.
