# Engineering Alpha Packages

These packages are the first production-oriented WordPress skeletons created after the research gate moved to **PROCEED TO ENGINEERING ALPHA WITH CONSTRAINTS**.

## Temporary naming

The final public product name is not cleared yet. `storefront-theme` and `storefront-core` are internal package names only. Do not publish marketplace/customer releases under these names.

## Boundaries

### `storefront-theme`

Presentation only:
- block theme templates/parts/patterns;
- `theme.json` design system;
- WooCommerce presentation integration;
- accessible storefront markup/styles.

### `storefront-core`

Product functionality that must survive a theme switch:
- delivery availability;
- Saved for later;
- Buy Again;
- setup/onboarding orchestration;
- carefully bounded Grovia/BhaivaTech-specific endpoints when required.

WooCommerce remains authoritative for products, price/stock, cart, customers, orders, checkout, payment, shipping and tax behavior.

## Alpha rule

Every meaningful feature remains issue-scoped and must follow `AGENTS.md`, `docs/TRD.md`, `docs/ARCHITECTURE.md`, `docs/SECURITY.md`, and the relevant research evidence.
