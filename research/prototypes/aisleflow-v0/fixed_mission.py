#!/usr/bin/env python3
"""Scripted lower-bound basket missions for AisleFlow V0.

These are deterministic interaction baselines, NOT human usability measurements.
Playwright auto-scrolls and knows selectors; real people will hesitate, search,
choose aisles differently, and may make errors. The value of this script is
regression detection: future UX changes should not silently increase the minimum
number of deliberate basket-building actions.
"""

from __future__ import annotations

import json
from pathlib import Path

from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parent
URL = (ROOT / "index.html").as_uri()
OUT = ROOT / "fixed-mission-result.json"
VIEWPORT = {"width": 390, "height": 844}

FIRST_TIME_TARGETS = {
    "milk-amul": 2,
    "eggs-farm": 2,
    "bread-wheat": 1,
    "rice-sona": 1,
    "dal-toor": 1,
    "oil-sunflower": 1,
    "tomato": 2,
    "banana": 1,
    "chips-bingo": 2,
    "surf": 1,
}


def metric_int(page, selector: str) -> int:
    return int(page.locator(selector).inner_text().strip())


def basket_total_text(page) -> str:
    return page.locator("#pulse-total").inner_text().strip()


def add_quantity(page, scope: str, product_id: str, qty: int) -> None:
    page.locator(f'#{scope} [data-product="{product_id}"][data-action="add"]').click()
    for _ in range(qty - 1):
        page.locator(f'#{scope} [data-product="{product_id}"][data-action="increment"]').click()


def first_time_mission(page) -> dict:
    page.goto(URL)
    page.wait_for_load_state("load")

    page.locator("#postcode").fill("560001")
    page.locator('#delivery-form button[type="submit"]').click()
    assert "available today" in page.locator("#delivery-result").inner_text().lower()

    for product_id, qty in FIRST_TIME_TARGETS.items():
        add_quantity(page, "product-list", product_id, qty)

    # Before opening the basket, the shopper should already be able to discover
    # current count/total from persistent basket state.
    assert page.locator("#dock-cart-count").inner_text().strip() == "14"
    assert basket_total_text(page) == "₹1,500"

    # Remove one product entirely after adding it (Toor Dal ×1).
    page.locator('#product-list [data-product="dal-toor"][data-action="decrement"]').click()
    assert page.locator("#dock-cart-count").inner_text().strip() == "13"
    assert basket_total_text(page) == "₹1,332"

    page.locator('[data-nav="cart"]').click()
    assert metric_int(page, "#cart-summary-items") == 13
    assert page.locator("#cart-summary-total").inner_text().strip() == "₹1,332"

    interactions = metric_int(page, "#metric-interactions")
    surfaces = metric_int(page, "#metric-surfaces")

    # 1 delivery check + 14 quantity/add actions + 1 removal + 1 cart open = 17.
    # Note: opening Cart is a surface interaction and is included in interactions.
    assert interactions == 17, interactions
    assert surfaces == 2, surfaces

    return {
        "scenario": "first-time-fixed-10-product-mission",
        "scripted_lower_bound": True,
        "deliberate_interactions": interactions,
        "surfaces": surfaces,
        "product_detail_transitions": 0,
        "final_item_count": 13,
        "final_subtotal": "₹1,332",
        "delivery_checks": 1,
        "note": "Playwright knows all selectors and auto-scrolls; do not treat as human task performance.",
    }


def returning_mission(page) -> dict:
    page.goto(URL)
    page.wait_for_load_state("load")

    # Facilitator changes research mode, then resets the research meter so the
    # mode-switch itself is not counted as a shopper action.
    page.locator(".research-console summary").click()
    page.locator('[data-mode-button="returning"]').click()
    page.locator("#reset-research").click()
    page.locator(".research-console summary").click()

    # Five known repeat products from This Week / Buy Again.
    repeat_ids = ["milk-amul", "eggs-farm", "bread-wheat", "rice-sona", "oil-sunflower"]
    for product_id in repeat_ids:
        add_quantity(page, "buy-again-list", product_id, 1)

    # Three additional products from the main grocery ledger.
    for product_id in ["tomato", "banana", "chips-bingo"]:
        add_quantity(page, "product-list", product_id, 1)

    # Change one repeated item.
    page.locator('#buy-again-list [data-product="milk-amul"][data-action="increment"]').click()

    # Inspect Shopping List, then continue to Cart.
    page.locator('[data-nav="list"]').click()
    assert page.locator("#list-panel").is_visible()
    page.locator('[data-nav="cart"]').click()
    assert page.locator("#cart-panel").is_visible()

    interactions = metric_int(page, "#metric-interactions")
    surfaces = metric_int(page, "#metric-surfaces")

    assert interactions == 11, interactions
    assert surfaces == 3, surfaces

    return {
        "scenario": "returning-household-reference-mission",
        "scripted_lower_bound": True,
        "deliberate_interactions": interactions,
        "surfaces": surfaces,
        "repeat_items_added_from_this_week": 5,
        "new_items_added": 3,
        "product_detail_transitions": 0,
        "note": "Scenario differs from the first-time mission; interaction totals are not a direct conversion comparison.",
    }


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(headless=True)
        page = browser.new_page(viewport=VIEWPORT)
        first_time = first_time_mission(page)
        returning = returning_mission(page)
        browser.close()

    result = {
        "viewport": VIEWPORT,
        "method": "scripted deterministic lower-bound; not human usability evidence",
        "results": [first_time, returning],
    }
    OUT.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    main()
