# V1 Visual Asset Strategy

**Status:** engineering-alpha decision for Modern Grocery  
**Issue:** #58  
**Scope:** typography, icons and canonical demo/starter imagery

## 1. Commercial objective

The visual system exists to help a buyer understand and trust the product, not to win a novelty contest.

The V1 asset strategy should make the live demo look polished while keeping the buyer's installed store fast, predictable and free from hidden remote dependencies or ambiguous asset rights.

The storefront's primary identity remains the shopping ledger defined in `docs/STOREFRONT-DESIGN-SYSTEM.md`. Imagery supports product identity; it does not replace the shopping interaction model.

## 2. Typography decision: system fonts for V1

V1 keeps the existing system font stacks from `packages/storefront-theme/theme.json`.

Primary:

`system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`

Secondary/editorial option:

`Iowan Old Style, Baskerville, Times New Roman, serif`

Reasons:

- zero font files in the customer package;
- zero remote font request;
- no flash/download dependency from a font CDN;
- no extra font redistribution record;
- reliable WordPress editor/front-end parity;
- no additional payload before the first meaningful storefront content.

WordPress supports both system font stacks and theme-bundled web fonts in `theme.json`. We deliberately choose the system-font path for V1.

A bundled open-source font is a future experiment, not a V1 requirement. It may be introduced only if moderated buyer testing shows a material commercial benefit that justifies the payload and maintenance cost.

## 3. Icon decision: first-party minimal SVG grammar

V1 does not require Lucide, Font Awesome, an icon font or another runtime icon package.

Product-owned UI icons use a small first-party SVG grammar:

- `viewBox="0 0 24 24"`;
- stroke-based rather than filled illustration;
- nominal 2px stroke;
- round line caps and joins;
- no embedded stylesheets or scripts;
- no external references;
- no text converted to paths;
- use `currentColor` so theme state controls appearance;
- decorative icons are `aria-hidden="true"`;
- icon-only controls require a programmatic accessible name;
- do not duplicate an icon when a clear text label is better.

Initial semantic set, only when needed by real UI:

- search;
- cart/basket;
- saved/bookmark;
- plus;
- minus;
- close;
- chevron/back;
- location/delivery;
- account;
- home/store.

Do not create a large icon library pre-emptively. A new icon should be added only when a shipped interaction needs it.

### Third-party fallback

If a future feature needs a broad third-party icon set, Lucide is an acceptable candidate for evaluation because it publishes an ISC license. It is not a V1 dependency and must still be entered into `release/third-party-assets.json` before redistribution.

## 4. Canonical demo imagery decision

The canonical Modern Grocery starter and the live sales demo should use the same controlled first-party image set wherever practical.

This avoids the common failure where marketplace screenshots show premium photography but the imported starter either has missing images or materially different visuals.

### Canonical provenance

Preferred V1 provenance:

1. `first-party-generated` — generated specifically for this product and reviewed;
2. `first-party-created` — photographed/illustrated by the product team;
3. `third-party-licensed` — allowed only as an exception with explicit redistribution evidence and third-party inventory linkage.

The OpenAI Terms of Use currently state that, as between the user and OpenAI and to the extent permitted by law, the user owns Output. Generated assets still require product review for similarity, trademarks, misleading content and suitability before release.

### Stock-photo libraries

Stock libraries are not a required V1 starter dependency.

Current provider terms differ. For example, Pexels expressly lists use in a template you sell as an allowed example, while Unsplash separately restricts selling images without significant modification and has help guidance warning about redistribution/selling. Rather than make starter reliability depend on interpreting provider-specific terms, the canonical V1 starter uses controlled first-party assets.

Stock assets may be used later on a marketing surface only after exact-use review. They must never be silently copied into the customer starter.

## 5. First image batch

The first canonical batch maps to the existing deterministic grocery fixtures:

1. Alpha Milk — dairy;
2. Alpha Bread — bakery;
3. Alpha Tomato — produce/out-of-stock state;
4. Alpha Apple — produce;
5. Alpha Lentils — pantry;
6. Alpha Rice Pack — pantry/variable product.

These fixture names are engineering labels, not final customer-facing product names. The image composition may survive naming changes, but image files must not contain visible `Alpha` text.

## 6. Product-image art direction

Product images should feel like a credible modern grocery catalogue, not generic organic lifestyle stock.

For the first batch:

- one product or simple product grouping per image;
- near-square composition;
- warm neutral/paper-compatible background;
- soft natural-looking light;
- realistic texture and scale;
- useful negative space around the object so WooCommerce crops remain safe;
- no readable packaging copy;
- no visible third-party logos or trademarks;
- no certification marks;
- no invented nutrition/health/organic claims;
- no identifiable people;
- no hands unless a later editorial asset explicitly needs them and is reviewed;
- avoid props that imply a country/brand/medical benefit unnecessarily.

Canonical product output target:

- aspect ratio: `1:1`;
- working source: at least `1200 x 1200`;
- packaged derivative target: `960 x 960` WebP where quality review permits;
- target compressed budget: `<= 200 KB` per product image;
- image must remain recognizable at a 72–96px rendered product-row size.

The byte budget is an internal performance target, not a claim that every image can hit the same number without visible quality loss.

## 7. Editorial/department imagery

Department or campaign imagery is optional for V1 and must not push Search/Departments out of the first useful viewport.

If used:

- maximum one dominant campaign image before the first product section;
- 3:2 or 16:9 depending surface;
- no text baked into the image;
- text/CTA remains HTML for accessibility/localization;
- no autoplay/video requirement;
- no image should be necessary to understand Add, quantity, Saved, delivery or Cart state.

## 8. Provenance contract

`release/demo-assets.json` is the source of truth for canonical starter/demo imagery.

Every real image entering the distribution root must record:

- stable asset ID;
- exact repository file path;
- role;
- linked fixture/content identity;
- provenance type;
- source/generation reference;
- rights evidence;
- surfaces where it is used;
- width/height target;
- file byte budget;
- whether people, logos or readable branded packaging are present;
- review status;
- reviewer/date;
- alt-text guidance.

No image is release-approved merely because it exists in the repository.

## 9. Third-party interaction

`release/third-party-assets.json` remains the source of truth for third-party redistribution.

A demo asset marked `third-party-licensed` must also point to an approved third-party inventory item. A first-party generated/created asset must not be misrepresented as a third-party dependency merely to satisfy the scanner.

## 10. Sales/demo/starter parity

For a canonical asset, prefer this path:

`one approved master -> optimized packaged derivative -> starter import -> live demo -> sales screenshots`

Do not maintain unrelated photo sets for the live demo and starter unless there is a documented reason.

Screenshots should represent what the buyer can actually achieve from the shipped starter, not a hand-curated private demo that cannot be reproduced.

## 11. Accessibility

- Product images use meaningful product alt text when the image conveys the product identity.
- Decorative editorial images may use empty alt text when surrounding text already provides the content.
- Do not encode price, availability, discount, delivery eligibility or status solely into pixels.
- Icon-only controls require accessible names independent of SVG shape.

## 12. Release gates

Engineering gate:

- strategy committed;
- demo manifest schema committed;
- CI rejects untracked demo images;
- CI rejects unreviewed first-party images;
- CI requires third-party linkage for third-party demo assets;
- no remote font/icon/photo dependency introduced.

Commercial visual gate:

- first six image assets generated/created;
- trademark/people/package review complete;
- mobile product-row review complete;
- desktop crop review complete;
- optimized files meet acceptable quality/payload trade-off;
- same assets visible in starter and live demo;
- target-buyer sessions confirm the imagery improves trust/comprehension without obscuring shopping actions.

## 13. Evidence references

- WordPress Theme Handbook — Typography: https://developer.wordpress.org/themes/global-settings-and-styles/settings/typography/
- WordPress Theme Handbook — Including Assets: https://developer.wordpress.org/themes/core-concepts/including-assets/
- OpenAI Terms of Use: https://openai.com/policies/terms-of-use/
- Pexels License: https://www.pexels.com/license/
- Unsplash License: https://unsplash.com/license
- Lucide: https://lucide.dev/
