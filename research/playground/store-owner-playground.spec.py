#!/usr/bin/env python3
"""Browser smoke for the disposable store-owner validation Playground."""

from __future__ import annotations

import os
from urllib.parse import urlparse

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_PLAYGROUND_BASE_URL", "http://127.0.0.1:9400")
ONBOARDING_URL = f"{BASE_URL}/wp-admin/admin.php?page=bhaivatech-storefront-personalize"


def assert_same_origin_href(locator, expected_fragment: str) -> None:
    href = locator.get_attribute("href")
    assert href, expected_fragment
    parsed = urlparse(href)
    base = urlparse(BASE_URL)
    assert parsed.netloc in ("", base.netloc), href
    assert expected_fragment in href, href


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 1280, "height": 900})
        page = context.new_page()

        page.goto(ONBOARDING_URL, wait_until="networkidle")
        assert "wp-login.php" not in page.url, page.url
        expect(page.get_by_role("heading", name="Personalize your store")).to_be_visible()
        expect(page.get_by_text("No settings or customer content are changed from these onboarding links.")).to_be_visible()
        expect(page.get_by_text("Choose a visual style")).to_be_visible()
        expect(page.get_by_text("Replace demo products and images")).to_be_visible()
        expect(page.get_by_text("Review launch readiness")).to_be_visible()

        assert_same_origin_href(page.get_by_role("link", name="Open Site Editor"), "/wp-admin/site-editor.php")
        assert_same_origin_href(page.get_by_role("link", name="Review products"), "post_type=product")
        assert_same_origin_href(page.get_by_role("link", name="Review WooCommerce settings"), "page=wc-settings")
        assert_same_origin_href(page.get_by_role("link", name="Review technical status"), "page=bhaivatech-storefront-setup")

        # The onboarding surface must remain navigation-only in the tester build.
        assert page.locator("form").count() == 0

        page.goto(BASE_URL + "/", wait_until="networkidle")
        expect(page.get_by_text("Modern Grocery Validation", exact=True).first).to_be_visible()

        context.close()
        browser.close()

    print("Disposable store-owner Playground browser smoke passed")


if __name__ == "__main__":
    main()
