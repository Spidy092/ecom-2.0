#!/usr/bin/env python3
"""Real WordPress + WooCommerce smoke for the mobile shopping navigation shell."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def search_for(page, query: str) -> None:
    search = page.locator("[data-bt-search]")
    search.fill(query)
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(r"(?:\d+ products? found\.|No exact matches\.)"),
        timeout=10_000,
    )


def checkout_url(page) -> str:
    response = page.request.get(f"{BASE_URL}/?rest_route=/wp/v2/pages&slug=checkout")
    assert response.ok, response.status
    pages = response.json()
    assert pages, "WooCommerce checkout page was not found."
    return pages[0]["link"]


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        mobile_context = browser.new_context(viewport={"width": 390, "height": 844})
        page = mobile_context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        nav = page.locator("[data-bt-mobile-shopping-nav]")
        expect(nav).to_be_visible()
        assert nav.count() == 1
        expect(nav).to_have_attribute("aria-label", "Shopping")

        labels = nav.locator(".bt-mobile-shopping-nav__item")
        assert labels.count() == 5
        assert labels.all_inner_texts() == ["Home", "Search", "Browse", "Cart\n0", "Account"]
        expect(labels.nth(0)).to_have_attribute("aria-current", "page")

        for index in range(5):
            box = labels.nth(index).bounding_box()
            assert box and box["height"] >= 44, (index, box)

        body_padding = page.evaluate(
            "() => parseFloat(getComputedStyle(document.body).paddingBottom)"
        )
        assert body_padding >= 70, body_padding

        # Current-page Search is a one-tap focus action, not a navigation round trip.
        search_link = nav.locator("[data-bt-mobile-search-link]")
        search_link.click()
        expect(page.locator("[data-bt-search]")).to_be_focused()
        assert page.url.endswith("#grocery-search"), page.url

        # Current-page Browse is also a focus action on the real department surface.
        browse_link = nav.locator("[data-bt-mobile-browse-link]")
        expect(browse_link).to_have_attribute("href", re.compile(r"#grocery-browse$"))
        browse_link.click()
        expect(page.locator("[data-bt-browse]")).to_be_focused()
        assert page.url.endswith("#grocery-browse"), page.url

        # Cart badge follows the same authoritative cart response used by the
        # product workspace; no polling or duplicate cart model is involved.
        search_for(page, "Alpha Milk")
        milk = product_card(page, "Alpha Milk")
        milk.locator('button[data-action="add"]').click()
        badge = nav.locator("[data-bt-mobile-cart-count]")
        expect(badge).to_have_text("1")
        expect(badge).to_have_attribute("aria-label", "1 item in cart")

        # From another storefront surface, Browse follows the real Home fragment
        # and focuses the same department workspace after navigation.
        shop_url = page.locator("[data-bt-browse-fallback]").get_attribute("href")
        assert shop_url
        page.goto(shop_url, wait_until="networkidle")
        shop_nav = page.locator("[data-bt-mobile-shopping-nav]")
        expect(shop_nav).to_be_visible()
        shop_nav.locator("[data-bt-mobile-browse-link]").click()
        page.wait_for_url(re.compile(r"#grocery-browse$"), timeout=10_000)
        expect(page.locator("[data-bt-browse]")).to_be_focused(timeout=10_000)

        # Search still returns/focuses the exact-product workspace.
        page.locator("[data-bt-mobile-search-link]").click()
        expect(page.locator("[data-bt-search]")).to_be_focused()
        assert page.url.endswith("#grocery-search"), page.url

        # Cart deliberately has no persistent shopping nav because the cart page
        # already owns the checkout transition and fixed Cart UI would duplicate it.
        cart_url = page.locator("[data-bt-mobile-cart-link]").get_attribute("href")
        assert cart_url
        page.goto(cart_url, wait_until="networkidle")
        assert page.locator("[data-bt-mobile-shopping-nav]").count() == 0

        # Checkout is also protected from competing bottom navigation.
        page.goto(checkout_url(page), wait_until="networkidle")
        assert page.locator("[data-bt-mobile-shopping-nav]").count() == 0
        mobile_context.close()

        # The bottom navigation is a mobile affordance, not a desktop redesign.
        desktop_context = browser.new_context(viewport={"width": 1024, "height": 900})
        desktop = desktop_context.new_page()
        desktop.goto(BASE_URL, wait_until="networkidle")
        desktop_nav = desktop.locator("[data-bt-mobile-shopping-nav]")
        expect(desktop_nav).to_be_hidden()
        desktop_padding = desktop.evaluate(
            "() => parseFloat(getComputedStyle(document.body).paddingBottom)"
        )
        assert desktop_padding < 70, desktop_padding
        desktop_context.close()

        browser.close()


if __name__ == "__main__":
    main()
