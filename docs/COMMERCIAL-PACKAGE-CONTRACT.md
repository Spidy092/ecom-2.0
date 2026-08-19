# Commercial Package Contract

**Status:** engineering-alpha release contract  
**Purpose:** define exactly what a paying customer receives and what CI must prove before any package is published.

## 1. Customer-facing download model

The direct-sales product ships two installable WordPress archives:

1. `storefront-theme-<version>.zip` — install from **Appearance > Themes > Add New > Upload Theme**.
2. `storefront-core-<version>.zip` — install from **Plugins > Add New > Upload Plugin**.

`storefront-theme` and `storefront-core` are internal alpha slugs until the final brand/package slugs are approved. The commercial storefront must display customer-facing product names rather than these internal labels.

The customer must never need to unzip an installable archive before WordPress can accept it.

Do not create an ambiguous `all-files.zip` for V1. If a future convenience bundle is offered, it must be clearly labelled **not installable in WordPress** and must contain the two installable archives unchanged.

## 2. Required archive roots

Each installable archive contains exactly one top-level WordPress package directory:

```text
storefront-theme-<version>.zip
└── storefront-theme/
    ├── style.css
    ├── theme.json
    ├── functions.php
    ├── LICENSE.txt
    ├── NOTICE.md
    ├── THIRD-PARTY-NOTICES.md
    ├── templates/
    ├── parts/
    └── assets/

storefront-core-<version>.zip
└── storefront-core/
    ├── storefront-core.php
    ├── LICENSE.txt
    ├── NOTICE.md
    ├── THIRD-PARTY-NOTICES.md
    ├── blocks/
    ├── includes/
    └── assets/
```

The archive must not wrap the package in repository/build folders such as `packages/`, `dist/`, a Git commit name, or a developer-machine directory.

## 3. Theme package requirements

Before CI can emit the theme ZIP it must prove:

- `style.css` exists at the theme root;
- the `Theme Name`, `Version`, `Requires at least`, `Requires PHP`, `License`, `License URI` and `Text Domain` headers exist;
- the Theme code license resolves to GPL-2.0-or-later under the package-license contract;
- `theme.json` parses as JSON;
- the expected block-theme templates/parts required by the release exist;
- no test suite, cache, repository metadata, credentials or development-only artifacts are shipped;
- reviewed `LICENSE.txt`, `NOTICE.md` and generated `THIRD-PARTY-NOTICES.md` are present in source and inside the built ZIP.

The complete package-license policy is in `docs/PACKAGE-LICENSE-CONTRACT.md`.

## 4. Core plugin package requirements

Before CI can emit the Core ZIP it must prove:

- `storefront-core.php` exists at the plugin root;
- `Plugin Name`, `Version`, `Requires at least`, `Requires PHP`, `Requires Plugins`, `License` and `License URI` headers exist;
- the Core code license resolves to GPL-2.0-or-later under the package-license contract;
- `Requires Plugins` includes `woocommerce` while WooCommerce remains the mandatory commerce dependency;
- the PHP version constant matches the plugin header version;
- only runtime-required source/assets are shipped;
- `tests/` is not shipped;
- seller API keys, webhook secrets, build credentials and real customer/license data are never shipped;
- reviewed `LICENSE.txt`, `NOTICE.md` and generated `THIRD-PARTY-NOTICES.md` are present in source and inside the built ZIP.

## 5. Explicit exclusions from customer ZIPs

At minimum CI rejects or omits:

```text
.git/
.github/
.env
.env.*
*.pem
*.key
*.p12
*.pfx
__pycache__/
.pytest_cache/
node_modules/
tests/
coverage/
artifacts/
*.log
.DS_Store
Thumbs.db
```

Additional source/build directories should be excluded once introduced unless they are proven runtime dependencies.

## 6. Version relationship

Theme and Core are one commercial customer purchase but remain independently versioned technical artifacts.

Rules:

- do not force identical Theme/Core version numbers;
- every commercial release manifest records both artifact versions;
- setup/system-status UI reports both versions;
- release notes state when a Theme version requires a minimum Core version or vice versa;
- compatibility requirements must be machine-readable before we ship versions that cannot operate independently;
- a customer entitlement may authorize both artifacts even when their update cadence differs.

## 7. Release manifest and checksums

Every automated package build emits a machine-readable `release-manifest.json` containing at least:

- schema version;
- build timestamp in UTC;
- source commit when provided by CI;
- Theme artifact filename/version/SHA-256/size;
- Core artifact filename/version/SHA-256/size;
- package-root slug for each artifact;
- normalized code license (`GPL-2.0-or-later`);
- canonical license URI;
- `LICENSE.txt`, `NOTICE.md` and `THIRD-PARTY-NOTICES.md` member names;
- release status (`engineering-alpha` until explicitly promoted).

The build also emits `SHA256SUMS` so support/release automation can verify package integrity without opening the archives.

Neither file may contain secrets, license keys, customer data, signed package URLs or provider credentials.

## 8. Deterministic-build principle

Customer artifacts are generated by CI from a known commit/tag. Never edit or repack a customer ZIP manually after CI validation.

The builder should normalize ZIP entry timestamps/permissions/order so rebuilding the same source tree with the same inputs produces stable artifact bytes where practical.

If a later compiled asset pipeline introduces nondeterministic output, the release must identify and remove that source of variance rather than silently accepting arbitrary package changes.

## 9. Paid-release gate

Engineering-alpha package generation is allowed before branding/provider/legal decisions are complete, but the mechanical Theme/Core license files are no longer optional.

A **paid/public** artifact remains blocked until all are true:

- final package/product names are approved;
- package-license CI proves Theme/Core headers, full GPLv2 copies and notice surfaces are intact;
- final legal copyright holder/entity is confirmed;
- customer-facing license treatment of non-code starter/demo assets is confirmed;
- third-party asset/dependency inventory is complete;
- demo assets have redistribution rights recorded;
- supported WordPress/WooCommerce/PHP matrix is recorded;
- install/activation smoke tests pass from the built ZIPs;
- Theme + Core compatibility relationship is verified;
- #14 records the commercial entitlement/update provider decision;
- checkout/download/update path is proven through the selected provider;
- release notes/support/refund/update terms are customer-ready.

A green package-license verifier is evidence of packaging consistency, not a claim of legal clearance.

## 10. Buyer UX contract

The sales/customer portal should show separate, unambiguous actions:

```text
Download Theme
Install in Appearance > Themes

Download Core
Install in Plugins

Setup Guide
Open documentation
```

The first-run Store Setup screen should detect whether the correct Theme/Core are active and explain the next action rather than assuming the buyer understands WordPress package architecture.

## 11. WordPress/WooCommerce compatibility principle

WordPress plugin metadata supports version/dependency requirements, including `Requires Plugins`; our Core uses that public contract for WooCommerce rather than silently bundling WooCommerce. WooCommerce compatibility claims are made only after the declared versions/components and relevant extension combinations are actually tested.

## 12. Commercial value

The package contract supports the business goal: a buyer receives clean official builds, clear installation instructions, maintained updates, compatibility evidence and supportable diagnostics. Packaging is therefore part of the product experience, not a final-minute release chore.
