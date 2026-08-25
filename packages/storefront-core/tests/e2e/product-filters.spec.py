#!/usr/bin/env python3
"""Real WooCommerce shopper smoke for contextual grocery filters."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def choose_department(page, name: str) -> None:
    button = page.locator("[data-bt-departments] button").filter(has_text=name)
    expect(button).to_be_visible()
    button.click()
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(rf"\d+ products? in {re.escape(name)}\."), timeout=10_000
    )


def open_filters(page) -> None:
    toggle = page.locator("[data-bt-filters-toggle]")
    expect(toggle).to_be_enabled(timeout=10_000)
    toggle.click()
    expect(page.locator("[data-bt-filters-panel]")).to_be_visible()
    expect(page.locator("[data-bt-filters-state]")).to_have_text(
        re.compile(r"Filters ready\.|Availability is the only filter"), timeout=10_000
    )


def clear_filters(page) -> None:
    open_filters(page)
    clear = page.locator("[data-bt-filters-clear]")
    expect(clear).to_be_enabled()
    clear.click()
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(r"Filters cleared\. Showing \d+ products\."), timeout=10_000
    )


def assert_no_horizontal_overflow(page) -> None:
    overflow = page.evaluate(
        "document.documentElement.scrollWidth - document.documentElement.clientWidth"
    )
    assert overflow <= 1, overflow


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        choose_department(page, "Produce")
        expect(card(page, "Alpha Tomato")).to_be_visible()
        expect(card(page, "Alpha Apple")).to_be_visible()

        open_filters(page)
        expect(page.locator("[data-bt-filters]")).to_have_attribute("aria-busy", "false")
        expect(page.locator("[data-bt-filter-price-group]")).to_be_visible()
        expect(page.locator("[data-bt-filter-min-price]")).to_have_attribute("placeholder", "59.00")
        expect(page.locator("[data-bt-filter-max-price]")).to_have_attribute("placeholder", "80.00")
        expect(page.locator("[data-bt-filter-attributes]")).to_contain_text("Dietary")
        expect(page.locator("[data-bt-filter-attributes]")).to_contain_text("Organic (1)")
        expect(page.locator("[data-bt-filter-attributes]")).to_contain_text("Vegan (1)")
        assert_no_horizontal_overflow(page)

        page.locator("[data-bt-filter-stock]").check()
        page.locator("[data-bt-filters-apply]").click()
        expect(page.locator("[data-bt-status]")).to_have_text("Showing 1 filtered products.", timeout=10_000)
        expect(card(page, "Alpha Apple")).to_be_visible()
        expect(card(page, "Alpha Tomato")).to_have_count(0)
        expect(page.locator("[data-bt-filters-count]")).to_have_text("1")
        clear_filters(page)
        expect(card(page, "Alpha Tomato")).to_be_visible()
        expect(card(page, "Alpha Apple")).to_be_visible()

        open_filters(page)
        page.locator("[data-bt-filter-max-price]").fill("70")
        page.locator("[data-bt-filters-apply]").click()
        expect(page.locator("[data-bt-status]")).to_have_text("Showing 1 filtered products.", timeout=10_000)
        expect(card(page, "Alpha Tomato")).to_be_visible()
        expect(card(page, "Alpha Apple")).to_have_count(0)
        clear_filters(page)

        open_filters(page)
        vegan = page.locator(".bt-product-filters__option").filter(has_text="Vegan").locator("input")
        expect(vegan).to_be_visible()
        vegan.check()
        page.locator("[data-bt-filters-apply]").click()
        expect(page.locator("[data-bt-status]")).to_have_text("Showing 1 filtered products.", timeout=10_000)
        expect(card(page, "Alpha Apple")).to_be_visible()
        expect(card(page, "Alpha Tomato")).to_have_count(0)
        clear_filters(page)

        card(page, "Alpha Apple").locator('button[data-action="add"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")

        page.locator("[data-bt-mobile-search-link]").click()
        search = page.locator("[data-bt-search]")
        search.fill("milk")
        expect(card(page, "Alpha Milk")).to_be_visible(timeout=10_000)
        expect(page.locator("[data-bt-filters-toggle]")).to_be_enabled(timeout=10_000)
        expect(page.locator("[data-bt-filters-count]")).to_be_hidden()
        open_filters(page)
        expect(page.locator("[data-bt-filter-stock]")).not_to_be_checked()
        expect(page.locator("[data-bt-filter-min-price]")).to_have_value("")
        expect(page.locator("[data-bt-filter-max-price]")).to_have_value("")
        assert_no_horizontal_overflow(page)
        context.close()

        for width in (320, 430):
            narrow = browser.new_context(viewport={"width": width, "height": 844})
            narrow_page = narrow.new_page()
            narrow_page.goto(BASE_URL, wait_until="networkidle")
            choose_department(narrow_page, "Produce")
            open_filters(narrow_page)
            assert_no_horizontal_overflow(narrow_page)
            narrow.close()

        browser.close()


if __name__ == "__main__":
    main()
