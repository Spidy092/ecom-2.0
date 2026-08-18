#!/usr/bin/env python3
"""Headless accessibility-focused smoke checks for the AisleFlow V0 prototype.

This is not a WCAG conformance test. It protects a few interaction invariants that
were identified during the static accessibility audit.
"""

from pathlib import Path
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parent
URL = (ROOT / "index.html").as_uri()


def active(page):
    return page.evaluate(
        """() => ({
            id: document.activeElement?.id || '',
            action: document.activeElement?.dataset?.action || '',
            product: document.activeElement?.dataset?.product || '',
            aisle: document.activeElement?.dataset?.aisle || '',
            nav: document.activeElement?.dataset?.nav || '',
            label: document.activeElement?.getAttribute?.('aria-label') || ''
        })"""
    )


def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 390, "height": 844})
        page.goto(URL)
        page.wait_for_load_state("load")
        page.wait_for_timeout(100)

        # Aisle Rail is a set of filter/navigation buttons, not an ARIA tabs widget.
        assert page.locator("#aisle-rail").get_attribute("role") is None
        assert page.locator('#aisle-rail [role="tab"]').count() == 0
        assert page.locator('#aisle-rail [data-aisle="All"]').get_attribute("aria-pressed") == "true"

        # Keyboard shortcut can move directly into grocery search.
        page.locator("body").focus()
        page.keyboard.press("/")
        assert page.evaluate("document.activeElement === document.querySelector('#search-input')")

        # Search status is centralized/debounced instead of attached to every result row.
        page.locator("#search-input").fill("milk")
        page.wait_for_timeout(600)
        search_status = page.locator("#a11y-search-status").inner_text()
        assert "product" in search_status.lower()

        # Clear search so product-ledger interactions are deterministic.
        page.locator("#search-input").fill("")
        page.wait_for_timeout(50)

        # Activating Add re-renders the row; focus must move to the replacement
        # increment control for the same product instead of falling back to <body>.
        add_milk = page.locator('#product-list [data-product="milk-amul"][data-action="add"]')
        add_milk.focus()
        page.keyboard.press("Enter")
        page.wait_for_timeout(100)
        current = active(page)
        assert current["product"] == "milk-amul"
        assert current["action"] == "increment"
        assert "Increase Amul Taaza Milk" in current["label"]

        # One centralized basket live region announces the resulting state.
        page.wait_for_timeout(250)
        basket_status = page.locator("#a11y-basket-status").inner_text()
        assert "Amul Taaza Milk" in basket_status
        assert "Basket:" in basket_status
        assert page.locator(".qty-value[aria-live]").count() == 0
        assert page.locator("#basket-pulse").get_attribute("role") is None

        # Save is a command whose label describes the next action, not a changing
        # label combined with aria-pressed toggle semantics.
        save_curd = page.locator('#product-list [data-product="curd-nandini"][data-action="save"]')
        assert save_curd.inner_text().strip() == "Save to list"
        assert save_curd.get_attribute("aria-pressed") is None
        save_curd.focus()
        page.keyboard.press("Enter")
        page.wait_for_timeout(100)
        replacement_save_curd = page.locator('#product-list [data-product="curd-nandini"][data-action="save"]')
        assert replacement_save_curd.inner_text().strip() == "Remove from list"
        assert replacement_save_curd.get_attribute("aria-pressed") is None
        assert active(page)["product"] == "curd-nandini"

        # Aisle selection also re-renders its controls; keyboard focus must survive.
        dairy = page.locator('#aisle-rail [data-aisle="Dairy"]')
        dairy.focus()
        page.keyboard.press("Enter")
        page.wait_for_timeout(100)
        assert active(page)["aisle"] == "Dairy"
        assert page.locator('#aisle-rail [data-aisle="Dairy"]').get_attribute("aria-pressed") == "true"

        # Cart surface receives focus on open and returns focus to its launcher on Escape.
        cart_launcher = page.locator('[data-nav="cart"]')
        cart_launcher.click()
        page.wait_for_timeout(100)
        assert active(page)["id"] == "cart-panel"
        page.keyboard.press("Escape")
        page.wait_for_timeout(100)
        assert active(page)["nav"] == "cart"

        # Delivery state is explicit text, not color-only.
        page.locator("#postcode").fill("999999")
        page.locator("#delivery-form button[type='submit']").click()
        delivery_text = page.locator("#delivery-result").inner_text().lower()
        assert "not available" in delivery_text

        # Secondary save controls have a practical minimum target height in this prototype.
        page.locator('[data-nav="home"]').click()
        page.locator("#search-input").fill("")
        page.wait_for_timeout(50)
        save_box = page.locator('#product-list [data-product="curd-nandini"][data-action="save"]').bounding_box()
        assert save_box and save_box["height"] >= 32

        # Reduced-motion preference disables smooth scrolling in the prototype CSS.
        page.emulate_media(reduced_motion="reduce")
        scroll_behavior = page.evaluate("getComputedStyle(document.documentElement).scrollBehavior")
        assert scroll_behavior == "auto"

        browser.close()

    print("AisleFlow V0 browser accessibility smoke checks passed")


if __name__ == "__main__":
    run()
