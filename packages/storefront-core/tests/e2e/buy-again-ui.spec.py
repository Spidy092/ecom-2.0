#!/usr/bin/env python3
"""Browser smoke for the account Buy Again surface."""

from __future__ import annotations

import os
import re
from urllib.parse import urlsplit

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")
SITE_URL = os.environ.get("BT_E2E_SITE_URL", f"{urlsplit(BASE_URL).scheme}://{urlsplit(BASE_URL).netloc}")
PASSWORD = "alpha-saved-pass"


def login(page, username: str) -> None:
    page.goto(f"{SITE_URL}/wp-login.php", wait_until="domcontentloaded")
    page.locator("#user_login").fill(username)
    page.locator("#user_pass").fill(PASSWORD)
    page.locator("#wp-submit").click()
    page.wait_for_load_state("networkidle")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 390, "height": 844})
        page = context.new_page()
        login(page, "alpha-saved-a")
        page.goto(f"{SITE_URL}/?pagename=my-account", wait_until="networkidle")
        link = page.get_by_role("link", name="Buy Again", exact=True).first
        expect(link).to_be_visible()
        link.click()
        expect(page.locator("[data-bt-buy-again]")).to_be_visible()
        expect(page.locator(".bt-buy-again__card").first).to_be_visible(timeout=10000)

        apple = page.locator(".bt-buy-again__card").filter(has_text="Alpha Apple")
        expect(apple).to_contain_text("Bought 1 last time")
        expect(apple.locator("[data-bt-buy-again-add]")).to_have_text("Add again")
        expect(page.locator(".bt-buy-again__card").filter(has_text="Alpha Rice Pack").locator(".bt-buy-again__choose")).to_have_text("Choose options")
        expect(page.locator(".bt-buy-again__card").filter(has_text="Alpha Tomato").locator(".bt-buy-again__state")).to_have_text("Out of stock")

        apple.locator("[data-bt-buy-again-add]").click()
        expect(page.locator("[data-bt-buy-again-status]")).to_have_text(re.compile(r"Added 1 × Alpha Apple to your cart\."), timeout=10000)
        context.close()
        browser.close()


if __name__ == "__main__":
    main()
