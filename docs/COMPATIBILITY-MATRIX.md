# V1 compatibility matrix

Status: engineering gate

This document is deliberately narrower than “works with WordPress/WooCommerce”. A customer-facing support claim exists only for an exact combination that is represented in `release/compatibility-matrix.json` and has a green compatibility workflow.

## Current exact matrix

| WordPress | WooCommerce | PHP | Tier |
|---|---:|---:|---|
| 7.0.3 | 11.0.1 | 8.3 | Reference |
| 7.0.3 | 11.0.1 | 8.4 | Validated |
| 6.9.6 | 11.0.1 | 8.3 | Validated |
| 6.9.6 | 11.0.1 | 8.4 | Validated |

### Reference

The reference row receives the full engineering regression suite plus the generated-package compatibility smoke. It is the environment used for ordinary engineering-alpha development.

### Validated

A validated row proves, on that exact combination:

- WordPress boots at the expected version;
- WooCommerce boots at the expected version;
- PHP major/minor matches the row;
- the generated customer Core ZIP installs and activates;
- the generated customer Theme ZIP installs and activates;
- WooCommerce remains active;
- the active Theme is the shipped Theme;
- HPOS is enabled for the clean new-store environment;
- the Woo Store API products route is registered and responds successfully;
- the Core serviceability REST route is registered and responds normally;
- the storefront returns HTTP 200.

This is a core platform compatibility statement. It does **not** certify every third-party plugin, payment gateway, shipping extension, page builder or hosting stack.

## CI lanes and release meaning

### Engineering alpha smoke — reference lane

`Engineering alpha smoke` uses the exact platform pins in `.wp-env.json`. It is the functional regression lane for the reference row. CI prints the actual WordPress, WooCommerce and PHP versions before browser E2E begins so the run log records the platform that produced the result.

Changing the reference version is a repository change and requires the full regression to pass again. The reference lane must never use a moving nightly or implicit latest version.

### Platform compatibility matrix — exact supported-row lane

`Platform compatibility matrix` derives its rows from `release/compatibility-matrix.json`. Each row builds the generated customer Theme/Core ZIPs and proves the exact runtime combination independently. Passing a neighboring version does not widen the claim.

### Future latest/nightly probes — non-release-gating

A moving WordPress latest/nightly or pre-release lane may be added later as an early-warning engineering probe. Such a probe is **not** a customer compatibility claim and is **not** a substitute for the pinned reference lane. A version moves into the support table only after an explicit matrix row and the required regression evidence pass.

## Deliberate non-claims

### WordPress 7.1

WordPress 7.1 was scheduled for August 19, 2026, but this matrix was prepared before a final stable 7.1 release was verified from WordPress.org. Do not advertise 7.1 support until the stable release is confirmed and the exact matrix process passes for it.

### PHP 8.5

WordPress support alone is insufficient. Current WooCommerce server guidance recommends PHP 8.3 or greater and documents testing through PHP 8.4. We therefore do not advertise PHP 8.5 support until a separate product/WooCommerce validation row exists.

### WooCommerce 10.x and earlier

V1 targets WooCommerce 11.0.1. We do not spend CI/support budget maintaining an older WooCommerce major unless real buyer evidence shows that it materially affects adoption.

### Elementor and other extensions

No universal compatibility claim. The architecture is designed around WordPress/WooCommerce public APIs, but each extension moves from architecture-compatible to validated only after specific evidence.

## Patch update rule

A newer patch number is not automatically added to the support table. When WordPress or WooCommerce publishes a security/bug-fix release:

1. verify the release from an official source;
2. update the reference pins and/or candidate rows;
3. run the full reference regression plus all compatibility rows;
4. investigate upstream behavior changes;
5. only then update customer-facing compatibility copy.

## HPOS boundary

The matrix verifies an HPOS-enabled clean-store environment. Our product continues to use supported WooCommerce CRUD/public APIs and must not depend on direct order-table or legacy `wp_posts` storage assumptions.

## Evidence sources reviewed 2026-08-19

- WordPress 7.0.3 security release announcement — WordPress.org.
- WordPress 6.9.6 security branch release — WordPress.org.
- WooCommerce 11.0.1 plugin release metadata — WordPress.org plugin directory.
- WooCommerce server recommendations — PHP 8.3+ recommended and tested through PHP 8.4.

Machine-readable source of truth: `release/compatibility-matrix.json`.
