# Product Requirements Document — Grovia V1

**Status:** Draft for founder review  
**Date:** 2026-08-18  
**Working product name:** Grovia  
**Product type:** Commercial WordPress + WooCommerce theme and companion core plugin

## 1. Product thesis

Grovia will be a fast, modern, grocery-first WooCommerce product that helps a store owner launch a polished online grocery store quickly and helps shoppers buy many everyday products with fewer steps.

The first release is intentionally narrow. We are not trying to beat every multipurpose WooCommerce theme on feature count. We are trying to make one grocery storefront experience meaningfully better on mobile, repeat shopping, delivery availability, setup simplicity, and performance.

## 2. Target customer

### Primary buyer

A small business owner, freelancer, or agency building a grocery, organic-food, farm-produce, or local fresh-food WooCommerce store.

### Primary shopper

A repeat grocery customer who frequently buys multiple inexpensive products and values speed, availability, quantity controls, and quick reordering more than elaborate product-comparison tools.

## 3. Core problems

### Store-owner problems

- Many commercial themes require numerous plugins and complicated demo imports.
- Setup frequently exposes implementation details instead of guiding a business owner.
- Theme feature count creates maintenance, compatibility, performance, and security burden.
- Store owners want a professional result quickly without being forced into a premium page-builder dependency.

### Shopper problems

- Grocery shoppers often need to add many products; opening a product page for each item is slow.
- Mobile ecommerce navigation is frequently only a reduced desktop navigation.
- Shoppers may spend time filling a cart before learning that delivery is unavailable in their area.
- Repeat customers must rediscover frequently purchased products.

## 4. V1 product promise

**Launch quickly. Shop quickly. Maintain easily.**

A competent WordPress user should be able to go from a clean WordPress installation to a working Modern Grocery storefront through a guided setup flow without needing developer assistance for the basic path.

A shopper should be able to discover, add, adjust, and purchase groceries with minimal full-page navigation.

## 5. Goals

### User goals

- Reduce friction between product discovery and adding an item to cart.
- Make quantity changes possible directly where products are discovered.
- Make delivery availability visible before checkout.
- Make repeat shopping faster through Shopping List and Buy Again experiences.
- Make the mobile store feel intentionally designed for grocery shopping.

### Business goals

- Ship one paid product before expanding into multiple product lines.
- Validate willingness to pay with the first 10 paying customers.
- Learn from support and usage evidence before adding new demos/features.
- Build a codebase that can support later commercial expansion without requiring a rewrite.

## 6. V1 functional scope

### 6.1 Storefront home

The Modern Grocery starter site must provide:
- prominent grocery search;
- delivery-location visibility;
- category entry points;
- popular/recommended products;
- fast add-to-cart controls;
- repeat-shopping section for eligible logged-in customers.

### 6.2 Instant product search

Search must:
- return relevant products without a full-page reload;
- show product image, title, price, availability, and direct add/quantity action where feasible;
- support keyboard navigation and accessible focus states;
- fail safely and degrade to a conventional search experience when JavaScript is unavailable or an endpoint fails.

### 6.3 Product cards and quantity shopping

Product cards must:
- show essential grocery information;
- allow add-to-cart without opening the product page for supported product types;
- switch to quantity controls after add;
- keep cart state synchronized;
- clearly handle loading, success, unavailable, and error states.

Complex products that require customer choices may route to the product page rather than hiding required choices.

### 6.4 Shop/category filters

V1 filters:
- category;
- price;
- stock/availability;
- relevant WooCommerce attributes.

Do not build a generic enterprise faceted-search platform in V1.

### 6.5 Delivery availability

V1 must allow a store owner to configure supported delivery postcodes/zones and allow a shopper to check whether delivery is available.

V1 does not include route optimization, driver assignment, live tracking, fleet management, or complex slot-capacity logistics.

### 6.6 Mobile navigation

Mobile storefront must provide a deliberate bottom-navigation experience for core destinations such as Home, Search, Categories, Cart, and Account.

Navigation must be keyboard/screen-reader friendly and must not obscure WooCommerce notices, cookie/privacy controls, or checkout UI.

### 6.7 Sticky cart feedback

After cart activity, the shopper should receive persistent but non-obstructive feedback containing item count, total where available, and a clear path to cart/checkout.

### 6.8 Shopping List

V1 supports one personal shopping list per customer.

Required behaviors:
- add/remove eligible products;
- view saved products;
- add selected/available products to cart;
- handle deleted/out-of-stock products gracefully.

Multiple named lists are deferred.

### 6.9 Buy Again

Eligible logged-in customers should be able to find products from previous orders and add currently purchasable products back to cart efficiently.

This feature must use WooCommerce customer/order capabilities and must respect access-control boundaries. It must not expose another customer's order data.

### 6.10 Product page

Must support standard WooCommerce product behavior and provide:
- clear price/variation information;
- quantity and add-to-cart;
- delivery availability entry point;
- Shopping List action;
- product description/reviews/related products using supported WooCommerce mechanisms.

### 6.11 Cart and checkout

Use WooCommerce's supported cart/checkout capabilities and blocks where appropriate. Grovia owns presentation and UX polish, not payment processing.

V1 must not implement a custom payment gateway.

### 6.12 Account

Prioritize:
- Orders;
- Buy Again;
- Shopping List;
- Addresses;
- Account details.

Avoid decorative dashboard analytics in V1.

### 6.13 Setup wizard

The guided setup should cover the minimum useful flow:
1. welcome;
2. store type;
3. logo/brand basics;
4. starter-site choice (Modern Grocery in V1);
5. essential WooCommerce defaults;
6. basic delivery-area configuration;
7. completion with next actions.

The wizard must not hide required WooCommerce legal/tax/shipping configuration behind misleading defaults.

### 6.14 Starter site

V1 ships one starter experience: **Modern Grocery**.

It must include the essential page patterns and demo content necessary to communicate the product, while ensuring every demo asset has a documented commercial redistribution license.

## 7. Non-functional requirements

### Performance

- Avoid loading feature assets on pages that do not use them.
- Prefer platform APIs and small modules to framework/plugin duplication.
- Optimize for strong Core Web Vitals on the controlled demo environment.
- Performance claims used in marketing must state test conditions and must not imply every customer installation will obtain identical scores.

### Accessibility

- Keyboard-operable interactive controls.
- Visible focus states.
- Semantic labels/names.
- Appropriate live announcements for asynchronous cart/search state where needed.
- Respect reduced-motion preferences.
- Target WCAG 2.2 AA for Grovia-owned storefront UI.

### Security

See `docs/SECURITY.md`. Security acceptance criteria are release blockers, not optional polish.

### Compatibility

V1 will define and continuously test a small supported matrix for WordPress, WooCommerce, PHP, and major browsers. Exact minimum versions will be locked immediately before implementation based on current supported upstream versions.

### Internationalization

- User-facing strings must be translation-ready.
- Avoid concatenated strings that cannot be translated safely.
- Design must tolerate longer translated labels.
- RTL support is a planned quality requirement; release timing depends on validation capacity and must not be claimed before tested.

## 8. V1 exclusions

Do not include in V1 unless the PRD is deliberately changed:
- multi-vendor marketplace;
- vendor dashboards;
- custom payment processing;
- route optimization or driver tracking;
- AI chatbot;
- AI product-description generation;
- multiple page builders;
- mandatory Elementor dependency;
- dozens of starter sites;
- advanced subscriptions/memberships engine;
- product comparison;
- custom analytics platform;
- CRM;
- native mobile app;
- SaaS control plane;
- white-label agency portal.

## 9. Commercial hypothesis

Initial pricing hypothesis: **US$59/year for one production site**, subject to validation before launch.

The commercial offering should primarily monetize updates, support, maintained premium product functionality, starter experiences, and future services while remaining compatible with WordPress ecosystem licensing requirements.

## 10. Success metrics

### First validation milestone

- 1 customer who is not a friend/team member pays for the product.

### Early validation

- 10 paying customers;
- successful installation/setup completion for those customers;
- documented reasons for every refund/support escalation;
- repeatable build/release process.

### Next validation

- 100 paying customers before broad product-line expansion;
- evidence-based list of top support issues and requested features;
- acceptable update adoption and compatibility failure rate.

## 11. V1 acceptance journey

A release candidate is not V1-ready until a new tester can complete this journey:

1. install/activate Grovia and required Grovia Core;
2. complete the setup wizard;
3. view the Modern Grocery storefront;
4. discover a product through category and search;
5. add and adjust quantity without unnecessary navigation;
6. validate delivery availability;
7. complete cart/checkout using WooCommerce-supported mechanisms;
8. sign in and use Shopping List;
9. after an eligible prior order exists, use Buy Again;
10. complete the core flow on mobile with keyboard/accessibility checks.

## 12. Open decisions before implementation

- final product/brand name after domain/trademark checks;
- exact supported WordPress/WooCommerce/PHP versions;
- direct-sales licensing/update provider;
- exact data-storage model for Shopping List and delivery configuration;
- telemetry policy (default is none unless explicitly designed as opt-in);
- final demo asset sources/licenses;
- whether RTL is launch-blocking or first maintenance release.

## 13. Product principle

When choosing between another feature and a simpler/faster core journey, choose the core journey until customer evidence proves otherwise.
