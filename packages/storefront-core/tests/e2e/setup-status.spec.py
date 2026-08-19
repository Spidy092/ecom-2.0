#!/usr/bin/env python3
"""Browser coverage for Store Setup & privacy-safe system status."""

from __future__ import annotations

import json
import os
from pathlib import Path

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
ADMIN_USER = "alpha-setup-admin"
ADMIN_PASSWORD = "alpha-setup-pass"
CUSTOMER_USER = "alpha-saved-a"
CUSTOMER_PASSWORD = "alpha-saved-pass"
SETUP_URL = f"{BASE_URL}/wp-admin/admin.php?page=bhaivatech-storefront-setup"


def login(page, username: str, password: str) -> None:
    page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
    page.locator("#user_login").fill(username)
    page.locator("#user_pass").fill(password)
    page.locator("#wp-submit").click()
    page.wait_for_load_state("networkidle")


def collect_keys(value):
    keys = []
    if isinstance(value, dict):
        for key, item in value.items():
            keys.append(str(key).lower())
            keys.extend(collect_keys(item))
    elif isinstance(value, list):
        for item in value:
            keys.extend(collect_keys(item))
    return keys


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        admin_context = browser.new_context(viewport={"width": 1280, "height": 900})
        admin = admin_context.new_page()
        login(admin, ADMIN_USER, ADMIN_PASSWORD)
        admin.goto(SETUP_URL, wait_until="networkidle")

        expect(admin.get_by_role("heading", name="Store Setup & Status")).to_be_visible()
        expect(admin.get_by_role("heading", name="Setup path")).to_be_visible()
        expect(admin.get_by_role("heading", name="Environment checks")).to_be_visible()
        expect(admin.get_by_role("heading", name="Plugin compatibility")).to_be_visible()
        expect(admin.get_by_role("heading", name="Support report")).to_be_visible()

        # The alpha deliberately exposes preflight/status before destructive import.
        expect(admin.get_by_text("Modern Grocery starter store", exact=False)).to_be_visible()
        expect(admin.get_by_text("import is intentionally disabled in this alpha", exact=False)).to_be_visible()
        assert admin.get_by_role("button", name="Import").count() == 0
        assert admin.get_by_role("button", name="Install starter store").count() == 0

        # Compatibility language distinguishes required, optional and validated support.
        expect(admin.get_by_text("WooCommerce is required", exact=False)).to_be_visible()
        expect(admin.get_by_text("Elementor and other page builders are not required", exact=False)).to_be_visible()
        expect(admin.get_by_text("only advertised as validated compatibility after we test it", exact=False)).to_be_visible()

        # Environment status should expose the release-relevant platform facts.
        page_text = admin.locator("body").inner_text()
        for required in [
            "WordPress",
            "WooCommerce",
            "PHP",
            "Product theme",
            "HTTPS",
            "WordPress REST API",
            "WP-Cron",
            "WordPress memory limit",
            "Woo template overrides",
        ]:
            assert required in page_text, required

        # Active plugin visibility helps diagnose extension conflicts without claiming
        # universal compatibility.
        assert "WooCommerce" in page_text
        assert "BhaivaTech Storefront Core" in page_text

        with admin.expect_download() as download_info:
            admin.get_by_role("button", name="Download safe system report").click()
        download = download_info.value
        report_path = Path(download.path())
        report = json.loads(report_path.read_text(encoding="utf-8"))

        assert report["product"]["product_theme_active"] is True
        assert report["platform"]["woocommerce_version"] != "Not active"
        assert report["platform"]["php_version"]
        assert isinstance(report["active_plugins"], list)
        assert isinstance(report["template_overrides"], list)

        # Export keys must remain technical and privacy-bounded.
        keys = collect_keys(report)
        forbidden_keys = {
            "site_url",
            "home_url",
            "domain",
            "email",
            "username",
            "customer",
            "orders",
            "order_data",
            "password",
            "database_password",
            "cookie",
            "nonce",
            "license_key",
            "license_secret",
            "filesystem_path",
        }
        assert forbidden_keys.isdisjoint(keys), sorted(forbidden_keys.intersection(keys))

        for relative_path in report["template_overrides"]:
            assert not relative_path.startswith("/")
            assert "wp-content" not in relative_path

        admin_context.close()

        # A shopper/customer must not be able to open the administrator setup page.
        customer_context = browser.new_context(viewport={"width": 390, "height": 844})
        customer = customer_context.new_page()
        login(customer, CUSTOMER_USER, CUSTOMER_PASSWORD)
        customer.goto(SETUP_URL, wait_until="networkidle")
        assert customer.get_by_role("heading", name="Store Setup & Status").count() == 0
        assert "not allowed" in customer.locator("body").inner_text().lower() or customer.url != SETUP_URL
        customer_context.close()

        browser.close()

    print("Store Setup & system-status E2E checks passed")


if __name__ == "__main__":
    main()
