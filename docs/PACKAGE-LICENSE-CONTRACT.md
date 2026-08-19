# Theme/Core package license contract

Status: engineering alpha  
Date: 2026-08-19

This document defines the machine-enforced license/notice boundary for customer-facing Theme/Core ZIPs. It is not legal advice and does not replace final review of the legal copyright holder, customer terms, trademark position, taxes/refunds/support, or non-code asset rights.

## Working code license

The V1 WordPress Theme and Storefront Core code use **GPL-2.0-or-later** as the working distribution license.

WordPress currently requires directory themes to be GPL-compatible and strongly recommends GPLv2 or later. The WordPress plugin handbook likewise strongly recommends the same license family for plugins and documents `License` / `License URI` plugin headers plus inclusion of the license terms.

Official references:
- https://developer.wordpress.org/themes/releasing-your-theme/
- https://developer.wordpress.org/themes/core-concepts/main-stylesheet/
- https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
- https://developer.wordpress.org/plugins/plugin-basics/including-a-software-license/
- https://www.gnu.org/licenses/gpl2.html

## Required files in each customer package

Each of these package roots must contain all three files:

- `LICENSE.txt` — reviewed full GNU GPL version 2 text;
- `NOTICE.md` — first-party package/license boundary and unresolved legal-holder note;
- `THIRD-PARTY-NOTICES.md` — generated third-party attribution/notice surface.

Package roots:
- `packages/storefront-theme`
- `packages/storefront-core`

The deterministic builder requires these files and requires them inside the generated ZIP. They are not optional documentation files.

## Header contract

Theme `style.css` and Core `storefront-core.php` must both declare:

- `License: GNU General Public License v2 or later`
- `License URI: https://www.gnu.org/licenses/gpl-2.0.html`

The release manifest normalizes the effective identifier to `GPL-2.0-or-later`.

## Full-license integrity

`tools/release/verify_package_licenses.py` pins the SHA-256 of the reviewed GNU GPL version 2 text used in both package roots. Theme/Core copies must also be byte-identical.

This prevents accidental edits to the license text, missing files, header drift, or one package silently shipping different legal terms.

## First-party and third-party notices

`NOTICE.md` is not a substitute for `LICENSE.txt` and does not rewrite the GPL.

`THIRD-PARTY-NOTICES.md` remains generated from the evidence-backed asset/dependency inventory. A package-level GPL notice must not erase a more specific attribution or license obligation for a third-party dependency.

Likewise, non-code starter/demo assets must have explicit provenance and redistribution records. A code license declaration must not be used as an undocumented assumption about an asset whose record says otherwise.

## Paid-release boundary

The mechanical Theme/Core license-file blocker is closed only when CI proves:

1. both package headers are consistent;
2. both full GPLv2 copies match the reviewed fingerprint;
3. both first-party notices exist;
4. both generated third-party notices exist;
5. generated ZIPs contain those files;
6. deterministic package hashes still match;
7. the generated Core/Theme ZIPs still install and activate in the pinned WordPress/WooCommerce environment.

The following are still human release blockers:

- final legal copyright holder/entity name;
- customer-facing treatment of non-code starter/demo assets;
- third-party asset/dependency review where applicable;
- trademark/product name review;
- commercial provider lifecycle;
- customer terms, support/refund language and applicable legal/tax review.

Do not describe the product as legally cleared merely because the package-license CI gate is green.
