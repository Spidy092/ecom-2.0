# V1 compatibility matrix

Status: engineering gate

This document defines the customer-facing support boundary for the current V1. It is intentionally narrower than “works on WordPress/WooCommerce”. A row becomes supportable only after the exact WordPress, WooCommerce and PHP combination passes CI using the generated customer Theme/Core ZIPs.

## Proposed exact V1 matrix

| WordPress | WooCommerce | PHP | Tier |
|---|---:|---:|---|
| 7.1 | 11.0.1 | 8.3 | Reference |
| 7.1 | 11.0.1 | 8.4 | Validated |
| 7.0.4 | 11.0.1 | 8.3 | Validated |
| 7.0.4 | 11.0.1 | 8.4 | Validated |

The **reference** row receives the full engineering regression suite plus the generated-package compatibility smoke. A **validated** row receives generated-package install/activation and the runtime checks below.

Do not turn this table into a customer-facing compatibility claim until the corresponding workflow rows are green on the release commit.

## Why the V1 support window is narrow

WordPress 7.1 became the latest stable release on August 19, 2026. WordPress 7.0.4 is the previous maintained 7.0 security line. V1 therefore spends support/CI budget on those two current lines instead of automatically promising every WordPress version allowed by the package's minimum metadata.

The Theme/Core headers currently permit WordPress 6.9 as an installation floor. That does **not** mean 6.9.x is an advertised V1 support row. A version can be architecture-compatible/installable while still being outside the paid support matrix until exact evidence is maintained for it.

## What each validated row proves

CI must prove the exact combination rather than infer from a neighboring version:

- exact WordPress version at runtime;
- exact WooCommerce version at runtime;
- exact PHP major/minor at runtime;
- WooCommerce is active;
- Storefront Core is installed from the generated customer ZIP and active;
- Storefront Theme is installed from the generated customer ZIP and active;
- WooCommerce HPOS is enabled in the clean new-store environment;
- Woo Store API products route exists and responds successfully;
- the Core serviceability REST route exists and responds without a server error;
- the storefront returns HTTP 200.

The matrix does **not** prove compatibility with every third-party extension, payment gateway, shipping extension, cache plugin, page builder or hosting stack. Those require separate evidence.

## CI lanes and release meaning

### Engineering alpha smoke — reference lane

`Engineering alpha smoke` uses the exact platform pins in `.wp-env.json`. It is the functional regression lane for the reference row. CI prints the actual WordPress, WooCommerce and PHP versions before browser E2E begins so the run log records the platform that produced the result.

Changing the reference version is a repository change and requires the full regression to pass again. The reference lane must never use a moving nightly or implicit latest version.

### Platform compatibility matrix — exact supported-row lane

`Platform compatibility matrix` derives its four rows from `release/compatibility-matrix.json`. Each row builds the generated customer Theme/Core ZIPs and proves the exact runtime combination independently. Passing a neighboring version does not widen the claim.

## Deliberate non-claims

### WordPress 6.9.6 and earlier

The package metadata still permits WordPress 6.9, but V1 customer support is deliberately limited to the latest two WordPress release lines. Add 6.9.6 only if buyer/install-base evidence justifies the extra testing and support burden.

### PHP 8.5

WordPress supports newer PHP versions, but WooCommerce's current server recommendations state PHP 8.3 or greater and document testing through PHP 8.4. We therefore do not advertise PHP 8.5 support until a separate product/WooCommerce validation row exists.

### WooCommerce 10.x

V1 targets the current WooCommerce 11.0 line. We do not spend CI/support budget maintaining an older WooCommerce major unless buyer evidence shows that it materially affects adoption.

### Elementor and other extensions

No universal compatibility claim. The architecture is designed around WordPress/WooCommerce public APIs, but an extension moves from architecture-compatible to validated only after specific tests.

## Patch/minor update rule

A newer WordPress or WooCommerce release is not automatically added to the support table. When upstream publishes a new security/bug-fix/minor release:

1. verify the release from an official upstream release/archive source;
2. update the reference pin or add a candidate row;
3. run the exact compatibility matrix;
4. run the full engineering suite on the reference row;
5. investigate upstream behavior changes;
6. only then update customer-facing compatibility copy.

A plugin-directory `Tested up to` field is useful compatibility metadata, but it is not itself proof that the referenced WordPress release exists or that our exact package passed on it.

## HPOS boundary

WooCommerce HPOS is the expected new-store order-storage path. The matrix verifies that the clean compatibility environment actually has HPOS enabled. Our code must continue to use supported WooCommerce CRUD/public APIs and must not depend on direct order-table or legacy `wp_posts` storage assumptions.

## Evidence sources reviewed 2026-08-21

- WordPress release archive: WordPress 7.1 released August 19, 2026; 7.0.4 is the current 7.0 line.
- Make WordPress Core 7.1 release tracker.
- WooCommerce 11.0.1 WordPress.org plugin changelog (August 10, 2026), including a WordPress 7.1 compatibility fix.
- WooCommerce server recommendations for PHP 8.3+ and testing through PHP 8.4.

Machine-readable source of truth: `release/compatibility-matrix.json`.
