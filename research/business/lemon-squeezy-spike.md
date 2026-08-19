# Lemon Squeezy WordPress Commercial Stack Spike

**Status:** Stage A refreshed from current official documentation; account-dependent Stage B pending  
**Issue:** #14  
**Account-dependent subtask:** #51  
**Research refreshed:** 2026-08-19

## 1. Purpose

Determine whether Lemon Squeezy is reliable enough to be the V1 Merchant of Record + subscription + license + WordPress-update platform for one commercial WordPress theme and companion Core plugin.

The business decision is not “which provider has the lowest headline percentage?” It is:

> Which provider gives the first paid product the lowest total cost of fees + engineering + release operations + support + failure risk while preserving our privacy and architecture boundaries?

No provider is selected by this document alone.

## 2. Current working product hypothesis

For comparison only; not a public price promise:

- one annual 1-production-site product;
- working price hypothesis: US$59/year;
- Theme + Core are one customer product relationship;
- already-installed GPL-compatible code keeps functioning after maintenance entitlement expires;
- annual entitlement controls maintained downloads, automatic updates and standard support, not the ability of the live storefront to render.

## 3. Stage A — current official evidence for Lemon Squeezy

### Merchant of Record / pricing

Current official pricing:

- base ecommerce fee: **5% + US$0.50 per transaction**;
- no monthly ecommerce charge;
- documented possible additions include:
  - +1.5% international transaction;
  - +1.5% PayPal transaction;
  - +0.5% subscription payment.

Sources:
- https://www.lemonsqueezy.com/pricing
- https://docs.lemonsqueezy.com/help/getting-started/fees

Illustrative fee-only calculation at the US$59/year hypothesis:

```text
US card subscription:
5% + 0.5% subscription + $0.50
= about $3.75 platform fee on $59

International card subscription:
5% + 1.5% international + 0.5% subscription + $0.50
= about $4.63 platform fee on $59
```

This excludes payout-specific effects and is not the provider decision by itself.

### Test environment exists

Lemon Squeezy Test mode officially supports:

- checkout;
- subscriptions;
- license keys;
- webhooks;
- API integrations.

Test/live keys and data are separate.

Source:
- https://docs.lemonsqueezy.com/help/getting-started/test-mode

### Critical test-mode limitation

Official documentation states that **file downloads are disabled for Test-mode purchases**.

Implication:

- checkout/subscription/license/webhook lifecycle can be proven in Test mode;
- a complete entitled WordPress package-download/update test cannot be claimed from Test mode alone;
- final update-delivery validation requires a vendor-supported controlled path after account activation or explicit vendor guidance.

This remains the most important Lemon Squeezy spike risk because maintained WordPress updates are core commercial infrastructure.

### WordPress update path exists

Official Lemon Squeezy WordPress documentation supports automatic updates for premium themes/plugins using:

- Lemon Squeezy license keys;
- versioned product files;
- Lemon Squeezy WordPress integration;
- updater code in the distributed theme/plugin.

Customers with a valid license can receive updates through normal WordPress dashboard behavior.

Source:
- https://docs.lemonsqueezy.com/help/wordpress/theme-plugin-updates
- https://docs.lemonsqueezy.com/help/products/managing-file-versions

### File URLs are not permanent public package URLs

The File API documents signed download URLs that expire after one hour and are rate-limited.

Source:
- https://docs.lemonsqueezy.com/api/files/the-file-object

This is directionally compatible with premium update delivery, but the exact WordPress updater flow still requires real entitlement testing.

### License model

The public License API supports:

- activate;
- validate;
- deactivate;
- activation limits;
- instance IDs;
- expired/disabled status.

Subscription license keys are tied to subscription lifecycle and become expired when the subscription expires.

Sources:
- https://docs.lemonsqueezy.com/api/license-api
- https://docs.lemonsqueezy.com/api/license-api/activate-license-key
- https://docs.lemonsqueezy.com/api/license-api/validate-license-key
- https://docs.lemonsqueezy.com/api/license-api/deactivate-license-key
- https://docs.lemonsqueezy.com/help/licensing/license-keys-subscriptions

This is sufficient for a 1-production-site entitlement model if we define and test our own staging/dev policy safely.

### Subscription lifecycle

Official subscription docs distinguish states including cancelled and expired. Cancellation remains valid until the current billing period ends; access should be retained until expiry.

Sources:
- https://docs.lemonsqueezy.com/help/products/subscriptions
- https://docs.lemonsqueezy.com/guides/developer-guide/managing-subscriptions

This aligns with our product principle that cancellation-at-period-end should not immediately break maintenance entitlement.

### Customer portal

The Customer Portal supports subscription cancellation/resume, payment-method management and billing information. Signed portal URLs are also available through API objects.

Sources:
- https://docs.lemonsqueezy.com/help/online-store/customer-portal
- https://docs.lemonsqueezy.com/guides/developer-guide/customer-portal

Therefore V1 does not need to build its own billing/subscription-management portal.

### Webhook security/testing

Lemon Squeezy signs webhook payloads using an HMAC SHA-256 digest sent in `X-Signature`. Test mode can simulate subscription events and refunded orders.

Sources:
- https://docs.lemonsqueezy.com/help/webhooks/signing-requests
- https://docs.lemonsqueezy.com/help/webhooks/simulate-webhook-events

Our webhook consumer must verify the raw request body before trusting the event and must process retries idempotently.

### Seller API secrets do not belong in customer packages

Privileged Lemon Squeezy API operations use bearer API keys. Customer WordPress license activation/validation/deactivation can use the public License API with customer license/instance data.

Source:
- https://docs.lemonsqueezy.com/guides/developer-guide/getting-started

Architecture rule:

- seller API key + webhook secret: trusted seller/CI/server infrastructure only;
- customer ZIP: no privileged seller API key;
- support export: never expose full customer license key.

## 4. India seller operations — current evidence

Lemon Squeezy currently lists India among supported bank-payout countries, but its official supported-countries documentation notes that new Stripe accounts in India are invite-only and merchants without an approved Stripe account may need PayPal for payouts.

Sources:
- https://docs.lemonsqueezy.com/help/getting-started/supported-countries
- https://docs.lemonsqueezy.com/help/getting-started/getting-paid

This means the actual seller onboarding/payout path must be proven for the legal/business account before selecting Lemon Squeezy.

Do not assume a bank payout path merely because India appears in the country list.

## 5. Freemius comparison — current official evidence

### Pricing

Freemius currently documents:

- 4.7% base software revenue share;
- +2.3% for the WordPress & Templates solution;
- therefore **7.0% WordPress platform share + gateway processing fees**;
- official documentation currently states an average effective gateway rate of about 3.5%.

Sources:
- https://freemius.com/pricing/
- https://freemius.com/help/documentation/getting-started/our-pricing/
- https://freemius.com/help/documentation/getting-started/saas-vs-wordpress-pricing/

Illustrative fee-only comparison using 7% + the documented 3.5% average gateway rate:

```text
$59 × 10.5% ≈ $6.20
```

Actual gateway cost varies, so this is a comparison input, not a quoted guaranteed fee.

### WordPress operational depth

Official Freemius documentation provides materially deeper WordPress-specific infrastructure, including:

- software licensing;
- automatic updates;
- release management;
- beta versions;
- staged rollouts;
- multisite licensing;
- explicit local/staging/dev handling;
- customer/account tooling in WordPress admin.

Sources:
- https://freemius.com/help/documentation/getting-started/saas-vs-wordpress-pricing/
- https://freemius.com/help/documentation/wordpress/software-updates-distribution/
- https://freemius.com/help/documentation/wordpress/license-utilization/

Freemius explicitly documents that recognized localhost/staging/dev sites can avoid consuming production activation limits under its default/recommended configuration. This is a significant support/UX advantage unless we can prove an equally customer-friendly Lemon Squeezy policy with low custom engineering.

### SDK surface / privacy

Freemius WordPress requires its SDK in the product for its WordPress-native feature set.

Official documentation exposes optional opt-in/usage/analytics functionality and supports an `anonymous_mode` that simulates skipping the opt-in flow.

Sources:
- https://freemius.com/help/documentation/wordpress/integration-with-sdk/
- https://freemius.com/help/documentation/wordpress-sdk/integration/integration-snippet/

If Freemius wins, our integration must explicitly preserve the no-usage-telemetry-by-default product policy.

### Payout operations

Freemius currently documents payout options including PayPal, Payoneer, wire and Wise for makers across many countries.

Sources:
- https://freemius.com/help/documentation/selling-with-freemius/your-earnings/
- https://freemius.com/help/documentation/selling-with-freemius/supported-countries/

This broader payout-tooling surface is a practical comparison point for an India-based business.

## 6. Current comparison matrix

| Requirement | Lemon Squeezy | Freemius WordPress | Current interpretation |
| --- | --- | --- | --- |
| Merchant of Record | Yes | Yes/reseller model | Both viable |
| Annual subscription | Yes | Yes | Both viable |
| License activation | Yes | Yes | Both viable |
| 1-site limit | Yes | Yes | Both viable |
| Test checkout | Test mode | Sandbox checkout | Both viable |
| Customer portal | Yes | Yes | Both viable |
| WP theme/plugin updates | Yes, documented | Yes, built into WP SDK | Both viable; Lemon delivery must be proven |
| Test-mode package download | **Disabled for test purchases** | Sandbox/deployment tooling is deeper | Lemon risk |
| Staging/dev handling | Product policy/custom work to prove | Explicit built-in handling | Freemius advantage |
| Multisite | Custom policy/work to prove | Built-in WP support | Freemius advantage |
| Beta/staged rollouts | Custom/release work to prove | Built-in | Freemius advantage |
| Product SDK footprint | Smaller potential surface | Broader WP SDK required | Lemon advantage for lean/privacy surface |
| Usage analytics surface | Not required for licensing | Available; must deliberately skip/disable | Lemon simpler for our default privacy posture |
| Indicative early fees | Lower in common $59 cases | Higher | Lemon advantage |
| India payout options | Bank may depend on Stripe approval; PayPal fallback documented | PayPal/Payoneer/wire/Wise | Freemius operational advantage until LS account proven |

## 7. Commercial adapter boundary

Source of truth:

`docs/COMMERCIAL-ENTITLEMENT-ADAPTER.md`

Provider-specific code may implement:

```text
activate
refresh/validate
deactivate
normalized entitlement status
maintained-update eligibility
billing portal URL
```

It must not own grocery/storefront behavior.

No Lemon Squeezy/Freemius runtime SDK is added during this research spike.

## 8. Theme + Core packaging question

A single paid customer relationship must cover both artifacts without forcing the buyer to manage two unrelated subscriptions.

Stage B must prove the best provider-supported model:

- one product/entitlement with two files/update channels;
- a bundle of theme + Core products under one purchase/license relationship;
- another supported structure.

Required outcome regardless of provider:

- Theme and Core may version/release independently;
- one purchase/renewal relationship;
- updates remain normal WordPress updates;
- support can understand entitlement without asking for two unrelated keys.

## 9. Expiry/refund product policy to prove

Working rule:

- cancelled but not yet expired: maintenance entitlement continues through paid period;
- expired: installed Theme/Core continue functioning; maintained premium updates/new downloads/standard support stop;
- refunded/revoked: exact maintained-download/update policy must be documented, but installed customer storefront must not be remotely sabotaged;
- temporary provider outage: never treat as a reason to disable the live store.

This policy is provider-independent and should remain true if we migrate vendors.

## 10. Account-dependent Stage B

Tracked separately in **#51** so documentation research cannot be mistaken for real commercial evidence.

Required groups:

### Checkout/subscription
- [ ] disposable annual 1-site subscription product;
- [ ] Test-mode checkout;
- [ ] subscription created;
- [ ] license key issued;
- [ ] customer portal behavior observed.

### License API
- [ ] activate;
- [ ] validate;
- [ ] second activation rejected at site limit;
- [ ] deactivate frees site;
- [ ] expired/disabled behavior;
- [ ] wrong-product/variant key rejected by our adapter.

### Webhooks
- [ ] signature verified using raw body + signing secret;
- [ ] subscription create/cancel/resume/expire simulated;
- [ ] refund event simulated;
- [ ] duplicate delivery handling proven idempotent.

### WordPress updater
- [ ] disposable Theme detects update;
- [ ] disposable Core plugin detects update;
- [ ] entitled package delivery proven through a supported path;
- [ ] expired/revoked entitlement stops maintained update access without breaking installed software;
- [ ] release process can publish versions without manual customer-ZIP surgery.

### Seller operations
- [ ] actual merchant account can be activated;
- [ ] actual India payout method is viable;
- [ ] payout timing/fees/currency implications are recorded.

## 11. Evidence discipline

Statuses used by this spike:

```text
DOC-PROVEN     official documentation confirms platform capability
TEST-PROVEN    we executed it in sandbox/test environment
LIVE-CONTROLLED controlled live-mode evidence where sandbox cannot test it
UNVERIFIED     not yet proven
FAILED         tested and did not meet requirement
```

No Stage B checkbox may be marked complete from documentation alone.

Do not use screenshots/blog posts/marketing claims as substitutes for actual account behavior when an acceptance criterion requires execution.

## 12. Current recommendation — 2026-08-19

**Continue testing Lemon Squeezy; do not select it yet.**

Why it remains the lead candidate:

1. lower/simpler early transaction economics in common cases;
2. Merchant of Record;
3. subscription + license + portal are sufficient on paper;
4. documented WordPress update path exists;
5. potentially smaller runtime/vendor surface than Freemius.

Why the decision is still open:

1. Test-mode purchases cannot prove file download/update delivery;
2. staging/dev activation policy is not as turnkey as Freemius;
3. Theme + Core coordinated release model still needs proof;
4. India seller onboarding/payout path needs account-level proof;
5. Freemius' staging/multisite/release-management tooling may save enough support/release work to justify the additional fee.

### Decision thresholds

Choose **LEMON SQUEEZY** only if #51 proves the missing account/update lifecycle with low recurring manual work.

Choose **FREEMIUS** if Lemon Squeezy requires us to build/operate substantial staging, release, update-entitlement or support tooling that Freemius already provides reliably.

Choose **RESEARCH AGAIN** if neither provider meets update reliability, privacy, payout, migration or customer-support requirements.

## 13. Final gate

Do not add vendor SDK/runtime integration to production until:

- #51 is completed with evidence;
- controlled maintained-update delivery is proven;
- Theme + Core entitlement model is decided;
- staging/dev/multisite policy is explicit;
- seller payout/onboarding path is viable;
- privacy/security review passes;
- migration/export path is acceptable;
- #14 records a final recommendation;
- `docs/DECISIONS.md` records the accepted provider and rationale.
