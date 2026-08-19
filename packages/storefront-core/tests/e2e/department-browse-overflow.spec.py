#!/usr/bin/env python3
"""Large-taxonomy smoke for adaptive department chooser mode."""

from __future__ import annotations

import os
import re

from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("BT_E2E_BASE_URL", "http://localhost:8888")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)

        for width in (320, 390, 430):
            context = browser.new_context(viewport={"width": width, "height": 844})
            page = context.new_page()
            page.goto(BASE_URL, wait_until="networkidle")

            browse = page.locator("[data-bt-browse]")
            expect(page.locator("[data-bt-browse-state]")).to_have_text(
                "Choose a department.", timeout=10_000
            )
            expect(browse).to_have_attribute("data-mode", "chooser")
            buttons = page.locator("[data-bt-departments] button")
            assert buttons.count() == 9, (width, buttons.all_inner_texts())

            overflow = page.evaluate(
                "() => document.documentElement.scrollWidth - document.documentElement.clientWidth"
            )
            assert overflow <= 1, (width, overflow)

            if width == 390:
                frozen = buttons.filter(has_text="Frozen")
                expect(frozen).to_be_visible()
                frozen.click()
                expect(page.locator("[data-bt-selected-department]")).to_be_visible()
                expect(page.locator("[data-bt-selected-department-name]")).to_have_text("Frozen")
                expect(page.locator("[data-bt-departments]")).to_be_hidden()
                expect(page.locator("[data-bt-status]")).to_have_text(
                    re.compile(r"\d+ products? in Frozen\."), timeout=10_000
                )
                expect(page.locator(".bt-product-card").filter(has_text="Alpha Bread")).to_be_visible()

                page.locator("[data-bt-show-departments]").click()
                expect(page.locator("[data-bt-departments]")).to_be_visible()
                expect(page.locator("[data-bt-departments] button").filter(has_text="Frozen")).to_be_focused()

            context.close()

        browser.close()


if __name__ == "__main__":
    main()
