#!/usr/bin/env python3
"""Security and ownership smoke for the private Buy Again endpoint."""

from __future__ import annotations

import os
from urllib.parse import urlsplit

from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
SITE_URL = os.environ.get("BT_E2E_SITE_URL", f"{urlsplit(BASE_URL).scheme}://{urlsplit(BASE_URL).netloc}")
PASSWORD = "alpha-saved-pass"


def login(page, username: str) -> None:
    """Establish cookie auth with one retry for a cold wp-env login page."""
    for attempt in range(2):
        page.goto(f"{SITE_URL}/wp-login.php", wait_until="domcontentloaded")
        page.locator("#user_login").fill(username)
        page.locator("#user_pass").fill(PASSWORD)
        page.locator("#wp-submit").click()
        page.wait_for_load_state("networkidle")

        if any(
            cookie["name"].startswith("wordpress_logged_in_")
            for cookie in page.context.cookies(SITE_URL)
        ):
            return

        if attempt == 0:
            page.context.clear_cookies()

    raise AssertionError(f"WordPress login cookie was not established for {username}.")


def request(page, endpoint: str, nonce: str = "") -> dict:
    return page.evaluate(
        """async ({ endpoint, nonce }) => {
            const headers = { Accept: 'application/json' };
            if (nonce) headers['X-WP-Nonce'] = nonce;
            const response = await fetch(endpoint, { credentials: 'same-origin', headers });
            let body = null;
            try { body = await response.json(); } catch (error) {}
            return { status: response.status, body };
        }""",
        {"endpoint": endpoint, "nonce": nonce},
    )


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        guest = browser.new_page()
        result = request(guest, f"{SITE_URL}/?rest_route=/bhaivatech-storefront/v1/buy-again")
        assert result["status"] in (401, 403), result
        guest.close()

        page = browser.new_page()
        login(page, "alpha-saved-a")
        page.goto(f"{SITE_URL}/?pagename=my-account", wait_until="networkidle")
        config = page.evaluate("() => window.BhaivaTechBuyAgainConfig || null")
        # The dedicated endpoint injects this config; query-form permalinks are
        # used by the pinned wp-env setup when pretty permalinks are disabled.
        if not config:
            link = page.get_by_role("link", name="Buy Again", exact=True).first
            assert link.count() == 1
            link.click()
            page.wait_for_load_state("networkidle")
            config = page.evaluate("() => window.BhaivaTechBuyAgainConfig")

        result = request(page, config["buyAgain"], config["restNonce"])
        assert result["status"] == 200, result
        assert "order_id" not in result["body"]
        assert "customer_id" not in result["body"]
        assert all("product_id" in item and "purchased_quantity" in item for item in result["body"]["items"])

        forged = request(page, config["buyAgain"] + "?customer_id=1&order_id=1", config["restNonce"])
        assert forged["status"] == 200
        assert forged["body"] == result["body"]
        page.close()
        browser.close()


if __name__ == "__main__":
    main()
