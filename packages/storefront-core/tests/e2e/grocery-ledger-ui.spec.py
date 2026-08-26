#!/usr/bin/env python3
"""Browser smoke for the seeded grocery product-ledger purchase rail."""

from __future__ import annotations

import os

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def ledger_row(page, name: str):
    # Product Collection wrappers vary between WooCommerce block releases,
    # but each product item exposes the stable public product-item class.
    # Anchoring on the item keeps normal, variable, and unavailable cards on
    # the same assertion path.
    return page.locator("li.wc-block-product").filter(
        has=page.get_by_role("link", name=name, exact=True)
    ).first


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        spinach = ledger_row(page, "Baby Spinach")
        expect(spinach).to_be_visible()
        quick_add = spinach.locator(".storefront-quick-add-block")
        expect(quick_add).to_be_visible()

        # Quantity is selected in the row before the Store API mutation, so a
        # grocery shopper does not need to open a product page or cart.
        quick_add.locator(".storefront-quick-add-btn--plus").click()
        expect(quick_add.locator(".storefront-quick-add-qty")).to_have_text("2")
        quick_add.locator(".storefront-quick-add-submit").click()
        expect(quick_add.locator(".storefront-quick-add-status")).to_have_text(
            "Added 2 to cart.", timeout=10_000
        )
        expect(page).to_have_url(BASE_URL.rstrip("/") + "/")

        # Variable products never silently select a variation.
        rice = ledger_row(page, "Everyday Basmati Rice")
        expect(rice.locator(".storefront-quick-add-fallback--options")).to_have_text(
            "Choose options"
        )
        assert rice.locator(".storefront-quick-add-block").count() == 0

        # Current product truth explains why an unavailable item cannot be
        # added instead of leaving a dead or misleading Add button.
        tomatoes = ledger_row(page, "Vine Tomatoes")
        expect(tomatoes.locator(".storefront-quick-add-fallback--unavailable")).to_have_text(
            "Out of stock"
        )
        assert tomatoes.locator(".storefront-quick-add-submit").count() == 0

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
