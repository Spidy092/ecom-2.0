# Lemon Squeezy WordPress Commercial Stack Spike

**Status:** Stage A complete from official documentation; account-dependent sandbox tests pending  
**Issue:** #14  
**Date:** 2026-08-18

## 1. Purpose

Determine whether Lemon Squeezy is reliable enough to be the V1 Merchant of Record + subscription + license + WordPress-update platform for one commercial theme and companion plugin.

This spike must test the entire lifecycle, not merely whether checkout renders.

## 2. Stage A — what official documentation already proves

### Test environment exists

Lemon Squeezy stores start in **Test mode**. Official documentation says Test mode supports:
- checkout;
- products/discounts;
- subscriptions;
- license keys;
- webhooks;
- API integrations.

Test and live data/keys are separated.

Source:
- https://docs.lemonsqueezy.com/help/getting-started/test-mode
- https://docs.lemonsqueezy.com/guides/developer-guide/testing-going-live

### Important test-mode limitation

Official documentation states:

> File downloads are disabled for all test mode purchases.

Implication:
- we can safely validate checkout, subscription, license activation/validation/deactivation and webhooks in Test mode;
- we should **not claim a complete WordPress update-download test from sandbox alone**;
- the final package-download/update entitlement path may need a controlled post-activation/live-mode test or vendor-confirmed testing method.

This is a real integration risk because update reliability is critical to the commercial product.

### WordPress theme/plugin update path exists

Official Lemon Squeezy WordPress documentation says premium themes/plugins can receive automatic upgrades using:
- Lemon Squeezy license keys;
- versioned product files;
- its WordPress integration/plugin;
- updater code added to the distributed theme/plugin.

A valid license can receive updates through normal WordPress dashboard update behavior.

Source:
- https://docs.lemonsqueezy.com/help/wordpress/theme-plugin-updates

### License activation model fits a 1-site product

Official License API guidance supports:
- activation limits;
- activation creates a unique instance ID;
- validation using license key + instance ID;
- deactivation removes that instance and decreases activation usage;
- product/store/variant IDs should be validated so keys issued for another product cannot activate ours.

Source:
- https://docs.lemonsqueezy.com/guides/tutorials/license-keys
- https://docs.lemonsqueezy.com/api/license-api/deactivate-license-key

This maps cleanly to a 1-production-site entitlement if we define a robust normalized site-instance naming/identity rule.

### Secret API keys should not ship to customer WordPress sites

The public License API operates with the customer's license key and instance ID for activation/validation/deactivation. Store-wide API keys are used for privileged management APIs and belong only in trusted server/CI infrastructure.

Architecture implication:
- customer package may contain public product/store/variant identifiers;
- customer package may handle the buyer-provided license key/instance ID;
- **seller API keys and webhook secrets never ship in the theme/plugin ZIP**.

## 3. Proposed commercial boundary

```text
Customer WordPress
  |
  +--> Commercial Entitlement Adapter
         |
         +--> activate/validate/deactivate license instance
         +--> ask update service for entitled update metadata
         |
         v
Seller update site / Lemon Squeezy integration
         |
         +--> Lemon Squeezy License API
         +--> versioned release file metadata
         |
         v
Lemon Squeezy
  - checkout
  - subscription
  - Merchant of Record
  - license issuance
  - customer portal
```

Core product modules do not know which commercial provider is used.

```text
Grocery UX / Shopping List / Delivery / Setup
                       X
                       X  no direct dependency
                       X
Commercial Entitlement Adapter
```

## 4. Product-side interface hypothesis

This is a research-level contract, not production code yet.

```text
CommercialEntitlement
- activate(licenseKey, siteInstance)
- deactivate()
- refresh()
- status()
- canReceiveMaintainedUpdates()
```

Possible states:
- unlicensed;
- active;
- expired;
- over_limit;
- revoked/refunded;
- temporarily_unreachable.

Important rule:
`temporarily_unreachable` must not break the already-installed store/theme experience. Network failure in the licensing service is not permission to take a customer's live store offline.

## 5. Commercial expiry policy hypothesis

For a GPL-compatible annual product:
- expiry/cancellation does **not** disable already-installed theme/plugin functionality;
- it ends maintained premium updates/download entitlement and standard support after the paid period;
- the UI should clearly say what expired and how to renew;
- no artificial storefront failure should be introduced to coerce renewal.

This policy must be tested against the selected provider's lifecycle events and final terms.

## 6. Site identity / staging question

Lemon Squeezy exposes activation limits and instances, but the official license model does not by itself define our product's policy for production vs staging/local environments.

We need to decide and test an application-level rule such as:
- production URL consumes a site activation;
- recognized localhost/local development does not permanently consume a production seat, if the provider/integration allows us to implement that reliably;
- staging domains should have a documented treatment rather than forcing customers to constantly deactivate production.

Do not invent loose hostname bypasses that can trivially be abused. Research/test before coding.

## 7. Update lifecycle questions still open

Account/live-mode evidence required for:
- one license granting coordinated entitlement to both theme and companion plugin;
- whether theme + plugin should be separate Lemon products/variants/files or one commercial entitlement with two update channels;
- update metadata cache behavior;
- package URL lifetime/signing;
- update behavior if license API is temporarily unavailable;
- emergency rollback;
- old customer release availability;
- CI upload/release automation;
- update entitlement immediately after renewal/refund/cancellation;
- test/live transition without hard-coded test IDs.

## 8. Privacy/security review — initial

### Allowed minimum data

For licensing/update entitlement, expect only what is required such as:
- license key;
- instance identifier/name;
- public product/store/variant identifiers;
- current product version and WordPress update request context as strictly needed by the updater.

### Explicitly not part of commercial entitlement

Do not send:
- product searches;
- grocery basket/customer orders;
- WooCommerce shopper identity;
- delivery postcodes;
- Shopping List contents;
- store revenue/order data;
- plugin/theme inventory for marketing analytics;
- arbitrary admin usage events.

### Storage

Treat license keys as credentials:
- do not print full keys in logs;
- mask them in support exports;
- protect state-changing license operations with WordPress capabilities/nonces where applicable;
- never expose privileged seller API credentials in the client.

## 9. Comparison pressure from Freemius

Lemon Squeezy only wins if the simpler/lower-cost commercial platform does not force us to reinvent expensive WordPress release operations.

Freemius already advertises:
- multisite licensing;
- automatic updates;
- release management;
- beta versions;
- staged rollouts;
- WordPress-native commercial SDK tooling.

If we end up building most of those ourselves just to save transaction percentage, the V1 decision is wrong.

## 10. Account-dependent Stage B tests

Pending access to a Lemon Squeezy test store:

### Checkout/subscription
- [ ] Create disposable annual 1-site subscription product.
- [ ] Enable license keys with activation limit 1.
- [ ] Complete test checkout with dummy data.
- [ ] Verify test order/subscription/license issuance.
- [ ] Inspect customer portal lifecycle.

### License API
- [ ] Activate instance.
- [ ] Validate correct instance.
- [ ] Reject wrong product/variant scope in our adapter.
- [ ] Reject second activation when limit reached.
- [ ] Deactivate and verify activation usage is released.
- [ ] Exercise invalid/expired/disabled cases.

### Webhooks
- [ ] Create test webhook.
- [ ] Verify signature/authenticity according to vendor docs.
- [ ] Simulate subscription lifecycle events.
- [ ] Verify idempotent event processing design.

### WordPress updater
- [ ] Configure seller-side WordPress integration.
- [ ] Configure disposable theme updater.
- [ ] Configure disposable companion-plugin updater.
- [ ] Prove update detection in a test WordPress install.
- [ ] Determine how package download can be tested given sandbox file-download limitation.
- [ ] Complete controlled entitled download/update test only through a supported vendor path.

## 11. Exit criteria

### Choose Lemon Squeezy if
- updater works reliably for theme + plugin;
- manual release operations are small and automatable;
- staging/activation behavior is customer-friendly;
- privacy boundary stays narrow;
- no critical live-mode-only testing trap remains;
- migration/export path is acceptable.

### Choose Freemius instead if
- coordinated releases/update entitlement require significant custom services;
- staging/multisite handling becomes a recurring support burden;
- staged/beta releases become necessary for safe WooCommerce compatibility updates;
- total engineering/support burden overtakes the fee difference.

### Research again if
Neither provider can meet reliability/privacy/distribution requirements cleanly.

## 12. Current recommendation

**Continue the Lemon Squeezy spike. Do not commit production architecture yet.**

Public documentation is strong enough to justify account-level testing, but the test-mode file-download limitation means automatic update delivery must be proven separately before final provider selection.
