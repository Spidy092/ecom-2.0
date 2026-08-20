#!/usr/bin/env python3
"""Visual-competitiveness contract for the premium Market Ledger layer."""

from __future__ import annotations

import os
from pathlib import Path

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
ARTIFACT_DIR = Path(os.environ.get("BT_VISUAL_ARTIFACT_DIR", "artifacts/visual-competitiveness"))


def css(locator, property_name: str) -> str:
    return locator.evaluate(
        "(el, propertyName) => getComputedStyle(el).getPropertyValue(propertyName).trim()",
        property_name,
    )


def choose_department(page, name: str) -> None:
    button = page.locator("[data-bt-departments] button").filter(has_text=name)
    expect(button).to_be_visible()
    button.click()
    expect(button).to_have_attribute("aria-pressed", "true")


def assert_no_page_overflow(page, width: int) -> None:
    overflow = page.evaluate(
        "() => document.documentElement.scrollWidth - document.documentElement.clientWidth"
    )
    assert overflow <= 1, (width, overflow)


def exercise_mobile(browser, width: int) -> None:
    context = browser.new_context(viewport={"width": width, "height": 844})
    page = context.new_page()
    page.goto(BASE_URL, wait_until="networkidle")
    assert_no_page_overflow(page, width)

    search = page.locator("[data-bt-search]")
    expect(search).to_be_visible()
    search_box = search.bounding_box()
    assert search_box and search_box["height"] >= 56, (width, search_box)
    assert float(css(search, "border-bottom-width").replace("px", "")) >= 2, css(search, "border-bottom-width")
    assert css(search, "box-shadow") == "none", css(search, "box-shadow")

    choose_department(page, "Dairy")
    milk = page.locator(".bt-product-card").filter(has_text="Alpha Milk")
    expect(milk).to_be_visible()

    card_box = milk.bounding_box()
    image_box = milk.locator(".bt-product-card__image-link").bounding_box()
    assert card_box and image_box, (width, card_box, image_box)
    assert image_box["width"] >= (88 if width <= 360 else 104), (width, image_box)
    assert css(milk, "border-radius") == "0px", (width, css(milk, "border-radius"))
    assert css(milk, "box-shadow") == "none", (width, css(milk, "box-shadow"))

    title = milk.locator(".bt-product-card__title")
    price = milk.locator(".bt-product-card__price")
    assert float(css(title, "font-weight")) >= 800, css(title, "font-weight")
    assert float(css(price, "font-weight")) >= 800, css(price, "font-weight")

    add = milk.locator('button[data-action="add"]')
    expect(add).to_be_visible()
    add_box = add.bounding_box()
    assert add_box and add_box["height"] >= 44, (width, add_box)
    add.click()

    cart = page.locator(".bt-product-workspace__cart")
    expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")
    cart_radius = float(css(cart, "border-radius").replace("px", ""))
    assert cart_radius <= 8, (width, cart_radius)

    ARTIFACT_DIR.mkdir(parents=True, exist_ok=True)
    page.screenshot(path=str(ARTIFACT_DIR / f"market-ledger-{width}.png"), full_page=True)
    context.close()


def exercise_desktop(browser) -> None:
    width = 1200
    context = browser.new_context(viewport={"width": width, "height": 900})
    page = context.new_page()
    page.goto(BASE_URL, wait_until="networkidle")
    assert_no_page_overflow(page, width)

    choose_department(page, "Produce")
    cards = page.locator(".bt-product-card")
    assert cards.count() >= 2, cards.count()

    apple = cards.filter(has_text="Alpha Apple")
    expect(apple).to_be_visible()
    card_box = apple.bounding_box()
    image_box = apple.locator(".bt-product-card__image-link").bounding_box()
    assert card_box and image_box, (card_box, image_box)

    # Desktop is an intentional grocery shelf: the image becomes the dominant
    # square block and spans almost the full card content width.
    assert image_box["width"] >= card_box["width"] * 0.84, (card_box, image_box)
    assert abs(image_box["width"] - image_box["height"]) <= 1.5, image_box
    assert css(apple, "border-radius") == "0px"
    assert css(apple, "box-shadow") == "none"

    results = page.locator(".bt-product-workspace__results")
    columns = css(results, "grid-template-columns").split()
    assert len(columns) == 4, columns
    assert css(results, "gap") == "0px", css(results, "gap")

    ARTIFACT_DIR.mkdir(parents=True, exist_ok=True)
    page.screenshot(path=str(ARTIFACT_DIR / "market-ledger-1200.png"), full_page=True)
    context.close()


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        for width in (320, 390, 430):
            exercise_mobile(browser, width)
        exercise_desktop(browser)
        browser.close()


if __name__ == "__main__":
    main()
