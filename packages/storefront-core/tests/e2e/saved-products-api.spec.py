#!/usr/bin/env python3
"""Security/ownership smoke for logged-in Saved REST persistence."""

from __future__ import annotations

import os

from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
PASSWORD = "alpha-saved-pass"


def has_logged_in_cookie(page) -> bool:
    return any(
        cookie["name"].startswith("wordpress_logged_in_")
        for cookie in page.context.cookies(BASE_URL)
    )


def login(page, username: str) -> None:
    for attempt in range(2):
        page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
        page.locator("#user_login").fill(username)
        page.locator("#user_pass").fill(PASSWORD)
        page.locator("#wp-submit").click()
        page.wait_for_load_state("networkidle")

        if has_logged_in_cookie(page):
            page.goto(BASE_URL, wait_until="networkidle")
            assert page.evaluate("() => Boolean(window.BhaivaTechStorefrontConfig?.saved?.loggedIn)")
            return

        if attempt == 0:
            page.context.clear_cookies()

    raise AssertionError(f"WordPress login cookie was not established for {username}.")


def saved_config(page) -> dict:
    return page.evaluate("() => window.BhaivaTechStorefrontConfig.saved")


def store_product_id(page, search_term: str) -> int:
    return page.evaluate(
        """async (term) => {
            const endpoint = new URL(window.BhaivaTechStorefrontConfig.endpoints.products);
            endpoint.searchParams.set('search', term);
            endpoint.searchParams.set('per_page', '1');
            endpoint.searchParams.set('catalog_visibility', 'search');
            const response = await fetch(endpoint.toString(), { credentials: 'same-origin' });
            const products = await response.json();
            if (!response.ok || !Array.isArray(products) || !products[0]) {
                throw new Error('Product fixture not found: ' + term);
            }
            return Number(products[0].id);
        }""",
        search_term,
    )


def api(page, endpoint: str, method: str = "GET", nonce: str = "") -> dict:
    return page.evaluate(
        """async ({ endpoint, method, nonce }) => {
            const headers = { Accept: 'application/json' };
            if (nonce) headers['X-WP-Nonce'] = nonce;
            const response = await fetch(endpoint, {
                method,
                credentials: 'same-origin',
                headers,
            });
            let body = null;
            try { body = await response.json(); } catch (error) {}
            return { status: response.status, body };
        }""",
        {"endpoint": endpoint, "method": method, "nonce": nonce},
    )


def product_endpoint(config: dict, product_id: int) -> str:
    return config["productTemplate"].replace("__PRODUCT_ID__", str(product_id))


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        # Guest requests cannot read account Saved data.
        guest_context = browser.new_context()
        guest = guest_context.new_page()
        guest.goto(BASE_URL, wait_until="networkidle")
        guest_config = saved_config(guest)
        assert guest_config["loggedIn"] is False
        assert guest_config["restNonce"] == ""
        guest_read = api(guest, guest_config["collection"])
        assert guest_read["status"] in (401, 403), guest_read
        guest_context.close()

        # Shopper A owns only A's list. Cookie auth without a REST nonce must
        # fail rather than treating the nonce itself as authorization.
        context_a = browser.new_context()
        page_a = context_a.new_page()
        login(page_a, "alpha-saved-a")
        config_a = saved_config(page_a)
        bread_id = store_product_id(page_a, "Alpha Bread")
        milk_id = store_product_id(page_a, "Alpha Milk")

        no_nonce = api(page_a, product_endpoint(config_a, bread_id), "POST")
        assert no_nonce["status"] in (401, 403), no_nonce

        add_a = api(
            page_a,
            product_endpoint(config_a, bread_id),
            "POST",
            config_a["restNonce"],
        )
        assert add_a["status"] == 200, add_a
        assert add_a["body"]["ids"] == [bread_id], add_a

        read_a = api(page_a, config_a["collection"], "GET", config_a["restNonce"])
        assert read_a["status"] == 200, read_a
        assert read_a["body"]["ids"] == [bread_id], read_a

        invalid = api(
            page_a,
            product_endpoint(config_a, 99999999),
            "POST",
            config_a["restNonce"],
        )
        assert invalid["status"] == 400, invalid

        # Shopper B starts empty and cannot see or alter A's Saved IDs because
        # no API route accepts a target user/customer identity.
        context_b = browser.new_context()
        page_b = context_b.new_page()
        login(page_b, "alpha-saved-b")
        config_b = saved_config(page_b)

        read_b = api(page_b, config_b["collection"], "GET", config_b["restNonce"])
        assert read_b["status"] == 200, read_b
        assert read_b["body"]["ids"] == [], read_b

        add_b = api(
            page_b,
            product_endpoint(config_b, milk_id),
            "POST",
            config_b["restNonce"],
        )
        assert add_b["status"] == 200, add_b
        assert add_b["body"]["ids"] == [milk_id], add_b

        read_a_again = api(page_a, config_a["collection"], "GET", config_a["restNonce"])
        assert read_a_again["body"]["ids"] == [bread_id], read_a_again

        remove_a = api(
            page_a,
            product_endpoint(config_a, bread_id),
            "DELETE",
            config_a["restNonce"],
        )
        assert remove_a["status"] == 200, remove_a
        assert remove_a["body"]["ids"] == [], remove_a

        context_a.close()
        context_b.close()
        browser.close()


if __name__ == "__main__":
    main()
