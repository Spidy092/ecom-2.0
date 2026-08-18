# AisleFlow V0 — pilot session results

**Status:** Preliminary pilot evidence; revise prototype language before the next research round.
**Date:** 2026-08-18
**Prototype:** `research/prototypes/aisleflow-v0/`
**Raw anonymized exports:** `research/users/results/aisleflow-v0/`

## Privacy

Six browser-local JSON exports were reviewed before inclusion. Each declares:

- `network_telemetry: false`;
- `search_terms_recorded: false`; and
- `postcode_recorded: false`.

The exports contain only anonymous participant codes, interaction events, prototype mode, viewport, and aggregate metrics. No recordings, real names, contact details, search text, or postcode values are included.

## Evidence collected

| Export count | Shopper | WooCommerce builder | Other | Viewport |
| ---: | ---: | ---: | ---: | --- |
| 6 | 3 | 2 | 1 | 1854 × 961 CSS px in every export |

The sessions are exploratory desktop sessions, not the mobile-sized fixed-grocery mission in `research/users/session-kit.md`. Therefore their interaction counts and timing must not be compared with the automated 390 × 844 baseline or used as a performance claim.

Reported first-add times ranged from **15 seconds** to **50 seconds**. This is useful directional evidence only: task instructions, participant experience, and researcher observation notes are incomplete.

## Participant feedback

One shopper reported that the primary shopping flow was good and easy to understand. The same participant could not explain:

1. what the returning-shopping experience meant;
2. the difference between adding/removing an item from the list; and
3. whether List was the same as Cart/Basket.

This is direct qualitative feedback. It should be treated as a prototype-language finding, not proof that the interaction model fails for all shoppers.

## Interpretation

### What appears promising

- Participants were able to add products, alter quantities, and reach cart-related surfaces.
- The primary grocery-shopping loop has no reported blocking failure in this pilot.

### Major clarity risk — repeat shopping

The prototype uses `Returning` in facilitator controls and `This week` / `Household rhythm` in the returning surface. At least one shopper did not understand its purpose.

**Prototype revision candidate:** present the shopper-facing surface as:

```text
Buy again
Products you bought before
```

Do not make `Returning` a shopper-facing navigation concept.

### Major clarity risk — Saved list versus current Cart

`List` and `Save to list` are ambiguous beside the Cart/Basket. A shopper may reasonably think both contain products they intend to buy.

**Prototype revision candidate:**

```text
Bottom navigation: Saved
Page title: Saved for later
Supporting text: Products you saved for a future basket.
Action: Save for later / Remove from saved
```

The Cart remains the explicit current-purchase container. Do not change the underlying V1 scope (one Shopping List) based on this wording revision.

## Decision

**REVISE, then continue research.**

This pilot supports refining the labels before additional testing. It does not yet justify production WordPress/WooCommerce implementation because:

- all captured sessions used a desktop viewport;
- the fixed mobile task was not consistently followed;
- session notes are incomplete; and
- the participant sample is too small and partly unclassified for a product decision.

## Next test round

1. Update the prototype copy using the revision candidates above.
2. Test at 390 × 844 or on real mobile devices.
3. Use a unique anonymous participant code and record the participant group before each run.
4. Run the fixed first-time and returning missions from `research/users/session-kit.md`.
5. Capture concise observation notes, especially whether shoppers can explain `Buy again`, `Saved`, and `Cart` before using them.
6. Reassess after at least five relevant shopper sessions.
