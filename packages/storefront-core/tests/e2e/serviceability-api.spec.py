#!/usr/bin/env python3
"""Real WooCommerce shipping-zone smoke for coarse serviceability semantics."""

from __future__ import annotations

import os

from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
ALLOWED_KEYS = {"status", "required"}


def serviceability_endpoint(page) -> str:
    return page.evaluate("() => window.BhaivaTechStorefrontConfig.endpoints.serviceability")


def check(page, payload: dict) -> dict:
    endpoint = serviceability_endpoint(page)
    return page.evaluate(
        """async ({ endpoint, payload }) => {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            let body = null;
            try { body = await response.json(); } catch (error) {}
            return { statusCode: response.status, body };
        }""",
        {"endpoint": endpoint, "payload": payload},
    )


def assert_public_shape(result: dict) -> None:
    assert result["statusCode"] == 200, result
    body = result["body"]
    assert isinstance(body, dict), result
    assert set(body).issubset(ALLOWED_KEYS), body
    serialized = str(body).lower()
    for forbidden in ("zone", "method", "flat_rate", "local_pickup", "instance", "cost"):
        assert forbidden not in serialized, body


def expect_status(page, payload: dict, expected: str, required: list[str] | None = None) -> dict:
    result = check(page, payload)
    assert_public_shape(result)
    assert result["body"]["status"] == expected, (payload, result)

    if required is None:
        assert "required" not in result["body"], result
    else:
        assert result["body"].get("required") == required, result

    return result


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        page.goto(BASE_URL, wait_until="networkidle")

        endpoint = serviceability_endpoint(page)
        assert endpoint, "Serviceability endpoint was not exposed to the storefront config."

        # Multiple shipping countries are configured in the fixture, so postcode
        # alone cannot safely infer a country.
        expect_status(page, {"postcode": "560001"}, "needs_more_location", ["country"])

        # India range zone: Woo's configured postcode range is the geographic
        # source of truth. No shipping quote/ETA detail is exposed.
        expect_status(page, {"country": "IN", "postcode": "560001"}, "served")
        expect_status(page, {"country": "IN", "postcode": "560050"}, "served")

        # Outside the explicit India range, Woo zone 0 contains an enabled
        # flat-rate method and therefore represents a served fallback area.
        expect_status(page, {"country": "IN", "postcode": "570000"}, "served")

        # A state-specific US zone means country + postcode alone is ambiguous;
        # the checker requests state rather than falling through incorrectly.
        expect_status(page, {"country": "US", "postcode": "90210"}, "needs_more_location", ["state"])
        expect_status(page, {"country": "US", "state": "CA", "postcode": "90210"}, "served")
        expect_status(page, {"country": "US", "state": "NY", "postcode": "10001"}, "served")

        # A specifically matched UK zone with local-pickup only is not described
        # as delivery serviceability, and must not fall through to zone 0.
        expect_status(page, {"country": "GB", "postcode": "SW1A 1AA"}, "not_served")

        # Countries outside Woo's configured shipping countries are rejected
        # before zone matching, even though zone 0 exists.
        expect_status(page, {"country": "CA", "postcode": "H2B 2Y5"}, "not_served")

        # Missing postcode is a structured location prompt, not a generic error.
        expect_status(page, {"country": "IN"}, "needs_more_location", ["postcode"])

        # Bounded validation rejects oversized/non-scalar input cheaply.
        oversized = check(page, {"country": "IN", "postcode": "1" * 33})
        assert oversized["statusCode"] == 400, oversized

        nonscalar = check(page, {"country": "IN", "postcode": ["560001"]})
        assert nonscalar["statusCode"] == 400, nonscalar

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
