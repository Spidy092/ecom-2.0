#!/usr/bin/env python3
"""Single-shipping-country inference and compact UI smoke for serviceability."""

from __future__ import annotations

import os

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        endpoint = page.evaluate("() => window.BhaivaTechStorefrontConfig.endpoints.serviceability")
        result = page.evaluate(
            """async (endpoint) => {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ postcode: '560001' }),
                });
                return { statusCode: response.status, body: await response.json() };
            }""",
            endpoint,
        )

        assert result["statusCode"] == 200, result
        assert result["body"] == {"status": "served"}, result

        # With exactly one Woo shipping country, the shopper should not need to
        # pick a country before checking a postcode.
        assert page.locator("select[data-bt-delivery-country]").count() == 0
        hidden_country = page.locator('input[type="hidden"][data-bt-delivery-country]')
        expect(hidden_country).to_have_value("IN")
        expect(page.locator(".bt-delivery-check__country-context")).to_have_text("India")

        postcode = page.locator("[data-bt-delivery-postcode]")
        postcode.fill("560001")
        page.locator("[data-bt-delivery-submit]").click()
        delivery_result = page.locator("[data-bt-delivery-result]")
        expect(delivery_result).to_have_attribute("data-status", "served")
        expect(delivery_result).to_have_text(
            "We serve this area. Shipping options are confirmed at checkout.",
            timeout=10_000,
        )

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
