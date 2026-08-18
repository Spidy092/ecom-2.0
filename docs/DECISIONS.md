# Grovia Decision Log

This lightweight log captures decisions already made so agents do not repeatedly reopen settled scope without new evidence.

## D-001 — Start as a business, but build one small product first

**Status:** Accepted  
**Date:** 2026-08-18

Decision: plan Grovia as a commercial product business, but V1 is one sellable product. Revenue/customer evidence comes before a large ecosystem.

## D-002 — Grocery/organic is the first niche

**Status:** Accepted

Decision: optimize first for grocery, organic food, farm produce, and related local-food stores.

Rationale: the category has proven commercial demand and distinct high-frequency/quantity shopping workflows that generic ecommerce themes do not always optimize deeply.

## D-003 — Block-first; Elementor is not mandatory

**Status:** Accepted

Decision: use modern WordPress/block-theme primitives and supported WooCommerce mechanisms for core behavior. Elementor may become an optional integration later; it is not a V1 runtime requirement.

## D-004 — Theme + Core plugin boundary

**Status:** Accepted

Decision: theme owns presentation; Grovia Core owns product functionality. WooCommerce remains authoritative for commerce domain data/behavior.

## D-005 — One starter site at V1

**Status:** Accepted

Decision: ship one exceptional **Modern Grocery** starter site rather than multiple mediocre demos.

## D-006 — V1 differentiators

**Status:** Accepted

Focus on:
- mobile grocery UX;
- fast product/quantity shopping;
- delivery availability;
- Shopping List;
- Buy Again;
- setup simplicity;
- performance/low dependency burden.

## D-007 — Product comparison excluded from V1

**Status:** Accepted

Rationale: generic product comparison is less central to repeat grocery behavior than Shopping List/Buy Again.

## D-008 — Do not duplicate WooCommerce functionality unnecessarily

**Status:** Accepted

Decision: when WooCommerce provides a supported capability that meets the requirement, extend/style it rather than creating a parallel commerce engine.

## D-009 — No mandatory third-party plugin bundle

**Status:** Accepted

Decision: V1 should fundamentally require WooCommerce + Grovia Core, not a chain of premium bundled plugins.

## D-010 — Initial price is a hypothesis, not a promise

**Status:** Accepted

Working hypothesis: **US$59/year for one production site**. Validate before public launch.

## D-011 — ThemeForest is optional, not critical path

**Status:** Accepted

Reason: current marketplace onboarding can change and the business should own its customer relationship/distribution. Re-verify availability near launch.

## D-012 — Automation-first small team

**Status:** Accepted

Use ChatGPT for product/research/docs/UX synthesis, Codex for issue-scoped engineering work, and GitHub Actions for deterministic validation/build/release. Human review remains responsible for product/security/legal/release decisions.

## D-013 — No broad implementation before UX critical flow

**Status:** Accepted

Design/prototype Home -> Search/Shop -> product quantity interaction -> cart -> mobile navigation before filling the codebase with features.

## D-014 — Third-party agent skills require review

**Status:** Accepted

Do not install skills merely because they rank highly. Review source, permissions/scripts, license, maintenance/security signals, and project relevance. Keep an inventory in `docs/SKILLS.md`.

## D-015 — Research and uniqueness gate before implementation

**Status:** Accepted  
**Date:** 2026-08-18

Decision: Grovia must not ship generic design or features. Every meaningful screen, component, flow, or feature requires current competitor/customer research and a written uniqueness thesis before implementation.

Required pre-build questions:
- What customer problem are we solving?
- What do the strongest alternatives already do?
- Where do their UX, setup, performance, accessibility, maintenance, or product decisions fall short?
- What will our approach do materially better or differently?
- What measurable criterion can prove the improvement?

If there is no credible differentiation or user value, do not build it.

## D-016 — Competitors are research inputs, never design/code sources

**Status:** Accepted

Decision: analyze competitors for user problems, interaction conventions, gaps, support pain, performance/maintenance burden, pricing, and positioning. Do not copy their code, images, copywriting, distinctive layouts, demo content, branding, or proprietary assets.

## D-017 — “Grovia” is only a working codename

**Status:** Accepted

Decision: do not invest in public branding, domain purchases, marketplace identity, or trademark claims under “Grovia” until formal name research is complete.

Reason: preliminary research found existing Grovia/GroVia brands, including software/AI businesses and a software-related trademark record. The final product name must pass a dedicated naming/domain/trademark screen and be more distinctive.

## D-018 — Store API is the primary shopper-facing commerce seam

**Status:** Accepted  
**Date:** 2026-08-18

Decision: use WooCommerce Store API and supported Blocks interfaces for product/catalog/cart context rather than building a parallel Grovia commerce API.

Reason: Store API is WooCommerce's supported customer-facing API and provides product, cart and checkout resources. Grovia should spend engineering effort on its grocery interaction advantage rather than duplicating Woo internals.

## D-019 — Sensitive Grovia customer data stays off public Store API schema

**Status:** Accepted

Decision: Shopping List and other Grovia-owned private user state use authenticated WordPress REST/service boundaries with strict permission/ownership checks. Public Store API extension data may contain only safe contextual data.

## D-020 — Cart state is WooCommerce/server authoritative

**Status:** Accepted

Decision: AisleFlow may show temporary optimistic UI, but all cart changes must reconcile to WooCommerce's supported Store API/cart mechanisms. Do not manually force Cart/Checkout Block client state.

## D-021 — Buy Again is HPOS-native

**Status:** Accepted

Decision: Buy Again must use public WooCommerce order CRUD/query APIs such as `wc_get_orders()` / `wc_get_order()` and must never read/write order data directly through `wp_posts` / `wp_postmeta`.

Reason: HPOS changes physical order storage and direct post-table access can become stale or incorrect.

## D-022 — WooCommerce internal APIs are forbidden in production

**Status:** Accepted

Decision: production code may not depend on `Automattic\WooCommerce\Internal\*` or code marked `@internal`. Experimental Woo APIs need an explicit ADR, fallback and removal/upgrade plan.

## D-023 — Cart and Checkout remain WooCommerce-owned

**Status:** Accepted

Decision: Grovia styles/composes and uses supported extensibility for Cart/Checkout Blocks. It does not replace WooCommerce checkout or create custom payment processing in V1.

## D-024 — AisleFlow is an interaction hypothesis, not the product brand

**Status:** Accepted

Decision: AisleFlow is the internal name for the current UX hypothesis combining Rapid Basket, Aisle Rail, Basket Pulse and Household Rhythm. It is not a public brand commitment and remains subject to user/task validation.

## D-025 — Modern PHP is a quality target

**Status:** Accepted in principle; exact minimum still open

Decision: do not adopt PHP 7.4 as Grovia's customer minimum merely because WooCommerce permits it. Develop/recommend on PHP 8.3+ and choose the final minimum after target-host/customer compatibility research.

Rationale: Grovia is a new product without a legacy installed base; supporting EOL runtimes creates avoidable test/security/maintenance cost.

---

## Open decisions

Record these as new entries when resolved:
- final brand/product name after naming/trademark/domain research;
- exact PHP customer minimum (development/recommended target is PHP 8.3+);
- exact WordPress/WooCommerce minimum support matrix at paid beta;
- licensing/update/payment provider;
- Shopping List persistence model;
- whether delivery serviceability wraps/synchronizes WooCommerce Shipping Zones;
- variable-product quick-add choice surface;
- whether any custom Interactivity API block is justified after prototype/platform testing;
- public vs private repository strategy before commercial code is committed;
- RTL launch timing;
- demo asset providers/licenses.
