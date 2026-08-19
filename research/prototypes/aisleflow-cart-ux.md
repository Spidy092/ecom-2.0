# AisleFlow cart UX prototype

**Status:** Prototype validation required  
**Branch:** `feat/aisleflow-foundation`

## Implemented slice

This prototype now exercises the first grocery basket interaction against WooCommerce's supported Store API:

1. load the current WooCommerce cart;
2. keep native WooCommerce add links as the no-JavaScript/failure fallback;
3. intercept simple-product Add only after Store API initialization succeeds;
4. add through `/wc/store/v1/cart/add-item` using a Store API nonce;
5. replace Add with inline `− quantity +` state after authoritative cart response;
6. update/remove through WooCommerce Store API cart operations;
7. reconcile every visible quantity from the full cart response;
8. show a persistent Basket Pulse with item count, total and a cart link;
9. announce add/update/remove actions through a polite live region;
10. lock a product control while its mutation is in flight to prevent rapid-request races.

## Deliberate boundaries

- Only simple product archive/shelf actions are enhanced in this slice.
- Variable/choice-required products continue through WooCommerce's normal product flow.
- WooCommerce owns cart/session/price/stock/purchasability truth.
- No custom Grovia cart database or duplicate cart endpoint exists.
- The feature does not disable Store API nonce checks.
- If initial Store API hydration fails, native WooCommerce add-to-cart remains available.
- The header uses a stable Basket link rather than presenting a second independently updated mini-cart count.

## Mobile validation checklist

- Add each demo product without leaving the shelf.
- Confirm Add becomes `− 1 +` after the response.
- Tap `+` and verify the quantity and Basket Pulse agree.
- Tap `−` at quantity 1 and verify the item returns to Add state.
- Confirm the Basket Pulse count/total matches `/cart/` after navigation.
- Trigger rapid taps and confirm controls lock instead of creating competing requests.
- Test with JavaScript disabled or Store API unavailable and verify native WooCommerce add links still work.
- Use keyboard focus on Add, quantity controls and View basket.
- Verify action announcements are useful and not continuously noisy.

## Validation note

Static source review is complete, but the repository currently has no CI checks attached to this branch and this environment cannot execute the remote Playground interactively. Keep the PR in draft until the branch Playground is exercised on mobile and the cart/session behavior is confirmed end to end.

## Not implemented yet

- delivery postcode/serviceability rules;
- instant product search;
- Shopping List;
- Buy Again;
- variable-product quick choice surface;
- setup wizard;
- automated browser coverage for this interaction.
