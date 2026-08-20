#!/usr/bin/env python3
"""Browser regression for AisleFlow's single authoritative cart state."""

from __future__ import annotations

import pathlib
import re

from playwright.sync_api import expect, sync_playwright

ROOT = pathlib.Path(__file__).resolve().parents[4]
CART_UX = ROOT / "packages" / "grovia-core" / "assets" / "js" / "cart-ux.js"

HTML = """
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Cart single truth</title></head>
<body>
<nav class="grovia-bottom-navigation"><a href="#">Cart</a></nav>
<div class="woocommerce">
<ul class="products">
  <li class="product" data-product="milk">
    <h2 class="woocommerce-loop-product__title">Whole Milk · 1 L</h2>
    <a href="?add-to-cart=101" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="101">Add to cart</a>
  </li>
  <li class="product" data-product="eggs">
    <h2 class="woocommerce-loop-product__title">Free Range Eggs · 6</h2>
    <a href="?add-to-cart=102" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="102">Add to cart</a>
  </li>
</ul>
</div>
</body>
</html>
"""

HARNESS = r"""
(() => {
  const prices = { 101: 320, 102: 440 };
  const state = {};
  let nonceNumber = 1;
  let nonce = `nonce-${nonceNumber}`;
  const pending = [];
  const calls = [];

  function cartPayload() {
    const items = Object.keys(state).map((rawId) => {
      const id = Number(rawId);
      return {
        id,
        key: `key-${id}`,
        quantity: state[id],
        quantity_limits: {
          minimum: 1,
          maximum: 99,
          multiple_of: 1,
          editable: true,
        },
      };
    });
    const total = items.reduce((sum, item) => sum + prices[item.id] * item.quantity, 0);
    return {
      items,
      totals: {
        currency_minor_unit: 2,
        currency_code: 'USD',
        total_price: String(total),
      },
    };
  }

  function response(payload) {
    return {
      ok: true,
      headers: { get: (name) => String(name).toLowerCase() === 'nonce' ? nonce : null },
      json: async () => payload,
    };
  }

  function applyMutation(call) {
    const body = call.body;
    if (call.url.endsWith('/add-item')) {
      state[body.id] = (state[body.id] || 0) + Number(body.quantity || 0);
    } else if (call.url.endsWith('/update-item')) {
      const id = Number(String(body.key).replace('key-', ''));
      state[id] = Number(body.quantity || 0);
    } else if (call.url.endsWith('/remove-item')) {
      const id = Number(String(body.key).replace('key-', ''));
      delete state[id];
    }
    nonceNumber += 1;
    nonce = `nonce-${nonceNumber}`;
    return response(cartPayload());
  }

  window.__cartHarness = {
    state,
    calls,
    pending,
    cartPayload,
    resolveNext() {
      if (!pending.length) throw new Error('No pending Store API mutation.');
      const next = pending.shift();
      next.resolve(applyMutation(next.call));
    },
  };

  window.fetch = async (url, options = {}) => {
    const method = String(options.method || 'GET').toUpperCase();
    if (method === 'GET') {
      return response(cartPayload());
    }

    const call = {
      url: String(url),
      nonce: options.headers && options.headers.Nonce ? options.headers.Nonce : '',
      body: options.body ? JSON.parse(options.body) : {},
    };
    calls.push(call);
    return new Promise((resolve) => pending.push({ call, resolve }));
  };
})();
"""

CONFIG = r"""
window.GroviaCartUx = {
  cartEndpoint: 'https://store.test/wp-json/wc/store/v1/cart',
  nonce: 'nonce-1',
  cartUrl: '/cart/',
  strings: {
    add: 'Add to cart',
    added: 'Added',
    updated: 'Updated',
    removed: 'Removed',
    viewBasket: 'View basket',
    item: 'item',
    items: 'items',
    increase: 'Increase quantity for',
    decrease: 'Decrease quantity for',
    genericError: 'Basket update failed. Please try again.'
  }
};
"""


def card(page, name: str):
    return page.locator("li.product").filter(has_text=name)


def resolve_new_call(page, expected_count: int, expected_nonce: str) -> None:
    page.wait_for_function(
        "count => window.__cartHarness.calls.length === count",
        arg=expected_count,
    )
    actual_nonce = page.evaluate("index => window.__cartHarness.calls[index].nonce", expected_count - 1)
    assert actual_nonce == expected_nonce, (expected_count, actual_nonce, expected_nonce)
    page.evaluate("window.__cartHarness.resolveNext()")


def main() -> None:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(channel="chrome", headless=True)
        page = browser.new_page(viewport={"width": 390, "height": 844})
        page.set_content(HTML)
        page.add_script_tag(content=HARNESS)
        page.add_script_tag(content=CONFIG)
        page.add_script_tag(path=str(CART_UX))

        page.wait_for_function("document.body.classList.contains('grovia-cart-enhanced')")

        milk = card(page, "Whole Milk")
        eggs = card(page, "Free Range Eggs")
        milk_add = milk.locator("a.add_to_cart_button")
        eggs_add = eggs.locator("a.add_to_cart_button")

        milk_add.click()
        eggs_add.click()
        page.wait_for_function("window.__cartHarness.calls.length === 1")
        assert page.evaluate("window.__cartHarness.calls[0].nonce") == "nonce-1"
        page.wait_for_timeout(80)
        assert page.evaluate("window.__cartHarness.calls.length") == 1

        page.evaluate("window.__cartHarness.resolveNext()")
        resolve_new_call(page, 2, "nonce-2")

        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("1")
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("1")
        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(re.compile(r"2 items.*7\.60"))

        milk.locator('[data-grovia-quantity-action="increase"]').click()
        resolve_new_call(page, 3, "nonce-3")
        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("2")

        milk.locator('[data-grovia-quantity-action="increase"]').click()
        resolve_new_call(page, 4, "nonce-4")
        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("3")

        eggs.locator('[data-grovia-quantity-action="increase"]').click()
        resolve_new_call(page, 5, "nonce-5")
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("2")

        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("3")
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("2")
        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(re.compile(r"5 items.*18\.40"))

        page.evaluate(
            """
            const button = document.querySelector('[data-product="milk"] a.add_to_cart_button');
            button.textContent = '3 in cart';
            button.classList.add('added');
            const link = document.createElement('a');
            link.className = 'added_to_cart wc-forward';
            link.textContent = 'View cart';
            button.insertAdjacentElement('afterend', link);
            """
        )
        expect(milk.locator("a.added_to_cart")).to_be_hidden()
        expect(milk_add).to_have_text("Add to cart")

        page.evaluate(
            """
            window.__cartHarness.state[101] = 1;
            window.__cartHarness.state[102] = 1;
            document.dispatchEvent(new CustomEvent('wc-blocks_added_to_cart'));
            """
        )
        expect(page.locator(".grovia-basket-pulse__summary")).to_have_text(
            re.compile(r"2 items.*7\.60"), timeout=5_000
        )
        expect(milk.locator(".grovia-quantity-control__value")).to_have_text("1")
        expect(eggs.locator(".grovia-quantity-control__value")).to_have_text("1")

        fallback = browser.new_page(viewport={"width": 390, "height": 844})
        fallback.set_content(HTML.replace("</li>", '<a class="added_to_cart wc-forward">View cart</a></li>', 1))
        fallback.add_script_tag(content="window.fetch = async () => { throw new Error('offline'); };")
        fallback.add_script_tag(content=CONFIG)
        fallback.add_script_tag(path=str(CART_UX))
        fallback.wait_for_timeout(100)
        assert not fallback.evaluate("document.body.classList.contains('grovia-cart-enhanced')")
        expect(fallback.locator("a.added_to_cart").first).to_be_visible()
        expect(fallback.locator('[data-product="milk"] a.add_to_cart_button')).to_have_text("Add to cart")

        fallback.close()
        page.close()
        browser.close()


if __name__ == "__main__":
    main()
