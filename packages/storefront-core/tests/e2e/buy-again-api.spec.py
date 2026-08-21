#!/usr/bin/env python3
"""Security and ownership smoke for the private Buy Again endpoint."""

from __future__ import annotations

import os

from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
PASSWORD = "alpha-saved-pass"


def has_logged_in_cookie(page) -> bool:
    return any(cookie["name"].startswith("wordpress_logged_in_") for cookie in page.context.cookies(BASE_URL))


def login(page, username: str) -> None:
    page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
    page.locator("#user_login").fill(username)
    page.locator("#user_pass").fill(PASSWORD)
    page.locator("#wp-submit").click()
    page.wait_for_load_state("networkidle")
    assert has_logged_in_cookie(page), f"Login cookie was not established for {username}."


def account_url(page) -> str:
    """Resolve the seeded account page without assuming pretty permalinks."""
    response = page.request.get(f"{BASE_URL}/?rest_route=/wp/v2/pages&slug=my-account&per_page=1")
    if response.ok:
        pages = response.json()
        if pages:
            return f"{BASE_URL}/?page_id={int(pages[0]['id'])}"
    return f"{BASE_URL}/?pagename=my-account"


def endpoint_config(page) -> dict:
    page.goto(account_url(page), wait_until="networkidle")
    link = page.get_by_role("link", name="Buy Again", exact=True).first
    assert link.count() == 1, "Buy Again account navigation link was not rendered."
    link.click()
    page.wait_for_load_state("networkidle")
    return page.evaluate("() => window.BhaivaTechBuyAgainConfig")


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


def product_id(page, endpoint: str, name: str) -> int:
    return page.evaluate(
        """async ({ endpoint, name }) => {
            const url = new URL(endpoint);
            url.searchParams.set('search', name);
            url.searchParams.set('per_page', '1');
            const response = await fetch(url.toString(), { credentials: 'same-origin' });
            const products = await response.json();
            const product = Array.isArray(products) && products.find((item) => item.name === name);
            if (!response.ok || !product) throw new Error('Fixture missing: ' + name);
            return Number(product.id);
        }""",
        {"endpoint": endpoint, "name": name},
    )


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        guest_context = browser.new_context()
        guest = guest_context.new_page()
        guest.goto(BASE_URL, wait_until="networkidle")
        # wp-env uses query-form REST routing in its pinned test configuration.
        guest_result = request(guest, f"{BASE_URL}/?rest_route=/bhaivatech-storefront/v1/buy-again")
        assert guest_result["status"] in (401, 403), guest_result
        guest_context.close()

        context_a = browser.new_context()
        page_a = context_a.new_page()
        login(page_a, "alpha-saved-a")
        config_a = endpoint_config(page_a)
        assert config_a and config_a["restNonce"]

        no_nonce = request(page_a, config_a["buyAgain"])
        assert no_nonce["status"] in (401, 403), no_nonce

        result_a = request(page_a, config_a["buyAgain"], config_a["restNonce"])
        assert result_a["status"] == 200, result_a
        assert "order_id" not in result_a["body"]
        assert "customer_id" not in result_a["body"]

        ids_a = {int(item["product_id"]): int(item["purchased_quantity"]) for item in result_a["body"]["items"]}
        apple_id = product_id(page_a, config_a["products"], "Alpha Apple")
        milk_id = product_id(page_a, config_a["products"], "Alpha Milk")
        tomato_id = product_id(page_a, config_a["products"], "Alpha Tomato")
        rice_id = product_id(page_a, config_a["products"], "Alpha Rice Pack")
        bread_id = product_id(page_a, config_a["products"], "Alpha Bread")
        assert ids_a[apple_id] == 1, ids_a
        assert ids_a[milk_id] == 3, ids_a
        assert tomato_id in ids_a, ids_a
        assert rice_id in ids_a, ids_a
        assert bread_id not in ids_a, ids_a

        for excluded_name in (
            "Alpha Pending Only",
            "Alpha Cancelled Only",
            "Alpha Refunded Only",
            "Alpha Failed Only",
        ):
            assert product_id(page_a, config_a["products"], excluded_name) not in ids_a

        # Query parameters cannot change the server-derived customer scope.
        separator = "&" if "?" in config_a["buyAgain"] else "?"
        forged = request(page_a, config_a["buyAgain"] + separator + "customer_id=1&order_id=1", config_a["restNonce"])
        assert forged["status"] == 200, forged
        assert forged["body"] == result_a["body"], forged
        context_a.close()

        context_b = browser.new_context()
        page_b = context_b.new_page()
        login(page_b, "alpha-saved-b")
        config_b = endpoint_config(page_b)
        result_b = request(page_b, config_b["buyAgain"], config_b["restNonce"])
        assert result_b["status"] == 200, result_b
        ids_b = {int(item["product_id"]) for item in result_b["body"]["items"]}
        bread_b = product_id(page_b, config_b["products"], "Alpha Bread")
        assert ids_b == {bread_b}, ids_b
        context_b.close()
        browser.close()


if __name__ == "__main__":
    main()
