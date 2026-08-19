# Third-Party Code & Demo Asset Licensing Gate

**Status:** engineering-alpha release gate  
**Issue:** #56  
**Related visual provenance:** #58

## 1. Goal

A paid WordPress product must not ship an asset merely because it was easy to download or easy to add to the repository.

Before any third-party code, font, icon, photograph, illustration, video, audio file or demo-content asset is redistributed, we need a traceable answer to:

- who owns it;
- where it came from;
- which license applies;
- whether commercial redistribution inside a theme/plugin/starter product is allowed;
- whether attribution/notice text is required;
- whether we modified it;
- exactly which customer/demo surfaces ship it;
- who reviewed the evidence and when.

Third-party source of truth:

`release/third-party-assets.json`

Canonical first-party demo-image source of truth:

`release/demo-assets.json`

The split is intentional. Product-owned/generated demo images should not be mislabeled as third-party dependencies, while stock/provider assets still need the stricter third-party redistribution record.

## 2. Current engineering-alpha state

The current customer package is intentionally lean.

Theme runtime assets currently contain first-party CSS and block-theme files. V1 typography uses system-font stacks; there is no approved bundled webfont or third-party icon pack.

Core currently contains first-party PHP/JavaScript and WordPress/WooCommerce integration code; there is no intentionally approved vendored runtime library in the customer package.

The canonical Modern Grocery image distribution root is reserved at:

`packages/storefront-core/starter-assets/modern-grocery`

No real image file is approved there yet. The first six planned product assets are defined in `release/demo-assets.json`.

Therefore the third-party inventory `items` array is currently empty **on purpose**.

## 3. Third-party machine-readable inventory

Each future third-party item must contain:

- `id` — stable internal identifier;
- `name` / `version`;
- `type`;
- `author`;
- `source_url`;
- `license` — SPDX identifier where practical, otherwise precise license name;
- `evidence` — reviewed evidence location/description;
- `paths` — exact repository paths redistributed;
- `surfaces` — Theme, Core, starter demo, docs or sales demo;
- `modified`;
- `notice_required`;
- `redistribution_status` — `approved`, `review_required`, or `blocked`;
- `reviewer` and `reviewed_on` for approved entries.

A record is not approval. `review_required` and `blocked` items cannot be shipped through a scanned redistributable path.

## 4. Canonical first-party demo provenance

`release/demo-assets.json` governs the product-owned Modern Grocery imagery.

Every real image entering the canonical starter root must have an exact-path record with:

- stable asset ID;
- role and fixture/content identity;
- provenance (`first-party-generated`, `first-party-created`, or `third-party-licensed`);
- source/generation reference;
- rights evidence;
- surfaces where used;
- dimensions and byte budget;
- people/logo/readable-brand flags;
- review status;
- reviewer/date;
- alt-text guidance.

An approved first-party image must not contain an identifiable person, third-party logo or readable branded packaging under the V1 canonical policy.

A `third-party-licensed` demo image must additionally link to an **approved** item in `release/third-party-assets.json`, and that third-party record must own the exact file path.

## 5. What CI scans automatically

The third-party verifier scans the customer Theme/Core package roots.

It treats these as license-sensitive by default:

- fonts: WOFF/WOFF2/TTF/OTF/EOT;
- images/icons: SVG/PNG/JPEG/WebP/GIF/AVIF/ICO;
- media: MP3/WAV/MP4/WebM;
- PDF/ZIP redistributable payloads;
- files inside explicit vendor/third-party directories;
- `.min.js` / `.min.css` files, because copied minified libraries are easy to hide accidentally.

The dedicated demo verifier separately scans the canonical Modern Grocery distribution root and rejects:

- an image with no demo-manifest entry;
- a file whose record is only draft/review-required/blocked;
- an approved record whose file is missing;
- a file over the role-specific byte budget;
- a first-party canonical image with a blocked people/logo/branded-packaging flag;
- a third-party image without an approved cross-linked third-party record.

The package workflow runs both verifier families before building customer ZIPs.

## 6. Remote asset/hotlink rule

Customer package runtime code must not quietly depend on hotlinked images/fonts/scripts.

The third-party verifier rejects common remote asset references such as CSS `url(https://...)`, remote CSS imports and direct remote media/script source tags inside redistributable runtime files.

A remote URL is not evidence of redistribution permission, and an external CDN is not a reliability/privacy substitute for an intentional product dependency decision.

If a future cloud/CDN service is genuinely part of the product, document it as architecture/privacy/commercial infrastructure instead of bypassing this gate.

## 7. Third-party notices

Theme and Core each contain:

`THIRD-PARTY-NOTICES.md`

These files are generated logically from the third-party inventory and are checked for drift by CI.

Today they state that no third-party notices are required for the engineering-alpha package. Once an approved item sets `notice_required: true`, its source/license metadata must appear in the relevant package notice file.

Do not edit notice files independently from the inventory.

## 8. V1 font/icon decision

The V1 strategy is now explicit in `docs/VISUAL-ASSET-STRATEGY.md`:

- system-font stacks are the canonical typography path;
- no remote font dependency;
- no bundled webfont required for V1;
- product-owned UI icons use a small first-party SVG grammar;
- no icon-pack runtime dependency is required.

A third-party font/icon library may still be introduced later, but only when a demonstrated product need justifies it and the normal third-party gate is satisfied.

## 9. Demo/starter image decision

The canonical starter should use one controlled first-party image set reused by the live demo and sales screenshots wherever practical.

Do not choose stock imagery by aesthetics first and licensing second. Stock assets are an exception path, not the baseline starter dependency.

A license that allows an image to appear on a website does not automatically answer whether its original/source file can be redistributed inside a paid theme/plugin/starter product.

## 10. Human review remains required

Automation can prove that an asset has a record. It cannot give legal advice or interpret every custom stock marketplace license correctly.

Require human/legal review before paid release when:

- license text is custom or ambiguous;
- redistribution/template resale rights are unclear;
- a stock provider distinguishes end-product use from source-file redistribution;
- trademark/personality/property-release questions exist;
- attribution obligations are unusual;
- proprietary demo assets are being bundled.

Generated/first-party assets also require product review for trademarks, misleading claims, similarity and visual suitability.

## 11. Paid/public release blockers

Engineering CI passing does **not** make the paid package legally or visually complete.

Still required before first paid/public release:

- final Theme/Core package `LICENSE` files;
- first canonical Modern Grocery image batch created and reviewed;
- complete approved inventory for every redistributed third-party item;
- generated notices included where required;
- compatibility/support/commercial-provider gates from the release plan;
- qualified review for any ambiguous licensing terms.

## 12. Adding a new third-party asset

```text
Need third-party asset/dependency
  -> identify exact source
  -> review license + commercial redistribution rights
  -> add file under intentional product/demo path
  -> add third-party inventory record
  -> add demo provenance record too if it is a canonical starter image
  -> update generated package notice if required
  -> run package CI
  -> review
```

## 13. Adding a new first-party demo image

```text
Need canonical starter image
  -> reserve stable asset ID in release/demo-assets.json
  -> create/generate candidate
  -> review trademarks/people/packaging/claims
  -> optimize derivative
  -> place file in canonical starter root
  -> add approved provenance record
  -> run demo + third-party + package CI
  -> inspect real storefront crop on mobile/desktop
```

Do not merge an image first and "work out the rights later."
