# Commercial Entitlement Adapter Contract

**Status:** research/architecture contract — no provider selected  
**Issue:** #14  
**Date:** 2026-08-19

## 1. Purpose

The commercial provider may change. Grocery storefront behavior must not.

The product therefore owns a very small commercial-entitlement boundary. Lemon Squeezy, Freemius, or another provider may implement that boundary, but no provider SDK/API is allowed to spread through Search, Browse, Cart, Saved, Delivery, Setup, WooCommerce data, or theme presentation.

```text
Theme / Storefront Core
        |
        v
Commercial Entitlement Interface
        |
        +--> Lemon Squeezy adapter (candidate)
        |
        +--> Freemius adapter (fallback candidate)
        |
        +--> future provider
```

The adapter answers commercial entitlement questions only. WooCommerce remains authoritative for store commerce and customer shopping behavior.

## 2. V1 product rule

The first paid product is an annual maintained WordPress theme + companion Core plugin package.

A paid entitlement may control:

- activation/site-limit policy;
- maintained download access;
- automatic update eligibility;
- support entitlement state;
- access to the customer billing portal.

A paid entitlement must **not** remotely disable the already-installed storefront merely because a subscription expired, a provider API is temporarily unavailable, or the seller changes providers later.

## 3. Normalized entitlement states

Provider-specific status strings must be mapped into this bounded product-owned model:

```text
unlicensed
active
expired
revoked
site_limit_reached
temporarily_unreachable
configuration_error
```

### `unlicensed`
No usable entitlement has been entered/activated.

### `active`
The install is currently entitled to maintained updates/support according to the paid plan.

### `expired`
The paid maintenance period ended. Installed code remains usable; maintained updates/downloads/support are no longer entitled until renewal.

### `revoked`
The entitlement was explicitly disabled/revoked/refunded according to final commercial policy. Installed GPL-compatible code still must not be sabotaged.

### `site_limit_reached`
The license is otherwise valid but cannot activate another production site under the purchased site limit.

### `temporarily_unreachable`
A provider/network failure prevented refresh. This is not equivalent to `expired` or `revoked`.

### `configuration_error`
The seller/product integration is misconfigured (wrong product scope, missing seller-side update configuration, unsupported response, etc.). Do not blame the customer.

## 4. Provider-neutral interface

Research-level shape, not production PHP yet:

```text
CommercialEntitlementProvider

activate(license_key, site_identity) -> EntitlementResult
refresh(current_instance)            -> EntitlementResult
deactivate(current_instance)         -> EntitlementResult
status()                              -> EntitlementSnapshot
can_receive_updates()                 -> bool
billing_portal_url()                  -> URL|null
```

The adapter may additionally expose provider diagnostics for support, but the rest of the product consumes only normalized results.

## 5. Entitlement snapshot

A normalized snapshot should contain only what the product needs:

```text
provider                 lemon_squeezy | freemius | ...
state                    normalized entitlement state
plan                     bounded internal plan identifier
site_limit               integer|null
site_usage               integer|null
instance_id              provider instance identifier|null
expires_at               ISO-8601|null
last_checked_at           ISO-8601|null
last_error_code           bounded internal error code|null
```

Do not persist full provider API responses.

## 6. License-key storage and display

Treat the buyer license key as a credential.

Requirements:

- never commit it;
- never place it in theme/plugin source;
- never include a full key in System Status/support exports;
- never log it in plaintext;
- mask it in admin UI after activation;
- restrict activation/deactivation actions to an appropriate WordPress capability;
- protect state-changing admin actions with nonce/CSRF controls;
- do not send it to analytics/marketing systems.

Seller API keys and webhook secrets are server/CI secrets and must never ship in customer ZIPs.

## 7. Site identity contract

Do not use a mutable display label alone as site ownership identity.

Before production integration, define and test a normalized site identity with these properties:

- stable enough to reconnect the same install;
- does not expose unnecessary private data;
- supports explicit production/staging/local policy;
- does not silently consume extra production seats because a customer clones to staging;
- cannot be trivially bypassed by loose hostname matching.

Provider-specific staging/dev behavior must be adapted into this product policy rather than leaking directly into product logic.

## 8. Update entitlement contract

WordPress should continue using its normal update UX.

The commercial layer may answer whether a maintained package/version is available, but must not:

- replace WordPress core update screens with a custom installer without necessity;
- expose privileged seller credentials;
- expose permanently reusable public premium-package URLs;
- corrupt update transients when the provider is unavailable;
- make an already-installed store fail because an entitlement refresh timed out.

Required behavior:

```text
provider reachable + active entitlement
  -> normal maintained update may be offered

provider reachable + expired/revoked entitlement
  -> no maintained premium update; installed code continues

provider temporarily unreachable
  -> preserve installed code; show bounded retry/support state
```

## 9. Theme + Core coordination

One customer purchase needs a coherent experience for both artifacts.

The spike must determine whether the selected provider should model this as:

- one commercial product/entitlement with two maintained update channels;
- one bundle containing separate theme/plugin products;
- another provider-supported relationship.

Product requirement regardless of provider:

- buyer should not manage two unrelated subscriptions for one product;
- Theme and Core versions may release independently;
- release automation must know which artifact/version is being published;
- one artifact update must not invalidate the other;
- support can identify Theme/Core entitlement from one commercial customer relationship.

## 10. Webhook/server boundary

Webhooks belong on trusted seller infrastructure, not customer WordPress sites unless there is a compelling reviewed reason.

Requirements:

- verify provider signature/authentication before parsing trusted events;
- process events idempotently because providers can retry delivery;
- store provider event IDs or another bounded deduplication key where needed;
- do not use a webhook to remotely delete/disable customer storefront functionality;
- customer WordPress installs should be able to refresh entitlement independently through the supported licensing/update path.

## 11. Privacy boundary

Commercial entitlement may exchange only data necessary for licensing, maintained downloads, updates, billing, and support entitlement.

It must not send:

- WooCommerce orders/customer records;
- grocery searches;
- cart contents;
- Saved/Shopping List contents;
- delivery postcode/zone checks;
- store revenue;
- arbitrary plugin/theme inventory for marketing;
- admin interaction telemetry.

Optional provider analytics/usage tracking must remain disabled/skipped unless separately justified and explicitly consented to.

## 12. Provider comparison contract

Each candidate is scored against the same evidence categories:

1. Merchant-of-Record / tax burden;
2. checkout + annual subscription;
3. license activation/site limits;
4. staging/dev/multisite policy;
5. Theme + Core update delivery;
6. release automation;
7. beta/staged rollout safety;
8. customer portal;
9. refunds/cancellation/expiry behavior;
10. privacy/telemetry surface;
11. India onboarding/payout operations;
12. migration/export path;
13. direct platform/gateway/payout fees;
14. expected engineering/support burden.

Do not choose based on percentage fee alone.

## 13. Decision rule

Choose **Lemon Squeezy** only if the account-level spike proves that its lower/smaller platform surface does not force us to build fragile WordPress licensing/update/release infrastructure.

Choose **Freemius** if its higher fee demonstrably removes enough recurring WordPress release, staging, multisite, licensing, and support work to lower total business risk.

Choose **RESEARCH AGAIN** if neither candidate meets reliability, privacy, payout, update-delivery, or migration requirements.

## 14. Production gate

No runtime provider integration is allowed until all of the following are true:

- #14 has a recorded final recommendation;
- account-dependent sandbox evidence exists;
- controlled maintained-update delivery has been proven;
- Theme + Core commercial relationship is decided;
- staging/dev/site-limit policy is decided;
- expiry/refund behavior is decided;
- seller onboarding/payout path is viable;
- security/privacy review passes;
- provider-specific code can be contained behind this adapter boundary;
- `docs/DECISIONS.md` records the accepted provider and exit/migration strategy.
