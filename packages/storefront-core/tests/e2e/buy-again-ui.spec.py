#!/usr/bin/env python3
"""Real browser smoke for the account Buy Again surface."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
PASSWORD = "alpha-saved-pass"


def login(page, username: str) -> None:
    for attempt in range(2):
        page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
        page.locator("#user_login").fill(username)
        page.locator("#user_pass").fill(PASSWORD)
        page.locator("#wp-submit").click()
        page.wait_for_load_state("networkidle")
        if any(cookie["name"].startswith("wordpress_logged_in_") for cookie in page.context.cookies(BASE_URL)):
            return
        if attempt == 0:
            page.context.clear_cookies()
    raise AssertionError(f"Login cookie was not established for {username}.")


def account_url(page) -> str:
    """Resolve the seeded account page without assuming pretty permalinks."""
    response = page.request.get(f"{BASE_URL}/?rest_route=/wp/v2/pages&slug=my-account&per_page=1")
    if response.ok:
        pages = response.json()
        if pages:
            return f"{BASE_URL}/?page_id={int(pages[0]['id'])}"
    return f"{BASE_URL}/?pagename=my-account"


def open_buy_again(page):
    page.goto(account_url(page), wait_until="networkidle")
    link = page.get_by_role("link", name="Buy Again", exact=True).first
    expect(link).to_be_visible()
    link.click()
    expect(page.locator("[data-bt-buy-again]")).to_be_visible()
    expect(page.locator("[data-bt-buy-again-results] .bt-buy-again__card").first).to_be_visible(timeout=10_000)


def open_buy_again_empty_or_failure(page):
    page.goto(account_url(page), wait_until="networkidle")
    link = page.get_by_role("link", name="Buy Again", exact=True).first
    expect(link).to_be_visible()
    link.click()
    expect(page.locator("[data-bt-buy-again]")).to_be_visible()


def card(page, name: str):
    return page.locator(".bt-buy-again__card").filter(has_text=name)


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        login(page, "alpha-saved-a")
        open_buy_again(page)

        apple = card(page, "Alpha Apple")
        expect(apple).to_contain_text("Bought 1 last time")
        expect(apple.locator("[data-bt-buy-again-add]")).to_have_text("Add again")
        expect(card(page, "Alpha Rice Pack").locator(".bt-buy-again__choose")).to_have_text("Choose options")
        expect(card(page, "Alpha Tomato").locator(".bt-buy-again__state")).to_have_text("Out of stock")
        assert card(page, "Alpha Pending Only").count() == 0
        apple.locator("[data-bt-buy-again-add]").click()
        expect(page.locator("[data-bt-buy-again-status]")).to_have_text(
            re.compile(r"Added 1 × Alpha Apple to your cart\."), timeout=10_000
        )
        cart = page.evaluate(
            """async (endpoint) => {
                const response = await fetch(endpoint, { credentials: 'same-origin' });
                return response.json();
            }""",
            page.evaluate("() => window.BhaivaTechBuyAgainConfig.cart"),
        )
        assert cart["items_count"] == 1, cart
        context.close()

        empty_context = browser.new_context(viewport={"width": 390, "height": 844})
        empty = empty_context.new_page()
        login(empty, "alpha-buy-empty")
        open_buy_again_empty_or_failure(empty)
        expect(empty.locator(".bt-buy-again__empty")).to_have_text(
            "Buy Again will appear after you have an eligible order.", timeout=10_000
        )
        empty_context.close()

        failure_context = browser.new_context(viewport={"width": 390, "height": 844})
        failure = failure_context.new_page()
        login(failure, "alpha-saved-a")
        failure.route(
            re.compile(r"/bhaivatech-storefront/v1/buy-again(?:\?|$)"),
            lambda route: route.fulfill(status=500, content_type="application/json", body='{"message":"failed"}'),
        )
        open_buy_again_empty_or_failure(failure)
        expect(failure.locator(".bt-buy-again__error")).to_contain_text("failed", timeout=10_000)
        expect(failure.locator(".bt-buy-again__retry")).to_have_text("Try again")
        failure_context.close()

        browser.close()


if __name__ == "__main__":
    main()
