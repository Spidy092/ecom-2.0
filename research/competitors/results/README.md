# Mobile competitor reconnaissance results

This directory is the output location for the read-only Playwright research harness in `research/competitors/mobile-recon.py`.

## What the harness measures

It records lab observations from one fresh mobile-sized Chromium context per target:

- DOM and visible control counts;
- search/cart/location/add/quantity/wishlist/compare/quick-view affordance signals;
- script/stylesheet/resource counts;
- distinct resource hostnames;
- browser-reported transfer-size signals where available;
- navigation timing observations;
- screenshots;
- console/page/request failures.

## What the numbers do **not** prove

A smaller DOM, fewer requests, or faster one-off navigation timing does not automatically mean a better ecommerce experience. Results are affected by hosting, cache state, geolocation, consent layers, third-party services, network conditions, browser behavior, and demo configuration.

The data must be interpreted alongside:

- the human critical-flow benchmark in Issue #2;
- actual shopper/builder validation in Issue #5;
- accessibility evidence;
- product-maintenance/security research.

## Ethical boundary

The harness performs normal page navigation and read-only browser inspection. It does not:

- submit orders;
- log in;
- submit personal data;
- bypass consent or bot protections;
- brute-force endpoints;
- run repeated load/performance attacks.

A blocked/403/timeout target is recorded as such rather than bypassed.

## Generated files

A successful research run produces:

```text
results/
├── mobile-recon.json
├── mobile-recon.csv
└── screenshots/
    ├── <target>-viewport.png
    └── <target>-full.png
```

Generated results are uploaded as a GitHub Actions artifact. They should only be committed back to the repository after human review confirms they are useful, current, and do not contain unexpected sensitive/session content.
