# Grovia Fresh Dispatch visual direction

**Status:** selected for implementation  
**Date:** 2026-08-27  
**Scope:** Modern Grocery homepage, catalog cards, account-aware Buy Again entry, and mobile shopping shell

## Customer problem

The current demo proves the grocery flows but does not yet look like a premium commercial theme. Store owners judge a theme first through the demo and marketplace thumbnail; shoppers then need to search, scan units and prices, change quantity, and reach the cart without promotional noise.

## Competitor observations

- Namm leads with cinematic farm photography and a complete visual world, but its slider, generic organic language, promotional density, and variable-product-heavy grid delay grocery tasks.
- Organio has a polished image-led hero and a prominent category/search header, but the dense navigation, green organic shorthand, floating utility chrome, generic claims, ratings, compare, wishlist, and quick-view actions compete with buying.
- Groffer creates a populated catalog impression with a strong condensed display face and many product/category panels, but its header is crowded, the product grid does not prioritize quantity shopping, and page content includes dated template filler.
- Ciasto demonstrates the commercial value of a dramatic image-led first viewport, but its demo contains placeholder copy and is closer to a brochure hero than a complete grocery-shopping system.

Unavoidable conventions are search, department navigation, product images, prices, account, and cart. Grovia must not copy the competitors' brand marks, imagery, hero arrangements, organic-green palette, decorative vegetable treatments, sliders, or demo copy.

## Alternatives considered

### A. Market Ledger

Warm limestone, ink, and persimmon; editorial serif plus Swiss sans; overhead market photography; open product columns with fine rules.

Strengths:
- premium, calm, highly legible;
- broad merchant appeal;
- easy to express accessibly with local/system fonts and minimal motion;
- product truth and quantity controls remain clear.

Risk:
- without exceptional photography it can regress into a tasteful but anonymous editorial shop.

References:
- `comps/market-ledger-hero.png`
- `comps/market-ledger-products.png`

### B. Fresh Dispatch

Deep aubergine, tomato red-orange, white, and pale apricot; compressed market-sign display typography plus neutral sans; numbered aisle index; flash-lit grocery crate; gapless merchandise board.

Strengths:
- recognizable without the Grovia wordmark;
- unusually strong marketplace-thumbnail presence;
- the numbered aisle system turns department navigation into the visual signature;
- hard rules and large actions support fast scanning instead of decorative card chrome;
- adapts naturally to a thumb-scrollable mobile aisle rail and fixed shopping navigation.

Risk:
- the display face and dark field must remain controlled so the interface does not become loud or reduce product readability.

References:
- `comps/fresh-dispatch-hero.png`
- `comps/fresh-dispatch-products.png`
- `comps/fresh-dispatch-mobile.png`

## Decision and uniqueness thesis

Select **Fresh Dispatch**.

Grovia will look like a contemporary urban market dispatch board, while behaving like a high-frequency grocery tool. The signature is not decoration alone: numbered aisles, a dominant search field, a fixed information order, visible quantity state, truthful availability, and repeat-purchase access all form one visual and interaction system.

The design beats the benchmark when a shopper can identify delivery context, search, choose an aisle, understand unit/price/stock, and add a simple product without decoding competing promotional controls.

## Extracted design specification

### Typography

- Display: very heavy compressed sans, all caps only for short hero and section statements.
- UI/body: neutral sans with high x-height and ordinary sentence case.
- Desktop hero headline: two lines, approximately 88–112px, tight line-height.
- Mobile hero headline: three compact lines are acceptable at 390px, approximately 54–64px, with the product image sharing the lower hero field.
- Product names use bold UI text; unit, stock, and supporting copy remain smaller but never below 14px.

### Palette

- Aubergine ink: `#1e1424`.
- Tomato action: `#ff4b2b`.
- Paper white: `#fffdf8`.
- Market apricot: `#f6d8c8`.
- Muted rule/text: derived from white/ink at accessible opacity; state is never communicated by color alone.

### Structure and spacing

- Desktop max content width remains broad enough for four useful product columns.
- Hero uses a disciplined asymmetric grid rather than a centered slider.
- Header search is the largest control and remains visible before promotional copy.
- Products form a gapless board with shared dividers, not floating cards.
- Major section spacing is generous; internal product spacing is compact and predictable.
- Radius is 0–2px for commerce surfaces. Shadow is unnecessary.

### Components

- Numbered aisle rail: `01 Produce`, `02 Dairy`, and so on, with horizontal overflow on mobile.
- Product board: image, title, unit, price, availability, primary quantity action, secondary list action.
- Direct add converts to an authoritative quantity state after Store API reconciliation.
- Variable products show `Choose options`; unavailable products explain the current state.
- Buy Again is a high-contrast dispatch band for authenticated shoppers, not a fake promotion.
- Mobile bottom navigation keeps Home, Shop, Search, Account, and Basket reachable and does not cover focused content.

### Image direction

- Flash-lit, high-contrast, honest grocery still lifes against simple studio surfaces.
- Fixed square or 4:3 frames for product imagery.
- No remote assets, brand labels, competitor images, lifestyle clichés, or decorative produce floating without a shopping purpose.

## Measurable quality bar

- Search is visible without scrolling at 320, 390, 430, and desktop widths.
- Delivery context is discoverable in one primary interaction.
- A simple seeded product can be added from Home in one product action.
- Unit, price, stock, and action are visible in a stable order.
- No horizontal page overflow at 320px; only the aisle rail may scroll horizontally.
- Mobile bottom navigation does not cover focused content or checkout controls.
- The page remains usable with reduced motion and at 200% zoom.
- No slider, autoplay, mandatory builder, remote font, copied asset, or added runtime dependency.

## Logo-removal critique

The direction remains identifiable through the aubergine/tomato field, compressed dispatch typography, numbered aisle rail, hard merchandise dividers, and flash-lit crate photography. Removing the wordmark does not reduce it to a generic green organic theme.
