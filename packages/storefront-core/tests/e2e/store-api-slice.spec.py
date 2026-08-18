#!/usr/bin/env python3
"""Real WordPress + WooCommerce browser smoke for the first storefront slice."""

from __future__ import annotations

import os

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def search_for(page, query: str):
    search = page.locator("[data-bt-search]")
    search.fill(query)
    expect(page.locator("[data-bt-status]")).not_to_have_text("Searching groceries…", timeout=10_000)


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()

        page.goto(BASE_URL, wait_until="networkidle")
        expect(page.locator("[data-bt-product-workspace]")).to_be_visible()
        expect(page.locator("[data-bt-search]")).to_be_visible()

        # Simple product: direct add -> quantity controls -> authoritative cart.
        search_for(page, "Alpha Milk")
        milk = product_card(page, "Alpha Milk")
        expect(milk).to_be_visible()
        add = milk.locator('button[data-action="add"]')
        expect(add).to_be_visible()
        add.click()

        expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")
        milk = product_card(page, "Alpha Milk")
        increment = milk.locator('button[data-action="increment"]')
        expect(increment).to_be_visible()
        expect(increment).to_be_focused()
        increment.click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("2 items")
        expect(product_card(page, "Alpha Milk").locator(".bt-product-card__quantity-value")).to_have_text("2")

        # Variable/choice-required product: never direct-add silently.
        search_for(page, "Alpha Rice")
        rice = product_card(page, "Alpha Rice Pack")
        expect(rice).to_be_visible()
        expect(rice.locator(".bt-product-card__choose")).to_have_text("Choose options")
        assert rice.locator('button[data-action="add"]').count() == 0

        # Out-of-stock product communicates state instead of offering Add.
        search_for(page, "Alpha Tomato")
        tomato = product_card(page, "Alpha Tomato")
        expect(tomato).to_be_visible()
        expect(tomato.locator(".bt-product-card__stock")).to_have_text("Out of stock")
        assert tomato.locator('button[data-action="add"]').count() == 0

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
