#!/usr/bin/env python3
"""Real mobile browser smoke for delivery-area serviceability UI."""

from __future__ import annotations

import os

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def local_storage_keys(page) -> list[str]:
    return page.evaluate("() => Object.keys(window.localStorage).sort()")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        delivery = page.locator("[data-bt-delivery]")
        search = page.locator(".bt-product-workspace__search")
        country = page.locator("select[data-bt-delivery-country]")
        postcode = page.locator("[data-bt-delivery-postcode]")
        submit = page.locator("[data-bt-delivery-submit]")
        result = page.locator("[data-bt-delivery-result]")
        state_select = page.locator("[data-bt-delivery-state-select]")
        state_select_field = page.locator("[data-bt-delivery-state-select-field]")

        expect(delivery).to_be_visible()
        expect(search).to_be_visible()
        delivery_box = delivery.bounding_box()
        search_box = search.bounding_box()
        assert delivery_box and search_box
        assert delivery_box["y"] < search_box["y"], (delivery_box, search_box)
        assert delivery_box["height"] < 300, delivery_box

        # Multi-country stores expose country selection; state remains hidden
        # until Woo's zone semantics say it is required.
        expect(country).to_be_visible()
        expect(postcode).to_be_visible()
        expect(state_select_field).to_be_hidden()
        assert country.locator("option").count() == 4  # blank + IN + US + GB

        config = page.evaluate("() => window.BhaivaTechStorefrontConfig.serviceability")
        assert set(config).issubset({"countries", "singleCountry", "stateOptions"}), config
        serialized_config = str(config).lower()
        for forbidden in ("zone", "flat_rate", "local_pickup", "instance", "cost"):
            assert forbidden not in serialized_config, config

        storage_before = local_storage_keys(page)

        # India range is served, but the UI explicitly defers rates to checkout.
        country.select_option("IN")
        postcode.fill("560001")
        submit.click()
        expect(result).to_have_attribute("data-status", "served")
        expect(result).to_have_text(
            "We serve this area. Shipping options are confirmed at checkout.",
            timeout=10_000,
        )
        assert local_storage_keys(page) == storage_before

        # Any location edit invalidates a previously positive answer immediately.
        postcode.fill("560002")
        expect(result).to_have_text("")
        assert result.get_attribute("data-status") is None

        # Pickup-only country is not described as delivery serviceability.
        country.select_option("GB")
        postcode.fill("SW1A 1AA")
        postcode.press("Enter")
        expect(result).to_have_attribute("data-status", "not_served")
        expect(result).to_have_text("We do not currently serve this area.", timeout=10_000)

        # US has a state-specific zone. The first check requests state and moves
        # keyboard focus to Woo's canonical state selector.
        country.select_option("US")
        postcode.fill("90210")
        submit.click()
        expect(result).to_have_attribute("data-status", "needs_more_location")
        expect(result).to_have_text("Choose a state or region to check this area.", timeout=10_000)
        expect(state_select_field).to_be_visible()
        expect(state_select).to_be_enabled()
        expect(state_select).to_be_focused()
        expect(state_select.locator('option[value="CA"]')).to_have_text("California")

        # Changing state clears the stale prompt; CA then resolves as served.
        state_select.select_option("CA")
        expect(result).to_have_text("")
        submit.click()
        expect(result).to_have_attribute("data-status", "served")
        expect(result).to_contain_text("Shipping options are confirmed at checkout.")

        # Serviceability never persists the shopper's location in local storage.
        assert local_storage_keys(page) == storage_before

        # No rate/ETA claims appear in the serviceability result.
        final_text = result.inner_text().lower()
        for forbidden in ("today", "tomorrow", "free shipping", "$", "₹", "delivery by"):
            assert forbidden not in final_text, final_text

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
