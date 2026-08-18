# V1 Monetization, Licensing & Update Stack

**Status:** Provisional business/technical decision — validate with sandbox spike before production integration  
**Research date:** 2026-08-18

## 1. Goal

Sell one commercial WordPress/WooCommerce product globally with the smallest operational burden possible.

V1 needs:
- checkout;
- annual subscriptions;
- Merchant of Record (tax/VAT/GST handling);
- license keys / site activation limits;
- premium theme/plugin downloads;
- WordPress dashboard updates;
- customer billing/subscription portal;
- refunds/cancellations;
- webhooks/API;
- an exit/migration path;
- no forced product-usage telemetry.

We should **not** build our own billing, tax, card, subscription, or license platform before product-market validation.

## 2. Candidates

### Lemon Squeezy

Current official pricing:
- base ecommerce fee: **5% + US$0.50 per transaction**;
- no monthly ecommerce fee;
- documented possible additions:
  - +1.5% international transaction;
  - +1.5% PayPal transaction;
  - +0.5% subscription payment.

Current relevant capabilities:
- Merchant of Record;
- global tax/VAT handling;
- one-time and recurring products;
- automatic license-key issuance;
- customer portal;
- API/webhooks;
- official WordPress plugin/theme update workflow;
- valid license keys can receive normal WordPress dashboard updates after the product/updater is configured.

WordPress update model:
- enable license keys on the Lemon Squeezy product;
- version uploaded product files;
- integrate the documented updater code into the theme/plugin;
- use Lemon Squeezy's WordPress plugin/API on the seller/update site;
- customer with a valid license receives updates through normal WordPress update UX.

Strengths for our V1:
- comparatively simple pricing;
- MoR removes global sales-tax plumbing;
- enough WordPress-specific functionality for licenses + updates;
- customer portal means we do not build subscription management UI;
- lighter commercial SDK footprint than adopting a broad monetization/analytics system.

Risks / questions to test:
- exact theme + companion-plugin update experience;
- how cleanly staging/development activations can be handled;
- release automation/upload API ergonomics;
- license behavior after annual subscription expiry;
- update-site availability/dependency model;
- rollback/emergency release behavior;
- India payout/onboarding experience for our eventual legal entity;
- whether the WordPress updater has enough hooks/tests for our architecture without becoming fragile.

Official sources reviewed:
- https://www.lemonsqueezy.com/pricing
- https://docs.lemonsqueezy.com/help/getting-started/fees
- https://docs.lemonsqueezy.com/help/wordpress/theme-plugin-updates
- https://docs.lemonsqueezy.com/help/online-store/customer-portal

### Freemius WordPress & Templates

Current official pricing:
- base: **4.7% + gateway fees**;
- WordPress & Templates solution: **+2.3%**;
- therefore WordPress platform share starts at **7.0% + gateway processing fees**.

Official vendor/pricing docs describe gateway fees as variable. Standard examples/reference ceilings include card processing around 2.9% + a fixed fee, plus possible international/subscription additions depending on transaction/payment method.

Relevant WordPress capabilities are significantly deeper:
- software licensing;
- multisite licenses;
- automatic updates;
- release management;
- beta releases;
- staged rollouts;
- WordPress SSO;
- in-dashboard selling;
- add-ons/bundles;
- affiliate/retention/customer portal tooling;
- WordPress SDK.

Privacy consideration:
- Freemius also offers opt-in usage tracking/audience analytics/deactivation feedback.
- Our product policy is **no product-usage telemetry by default**.
- Official SDK documentation supports `anonymous_mode`, which simulates skipping the opt-in flow, and official docs say users can fully skip optional information sharing.
- If Freemius is ever selected, licensing/update integration must be configured so optional marketing/usage tracking is not silently enabled merely because it is available.

Strengths:
- most WordPress-native commercial operating system of the candidates;
- release/staged rollout/multisite features could eliminate substantial engineering later;
- mature license/customer/affiliate ecosystem;
- strong fit once the product has significant revenue and release complexity.

Risks for lean V1:
- materially higher effective transaction cost than Lemon Squeezy for many sales;
- broader SDK/product surface than we need for first 10–100 customers;
- product-analytics features conflict with our privacy posture unless deliberately disabled/skipped;
- switching cost grows if product logic becomes tightly coupled to SDK-specific APIs.

Official sources reviewed:
- https://freemius.com/pricing/
- https://freemius.com/help/documentation/getting-started/our-pricing/
- https://freemius.com/help/documentation/getting-started/saas-vs-wordpress-pricing/
- https://freemius.com/help/documentation/wordpress-sdk/integration/integration-snippet/

### Paddle

Current standard checkout pricing in official terms: **5% + US$0.50**.

Capabilities:
- Merchant of Record;
- subscriptions;
- tax/compliance/fraud;
- checkout;
- customer portal;
- API/webhooks;
- digital product/software-license fulfillment patterns.

Why not first choice for V1:
- excellent general software billing infrastructure;
- but less WordPress-specific than Lemon Squeezy/Freemius for our immediate theme + plugin updater/licensing workflow;
- we would own more WordPress integration/release plumbing.

Official sources reviewed:
- https://developer.paddle.com/get-started/how-paddle-works/digital-products/
- https://www.paddle.com/legal/terms

### WooCommerce Marketplace

Current official developer guidance states:
- partner/vendor application required;
- product review covers functionality, security, compatibility and UX;
- vendor receives **70% of sales** under the current marketplace revenue-share model.

Why it is not V1 commercial infrastructure:
- useful future distribution/trust channel;
- not our owned checkout/customer channel;
- approval/review dependency;
- significantly larger platform share;
- should complement direct sales later rather than determine our core licensing architecture today.

Official source reviewed:
- https://developer.woocommerce.com/docs/woo-marketplace/getting-started/

## 3. Comparison for our current stage

| Requirement | Lemon Squeezy | Freemius WP | Paddle | Woo Marketplace |
| --- | --- | --- | --- | --- |
| Merchant of Record | Yes | Yes/reseller model | Yes | Marketplace handles sale |
| Annual subscriptions | Yes | Yes | Yes | Marketplace terms/product model |
| License keys | Yes | Yes | Custom/fulfillment integration | Marketplace-specific distribution |
| Official WP theme/plugin updates | **Yes** | **Yes, deep** | Custom work | Marketplace delivery/update model |
| Staged/beta WP releases | Basic/manual research needed | **Built in** | Custom | Marketplace process |
| Customer portal | Yes | Yes | Yes | Marketplace customer account |
| Affiliates | Yes | Yes | Not core reason to choose | Marketplace discovery |
| V1 engineering burden | **Low** | Low | Medium | External approval dependency |
| V1 transaction economics | **Strong** | Higher fee for WP stack | Strong | 30% marketplace share |
| Privacy-fit by default | Stronger/minimal product SDK surface | Must deliberately disable/skip optional tracking | Strong | Marketplace-dependent |

## 4. Provisional decision

### Preferred V1 candidate: **Lemon Squeezy**

This is a change from the earlier assumption that Lemon Squeezy would require us to build our own WordPress updater. Current official documentation now provides a WordPress theme/plugin update flow using license keys and its WordPress integration.

Why it leads for the first product:
1. We need first revenue, not the most elaborate monetization platform.
2. It is a Merchant of Record, so we avoid building global sales-tax/payment plumbing.
3. It supports licenses, subscriptions, customer portal and WordPress updates.
4. Its commercial model is cheaper/simpler at our expected early price point than Freemius's full WordPress solution in many common transactions.
5. It leaves us with a smaller vendor-specific product surface while our architecture/product-market fit is still changing.

### Fallback / scale candidate: **Freemius**

Choose Freemius instead if the sandbox spike proves Lemon Squeezy creates too much operational work around:
- theme + plugin coordinated releases;
- staging/multisite activations;
- release channels / staged rollouts;
- update reliability;
- license lifecycle/support.

The extra fee can be rational if it saves enough ongoing engineering/support work after traction.

## 5. Non-negotiable architecture rule

Commercial infrastructure must be behind a small product-owned adapter.

Conceptually:

```text
Our Theme / Core
      |
      v
Commercial License Interface
      |
      +--> Lemon Squeezy adapter (V1 candidate)
      |
      +--> Future Freemius / other adapter if needed
```

Do not scatter vendor SDK/API calls through shopping, delivery, setup or other domain code.

The commercial vendor may answer:
- is this install entitled to updates?
- which plan/license is active?
- can this site activate/deactivate?

It must **not** become the owner of core store/customer-shopping behavior.

## 6. Subscription-expiry product rule to test

Working commercial principle:
- customer keeps the installed GPL-compatible product/code after subscription expiration;
- premium automatic updates, new maintained downloads and support stop when entitlement expires;
- store functionality should not suddenly break merely because an update/support subscription ended;
- cloud-only services, if any exist later, can have separate lifecycle rules clearly disclosed before purchase.

Validate the exact technical implementation and customer messaging before launch.

## 7. Sandbox spike before commitment

Do not integrate a vendor into production until one small spike proves:

1. test checkout for an annual 1-site product;
2. license key issued after purchase;
3. theme activation/deactivation;
4. companion-plugin entitlement behavior;
5. local/staging site policy;
6. WordPress dashboard sees an update;
7. valid license downloads update successfully;
8. expired/cancelled license keeps installed software functioning but stops premium update entitlement;
9. refund/revocation behavior is predictable;
10. customer portal supports cancellation/billing management;
11. CI can publish a test release without manual ZIP surgery;
12. no usage/search/store/customer telemetry is introduced by the licensing path;
13. vendor-specific code is contained behind an adapter;
14. migration/export path is documented.

## 8. Decision gate

After the spike choose:
- **LEMON SQUEEZY** — if updater/licensing/release lifecycle is reliable with low maintenance;
- **FREEMIUS** — if the additional WordPress operating features demonstrably save more work than their extra cost;
- **RESEARCH AGAIN** — if neither meets privacy, reliability, payout or GPL/distribution requirements.

Do not choose solely on transaction percentage. The relevant business metric is total cost: fees + engineering + release work + support + failure risk.
