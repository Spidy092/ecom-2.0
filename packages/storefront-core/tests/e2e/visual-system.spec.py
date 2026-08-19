#!/usr/bin/env python3
"""Browser contract for the Modern Grocery engineering-alpha visual system."""

from __future__ import annotations

import os

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
COPPER = "rgb(154, 52, 18)"
INK = "rgb(23, 32, 31)"
WHITE = "rgb(255, 255, 255)"


def css(locator, property_name: str) -> str:
    return locator.evaluate(
        "(el, propertyName) => getComputedStyle(el).getPropertyValue(propertyName).trim()",
        property_name,
    )


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def assert_product_image(card, label: str, viewport_width: int) -> None:
    image_link = card.locator(".bt-product-card__image-link")
    image = image_link.locator("img")
    expect(image_link).to_be_visible()
    expect(image).to_be_visible()

    image.wait_for(state="visible")
    loaded = image.evaluate(
        "(img) => ({ complete: img.complete, naturalWidth: img.naturalWidth, naturalHeight: img.naturalHeight })"
    )
    assert loaded["complete"], (label, viewport_width, loaded)
    assert loaded["naturalWidth"] > 0 and loaded["naturalHeight"] > 0, (
        label,
        viewport_width,
        loaded,
    )

    link_box = image_link.bounding_box()
    image_box = image.bounding_box()
    card_box = card.bounding_box()
    assert link_box and image_box and card_box, (label, viewport_width)

    # Canonical product assets are square and the storefront must preserve a
    # square crop without leaking beyond the card at any commercial target.
    assert abs(link_box["width"] - link_box["height"]) <= 1.5, (
        label,
        viewport_width,
        link_box,
    )
    assert abs(image_box["width"] - image_box["height"]) <= 1.5, (
        label,
        viewport_width,
        image_box,
    )
    assert css(image, "object-fit") == "cover", (label, viewport_width, css(image, "object-fit"))

    epsilon = 1.5
    assert image_box["x"] >= card_box["x"] - epsilon, (label, viewport_width, image_box, card_box)
    assert image_box["y"] >= card_box["y"] - epsilon, (label, viewport_width, image_box, card_box)
    assert image_box["x"] + image_box["width"] <= card_box["x"] + card_box["width"] + epsilon, (
        label,
        viewport_width,
        image_box,
        card_box,
    )
    assert image_box["y"] + image_box["height"] <= card_box["y"] + card_box["height"] + epsilon, (
        label,
        viewport_width,
        image_box,
        card_box,
    )


def exercise_mobile(browser, width: int) -> None:
    context = browser.new_context(viewport={"width": width, "height": 844})
    page = context.new_page()
    page.goto(BASE_URL, wait_until="networkidle")

    workspace = page.locator("[data-bt-product-workspace]")
    expect(workspace).to_be_visible()

    # The page itself must never become horizontally scrollable at alpha targets.
    overflow = page.evaluate(
        "() => document.documentElement.scrollWidth - document.documentElement.clientWidth"
    )
    assert overflow <= 1, (width, overflow)

    # Search remains the strongest form control and gets the branded focus ring.
    search = page.locator("[data-bt-search]")
    search_box = search.bounding_box()
    assert search_box and search_box["height"] >= 52, (width, search_box)
    search.focus()
    assert css(search, "outline-color") == COPPER, (width, css(search, "outline-color"))

    # Aisle state uses the same primary Copper action language.
    expect(page.locator("[data-bt-browse-state]")).to_have_text(
        "Choose a department.", timeout=10_000
    )
    dairy = page.locator("[data-bt-departments] button").filter(has_text="Dairy")
    dairy.click()
    expect(dairy).to_have_attribute("aria-pressed", "true")
    assert css(dairy, "background-color") == COPPER, (width, css(dairy, "background-color"))
    assert css(dairy, "color") == WHITE, (width, css(dairy, "color"))

    # Product imagery uses the same canonical local asset at every mobile target.
    milk = product_card(page, "Alpha Milk")
    expect(milk).to_be_visible()
    assert_product_image(milk, "Alpha Milk", width)

    # Product Add and Cart use one deliberate primary hierarchy.
    add = milk.locator('button[data-action="add"]')
    assert css(add, "background-color") == COPPER, (width, css(add, "background-color"))
    assert css(add, "color") == WHITE, (width, css(add, "color"))
    add.click()

    cart = page.locator(".bt-product-workspace__cart")
    expect(page.locator("[data-bt-cart-count]")).to_have_text("1 item")
    assert css(cart, "background-color") == INK, (width, css(cart, "background-color"))
    assert css(page.locator(".bt-product-workspace__cart-link"), "background-color") == COPPER

    nav = page.locator("[data-bt-mobile-shopping-nav]")
    expect(nav).to_be_visible()
    home = nav.locator('[aria-current="page"]')
    expect(home).to_be_visible()
    assert css(home, "color") == COPPER, (width, css(home, "color"))

    context.close()


def exercise_desktop(browser) -> None:
    width = 1200
    context = browser.new_context(viewport={"width": width, "height": 900})
    page = context.new_page()
    page.goto(BASE_URL, wait_until="networkidle")

    expect(page.locator("[data-bt-product-workspace]")).to_be_visible()
    expect(page.locator("[data-bt-mobile-shopping-nav]")).to_be_hidden()

    overflow = page.evaluate(
        "() => document.documentElement.scrollWidth - document.documentElement.clientWidth"
    )
    assert overflow <= 1, overflow

    produce = page.locator("[data-bt-departments] button").filter(has_text="Produce")
    produce.click()
    expect(produce).to_have_attribute("aria-pressed", "true")

    apple = product_card(page, "Alpha Apple")
    tomato = product_card(page, "Alpha Tomato")
    expect(apple).to_be_visible()
    expect(tomato).to_be_visible()
    assert_product_image(apple, "Alpha Apple", width)
    assert_product_image(tomato, "Alpha Tomato", width)

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
