# Store Setup & Plugin Compatibility — V1 Contract

**Status:** engineering-alpha contract for Issue #18  
**Commercial objective:** make a buyer confident that the product can be installed, diagnosed and extended without pretending that every third-party plugin is automatically compatible.

## 1. Compatibility promise

The V1 product is designed to coexist with WordPress and WooCommerce extensions that use supported public platform APIs.

That is different from promising universal compatibility.

Use four compatibility levels:

### Required

Needed for the intended product to operate.

- supported WordPress version;
- supported PHP version;
- WooCommerce;
- Storefront Core for product-owned grocery functionality;
- product theme for the intended storefront presentation.

### Platform-compatible by architecture

Extensions using normal WordPress/WooCommerce hooks, blocks and public APIs should be able to coexist with the product unless they replace or conflict with the same behavior.

This is an architectural expectation, not a guarantee that every plugin/version combination has been tested.

### Validated compatibility

A plugin can be marketed as validated only after we record:

- exact plugin version;
- WordPress/WooCommerce/PHP baseline;
- tested critical scenario;
- result;
- known limitation/workaround, if any;
- date/build tested.

The compatibility matrix becomes a release artifact and should be refreshed before major releases.

### Known incompatible / limited

Only list a plugin here after reproducible evidence exists. Explain the actual conflict and supported workaround; do not use this as competitor-bashing.

## 2. Page builders

Elementor, Gutenberg add-on suites and other builders may be compatibility targets where buyers need them, but they are **not required dependencies for the V1 core storefront**.

A future Elementor compatibility layer must preserve:

- WooCommerce commerce truth;
- Storefront Core functionality;
- Cart/Checkout behavior;
- accessibility of critical shopping tasks;
- conditional asset loading;
- update safety.

Do not duplicate the entire storefront architecture inside a builder merely to add an Elementor badge to the sales page.

## 3. Setup flow states

### A. Download/package clarity

Paid delivery should distinguish:

- Theme ZIP — install under Appearance > Themes;
- Core ZIP — install under Plugins;
- documentation;
- any all-files/source archive.

The all-files archive must never be visually confused with the installable theme ZIP.

### B. Activation

Normal activation path:

1. install/activate WooCommerce;
2. install/activate product theme;
3. install/activate Storefront Core;
4. open Store Setup;
5. review environment checks before starter-store changes.

Core must never silently force-install arbitrary code.

### C. Preflight

Before a starter import is allowed, the setup screen must be able to evaluate at minimum:

- WordPress/PHP/WooCommerce versions;
- product theme/Core state;
- HTTPS readiness for production;
- REST availability;
- cron configuration;
- memory/server constraints that materially affect import;
- WooCommerce page readiness;
- selected package/import version;
- whether this starter version has already been applied.

### D. Import transaction

The final importer is not implemented in the current alpha.

When implemented it must use an explicit transaction/state model rather than one opaque request:

```text
not_started
  -> preflight_ready
  -> importing_content
  -> configuring_store
  -> verifying
  -> complete
```

Failure must record the last completed safe step and provide a retry/recovery action.

Idempotency is required: rerunning setup must not silently create duplicate pages, menus, categories, templates or demo content.

### E. Completion

Completion should show business actions, not implementation internals:

- view storefront;
- replace demo products;
- configure payments;
- configure shipping/taxes;
- review delivery-area behavior;
- set brand identity;
- review system status;
- open documentation/support.

## 4. System Status scope

The engineering-alpha screen under WooCommerce > Store Setup reports:

- WordPress version;
- WooCommerce version;
- PHP version;
- active product theme/version;
- Core version;
- HTTPS state;
- REST availability;
- WP-Cron configuration;
- WordPress memory limit;
- active plugin names/versions;
- WooCommerce template-override inventory.

The status report is support-oriented. It must not become a generic server-information dump.

## 5. Privacy rules for exported reports

Default export must exclude:

- site URL/domain unless the customer explicitly chooses to include it later;
- usernames/email addresses;
- customer/order/product records;
- passwords/database credentials/API secrets;
- cookies/nonces/session tokens;
- full filesystem paths;
- full license keys/entitlement secrets;
- arbitrary WordPress option values.

Plugin names/versions and relative WooCommerce override filenames are allowed because they materially help compatibility diagnosis.

## 6. Plugin-conflict diagnosis

When a support case may involve a plugin conflict:

1. collect the safe system report;
2. compare versions against the validated compatibility matrix;
3. reproduce on the supported baseline where possible;
4. identify which public integration surface conflicts;
5. fix our code if we violate a supported platform contract;
6. document a limitation only when the third-party conflict is reproducible and cannot safely be solved on our side.

Never respond to a buyer with "disable all plugins permanently" as the product strategy. Temporary isolation can be a diagnostic step, not the final solution.

## 7. Commercial acceptance for Issue #18

Before paid launch, demonstrate with relevant store owners/builders that they can:

- identify the correct Theme/Core packages;
- activate the required stack without FTP in the normal path;
- understand which plugins are required versus optional;
- see environment problems before import;
- recover from a deliberately failed starter import without duplicates;
- identify where payments/shipping/taxes remain WooCommerce responsibilities;
- download a support report that contains enough compatibility context without exposing secrets.

## 8. Current alpha boundary

The current implementation intentionally stops before destructive starter import.

That is deliberate because Issue #18 requires the commercial package/update provider and retry/update contract to be known before the importer is finalized.

The current slice validates the most reusable pieces first:

- setup mental model;
- environment preflight;
- plugin compatibility language;
- template-override visibility;
- privacy-safe support report.
