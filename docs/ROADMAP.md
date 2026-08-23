# Grovia Lean Roadmap

This roadmap is deliberately ordered to prevent premature feature development.

## Phase 0 — Foundation (complete)

Deliverables:
- V1 PRD;
- TRD;
- architecture boundaries;
- security baseline;
- lean business plan;
- Codex/agent guardrails;
- skill policy;
- decision log;
- initial release strategy.

Exit condition: founder review accepts the V1 problem, target customer, differentiators, exclusions, and technical boundaries. Met by the accepted PRD, TRD, research notes and decision log.

## Phase 1 — UX prototype (complete)

Design only the critical flow first:
1. Home;
2. Search/Shop;
3. Product card/quantity interaction;
4. Cart feedback/cart;
5. mobile navigation.

Then extend to:
- product detail;
- delivery checker;
- Shopping List;
- Buy Again;
- account;
- setup wizard.

Exit condition: clickable/prototype flow can be tested with representative users before theme architecture is filled with features. Met by AisleFlow V0 and its browser mission/accessibility checks.

## Phase 2 — Engineering skeleton (complete)

Build:
- monorepo/package layout;
- block-theme shell;
- Grovia Core bootstrap;
- Composer/npm tooling only as justified;
- lint/static checks;
- base test harness;
- deterministic build scripts;
- minimal CI.

Exit condition: empty product packages build/test consistently. Met by the package layout, PHP CI smoke check and block-theme detection.

## Phase 3 — Core shopping vertical slice (complete for engineering alpha)

Build one end-to-end behavior:

Search -> product card -> add -> quantity update -> cart state.

Current delivery: the block-theme search/product composition, Shipping-Zone delivery checker, custom-table Shopping List, Buy Again, setup wizard, and Store API cart feedback. The flow is validated in the disposable Playground with authenticated browser smoke coverage.

This proves the hardest common client/server UX loop before many features are added.

Exit condition: tested desktop/mobile flow with accessible states and acceptable performance. Engineering-alpha desktop/browser and REST checks pass; broader device/performance evidence remains a Phase 6 activity.

## Phase 4 — Grocery V1 capabilities (alpha slice delivered; hardening remains)

Delivered sequentially:
- delivery availability;
- Shopping List;
- Buy Again;
- category/filter polish;
- product detail integration;
- cart/checkout styling;
- account experience.

Remaining hardening:
- product-card quantity controls and mobile quantity verification;
- variable-product quick-add decisions;
- deeper cart/checkout styling and compatibility matrix coverage.

Every feature must satisfy `docs/TRD.md` definition of done.

## Phase 5 — Store-owner setup (alpha delivered; importer hardening remains)

Delivered:
- capability-protected setup wizard;
- Modern Grocery starter-site theme composition;

Remaining:
- safe importer/configuration path;
- system-status/help basics;
- clean uninstall/upgrade behavior where applicable.

Exit condition: fresh-install acceptance journey passes without developer intervention.

## Phase 6 — Private alpha

Use a very small tester group.

Measure:
- setup failures;
- confusing UX;
- compatibility failures;
- cart/search bugs;
- security/access-control issues;
- performance regressions;
- documentation gaps.

Do not optimize for public marketing traffic during unstable alpha.

## Phase 7 — Paid beta / first customers

Goal: first unrelated paying customer, then 10.

Requirements:
- payment/license/update delivery selected;
- support channel;
- docs;
- demo;
- terms/privacy/refund/support policies;
- release/update path;
- rollback/incident process.

## Phase 8 — V1 public launch

Launch one product, one starter site, one simple paid plan.

Do not announce unbuilt roadmap features as if included.

## Phase 9 — Evidence-driven iteration

After 10/50/100-customer milestones, rank expansion by customer evidence.

Possible later candidates:
- Organic starter site;
- Farm starter site;
- bakery/food verticals;
- agency licensing;
- Lite acquisition product;
- broader builder integrations;
- advanced delivery capabilities.

These are candidates, not commitments.
