#!/usr/bin/env python3
"""Real WooCommerce shopper smoke for adaptive department browsing."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def choose_department(page, name: str) -> None:
    button = page.locator("[data-bt-departments] button").filter(has_text=name)
    expect(button).to_be_visible()
    button.click()
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(rf"\d+ products? in {re.escape(name)}\."),
        timeout=10_000,
    )
    expect(button).to_have_attribute("aria-pressed", "true")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        browse = page.locator("[data-bt-browse]")
        expect(browse).to_be_visible()
        expect(page.locator("[data-bt-browse-state]")).to_have_text(
            "Choose a department.", timeout=10_000
        )
        expect(browse).to_have_attribute("data-mode", "rail")
        expect(page.locator("[data-bt-browse-fallback]")).to_be_hidden()

        buttons = page.locator("[data-bt-departments] button")
        assert 2 <= buttons.count() <= 8, buttons.count()
        names = buttons.all_inner_texts()
        for required in ("Produce", "Dairy", "Pantry", "Bakery"):
            assert required in names, names
        assert "Leafy Greens" not in names, names

        for index in range(buttons.count()):
            box = buttons.nth(index).bounding_box()
            assert box and box["height"] >= 44, (index, box)

        # Mobile Browse is now a real current-page destination, not a Shop detour.
        nav_browse = page.locator("[data-bt-mobile-browse-link]")
        expect(nav_browse).to_have_attribute("href", re.compile(r"#grocery-browse$"))
        nav_browse.click()
        expect(browse).to_be_focused()
        assert page.url.endswith("#grocery-browse"), page.url
        stable_url = page.url

        # Produce preserves out-of-stock safety while an in-stock item can be added.
        choose_department(page, "Produce")
        assert page.url == stable_url, page.url
        expect(product_card(page, "Alpha Tomato").locator(".bt-product-card__stock")).to_have_text("Out of stock")
        apple = product_card(page, "Alpha Apple")
        expect(apple).to_be_visible()
        apple.locator('button[data-action="add"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")

        # Dairy switches on the same surface and the authoritative Cart survives.
        choose_department(page, "Dairy")
        assert page.url == stable_url, page.url
        milk = product_card(page, "Alpha Milk")
        milk.locator('button[data-action="add"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("2 items")

        # Pantry keeps variable-product safety and adds a separate simple fixture.
        choose_department(page, "Pantry")
        assert page.url == stable_url, page.url
        expect(product_card(page, "Alpha Rice Pack").locator(".bt-product-card__choose")).to_have_text("Choose options")
        lentils = product_card(page, "Alpha Lentils")
        lentils.locator('button[data-action="add"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("3 items")
        expect(page.locator("[data-bt-mobile-cart-count]")).to_have_text("3")

        # Return to exact-product Search through the persistent mobile nav.
        page.locator("[data-bt-mobile-search-link]").click()
        expect(page.locator("[data-bt-search]")).to_be_focused()
        assert page.url.endswith("#grocery-search"), page.url

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
