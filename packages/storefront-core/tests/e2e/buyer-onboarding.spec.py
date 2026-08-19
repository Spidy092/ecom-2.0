#!/usr/bin/env python3
"""Browser coverage for the non-destructive buyer personalization guide."""

from __future__ import annotations

import os
from urllib.parse import urlparse

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
ADMIN_USER = "alpha-setup-admin"
ADMIN_PASSWORD = "alpha-setup-pass"
CUSTOMER_USER = "alpha-saved-a"
CUSTOMER_PASSWORD = "alpha-saved-pass"
ONBOARDING_URL = f"{BASE_URL}/wp-admin/admin.php?page=bhaivatech-storefront-personalize"


def has_logged_in_cookie(page) -> bool:
    return any(
        cookie["name"].startswith("wordpress_logged_in_")
        for cookie in page.context.cookies(BASE_URL)
    )


def login(page, username: str, password: str) -> None:
    for attempt in range(2):
        page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
        page.locator("#user_login").fill(username)
        page.locator("#user_pass").fill(password)
        page.locator("#wp-submit").click()
        page.wait_for_load_state("networkidle")
        if has_logged_in_cookie(page):
            return
        if attempt == 0:
            page.context.clear_cookies()
    raise AssertionError(f"WordPress login cookie was not established for {username}")


def assert_same_origin_admin_href(locator, expected_fragment: str) -> None:
    href = locator.get_attribute("href")
    assert href, expected_fragment
    parsed = urlparse(href)
    base = urlparse(BASE_URL)
    assert parsed.netloc == base.netloc, href
    assert expected_fragment in href, href


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        admin_context = browser.new_context(viewport={"width": 1280, "height": 900})
        admin = admin_context.new_page()
        login(admin, ADMIN_USER, ADMIN_PASSWORD)
        admin.goto(ONBOARDING_URL, wait_until="networkidle")

        expect(admin.get_by_role("heading", name="Personalize your store")).to_be_visible()
        for heading in ["Brand and presentation", "Products and demo content", "Review launch readiness"]:
            expect(admin.get_by_role("heading", name=heading)).to_be_visible()

        body = admin.locator("body").inner_text()
        for text in [
            "Add or change logo and store name",
            "Choose a visual style",
            "Adjust colors and typography",
            "Edit header, navigation and footer",
            "Replace demo products and images",
            "No settings or customer content are changed from these onboarding links.",
            "This guide does not certify legal, tax, payment, shipping or business compliance.",
        ]:
            assert text in body, text

        assert_same_origin_admin_href(admin.get_by_role("link", name="Open Site Editor"), "/wp-admin/site-editor.php")
        assert_same_origin_admin_href(admin.get_by_role("link", name="Review products"), "post_type=product")
        assert_same_origin_admin_href(admin.get_by_role("link", name="Review WooCommerce settings"), "page=wc-settings")
        assert_same_origin_admin_href(admin.get_by_role("link", name="Review technical status"), "page=bhaivatech-storefront-setup")

        # Guidance must remain navigation-only: no forms that persist onboarding state.
        assert admin.locator("form").count() == 0
        assert admin.get_by_role("button", name="Import Modern Grocery").count() == 0
        assert admin.get_by_role("button", name="Save onboarding").count() == 0

        admin_context.close()

        customer_context = browser.new_context(viewport={"width": 390, "height": 844})
        customer = customer_context.new_page()
        login(customer, CUSTOMER_USER, CUSTOMER_PASSWORD)
        customer.goto(ONBOARDING_URL, wait_until="networkidle")
        assert customer.get_by_role("heading", name="Personalize your store").count() == 0
        customer_context.close()

        browser.close()

    print("Buyer onboarding E2E checks passed")


if __name__ == "__main__":
    main()
