#!/usr/bin/env python3
"""Static/browser smoke checks for the low-fidelity sales-message experiment.

This does not validate conversion or comprehension. It protects the research
constraints so the prototype cannot silently turn into an unsubstantiated
marketing page while buyer testing is still pending.
"""

from pathlib import Path
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parent
BASE = (ROOT / "index.html").as_uri()

VARIANTS = {
    "A": "Build the grocery store people can shop quickly.",
    "B": "Launch a serious grocery store without assembling a plugin pile.",
    "C": "Make next week’s grocery order easier than this week’s.",
}


def validate_common(page):
    body = page.locator("body").inner_text()
    lower = body.lower()

    # Research state must be obvious and checkout must not be live.
    assert "research prototype" in lower
    assert "no checkout" in lower
    assert "commercial hypothesis" in lower
    assert "$59 / year" in body
    assert "installed gpl-compatible product keeps working" in lower

    # Core positioning/comprehension anchors.
    assert "woocommerce" in lower
    assert "no mandatory elementor" in lower
    assert "wordpress" in lower
    assert "product theme" in lower and "product core" in lower
    assert "buy again" in lower
    assert "delivery" in lower

    # The page's primary action must demonstrate the product rather than sell it.
    primary = page.locator(".hero .button.primary")
    assert primary.inner_text().strip() == "Try the shopping flow"
    assert "aisleflow-v0/index.html" in (primary.get_attribute("href") or "")

    # No actual purchase/checkout control is allowed in research V0.
    assert page.get_by_role("button", name="Buy now").count() == 0
    assert page.get_by_role("link", name="Buy now").count() == 0
    assert page.get_by_role("button", name="Checkout").count() == 0
    assert page.get_by_role("link", name="Checkout").count() == 0

    # No fake social proof or artificial urgency in V0.
    forbidden = [
        "10,000+ customers",
        "100,000+ customers",
        "five star reviews",
        "trusted by leading brands",
        "limited time",
        "countdown",
    ]
    for phrase in forbidden:
        assert phrase not in lower, phrase

    # V0 intentionally uses no marketing imagery: message/structure first.
    assert page.locator("img").count() == 0

    # Narrow viewport must not produce horizontal page overflow.
    dimensions = page.evaluate(
        """() => ({
            scrollWidth: document.documentElement.scrollWidth,
            clientWidth: document.documentElement.clientWidth
        })"""
    )
    assert dimensions["scrollWidth"] <= dimensions["clientWidth"] + 1

    # Primary interactive controls have keyboard focus.
    primary.focus()
    assert page.evaluate("document.activeElement === document.querySelector('.hero .button.primary')")

    # Reduced motion should disable smooth scrolling.
    page.emulate_media(reduced_motion="reduce")
    assert page.evaluate("getComputedStyle(document.documentElement).scrollBehavior") == "auto"


def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)

        for key, expected_headline in VARIANTS.items():
            page = browser.new_page(viewport={"width": 390, "height": 844})
            page.goto(f"{BASE}?variant={key}")
            page.wait_for_load_state("load")

            assert page.locator("html").get_attribute("data-message-variant") == key
            assert page.locator("#hero-title").inner_text().strip() == expected_headline
            assert page.locator(".variant-marker").inner_text().strip() == f"Message variant {key}"

            validate_common(page)
            page.screenshot(path=str(ROOT / f"sales-page-mobile-{key}.png"), full_page=True)
            page.close()

        browser.close()

    print("Sales Page V0 research smoke checks passed for variants A/B/C")


if __name__ == "__main__":
    run()
