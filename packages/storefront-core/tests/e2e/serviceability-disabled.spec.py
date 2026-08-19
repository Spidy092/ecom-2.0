#!/usr/bin/env python3
"""Disabled-shipping smoke for coarse serviceability."""

from __future__ import annotations

import os

from playwright.sync_api import sync_playwright

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
                    body: JSON.stringify({ country: 'IN', postcode: '560001' }),
                });
                return { statusCode: response.status, body: await response.json() };
            }""",
            endpoint,
        )

        assert result["statusCode"] == 200, result
        assert result["body"] == {"status": "not_served"}, result

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
