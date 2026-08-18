# Variable Product Quick-Add Rules

**Status:** V1 technical/UX rule  
**Date:** 2026-08-18  
**Issue:** #9

## 1. Problem

A grocery-first product card must be fast, but WooCommerce products are not all simple.

Examples:
- milk: 500 ml / 1 L;
- rice: 1 kg / 5 kg;
- produce: pack / weight;
- color/flavour/size combinations;
- products with required custom variation attributes.

A fast interface becomes incorrect if it silently selects a variation the shopper did not choose.

## 2. Current WooCommerce facts

WooCommerce Store API product responses expose `has_options`, allowing the frontend to distinguish products that require choices from products that can be added directly.

Store API excludes variations from normal product collections by default. Variations can be queried explicitly with product-type/parent filtering.

Adding a variable product/cart item requires the selected variation/product ID and variation attributes through the supported Store API cart endpoint.

WooCommerce currently documents a `Variation Selector` block under `woocommerce/add-to-cart-with-options-variation-selector`, but it is marked **Beta**. Grovia should learn from/use stable Woo behavior where appropriate but should not make a Beta block a V1 architectural dependency.

Official sources:
- https://developer.woocommerce.com/docs/apis/store-api/resources-endpoints/products/
- https://developer.woocommerce.com/docs/apis/store-api/resources-endpoints/cart/
- https://developer.woocommerce.com/docs/block-development/reference/block-references/

## 3. Product-card behavior classes

### Class A — direct add

Use direct `Add` / inline quantity when all are true:
- product is purchasable;
- product is in a supported stock state;
- `has_options` is false;
- no Grovia/third-party integration has introduced a mandatory shopper choice;
- WooCommerce can add the product through the supported Store API without missing data.

Result:

```text
Milk 1 L
₹68                 [ Add ]

After add:
₹68              [−] 1 [+]
```

This is the fastest path and should remain the dominant grocery interaction.

## 4. Choice-required products

If `has_options` is true, the primary product-card action is **Choose**, not Add.

Never:
- silently choose the cheapest variation;
- silently choose the first variation returned by the API;
- silently choose the merchant's default attributes unless the UX clearly communicates the selected choice and WooCommerce semantics make it valid;
- show `Add` if the request will predictably fail because required attributes are missing.

## 5. Compact chooser eligibility

A variable product may use a compact inline/bottom-sheet chooser only when the choice remains easy to understand.

Recommended V1 eligibility:
- one required attribute; or
- two small attributes whose valid combinations can be presented clearly;
- limited purchasable combinations;
- unit/pack/price changes fit in a compact choice surface;
- variation data can be fetched on demand without loading a large payload into every product row.

Example:

```text
Milk
From ₹34                 [ Choose ]

Choose pack
( ) 500 ml    ₹34
(●) 1 L       ₹68

                     [ Add 1 L ]
```

The selected unit becomes part of the product row after add:

```text
Milk
1 L · ₹68             [−] 1 [+]
```

## 6. Escalate to product detail

Route to the product-detail experience when:
- several required attributes create a complex combination matrix;
- important descriptive/visual information is required to choose safely;
- add-ons/bundles/configuration are owned by another extension and cannot be represented reliably;
- variation payload is large enough that inline loading would harm catalog performance;
- WooCommerce/product integration requires a richer supported flow.

The product card should say `Choose options`, not pretend this is an error/failure.

## 7. Data loading strategy

Do **not** preload every variation of every product in a grocery category.

V1 strategy:
1. normal catalog/search request loads parent product data;
2. `has_options=false` -> direct Add path;
3. `has_options=true` -> render Choose action;
4. when shopper activates Choose, fetch only the data needed for that product/parent;
5. cache safe choice data for the current session where beneficial;
6. always revalidate selected variation through WooCommerce when adding.

This keeps the common simple-product path cheap.

## 8. Store API cart rule

For a selected variation, submit through WooCommerce's supported cart API with:
- the variation/product ID expected by WooCommerce;
- quantity;
- required variation attribute/value pairs using WooCommerce's documented naming rules;
- nonce/cart-token requirements from the current Store API integration.

Never construct or persist a fake Grovia cart line independently of WooCommerce.

## 9. Price / stock / unit truth

The chooser must show the truth for the selected variation:
- current variation price;
- current availability/stock condition;
- current pack/unit label;
- selected image where it materially helps recognition.

Do not calculate final price by applying frontend arithmetic to a parent product if WooCommerce already provides the variation's authoritative price.

## 10. Accessibility

Compact chooser must:
- open from a native button;
- identify product and required choice in its accessible name/heading;
- use real radio/select/button semantics for options;
- expose disabled/unavailable combinations clearly;
- not rely on color alone;
- manage focus on open/close;
- return focus to the initiating product card;
- make the final selected pack/unit visible after add;
- work at 200% zoom/narrow mobile width.

Do not invent a custom select widget unless native/standard controls cannot satisfy the design.

## 11. Performance budget

The variable-product experience must not make the entire product ledger slow.

Rules:
- parent collection remains the default fetch;
- variations load on intent, not globally;
- bound variation result requests to the parent/product context;
- do not duplicate variation datasets in multiple page components;
- measure catalog pages with realistic variable-product counts before release.

## 12. Failure behavior

If variation data fails to load:
- keep the product card stable;
- show a specific retry path;
- offer `View product` as a safe fallback;
- do not guess a variation;
- do not add the parent product if required choices are unresolved.

If a selected variation becomes unavailable between selection and Add, reconcile from WooCommerce and explain that the selected option is no longer available.

## 13. V1 interaction rule

```text
Product result
   |
   +-- has_options = false
   |       -> Add
   |       -> inline quantity
   |
   +-- has_options = true
           -> Choose
           |
           +-- simple choice model
           |       -> compact chooser
           |       -> selected variation Add
           |
           +-- complex/unsupported choice model
                   -> product detail
```

## 14. Why this is better than pretending everything is quick-add

The goal is not the fewest possible taps at any cost. The goal is the fewest **correct and understandable** taps.

For ordinary grocery items, Grovia remains extremely fast. For products where a shopper must make a choice, Grovia makes that choice explicit without forcing every product through a full detail page.

## 15. Decision

V1 approves the three-path model:
- **Add** — simple/no-options products;
- **Choose** — compact eligible variation choices;
- **Choose options / View product** — complex products.

The exact compact-chooser visual design remains subject to prototype/user testing, but production code must follow these truth/authorization/performance rules.
