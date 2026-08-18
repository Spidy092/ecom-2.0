# Grovia Licensing & Commercial Distribution Strategy

**Status:** Planning document — not legal advice  
**Date:** 2026-08-18

## 1. Recommended product licensing direction

Use a **GPL-compatible WordPress distribution model**, with GPLv2-or-later as the default working direction for Grovia-owned WordPress theme/plugin code unless qualified legal review identifies a reason to structure particular non-code assets differently for a specific marketplace.

Why:
- WordPress.org theme requirements require GPL-compatible licensing and strongly recommend GPLv2 or later;
- WordPress explicitly recognizes commercially supported GPL themes;
- a paid WordPress business can charge for maintained downloads, automatic updates, support, starter experiences, services and brand value without attempting to restrict the freedoms granted by the GPL.

Official references:
- https://make.wordpress.org/themes/handbook/review/required/
- https://developer.wordpress.org/themes/releasing-your-theme/
- https://wordpress.org/themes/commercial/

## 2. Theme vs plugin functionality

WordPress theme guidance says themes should not carry non-design functionality that disappears when users switch themes. This supports Grovia's architecture decision:
- theme -> presentation;
- Grovia Core -> persistent product functionality.

Reference:
- https://developer.wordpress.org/themes/releasing-your-theme/

## 3. Third-party assets

Every redistributed dependency/asset must have documented rights and compatibility.

Inventory at minimum:
- PHP/JS libraries;
- fonts;
- icons;
- images/photography;
- illustrations;
- demo content;
- agent skills vendored into the repository;
- build tooling included in release archives (ideally none unless required at runtime).

For each record:
- name/version/source;
- author/copyright;
- license;
- whether redistributed to customers;
- required notices/attribution;
- source URL;
- modification status.

WordPress.org requires resources in directory theme packages to be GPL/GPL-compatible and documented with source/license information. Even if the commercial product is distributed elsewhere, following a strict asset inventory reduces legal and marketplace risk.

## 4. ThemeForest / Envato

Current Envato documentation supports GPL-compliant WordPress distribution through either its default split-license model or an optional 100% GPL model. If Grovia later uses Envato, choose deliberately based on business/legal review rather than assuming the marketplace license is identical to our direct-sales packaging.

Reference:
- https://help.author.envato.com/hc/en-us/articles/360000534626-Theme-Plugin-Licensing-Options

Important: ThemeForest is not a V1 critical path. Marketplace policy and author onboarding must be re-verified before submission.

## 5. Woo Marketplace

Woo Marketplace accepts themes/extensions after partner/product review. Current guidance prioritizes block themes and requires modern compatibility expectations such as HPOS for new extension submissions.

Reference:
- https://developer.woocommerce.com/docs/woo-marketplace/submitting-your-product/

This reinforces our block-first and WooCommerce-public-API direction, but Marketplace acceptance is not guaranteed and should not dictate V1 architecture unless the product itself benefits.

## 6. What customers pay for

Do not build the business model around pretending GPL code can never be redistributed.

Commercial value can include:
- trusted official builds;
- automatic updates;
- compatibility maintenance;
- security fixes;
- support;
- premium starter sites/content whose licenses permit distribution;
- documentation/training;
- future cloud services;
- brand/reputation;
- agency workflow/services.

## 7. Trademark and brand

Software licensing does not transfer ownership of our brand/trademark automatically. The final product name/logo must be separately researched and protected where commercially justified.

`Grovia` is currently only a codename; see `docs/BRAND-RESEARCH.md`.

## 8. Repository visibility and commercial code

This repository is currently being used to bootstrap planning. Before proprietary business assets or unreleased commercial code are committed, decide:
- which repositories are public/open-source;
- which commercial assets remain private;
- whether the public theme/core source is distributed from a public repository;
- how official releases/updates are authenticated;
- contributor terms for external contributions.

Do not accidentally publish secrets, customer data, licensed demo assets, unreleased marketplace packages, or private business credentials.

## 9. License files before first customer release

Before paid beta/public release create and review:
- root/project license policy;
- theme `LICENSE` / headers as required;
- plugin `LICENSE` / headers as required;
- `THIRD-PARTY-NOTICES.md`;
- demo asset manifest;
- font/icon/image attribution/source records;
- customer-facing license/updates/support explanation.

## 10. Legal review trigger

Get qualified legal/accounting advice before final decisions involving:
- trademark filing/clearance;
- company/entity structure;
- GST/VAT/sales-tax obligations;
- refund/consumer-law terms;
- split-license/proprietary asset strategy;
- contributor IP assignments;
- privacy/telemetry/customer data practices.
