#!/usr/bin/env python3
"""Live WordPress/WooCommerce cart-session proof for AisleFlow."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("GROVIA_PLAYGROUND_BASE_URL", "http://127.0.0.1:9401")


def card(page, name: str):
    return page.locator("li.product").filter(has_text=name)


def store_cart(page):
    return page.evaluate(
        """
        async () => {
          const response = await fetch('/wp-json/wc/store/v1/cart', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
          });
          if (!response.ok) throw new Error(`Store API cart failed: ${response.status}`);
          return response.json();
        }
        """
    )


def quantities_by_name(cart):
    return {str(item.get("name")): int(item.get("quantity", 0)) for item in cart.get("items", [])}


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()

        page.goto(BASE_URL, wait_until="networkidle")
        page.wait_for_function("document.body.classList.contains('grovia-cart-enhanced')")

        milk = card(page, "Whole Milk")
        eggs = card(page, "Free Range Eggs")
        expect(milk).to_be_visible()
        expect(eggs).to_be_visible()

        # Two different products are intentionally started back-to-back. The
        # deterministic race harness separately proves only one Store API write
        # is on the wire; this live session proves Woo's real cart remains whole.
        milk.locator("a.add_to_cart_button").click()
        eggs.locator("a.add_to_cart_button").click()
        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("1", timeout=10_000)
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("1", timeout=10_000)

        for target in ("2", "3"):
            milk.locator('[data-grovia-quantity-action="increase"]').click()
            expect(milk.locator(".grovia-quantity-control__value")).to_have_text(target, timeout=10_000)

        eggs.locator('[data-grovia-quantity-action="increase"]').click()
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("2", timeout=10_000)

        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(
            re.compile(r"5 items.*18\.40"), timeout=10_000
        )
        assert page.locator("a.added_to_cart:visible").count() == 0

        cart_before_navigation = store_cart(page)
        assert quantities_by_name(cart_before_navigation).get("Whole Milk · 1 L") == 3
        assert quantities_by_name(cart_before_navigation).get("Free Range Eggs · 6") == 2

        # Navigate through the actual Woo cart page and verify the same session
        # is visible there, not merely in Grovia's in-memory snapshot.
        cart_href = page.locator(".grovia-basket-pulse__link").get_attribute("href")
        assert cart_href
        page.goto(cart_href, wait_until="networkidle")
        expect(page.locator("body")).to_contain_text("Whole Milk · 1 L")
        expect(page.locator("body")).to_contain_text("Free Range Eggs · 6")

        cart_on_cart_page = store_cart(page)
        assert quantities_by_name(cart_on_cart_page).get("Whole Milk · 1 L") == 3
        assert quantities_by_name(cart_on_cart_page).get("Free Range Eggs · 6") == 2

        # Checkout must read the same Woo session too. We do not attempt to
        # place an order; this only proves order-summary/cart continuity.
        page.goto(f"{BASE_URL}/checkout/", wait_until="networkidle")
        checkout_cart = store_cart(page)
        assert quantities_by_name(checkout_cart).get("Whole Milk · 1 L") == 3
        assert quantities_by_name(checkout_cart).get("Free Range Eggs · 6") == 2

        # Returning to the storefront must rehydrate product controls and Pulse
        # from Woo's existing session rather than starting a new visual basket.
        page.goto(BASE_URL, wait_until="networkidle")
        page.wait_for_function("document.body.classList.contains('grovia-cart-enhanced')")
        milk = card(page, "Whole Milk")
        eggs = card(page, "Free Range Eggs")
        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("3", timeout=10_000)
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("2", timeout=10_000)
        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(
            re.compile(r"5 items.*18\.40"), timeout=10_000
        )

        # Removal must reconcile every remaining surface from the same Woo cart.
        eggs.locator('[data-grovia-quantity-action="decrease"]').click()
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("1", timeout=10_000)
        eggs.locator('[data-grovia-quantity-action="decrease"]').click()
        expect(eggs.locator("a.add_to_cart_button")).to_be_visible(timeout=10_000)
        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(
            re.compile(r"3 items.*9\.60"), timeout=10_000
        )

        final_cart = store_cart(page)
        assert quantities_by_name(final_cart).get("Whole Milk · 1 L") == 3
        assert "Free Range Eggs · 6" not in quantities_by_name(final_cart)

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
