# V1 Customer Customization Contract

**Status:** engineering-alpha  
**Issue:** #62

## 1. Commercial promise

The V1 storefront is customizable, but it is not an unrestricted page-builder product.

The product rule is:

> Strong default + easy customization + safe limits.

A buyer should be able to make the store visibly theirs without learning CSS or replacing the grocery workflow architecture. Advanced users can go further through WordPress-native template/block controls and normal developer extension points.

## 2. Ownership boundary

### WordPress / Theme owns presentation

Use the Site Editor and Global Styles for:

- site logo and site title;
- global colors;
- approved typography families;
- header content/navigation;
- footer content;
- homepage copy and block arrangement;
- block spacing, borders and other supported appearance tools;
- selecting a bundled style variation;
- user template/template-part overrides.

The Theme provides defaults. WordPress stores the buyer's editor/global-style customizations.

### WooCommerce owns store content and commerce truth

Use normal WooCommerce/Product/Media controls for:

- product names/descriptions;
- product/category images;
- prices;
- stock;
- variations;
- categories;
- cart/checkout/order/payment/shipping/tax state.

The Theme/Core must not create a second price/stock/cart model merely to make those values 'customizable.'

### Core owns grocery workflow behavior

Core owns the product workspace, Saved/Buy Again, delivery/serviceability, setup/status and related grocery workflow behavior.

Core may expose intentional product settings later, but it should not duplicate controls already owned by WordPress or WooCommerce.

## 3. Safe/common customization controls

These are the controls we expect a non-developer buyer to use confidently.

| Need | Owner / UI | V1 behavior |
| --- | --- | --- |
| Logo | Site Editor → Header → Site Logo | Native Site Logo block; no hard-coded brand image. |
| Store name | Settings / Site Editor → Site Title | Native Site Title block appears in header/footer. |
| Brand colors | Appearance → Editor → Styles | Semantic palette can be customized by the user. |
| Quick visual restyle | Appearance → Editor → Styles | Default plus `Fresh Grove` and `Minimal Market` style variations. |
| Typography | Global Styles | System Sans and System Serif only in V1; no remote font dependency. |
| Main navigation | Site Editor → Store Header | Native Navigation/Page List blocks. |
| Footer | Site Editor → Store Footer | Native blocks; Core mobile shopping nav remains part of the footer template. |
| Homepage copy/sections | Site Editor → front-page template | Normal block editing; product workspace remains a Core block. |
| Product/category images | WooCommerce / Media Library | Starter imagery is replaceable customer content after import. |
| Product content | WooCommerce Products | Normal Woo product editing. |

## 4. Advanced customization

WordPress `appearanceTools` remains enabled so capable buyers/designers can use supported block-level spacing, dimensions, borders and related design controls.

Advanced users may also:

- edit templates/template parts in the Site Editor;
- add custom CSS using supported WordPress mechanisms;
- use a child theme when code/file overrides are genuinely required;
- integrate compatible plugins/builders through public WordPress/WooCommerce contracts.

Advanced customization is not permission for the Theme/Core to depend on private WooCommerce internals.

## 5. Deliberate limits

V1 deliberately does **not** provide:

- a proprietary mega 'Theme Options' panel duplicating Global Styles;
- arbitrary uploaded/remote webfont infrastructure;
- dozens of header/footer builders;
- per-product custom commerce truth outside WooCommerce;
- unlimited layout combinations with no testing contract;
- a claim that every page builder/plugin combination is validated.

These limits reduce support cost and make the default experience harder to break.

## 6. Style variations

Theme style variations live in `packages/storefront-theme/styles/`.

V1 ships:

1. **Default / Market Ledger** — paper, ink and copper system-sans direction.
2. **Fresh Grove** — green-led palette with system-serif headings.
3. **Minimal Market** — neutral palette with restrained teal accent.

All variants keep the same semantic color slugs (`paper`, `ink`, `surface`, `soft`, `muted`, `line`, `copper`, `copper-dark`, `success`, `danger`, `info`). Product CSS consumes those slugs so choosing another style does not fork the grocery interaction implementation.

WordPress stores a selected style variation as a user customization. Therefore a future Theme update to a variation must not assume that existing users automatically receive the changed variation values. Release/support notes must treat buyer-saved styles as user-owned state.

## 7. Header/footer contract

`theme.json` registers:

- `header` as **Store Header** in the header area;
- `footer` as **Store Footer** in the footer area.

The default header contains:

- Site Logo;
- Site Title;
- Navigation with a mobile overlay behavior.

The default footer contains editable native blocks plus the Core mobile-shopping-navigation block.

Do not replace these with hard-coded company logos/menu HTML.

## 8. Homepage contract

The front page includes registered header/footer parts and the Core product workspace.

Buyer-editable marketing copy and block structure belongs to the Site Editor. Grocery transaction behavior remains inside supported Core/Woo blocks.

A future starter importer may create/manage additional homepage blocks only under the stable ownership/idempotency rules in `docs/STARTER-IMPORT-CONTRACT.md`.

## 9. Demo images are defaults, not lock-in

The canonical Modern Grocery images are starter/demo defaults. When imported they become normal WordPress Media attachments assigned to normal WooCommerce products.

A buyer can replace them through WooCommerce/Media controls. Runtime storefront code must not depend on a specific canonical image filename or generation ID.

## 10. Release/test gate

Before calling customization commercially ready:

- validate `theme.json` and every `/styles/*.json` file;
- verify the two registered template parts exist and use native branding/navigation primitives;
- verify the front page references both parts;
- verify the mobile shopping nav remains present;
- browser-test header/footer/navigation/storefront at target mobile and desktop widths;
- prove selecting/customizing presentation does not change WooCommerce commerce truth;
- document the customer-facing customization path with screenshots before paid launch;
- run target-buyer setup/customization observation.

CI can prove structural and browser contracts. It cannot prove that buyers understand the Site Editor or prefer a visual preset; that remains a moderated commercial validation task.
