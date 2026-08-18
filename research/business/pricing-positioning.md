# Pricing & Offer Positioning Research

**Status:** Current hypothesis research  
**Date:** 2026-08-18

## 1. The central pricing problem

Our working V1 price is **US$59/year for one production site**.

This price cannot be justified merely by saying "premium WooCommerce theme" because WoodMart currently sells a regular single-end-product license for $59 with future/lifetime theme updates and 6 months of included support through Envato. WoodMart also has enormous social proof and breadth.

Therefore our recurring price must be justified by a different value model:
- specialized grocery outcome;
- continuously maintained WooCommerce compatibility/security;
- focused Theme + Core product rather than generic multipurpose breadth;
- support and official maintained distribution;
- workflow improvements that matter to grocery stores;
- future evidence that store owners/builders value that specialization.

Sources:
- https://themeforest.net/item/woodmart-woocommerce-wordpress-theme/20264492
- https://woodmart.xtemos.com/

## 2. Annual WordPress pricing is normal outside ThemeForest

Current direct-sale WordPress products show that annual maintenance pricing is established:

### GeneratePress GP Premium
- $59/year;
- premium theme;
- updates and support during the active year.

Source: https://generatepress.com/pricing

### Blocksy Pro Personal
- $69/year;
- 1 site;
- one year of updates/support;
- lifetime option also offered.

Source: https://creativethemes.com/blocksy/pricing/

### Astra Pro
- current entry pricing around $69/year depending site-count/offer configuration;
- annual and lifetime purchasing are both offered.

Source: https://wpastra.com/pricing/

### Kadence
- current entry bundle around $69/year;
- higher bundles increase site count and feature breadth;
- separate premium products are also annual.

Source: https://www.kadencewp.com/pricing

## 3. Conclusion on $59/year

**Keep $59/year as a hypothesis. Do not lower it yet.**

Why:
- it sits inside the normal direct-sale premium WordPress range;
- lowering price before value testing gives us no information about willingness to pay;
- support, WooCommerce compatibility and security maintenance create recurring costs;
- a $29–39 price risks positioning the product as another marketplace template rather than maintained commerce software.

But:
- we have no right to assume customers will accept recurring pricing merely because other WordPress companies do;
- WoodMart's $59 one-time/lifetime-update offer is a powerful objection;
- Issue #15 must explicitly test annual-price resistance with target buyers.

## 4. How to explain annual pricing

Do **not** say:

> Pay every year because that's our business model.

The value explanation should be:

> The annual plan funds maintained WooCommerce/WordPress compatibility, security fixes, official updates, support and continued improvement of the grocery-specific product.

This explanation still needs customer validation.

## 5. We should not compete on raw feature value

WoodMart can credibly compare dozens of built-in features against separate plugin costs and show years of reviews, releases and customers.

A new product cannot win that argument.

Our price page should emphasize:
- grocery specialization;
- task proof;
- low required-plugin burden;
- transparent compatibility/security maintenance;
- live demo;
- support/refund clarity.

Do not publish a fake "$900 plugin value" calculation.

## 6. Refund research

There are two established patterns among premium WordPress products:

### 14-day examples
- Blocksy: 14-day refund window;
- Astra: 14-day original-purchase refund window.

Sources:
- https://creativethemes.com/blocksy/terms-and-conditions/
- https://wpastra.com/faq/

### 30-day examples
- GeneratePress: 30-day full/no-questions-asked first-purchase refund;
- Kadence pricing currently markets a 30-day satisfaction / money-back guarantee.

Sources:
- https://generatepress.com/refund-policy/
- https://www.kadencewp.com/pricing

## 7. Refund recommendation for our launch

Change the working initial-purchase hypothesis from **14 days to 30 days**.

Reason:
- we will start with little/no public review history;
- the buyer is taking more product risk than with an established theme;
- a 30-day window is already normal in the WordPress premium-product market;
- a stronger guarantee is a better trust mechanism than artificial discounts or fake social proof;
- first refunds are valuable product/onboarding evidence.

Keep this as a hypothesis until MoR/legal validation.

### Renewal refund

Keep the working **7-day accidental-renewal review/refund** policy as customer-friendly research direction, subject to provider/legal confirmation.

This is more generous than many established products, which often exclude renewal refunds. That is intentional during early trust-building, but abuse and processor/MoR constraints must be validated.

## 8. Staging/dev activation research

Blocksy explicitly excludes many common developer/staging host patterns from counting against its activation limit.

This is a better UX model than making a customer spend their only license activation on staging.

Source: https://creativethemes.com/blocksy/docs/general/licence-utilisation/

### Preferred direction

For a 1-production-site plan:
- production domain counts as the paid site;
- recognized local/dev/staging environments should **not consume the paid production seat** where we can implement this safely;
- normal domain migration should support deactivate -> activate without support intervention.

This improves on the earlier "1 production + exactly 1 staging" hypothesis.

Issue #14 must determine how much of this can be delivered cleanly with the selected license provider.

## 9. Lifetime pricing

Do **not** offer lifetime pricing at launch.

Reasons:
- WooCommerce compatibility/security/support costs recur indefinitely;
- we do not yet know lifetime support economics;
- lifetime pricing can generate early cash while creating long-term obligations before product-market fit;
- it weakens the feedback signal of annual renewal/retention.

We can revisit only after real customer and retention evidence.

## 10. Introductory discounts

Do not launch with permanent fake discounts such as:
- `$99` crossed out, `$59 today` forever;
- countdown timers;
- "Black Friday" pricing outside a real campaign.

For first customers, if we later want a founder benefit, prefer something transparent such as:
- early-customer renewal price protection;
- founder badge/community access;
- longer support onboarding;
- limited beta cohort.

Any founder pricing still needs explicit economic validation.

## 11. Offer hypothesis after this research

```text
US$59/year

1 production store
recognized dev/staging should not consume production seat (desired)
Theme + Core
Modern Grocery starter site
maintained updates
standard support
30-day first-purchase refund hypothesis
7-day accidental-renewal review/refund hypothesis
```

When entitlement expires:
- installed product continues working;
- official maintained updates stop;
- standard support stops;
- no storefront kill switch.

## 12. What Issue #15 must test

Ask buyers directly:
- Is $59/year acceptable for a new niche product with strong live proof but no marketplace review history?
- What would make WoodMart's $59 one-time offer win instead?
- Is specialization valuable enough to renew annually?
- Do buyers care about unlimited/recognized staging environments?
- Does 30-day refund reduce purchase risk?
- What proof makes annual maintenance credible: changelog, compatibility matrix, security history, response times, update cadence, support quality?

## 13. Decision status

- `$59/year`: **KEEP AS HYPOTHESIS**
- 1 production site: **KEEP AS HYPOTHESIS**
- dev/staging: **IMPROVE — aim not to count recognized non-production environments**
- initial refund: **CHANGE HYPOTHESIS FROM 14 TO 30 DAYS**
- lifetime plan: **NO FOR V1**
- permanent launch discount: **NO**
