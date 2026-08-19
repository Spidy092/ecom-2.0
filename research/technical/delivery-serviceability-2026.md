# Delivery Serviceability — 2026 Platform Research

**Date:** 2026-08-18  
**Status:** Engineering-alpha decision for Issue #29

## Customer problem

A grocery shopper should be able to check whether the store serves their area before building a large basket, without the product maintaining a second postcode database that can disagree with WooCommerce checkout.

## Platform findings

WooCommerce already owns geographic shipping configuration through Shipping Zones.

Public APIs/code-reference seams available in WooCommerce 11 include:

- `WC_Shipping_Zones::get_zone_matching_package( $package )`;
- wrapper `wc_get_shipping_zone( $package )`;
- `WC_Shipping_Zone::get_shipping_methods( true )` for enabled methods;
- `WC_Shipping_Method::supports( $feature )`;
- `WC_Shipping_Method::is_available( $package )`.

Woo's zone matcher already normalizes destination country/state/postcode and applies configured country, state, postcode range and wildcard rules in zone order.

References:
- https://woocommerce.github.io/code-reference/classes/WC-Shipping-Zones.html
- https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-shipping-zones.html
- https://woocommerce.github.io/code-reference/classes/WC-Shipping-Method.html

## Key semantic decision

### `served` means geographic service area, not a shipping quote

The early checker must answer:

> Is this destination covered by a Woo shipping zone that has at least one enabled delivery-capable shipping method?

It must **not** claim:
- a particular shipping price;
- free shipping eligibility;
- a delivery date or time slot;
- that every cart/product can be shipped;
- that checkout is guaranteed to expose a rate.

Those facts can depend on cart contents, coupons, minimum amounts and third-party method rules. Checkout remains authoritative.

Recommended shopper wording:

```text
560001
✓ We serve this area
Shipping options are confirmed at checkout.
```

## What counts as delivery-capable

For the coarse serviceability check, inspect the enabled methods configured on the matched zone.

Default rule:
- exclude `local_pickup`;
- exclude `legacy_local_pickup`;
- exclude any method that advertises `supports( 'local-pickup' )`;
- treat another enabled method as delivery-capable for coarse geographic serviceability.

Do **not** call `is_available( $package )` as the deciding rule for the early serviceability result. A method such as free shipping can legitimately depend on cart value/coupon state that does not exist before shopping. Using cart availability here would incorrectly describe an otherwise served area as unsupported.

Provide a filter so third-party integrations can override whether a method should count as delivery without Grovia/BhaivaTech maintaining a hardcoded catalog of courier plugins.

## Zone 0

WooCommerce zone `0` represents locations not covered by more specific zones. It is valid configuration, not automatically an error.

If zone `0` contains an enabled delivery-capable method, an otherwise unmatched destination can be `served`.

If the matched zone has only pickup or no enabled methods, return `not_served`. Do not fall through to another zone after Woo has selected the matching zone.

## Country handling

Use `WC()->countries->get_shipping_countries()` as the store's allowed shipping-country source.

- If exactly one shipping country is configured and the request omits country, use that country automatically.
- If multiple shipping countries exist and country is missing, return `needs_more_location` with `country` required.
- If the supplied country is not in Woo's shipping countries, return `not_served` without running zone matching.

Do not assume the store base country is the shopper's country when the store ships to multiple countries.

## State handling

Postcode alone is unsafe when the store has state-specific zones for the selected country: an empty state can incorrectly fall through to a broader country/zone-0 rule.

Before matching, inspect configured zone locations. If any state-specific zone exists for the selected country and state was not supplied, return `needs_more_location` with `state` required.

This adds a state field only for configurations where it is needed for a correct answer.

## Postcode handling

- accept bounded text only;
- normalize using Woo helpers rather than an India-only or numeric-only regular expression;
- preserve compatibility with international alphanumeric postcodes;
- rely on Woo's existing range/wildcard matcher;
- do not invent a second postcode grammar.

Working maximum input length: 32 characters before normalization.

## Endpoint decision

Create a stateless public Storefront Core endpoint:

```text
POST /wp-json/bhaivatech-storefront/v1/serviceability
```

Use POST even though the operation is read-only so the shopper's postcode is not routinely placed in the page URL/query string.

No authentication or nonce is required because the operation changes no server state and serviceability is intentionally public information.

Request fields:

```json
{
  "country": "IN",
  "state": "KA",
  "postcode": "560001"
}
```

`country` and `state` can be omitted when the server can resolve them safely.

Response exposes only coarse public state:

```json
{
  "status": "served"
}
```

or:

```json
{
  "status": "needs_more_location",
  "required": ["state"]
}
```

Allowed status values:
- `served`;
- `not_served`;
- `needs_more_location`;
- `unknown` only for unexpected platform/configuration failure.

Do not expose:
- zone ID/name;
- method IDs/titles;
- admin settings;
- shipping cost;
- customer/session identity.

## Privacy decision

V1 does not persist serviceability input.

Do not write entered postcode/country/state to:
- user meta;
- options/custom tables;
- analytics/telemetry;
- cookies/local storage;
- order/customer records;
- application logs intentionally.

No server result cache is required for alpha. The zone lookup is bounded and avoiding persistence keeps the privacy model simple. Optimize only after measurement.

## Abuse/performance boundary

The public endpoint performs only:
1. bounded input validation/normalization;
2. allowed-country resolution;
3. state-rule detection;
4. one Woo zone match;
5. inspection of enabled methods on that one zone.

No product queries, geocoding, external requests or full shipping-rate calculation are allowed.

## Required real-Woo fixtures

Engineering alpha must prove:
- Indian postcode/range zone;
- state-specific zone requiring state;
- unsupported shipping country;
- zone-0 fallback with a delivery-capable method;
- specific zone with local pickup only -> `not_served`;
- malformed/oversized postcode rejected;
- response does not reveal zone/method details;
- serviceability checks create no options/user-meta/customer-state writes.

## UI scope after endpoint proof

Only after the endpoint is green:
- compact serviceability check before product search;
- single-country stores ask only for postcode initially;
- multi-country stores expose country;
- state appears only when the endpoint says it is required;
- results use `We serve this area` / `We don't currently serve this area`;
- never show ETA/fee promises in this slice.

## Explicitly deferred

- delivery slots;
- ETA/logistics engine;
- geolocation/maps;
- courier API integrations;
- dynamic shipping quotes before a cart exists;
- remembering shopper location;
- admin UI duplicating Woo shipping zones.
