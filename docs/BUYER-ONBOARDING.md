# Buyer onboarding contract

Status: engineering alpha

## Objective

A paying store owner should be able to move from installed Theme + Core to a recognizable, launch-reviewable store without hunting through unrelated WordPress menus or learning a proprietary options panel.

The guided path is:

1. Platform and Theme + Core readiness.
2. WooCommerce store basics.
3. Brand identity: logo/site title.
4. Visual starting point: choose a native WordPress style variation.
5. Brand colors and typography through Global Styles.
6. Header and footer through registered template parts.
7. Demo products and product images through WooCommerce/Media Library.
8. Storefront review on desktop/mobile.
9. Launch readiness review.

## Ownership

| Task | Owner | Customer surface |
|---|---|---|
| Logo / site title | WordPress | Site Editor / Settings |
| Style variation | WordPress + Theme defaults | Site Editor > Styles |
| Colors / typography | WordPress Global Styles | Site Editor > Styles |
| Header / footer / navigation | WordPress template parts | Site Editor |
| Product names, prices, stock, images | WooCommerce | Products / Media Library |
| Cart, checkout, payment, shipping, tax | WooCommerce | WooCommerce settings |
| Grocery workflow behavior | Core | Storefront runtime |
| Setup/status guidance | Core | WooCommerce > Store Setup |

## Product rules

- Do not create a second proprietary theme-options panel for settings WordPress already owns.
- Do not rewrite customer-owned Site Editor changes from the setup screen.
- Do not hard-code the generated demo images after starter setup; they are replaceable WooCommerce content.
- Do not treat choosing a style variation as a destructive import.
- Do not change price, stock, cart, order, shipping, payment or tax truth from onboarding UI.
- Do not require Elementor for the V1 onboarding path.
- Never claim a store is production-ready only because presentation tasks are complete; checkout/payment/shipping/legal/business checks remain the merchant's responsibility.

## Guided-link policy

The setup screen may deep-link buyers to supported WordPress/WooCommerce admin surfaces. A deep link is navigation only. It must not silently write user settings or content.

Where WordPress editor URLs are version-sensitive, the setup UI should favor stable admin entry points and plain-language instructions over brittle undocumented query parameters.

## Completion model

V1 onboarding uses a review checklist, not a fake automatic completion percentage. Some tasks cannot be reliably inferred without confusing a customer—for example whether a chosen logo is final, whether navigation wording is appropriate, or whether a store's legal pages are sufficient.

Machine-checkable readiness remains limited to technical facts such as:

- Theme/Core/WooCommerce active;
- required WooCommerce pages available;
- starter resources present;
- product images/media technically valid;
- HTTPS/REST/cron/environment status.

Human-owned customization steps are presented as actions to review, not automatically marked complete based on guesses.

## Launch review

The final onboarding section should send the store owner to verify:

- brand/logo/title;
- chosen visual style and colors;
- header/footer/navigation;
- real product names, prices, stock and images;
- delivery/shipping coverage;
- cart and checkout flow;
- payment configuration;
- tax configuration where applicable;
- store policies/legal copy;
- responsive storefront review.

The product may help surface these checks but must not provide false legal, tax, payment or shipping assurance.
