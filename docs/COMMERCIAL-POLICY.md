# First-Customer Commercial Policy

**Status:** V1 commercial hypothesis for validation  
**Date:** 2026-08-18  
**Working product name:** internal codename only

This document defines the smallest customer promise we should be willing to operate for the first 10–100 paying customers. It is a product/business policy, not legal advice, and must be reviewed against applicable law and the final Merchant of Record terms before public launch.

## 1. One product, one paid plan

### V1 Personal

**Working price:** US$59/year  
**Sites:** 1 production site  
**Billing:** annual recurring subscription

Includes:
- official Grovia-theme release package;
- official Grovia Core companion plugin;
- one Modern Grocery starter site;
- updates while entitlement is active;
- security and compatibility maintenance for supported versions;
- standard product support;
- access to documentation and release notes;
- one production activation;
- one non-production staging/local activation where technically enforceable without abuse.

Do not launch multiple paid tiers until customers create a real reason for them.

## 2. What the license controls

The commercial entitlement controls access to our **maintained official distribution and support**, not whether a customer's existing store is allowed to render.

While active:
- automatic maintained updates are available;
- support is available;
- official premium downloads remain available;
- license-dependent future services, if any, may be available.

When expired/cancelled after the paid period ends:
- the already-installed GPL-compatible product keeps functioning;
- customer content, products, orders and storefront are not disabled;
- no artificial admin lockout or storefront kill switch;
- maintained premium updates stop;
- standard support stops;
- future entitlement-based hosted services may stop if they are clearly disclosed and not required to preserve the existing store.

Never design a licensing failure that can take a production shop offline.

## 3. Activation policy

### Desired customer experience

The 1-site plan should allow:
- **1 production domain**;
- **1 staging/local development instance** associated with the same customer/site where practical.

Examples of non-production environments we may recognize after technical validation:
- `localhost`;
- loopback/local development hosts;
- common `.local`, `.test`, or recognized staging host patterns;
- a clearly marked staging subdomain associated with the production site.

This is a desired policy, not yet a proven Lemon Squeezy implementation. Issue #14 must determine whether this can be delivered reliably without making abuse prevention or support too complex.

### Domain changes

Customers should be able to deactivate an old instance and activate a replacement without opening a support ticket in the normal case.

## 4. Subscription lifecycle

### Active

Customer receives updates/support/official premium distribution.

### Cancelled

Cancellation is normally **cancel-at-period-end**. The customer keeps active entitlement until the paid period finishes.

### Expired

After the entitlement expires:
- installed product continues;
- official maintained updates/support stop;
- customer can renew to restore entitlement.

Lemon Squeezy subscription license keys remain active while the subscription is active and expire when the subscription expires, which matches this policy direction. The implementation must still ensure the WordPress product fails gracefully when entitlement checks cannot be completed.

### Network/API outage

A temporary failure to reach the licensing provider must never disable the existing storefront. Entitlement validation should use conservative caching/grace behavior for update/support checks rather than making runtime storefront rendering dependent on a vendor API.

## 5. Refund policy hypothesis

### Initial purchase

Offer a **14-day refund window** for the first purchase.

Goals:
- reduce risk for a new product with little review history;
- make the buying decision easier;
- surface genuine onboarding/product problems quickly;
- avoid a complicated list of hostile refund exclusions.

A customer asking within the window should not need to prove an elaborate technical failure. We may ask why solely to improve the product, not to trap the customer in the purchase.

### Renewal

Working policy: allow an **accidental-renewal refund request within 7 days** of the renewal charge when there has been no obvious abuse. Validate this against the final billing/MoR setup before publication.

### Abuse

We can refuse repeated/refund-abuse patterns where legally permitted, but do not build the public policy around adversarial wording.

### Mandatory/legal/MoR rights

Applicable consumer law and the Merchant of Record's rights take precedence. Lemon Squeezy currently lets sellers define their own policy but reserves the right to issue refunds within 60 days to prevent chargebacks. Public terms must reflect the final MoR/legal review rather than claiming absolute control over refunds.

## 6. Support promise

### Included

Standard support covers:
- installation/activation problems;
- setup-wizard and starter-site problems;
- bugs in documented Grovia functionality;
- supported WordPress/WooCommerce compatibility issues;
- clarification of documented settings/features;
- reasonable help diagnosing whether Grovia is involved in a conflict;
- license/update-delivery problems.

### Not included

Standard support does not include:
- building the customer's entire store;
- entering products/content for them;
- custom PHP/JS/CSS development;
- bespoke redesign work;
- server administration/hosting migrations;
- fixing unrelated third-party plugins/themes;
- legal, tax, shipping-business or payment-provider consulting;
- guaranteed integration with every WooCommerce extension in existence.

When a third-party conflict is plausible, we should still help identify the boundary and document reproducible incompatibilities instead of replying "not our problem."

## 7. Support service level

For the first 10–100 customers:
- support channel: private ticket/email-style support;
- target first human response: **within 1 business day**;
- no 24/7 promise;
- security-critical reports bypass normal support priority;
- weekends/holidays are not guaranteed support days unless explicitly staffed.

The 1-business-day target is an operating goal, not a financially backed SLA.

If support volume makes this unrealistic, fix the product/docs/process before silently degrading service.

## 8. Compatibility support

Every release must publish the versions actually tested.

Support is strongest for:
- current supported WordPress line(s);
- current/previous supported WooCommerce line(s) defined by our compatibility matrix;
- our declared PHP minimum/recommended version;
- current major Chrome, Firefox, Safari and Edge versions within the documented policy.

We should not promise indefinite compatibility with outdated/EOL PHP, WordPress, WooCommerce or browsers.

## 9. Update policy

### Security

High-impact security fixes are highest priority. Supported customers should receive clear upgrade guidance.

### Compatibility

Track upstream WordPress/WooCommerce releases and test before claiming support.

### Product updates

Use semantic versioning as our communication model. Do not ship constant feature churn merely to make the changelog look active.

### Expired customers

Do not remotely remove or disable features already installed. Expiry stops access to newly maintained official releases/support according to the commercial entitlement policy.

## 10. First sales funnel

Keep V1 extremely short:

```text
Search / content / referral
        ↓
Product landing page
        ↓
Live Modern Grocery demo
        ↓
Clear "Who this is for" + proof
        ↓
$59/year — 1 production site
        ↓
Hosted checkout (MoR)
        ↓
Receipt + license key
        ↓
Download theme + Grovia Core
        ↓
Install / activate
        ↓
Setup wizard
        ↓
Working store
        ↓
Follow-up: setup help + feedback
```

Do not add a complex pricing calculator, sales-call gate, trial infrastructure, upsell maze, or five checkout plans before demand exists.

## 11. Landing-page proof hierarchy

A new product has no trust moat, so the page should prove capability instead of making large claims.

Order of proof:
1. interactive/live demo;
2. short video of the exact setup/shopping flow;
3. reproducible performance test conditions;
4. current compatibility matrix;
5. dependency count / what is required;
6. documentation quality;
7. security/update policy;
8. real early-customer reviews only after they exist;
9. refund/support policy;
10. changelog/release history.

Never use fake testimonials, fake sales counters, fake ratings, or unqualified "#1/fastest" claims.

## 12. First-customer onboarding

For the first 10 customers, intentionally provide higher-touch onboarding because their friction is research.

After purchase:
- send install/setup instructions;
- make system-status/export diagnostics easy;
- ask permission for a short follow-up about setup friction;
- log every ticket as product research;
- fix repeated onboarding confusion in product/docs before automating around it.

Do not manually configure every customer's site; we still need to learn whether the product can stand on its own.

## 13. Customer portal

Prefer the MoR-hosted billing/customer portal for V1 rather than building our own billing dashboard.

The product account area only needs to provide or link to:
- license status;
- active domain/instance status;
- update entitlement;
- documentation/support;
- billing portal link.

Lemon Squeezy's hosted Customer Portal already handles subscription status, cancellation/resume, billing details, payment methods, billing history and related license/download information. This is a strong reason not to build billing UX in V1.

## 14. Commercial data/privacy

The licensing/update system should exchange only what is required for entitlement and delivery.

Do not use the commercial system as hidden product telemetry.

Default rules:
- no grocery shopper data sent to license provider;
- no WooCommerce orders/products/customer data sent for licensing;
- no search terms;
- no analytics piggybacking on update requests;
- do not log full license keys unnecessarily;
- keep provider API secrets server-side and out of customer ZIPs.

## 15. Future pricing gates

Do not add 5-site/agency plans merely because competitors have them.

Create additional tiers only when evidence shows:
- multiple customers ask to use it across several client sites;
- agencies represent meaningful revenue;
- staging/site-management needs are clearly understood;
- support economics remain sustainable.

Possible later structure, not a promise:
- 1 site;
- 5 sites;
- agency/high-site-count.

## 16. Decisions still requiring validation

Before public paid launch:
- validate Lemon Squeezy vs Freemius with Issue #14;
- confirm production + staging activation model;
- confirm final refund wording with MoR/legal requirements;
- confirm support channel/tooling;
- confirm exact compatibility matrix;
- confirm final public product name;
- confirm actual checkout/store approval;
- prove theme + plugin update delivery end to end.

## 17. V1 commercial principle

A customer should understand the offer in one sentence:

> One maintained grocery-first WooCommerce product for one live store, with updates and human support for one year, without risking their store when the subscription ends.
