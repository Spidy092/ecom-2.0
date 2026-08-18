# Round 2 Decision — S05 / S06

**Date:** 2026-08-18  
**Status:** **PROCEED TO ENGINEERING ALPHA WITH CONSTRAINTS**

## Evidence used

This decision uses:
- the earlier six pilot exports;
- the terminology revision from `List / Basket / Cart` to `Saved / Cart`;
- S05 mobile observation + export;
- S06 mobile observation + export;
- automated accessibility and deterministic mission checks.

No claim is made that this is statistically representative research.

## S05

- Shopper, first-time mode, 440×956 viewport.
- Understood `Cart` without facilitator guidance.
- Understood `Saved` as products kept for a later purchase.
- Understood removing products from Saved.
- Reported being able to use both concepts without explanation.

## S06

- Shopper, first-time mode, 412×924 viewport.
- Described the experience as simple and good.
- Understood `Cart` and `Saved for later` without facilitator guidance.
- Liked that Saved stays outside the main shopping surface until requested.
- Typed `tomoto` while searching for Tomato and initially believed Tomato was unavailable.

## Decision

The specific mental-model issue discovered in the first pilot — confusion between the current purchase and future saved items — is considered sufficiently resolved for **engineering alpha** under the available participant constraint.

We will not wait for S07–S09 because no additional participants are currently available. This reduces confidence and must remain visible in release/marketing decisions.

### What is unlocked

We may now build the production engineering skeleton and implement the validated primary grocery loop incrementally:
- product discovery;
- inline simple-product add/quantity;
- persistent Cart feedback;
- Saved for later;
- delivery availability;
- mobile navigation.

### What remains unproven

- Aisle navigation being superior to conventional category navigation;
- Buy Again providing measurable repeat-shopping advantage;
- shopper task-time superiority over competitors;
- willingness to pay / renewal willingness;
- store-owner setup value;
- broad accessibility conformance beyond current automated/manual checks.

These are **not engineering-alpha blockers**, but they block unsupported marketing claims and final launch confidence.

## New product risk from S06 — typo recovery

A shopper entered `tomoto` and interpreted the empty result as product unavailability.

V1 should research/implement bounded search recovery, starting with a helpful no-result state and conservative typo suggestion rather than unbounded fuzzy matching.

Desired UX direction:

```text
No products found for “tomoto”.
Did you mean “tomato”?
[ Search tomato ]

or

No exact match.
Try a shorter product name or browse Produce.
```

Search recovery must remain performant and must not silently substitute a different product.

## Engineering-alpha constraint

Production code should be built on a separate branch/stacked PR from the large research foundation. The internal package name must remain temporary until final product naming is cleared.
