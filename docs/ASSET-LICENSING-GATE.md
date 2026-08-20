# Third-Party Code & Demo Asset Licensing Gate

**Status:** engineering-alpha release gate  
**Issue:** #56

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

The source of truth is:

`release/third-party-assets.json`

## 2. Current engineering-alpha state

The current customer package is intentionally lean.

Theme runtime assets currently contain first-party CSS and block-theme files; there is no approved bundled webfont, stock-photo set, illustration pack or third-party icon pack.

Core currently contains first-party PHP/JavaScript and WordPress/WooCommerce integration code; there is no intentionally approved vendored runtime library in the customer package.

Therefore the inventory `items` array is currently empty **on purpose**.

This is not a statement that the final paid product will never use third-party assets. It means none has yet passed the redistribution gate.

## 3. Machine-readable inventory

Each future item must contain:

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

## 4. What CI scans automatically

The verifier scans the customer Theme/Core package roots.

It treats these as license-sensitive by default:

- fonts: WOFF/WOFF2/TTF/OTF/EOT;
- images/icons: SVG/PNG/JPEG/WebP/GIF/AVIF/ICO;
- media: MP3/WAV/MP4/WebM;
- PDF/ZIP redistributable payloads;
- files inside explicit vendor/third-party directories;
- `.min.js` / `.min.css` files, because copied minified libraries are easy to hide accidentally.

Every such file needs an approved exact-path manifest record.

This is deliberately conservative. If a first-party asset uses one of these formats, record it explicitly with first-party evidence or adjust the policy through review rather than creating an undocumented exception.

## 5. Remote asset/hotlink rule

Customer package runtime code must not quietly depend on hotlinked images/fonts/scripts.

The verifier rejects common remote asset references such as CSS `url(https://...)`, remote CSS imports and direct remote media/script source tags inside redistributable runtime files.

A remote URL is not evidence of redistribution permission, and an external CDN is not a reliability/privacy substitute for an intentional product dependency decision.

If a future cloud/CDN service is genuinely part of the product, document it as architecture/privacy/commercial infrastructure instead of bypassing this gate.

## 6. Third-party notices

Theme and Core each contain:

`THIRD-PARTY-NOTICES.md`

These files are generated logically from the machine-readable inventory and are checked for drift by CI.

Today they state that no third-party notices are required for the engineering-alpha package. Once an approved item sets `notice_required: true`, its source/license metadata must appear in the relevant package notice file.

Do not edit notice files independently from the inventory.

## 7. Demo/starter asset rule

The final Modern Grocery starter will need credible product imagery and possibly other visual assets.

Do **not** choose those assets by aesthetics first and licensing second.

Before bundling/importing a demo asset:

1. choose a source with terms suitable for commercial template/product redistribution;
2. record exact source/license evidence;
3. determine whether embedding the original file, a modified derivative, or only a preview is permitted;
4. record required attribution;
5. add the dedicated demo distribution root to the inventory policy;
6. add each redistributable asset path to an approved manifest entry;
7. run the release gate.

A license that allows use in a website does not automatically mean the original asset can be redistributed inside a paid theme/starter package.

## 8. First-party icons/fonts strategy

Until evidence says otherwise, prefer:

- system font stacks or properly licensed self-hosted open fonts;
- first-party simple SVG/interface icons where practical;
- WooCommerce/WordPress-provided UI primitives where supported;
- original demo graphics/assets whose rights are documented.

Do not add a large icon/font/slider dependency merely to match competitor feature lists.

## 9. Human review remains required

Automation can prove that an asset has a record. It cannot give legal advice or interpret every custom stock marketplace license correctly.

Require human/legal review before paid release when:

- license text is custom or ambiguous;
- redistribution/template resale rights are unclear;
- a stock provider distinguishes end-product use from source-file redistribution;
- trademark/personality/property-release questions exist;
- attribution obligations are unusual;
- proprietary demo assets are being bundled.

## 10. Paid/public release blockers

Engineering CI passing does **not** make the paid package legally complete.

Still required before first paid/public release:

- final Theme/Core package `LICENSE` files;
- final demo image/illustration provider decision;
- final font/icon strategy;
- complete approved inventory for every redistributed third-party item;
- generated notices included where required;
- compatibility/support/commercial-provider gates from the release plan;
- qualified review for any ambiguous licensing terms.

## 11. Adding a new asset

Use this sequence:

```text
Need asset/dependency
  -> identify exact source
  -> review license + commercial redistribution rights
  -> add file under intentional product/demo path
  -> add inventory record
  -> update generated package notice if required
  -> run asset inventory CI
  -> review
```

Do not merge the file first and "work out the license later."
