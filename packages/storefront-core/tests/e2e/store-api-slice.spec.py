#!/usr/bin/env python3
"""Real WordPress + WooCommerce browser smoke for the storefront shopping slice."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def search_for(page, query: str):
    search = page.locator("[data-bt-search]")
    search.fill(query)
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(r"(?:\d+ products? found\.|No exact matches\.)"),
        timeout=10_000,
    )


def store_api_cart(page):
    """Read the authoritative Woo Store API cart in the current browser session."""
    return page.evaluate(
        """async () => {
            const configured = window.BhaivaTechStorefrontConfig?.endpoints?.cart;
            const apiLink = document.querySelector('link[rel="https://api.w.org/"]')?.href;
            let endpoint = configured || null;

            if (!endpoint && apiLink) {
                const root = new URL(apiLink, window.location.href);
                if (root.searchParams.has('rest_route')) {
                    root.searchParams.set('rest_route', '/wc/store/v1/cart');
                    endpoint = root.toString();
                } else {
                    endpoint = new URL('wc/store/v1/cart', root.toString().replace(/\/?$/, '/')).toString();
                }
            }

            if (!endpoint) {
                throw new Error('Store API cart endpoint could not be discovered');
            }

            const response = await fetch(endpoint, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                throw new Error('Store API cart read failed with ' + response.status + ' at ' + endpoint);
            }
            return response.json();
        }"""
    )


def cart_quantities(cart):
    return {
        item["name"]: int(item.get("quantity", 0))
        for item in cart.get("items", [])
    }


def main() -> None:
    with sync_playwright() as playwright:
        # GitHub-hosted Ubuntu runners include stable Google Chrome. Using the
        # branded channel avoids downloading a second browser just for this gate.
        browser = playwright.chromium.launch(channel="chrome", headless=True)
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

        # Quantity decrement remains authoritative and quantity 1 -> 0 removes
        # the Woo cart line, restoring the Add action and keyboard focus.
        milk = product_card(page, "Alpha Milk")
        decrement = milk.locator('button[data-action="decrement"]')
        decrement.click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")
        expect(product_card(page, "Alpha Milk").locator(".bt-product-card__quantity-value")).to_have_text("1")

        milk = product_card(page, "Alpha Milk")
        decrement = milk.locator('button[data-action="decrement"]')
        expect(decrement).to_be_focused()
        decrement.click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("0 items")
        restored_add = product_card(page, "Alpha Milk").locator('button[data-action="add"]')
        expect(restored_add).to_be_visible()
        expect(restored_add).to_be_focused()

        # Regression #83: two different simple products must produce one cart truth.
        # The original founder screenshot was Milk x3 + Eggs x2; the deterministic
        # engineering fixture uses Bread as the second simple in-stock product.
        restored_add.click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")
        product_card(page, "Alpha Milk").locator('button[data-action="increment"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("2 items")
        product_card(page, "Alpha Milk").locator('button[data-action="increment"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("3 items")
        expect(product_card(page, "Alpha Milk").locator(".bt-product-card__quantity-value")).to_have_text("3")

        search_for(page, "Alpha Bread")
        bread = product_card(page, "Alpha Bread")
        expect(bread).to_be_visible()
        bread.locator('button[data-action="add"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("4 items")
        product_card(page, "Alpha Bread").locator('button[data-action="increment"]').click()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("5 items")
        expect(product_card(page, "Alpha Bread").locator(".bt-product-card__quantity-value")).to_have_text("2")

        nav = page.locator("[data-bt-mobile-shopping-nav]")
        expect(nav).to_be_visible()
        expect(nav.locator("[data-bt-mobile-cart-count]")).to_have_text("5")
        expect(nav.locator("[data-bt-mobile-cart-count]")).to_have_attribute("aria-label", "5 items in cart")

        cart_before_navigation = store_api_cart(page)
        quantities_before = cart_quantities(cart_before_navigation)
        assert quantities_before.get("Alpha Milk") == 3, quantities_before
        assert quantities_before.get("Alpha Bread") == 2, quantities_before
        assert sum(quantities_before.values()) == 5, quantities_before
        total_before = cart_before_navigation.get("totals", {}).get("total_price")
        assert total_before and int(total_before) > 0, cart_before_navigation.get("totals")

        cart_url = nav.locator("[data-bt-mobile-cart-link]").get_attribute("href")
        assert cart_url
        page.goto(cart_url, wait_until="networkidle")
        expect(page.locator("body")).to_contain_text("Alpha Milk")
        expect(page.locator("body")).to_contain_text("Alpha Bread")

        cart_on_cart_page = store_api_cart(page)
        quantities_on_cart_page = cart_quantities(cart_on_cart_page)
        assert quantities_on_cart_page.get("Alpha Milk") == 3, quantities_on_cart_page
        assert quantities_on_cart_page.get("Alpha Bread") == 2, quantities_on_cart_page
        assert sum(quantities_on_cart_page.values()) == 5, quantities_on_cart_page
        assert cart_on_cart_page.get("totals", {}).get("total_price") == total_before

        page.goto(BASE_URL, wait_until="networkidle")
        expect(page.locator("[data-bt-product-workspace]")).to_be_visible()
        expect(page.locator("[data-bt-cart-count]")).to_have_text("5 items")
        expect(page.locator("[data-bt-mobile-cart-count]")).to_have_text("5")

        search_for(page, "Alpha Milk")
        expect(product_card(page, "Alpha Milk").locator(".bt-product-card__quantity-value")).to_have_text("3")
        search_for(page, "Alpha Bread")
        expect(product_card(page, "Alpha Bread").locator(".bt-product-card__quantity-value")).to_have_text("2")

        cart_after_return = store_api_cart(page)
        quantities_after_return = cart_quantities(cart_after_return)
        assert quantities_after_return == quantities_on_cart_page
        assert cart_after_return.get("totals", {}).get("total_price") == total_before

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

        # Direct participant evidence: `tomoto` should not look like proof that
        # Tomato is unavailable. Recovery stays explicit and never auto-replaces.
        search_for(page, "tomoto")
        recovery = page.locator(".bt-search-recovery")
        expect(recovery).to_be_visible()
        expect(recovery.locator(".bt-search-recovery__message")).to_contain_text("tomoto")
        suggestion = recovery.locator('button[data-action="search-suggestion"]')
        expect(suggestion).to_have_text("Search Tomato")
        expect(recovery.locator(".bt-search-recovery__browse")).to_have_text("Browse products")
        suggestion.click()
        expect(page.locator("[data-bt-search]")).to_have_value("Tomato")
        expect(page.locator("[data-bt-search]")).to_be_focused()
        expect(page.locator("[data-bt-status]")).to_have_text(re.compile(r"\d+ products? found\."), timeout=10_000)
        expect(product_card(page, "Alpha Tomato")).to_be_visible()

        # If the bounded prefix lookup cannot support a close suggestion, we
        # provide Browse rather than inventing a fuzzy result.
        search_for(page, "mlik")
        recovery = page.locator(".bt-search-recovery")
        expect(recovery).to_be_visible()
        assert recovery.locator('button[data-action="search-suggestion"]').count() == 0
        expect(recovery.locator(".bt-search-recovery__browse")).to_be_visible()

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
