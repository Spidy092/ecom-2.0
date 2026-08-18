---
name: grovia-wordpress-engineering
description: Implements Grovia using maintainable WordPress and WooCommerce extension patterns. Use for architecture, PHP, blocks, Store API, theme/plugin boundaries, performance, compatibility, data storage, and build decisions.
---

# Grovia WordPress Engineering Skill

## Read first

- `docs/TRD.md`
- `docs/ARCHITECTURE.md`
- `docs/SECURITY.md`
- `docs/DECISIONS.md`
- relevant product/research notes.

## Platform rule

Grovia extends WordPress and WooCommerce; it does not fork them.

Prefer supported public APIs, hooks, blocks, Store API extension mechanisms, settings/data abstractions, and WooCommerce domain behavior.

## Ownership

Theme:
- presentation;
- theme.json/design tokens;
- templates/parts/patterns;
- storefront styling/composition.

Grovia Core:
- Shopping List;
- Buy Again orchestration;
- delivery availability;
- setup orchestration;
- Grovia-specific endpoint/application behavior.

WooCommerce:
- products/variations;
- price/stock truth;
- cart/order/customer lifecycle;
- checkout/payment/shipping/tax contracts.

## Engineering constraints

- No mandatory Elementor or broad addon bundle in V1.
- Do not override WooCommerce templates unless supported styling/hooks/blocks cannot meet the requirement.
- Avoid custom tables until an ADR proves the need.
- Keep queries bounded; avoid N+1 product/order lookups.
- Load assets conditionally.
- Prefer progressive enhancement for storefront JS.
- Build accessible loading/error/empty/success states.
- Never use client data as authorization truth.
- Document public hooks/APIs once introduced.

## Dependency gate

A new runtime dependency must document:
- problem solved;
- why core/platform cannot reasonably solve it;
- license;
- maintainer/update health;
- frontend/runtime cost;
- security surface;
- removal/migration path.

## Implementation sequence

1. Confirm product/research gate.
2. Identify the platform seam.
3. Define observable behavior and tests.
4. Implement the smallest change.
5. Run lint/static/tests.
6. Review security/accessibility/performance.
7. Update docs/ADR when architecture changes.

## Never

- edit WordPress/WooCommerce core;
- ship secrets;
- trust a nonce without authorization;
- load all products/orders for convenience;
- copy competitor/theme code;
- invent compatibility claims that were not tested.
