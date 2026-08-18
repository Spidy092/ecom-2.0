#!/usr/bin/env python3
"""Real browser smoke for Saved-for-later shopper behavior."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
PASSWORD = "alpha-saved-pass"


def search_for(page, query: str) -> None:
    search = page.locator("[data-bt-search]")
    search.fill(query)
    expect(page.locator("[data-bt-status]")).to_have_text(
        re.compile(r"(?:\d+ products? found\.|No exact matches\.)"),
        timeout=10_000,
    )


def product_card(page, name: str):
    return page.locator(".bt-product-card").filter(has_text=name)


def saved_card(page, name: str):
    return page.locator(".bt-saved-card").filter(has_text=name)


def open_saved(page) -> None:
    toggle = page.locator("[data-bt-saved-toggle]")
    if toggle.get_attribute("aria-expanded") != "true":
        toggle.click()
    expect(page.locator("[data-bt-saved-panel]")).to_be_visible()


def close_saved(page) -> None:
    page.locator("[data-bt-saved-close]").click()
    expect(page.locator("[data-bt-saved-panel]")).to_be_hidden()
    expect(page.locator("[data-bt-saved-toggle]")).to_be_focused()


def login(page, username: str) -> None:
    page.goto(f"{BASE_URL}/wp-login.php", wait_until="domcontentloaded")
    page.locator("#user_login").fill(username)
    page.locator("#user_pass").fill(PASSWORD)
    page.locator("#wp-submit").click()
    page.wait_for_load_state("domcontentloaded")
    page.goto(BASE_URL, wait_until="networkidle")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        # Guest: Saved is browser-local and remains distinct from the current Cart.
        guest_context = browser.new_context(viewport={"width": 390, "height": 844})
        guest = guest_context.new_page()
        guest.goto(BASE_URL, wait_until="networkidle")
        expect(guest.locator("[data-bt-cart-count]")).to_have_text("0 items")
        expect(guest.locator("[data-bt-saved-count]")).to_have_text("0")

        search_for(guest, "Alpha Bread")
        bread = product_card(guest, "Alpha Bread")
        save_bread = bread.locator("[data-bt-save-product]")
        expect(save_bread).to_have_text("Save for later")
        expect(save_bread).to_have_attribute("aria-pressed", "false")
        save_bread.click()

        expect(guest.locator("[data-bt-saved-count]")).to_have_text("1")
        expect(product_card(guest, "Alpha Bread").locator("[data-bt-save-product]")).to_have_text("Saved")
        expect(guest.locator("[data-bt-cart-count]")).to_have_text("0 items")

        # Refresh proves guest persistence is browser-local, not session-only memory.
        guest.reload(wait_until="networkidle")
        expect(guest.locator("[data-bt-saved-count]")).to_have_text("1")
        expect(guest.locator("[data-bt-saved-scope]")).to_have_text("Saved on this browser.")

        open_saved(guest)
        bread_saved = saved_card(guest, "Alpha Bread")
        expect(bread_saved).to_be_visible()
        add_to_cart = bread_saved.locator("[data-bt-saved-add-cart]")
        expect(add_to_cart).to_have_text("Add to cart")
        add_to_cart.click()

        # Explicit Saved -> Cart changes Cart but keeps Saved until the shopper
        # explicitly removes it.
        expect(guest.locator("[data-bt-cart-count]")).to_have_text("1 item")
        expect(guest.locator("[data-bt-saved-count]")).to_have_text("1")
        expect(saved_card(guest, "Alpha Bread")).to_be_visible()

        saved_card(guest, "Alpha Bread").locator("[data-bt-remove-saved]").click()
        expect(guest.locator("[data-bt-saved-count]")).to_have_text("0")
        expect(guest.locator("[data-bt-cart-count]")).to_have_text("1 item")
        expect(guest.locator("[data-bt-saved-products]")).to_contain_text("No products saved yet.")
        expect(guest.locator("[data-bt-saved-close]")).to_be_focused()

        close_saved(guest)

        # Choice-required products can be Saved, but Saved must never silently
        # select a variation to make Add look faster.
        search_for(guest, "Alpha Rice")
        rice = product_card(guest, "Alpha Rice Pack")
        rice.locator("[data-bt-save-product]").click()
        expect(guest.locator("[data-bt-saved-count]")).to_have_text("1")
        open_saved(guest)
        rice_saved = saved_card(guest, "Alpha Rice Pack")
        expect(rice_saved.locator(".bt-saved-card__choose")).to_have_text("Choose options")
        assert rice_saved.locator("[data-bt-saved-add-cart]").count() == 0
        guest_context.close()

        # Logged-in shopper: Saved is account-backed and survives refresh without
        # relying on guest local storage.
        account_context = browser.new_context(viewport={"width": 390, "height": 844})
        account = account_context.new_page()
        login(account, "alpha-saved-a")
        expect(account.locator("[data-bt-saved-scope]")).to_have_text("Saved to your account.")
        expect(account.locator("[data-bt-saved-count]")).to_have_text("0", timeout=10_000)

        search_for(account, "Alpha Bread")
        account_bread = product_card(account, "Alpha Bread")
        account_bread.locator("[data-bt-save-product]").click()
        expect(account.locator("[data-bt-saved-count]")).to_have_text("1", timeout=10_000)
        expect(account.locator("[data-bt-cart-count]")).to_have_text("0 items")

        account.reload(wait_until="networkidle")
        expect(account.locator("[data-bt-saved-count]")).to_have_text("1", timeout=10_000)
        open_saved(account)
        expect(saved_card(account, "Alpha Bread")).to_be_visible()

        saved_card(account, "Alpha Bread").locator("[data-bt-remove-saved]").click()
        expect(account.locator("[data-bt-saved-count]")).to_have_text("0", timeout=10_000)
        expect(account.locator("[data-bt-saved-close]")).to_be_focused()
        account_context.close()

        browser.close()


if __name__ == "__main__":
    main()
