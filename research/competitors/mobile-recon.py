#!/usr/bin/env python3
"""Read-only mobile reconnaissance for grocery WooCommerce competitor demos.

The output is research evidence, not a performance ranking. The script performs
normal navigation and DOM/resource inspection only; it does not add to cart,
log in, submit customer data, bypass consent/bot protections, or place orders.
"""

from __future__ import annotations

import csv
import json
from pathlib import Path
from urllib.parse import urlparse

from playwright.sync_api import Error as PlaywrightError
from playwright.sync_api import TimeoutError as PlaywrightTimeoutError
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parent
TARGETS_FILE = ROOT / "targets.json"
RESULTS_DIR = ROOT / "results"
SCREENSHOTS_DIR = RESULTS_DIR / "screenshots"
JSON_OUT = RESULTS_DIR / "mobile-recon.json"
CSV_OUT = RESULTS_DIR / "mobile-recon.csv"

VIEWPORT = {"width": 390, "height": 844}
NAVIGATION_TIMEOUT_MS = 45_000
SETTLE_MS = 2_000


JS_METRICS = r"""
() => {
  const visible = (el) => {
    const style = getComputedStyle(el);
    const rect = el.getBoundingClientRect();
    return style.visibility !== 'hidden' &&
      style.display !== 'none' &&
      Number(style.opacity || 1) > 0 &&
      rect.width > 0 && rect.height > 0;
  };

  const controls = [...document.querySelectorAll(
    'a[href], button, input, select, textarea, [role="button"], [role="link"], [role="searchbox"]'
  )].filter(visible);

  const controlText = (el) => [
    el.innerText,
    el.value,
    el.placeholder,
    el.getAttribute('aria-label'),
    el.getAttribute('title')
  ].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();

  const controlsByText = (pattern) => controls.filter((el) => pattern.test(controlText(el)));
  const topOf = (el) => Math.round(el.getBoundingClientRect().top + window.scrollY);

  const searchCandidates = [...document.querySelectorAll(
    'input[type="search"], [role="searchbox"], input[name*="search" i], input[id*="search" i], input[placeholder*="search" i]'
  )].filter(visible);

  const cartCandidates = controlsByText(/\b(cart|basket|bag)\b/i);
  const locationCandidates = controlsByText(/\b(location|postcode|postal|zip|deliver(?:y|ing)?\s+to|ship(?:ping)?\s+to)\b/i);
  const addCandidates = controlsByText(/\badd\s+to\s+(?:cart|basket)|\badd\b/i);
  const wishlistCandidates = controlsByText(/wishlist|wish\s*list/i);
  const compareCandidates = controlsByText(/compare/i);
  const quickViewCandidates = controlsByText(/quick\s*view/i);

  const quantityCandidates = [...document.querySelectorAll(
    'input[type="number"], input[name*="qty" i], input[name*="quantity" i], .qty, [class*="quantity" i] button, button[aria-label*="quantity" i], button[aria-label^="increase" i], button[aria-label^="decrease" i]'
  )].filter(visible);

  const fixedSticky = [...document.querySelectorAll('body *')].filter((el) => {
    if (!visible(el)) return false;
    const position = getComputedStyle(el).position;
    return position === 'fixed' || position === 'sticky';
  });

  const resources = performance.getEntriesByType('resource');
  const nav = performance.getEntriesByType('navigation')[0];
  const resourceHosts = [...new Set(resources.map((entry) => {
    try { return new URL(entry.name).hostname; } catch (_) { return ''; }
  }).filter(Boolean))];

  const transferBytes = resources.reduce((total, entry) => total + (entry.transferSize || 0), 0);

  return {
    title: document.title,
    final_url: location.href,
    dom_elements: document.querySelectorAll('*').length,
    visible_controls: controls.length,
    visible_links: controls.filter((el) => el.matches('a[href], [role="link"]')).length,
    visible_buttons: controls.filter((el) => el.matches('button, [role="button"]')).length,
    visible_inputs: controls.filter((el) => el.matches('input, select, textarea, [role="searchbox"]')).length,
    search_inputs: searchCandidates.length,
    first_search_top_px: searchCandidates.length ? topOf(searchCandidates[0]) : null,
    cart_affordances: cartCandidates.length,
    first_cart_top_px: cartCandidates.length ? topOf(cartCandidates[0]) : null,
    location_affordances: locationCandidates.length,
    first_location_top_px: locationCandidates.length ? topOf(locationCandidates[0]) : null,
    add_affordances: addCandidates.length,
    quantity_affordances: quantityCandidates.length,
    wishlist_affordances: wishlistCandidates.length,
    compare_affordances: compareCandidates.length,
    quick_view_affordances: quickViewCandidates.length,
    fixed_or_sticky_visible_elements: fixedSticky.length,
    script_elements: document.scripts.length,
    stylesheet_links: document.querySelectorAll('link[rel~="stylesheet"]').length,
    resource_requests: resources.length,
    distinct_resource_hosts: resourceHosts.length,
    resource_hostnames: resourceHosts.sort(),
    observed_transfer_bytes: transferBytes,
    dom_content_loaded_ms: nav ? Math.round(nav.domContentLoadedEventEnd) : null,
    load_event_ms: nav ? Math.round(nav.loadEventEnd) : null,
    document_height_px: Math.round(document.documentElement.scrollHeight)
  };
}
"""


def resolve_target(target: dict) -> str:
    if target["kind"] == "local":
        path = (ROOT / target["path"]).resolve()
        return path.as_uri()
    return target["url"]


def hostname_for(url: str) -> str:
    parsed = urlparse(url)
    return parsed.hostname or "local-file"


def classify_http_status(http_status: int | None) -> tuple[str, bool]:
    """Return research status and whether page-level UX metrics are interpretable."""
    if http_status is None:
        # file:// and some browser navigations have no HTTP response object.
        return "ok", True
    if http_status in (401, 403):
        return "blocked", False
    if 400 <= http_status:
        return "http_error", False
    return "ok", True


def measure_target(browser, target: dict) -> dict:
    context = browser.new_context(
        viewport=VIEWPORT,
        device_scale_factor=1,
        locale="en-US",
        reduced_motion="reduce",
    )
    page = context.new_page()
    page.set_default_timeout(10_000)

    console_errors: list[str] = []
    page_errors: list[str] = []
    request_failures: list[str] = []

    page.on(
        "console",
        lambda message: console_errors.append(message.text)
        if message.type == "error" and len(console_errors) < 20
        else None,
    )
    page.on(
        "pageerror",
        lambda error: page_errors.append(str(error)) if len(page_errors) < 20 else None,
    )
    page.on(
        "requestfailed",
        lambda request: request_failures.append(
            f"{request.method} {request.url}: {request.failure}"
        )
        if len(request_failures) < 30
        else None,
    )

    requested_url = resolve_target(target)
    result = {
        "id": target["id"],
        "name": target["name"],
        "kind": target["kind"],
        "requested_url": requested_url,
        "requested_hostname": hostname_for(requested_url),
        "viewport": VIEWPORT,
        "status": "unknown",
        "http_status": None,
        "metrics_interpretable": False,
        "error": None,
    }

    try:
        response = page.goto(
            requested_url,
            wait_until="domcontentloaded",
            timeout=NAVIGATION_TIMEOUT_MS,
        )
        if response is not None:
            result["http_status"] = response.status

        page.wait_for_timeout(SETTLE_MS)
        result.update(page.evaluate(JS_METRICS))
        result["status"], result["metrics_interpretable"] = classify_http_status(
            result["http_status"]
        )

        SCREENSHOTS_DIR.mkdir(parents=True, exist_ok=True)
        page.screenshot(
            path=str(SCREENSHOTS_DIR / f"{target['id']}-viewport.png"),
            full_page=False,
        )
        page.screenshot(
            path=str(SCREENSHOTS_DIR / f"{target['id']}-full.png"),
            full_page=True,
        )
    except PlaywrightTimeoutError as error:
        result["status"] = "timeout"
        result["metrics_interpretable"] = False
        result["error"] = str(error)
    except PlaywrightError as error:
        result["status"] = "browser_error"
        result["metrics_interpretable"] = False
        result["error"] = str(error)
    except Exception as error:  # Research harness must record one target failure and continue.
        result["status"] = "error"
        result["metrics_interpretable"] = False
        result["error"] = f"{type(error).__name__}: {error}"
    finally:
        result["console_errors"] = console_errors
        result["page_errors"] = page_errors
        result["request_failures"] = request_failures
        context.close()

    return result


def write_csv(results: list[dict]) -> None:
    scalar_fields = [
        "id",
        "name",
        "kind",
        "status",
        "http_status",
        "metrics_interpretable",
        "requested_url",
        "final_url",
        "dom_elements",
        "visible_controls",
        "visible_links",
        "visible_buttons",
        "visible_inputs",
        "search_inputs",
        "first_search_top_px",
        "cart_affordances",
        "first_cart_top_px",
        "location_affordances",
        "first_location_top_px",
        "add_affordances",
        "quantity_affordances",
        "wishlist_affordances",
        "compare_affordances",
        "quick_view_affordances",
        "fixed_or_sticky_visible_elements",
        "script_elements",
        "stylesheet_links",
        "resource_requests",
        "distinct_resource_hosts",
        "observed_transfer_bytes",
        "dom_content_loaded_ms",
        "load_event_ms",
        "document_height_px",
        "error",
    ]
    with CSV_OUT.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=scalar_fields, extrasaction="ignore")
        writer.writeheader()
        writer.writerows(results)


def main() -> None:
    targets = json.loads(TARGETS_FILE.read_text(encoding="utf-8"))
    RESULTS_DIR.mkdir(parents=True, exist_ok=True)

    results: list[dict] = []
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(headless=True)
        for target in targets:
            print(f"Measuring {target['name']} ({target['id']})")
            result = measure_target(browser, target)
            print(
                f"  status={result['status']} http={result.get('http_status')} "
                f"interpretable={result.get('metrics_interpretable')} "
                f"controls={result.get('visible_controls', 'n/a')} "
                f"resources={result.get('resource_requests', 'n/a')}"
            )
            results.append(result)
        browser.close()

    JSON_OUT.write_text(json.dumps(results, indent=2, ensure_ascii=False), encoding="utf-8")
    write_csv(results)

    failed = [result for result in results if result["status"] != "ok"]
    print(f"\nCompleted {len(results)} targets; {len(failed)} target(s) recorded as non-ok.")
    for result in failed:
        print(f"  - {result['id']}: {result['status']} {result.get('http_status') or ''}")

    # Competitor failures are evidence to inspect, not a reason to bypass site protections.
    # The harness itself succeeds as long as it produced structured output.


if __name__ == "__main__":
    main()
