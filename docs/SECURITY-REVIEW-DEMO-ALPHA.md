# Grovia demo-to-alpha security review

Review date: 25 August 2026

Scope: Blueprint changes, the `wp grovia seed-demo` command, bundled demo
media, canonical theme parts, and the homepage composition.

## Findings

### BLOCKER / HIGH

None identified in the reviewed change set.

### MEDIUM

None identified. The seeder refuses WordPress environments other than local,
development, or staging, and the Blueprint explicitly declares its disposable
environment as local.

### LOW / NOTE

- The public Blueprint still references the last published release ZIP until
  the new `0.0.3-alpha` artifacts are uploaded. Updating those URLs before the
  release exists would make a clean public launch fail, so this is a release
  sequencing item rather than a code bypass.
- The browser/Playwright gate could not run in this workspace because the
  pinned WordPress download timed out. CI must run the clean-browser and CLI
  smoke checks before alpha release.

## Boundary checks

- Demo command registration is conditional on `WP_CLI`; it is not exposed as a
  web or REST action.
- `--reset` deletes only posts, product variations, attachments, marked product
  categories, and the marked Grovia navigation menu. Products and variations
  are deleted through WooCommerce CRUD; unrelated content is not selected.
- Demo image paths are fixed filenames resolved from the active theme. No
  request value can choose a path, URL, callback, or executable file.
- Product, category, variation, page, media, and option writes use public
  WordPress/WooCommerce APIs; no order/customer data or direct SQL is involved.
- Buy Again remains current-user scoped, status-bounded, and nonce-protected
  through the existing REST and Store API contracts.
- Theme output uses escaped values at render boundaries; the custom product
  unit block reads only bounded product metadata.

## Required release follow-up

Publish the generated ZIPs, update the root Blueprint release URLs, then run
the clean Blueprint, existing E2E, extended accessibility checks, and this
review again against the published artifacts.
