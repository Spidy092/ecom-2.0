# AisleFlow V0 — pilot session results

**Status:** Preliminary human evidence; revise shopper-facing language before the next research round.  
**Date:** 2026-08-18  
**Prototype:** `research/prototypes/aisleflow-v0/`  
**Raw anonymized exports:** `research/users/results/aisleflow-v0/`

## Privacy

Six browser-local JSON exports were reviewed before inclusion. Each declares:

- `network_telemetry: false`;
- `search_terms_recorded: false`; and
- `postcode_recorded: false`.

The exports contain anonymous participant codes, interaction events, prototype mode, viewport, and aggregate metrics. No recordings, real names, contact details, search text, or postcode values are present in the exported JSON.

## Evidence collected

| Export count | Shopper | WooCommerce builder | Other |
| ---: | ---: | ---: | ---: |
| 6 | 3 | 2 | 1 |

### Viewports actually captured

| Viewport | Exports | Notes |
| --- | ---: | --- |
| 1854 × 961 | 4 | exploratory desktop-sized sessions |
| 400 × 874 | 2 | one first-time shopper session and one returning-shopper session |

An earlier synthesis incorrectly stated that all six exports were 1854 × 961. The raw exports are authoritative; this document corrects that error.

The sessions were exploratory and the fixed grocery mission in `research/users/session-kit.md` was not followed consistently. Therefore interaction counts and timing must **not** be compared directly with the automated 390 × 844 fixed-mission baseline or used as a performance/UX superiority claim.

Reported first-add times across the six exports ranged from **15 seconds** to **50 seconds**.

The two mobile-sized shopper exports were:

| Participant | Mode | Viewport | Elapsed | First add | Deliberate interactions | Surfaces | Final cart items |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `s03` | First-time | 400 × 874 | 1:49 | 25s | 48 | 19 | 16 |
| `S04` | Returning | 400 × 874 | 1:07 | 15s | 70 | 6 | 49 |

These are **exploratory traces, not task-completion scores**. For example, the returning mobile trace contains many rapid quantity increments and repeated save toggles, which shows the controls were exercised but does not by itself tell us the participant's intent or whether the interaction was successful.

## Direct participant feedback available

One shopper reported that the primary shopping flow was good and easy to understand. The same participant could not clearly explain:

1. what the returning-shopping experience meant;
2. the difference between adding/removing an item from the list; and
3. whether `List` was the same thing as `Cart` / `Basket`.

This is direct qualitative evidence from the pilot. It is enough to justify a terminology revision, but not enough to conclude that the underlying repeat-shopping or saved-items concepts fail for shoppers generally.

## Raw-event observations

The raw traces show that participants were able to:

- check delivery availability;
- search;
- change aisles;
- add products;
- change quantities;
- open cart-related surfaces;
- open the saved/list surface; and
- exercise Buy Again / repeat-product controls.

No pilot export demonstrates a blocking failure of the core add/quantity/cart interaction.

The `S04` returning-mobile trace includes four rapid `save` actions on the same milk product shortly after the first add. Combined with the direct feedback about List ambiguity, this increases the priority of testing clearer saved-item language. We **cannot** infer the participant's exact intent from event data alone.

## Interpretation

### What appears promising

- The primary add/quantity/cart loop was operable in the pilot.
- Two real mobile-sized traces now exist, so the evidence set is not desktop-only.
- Search, aisle navigation, delivery checking, saved items, and cart surfaces were all exercised.
- There is no current evidence that the grocery interaction thesis should be abandoned.

### Major clarity risk — repeat shopping

The prototype currently uses facilitator/internal terminology such as `Returning`, plus shopper-facing `This week` / `Household rhythm`. At least one shopper did not understand what that experience represented.

**Revision for the next prototype round:**

```text
Buy again
Products you bought before
```

`Returning` can remain an internal research mode if useful, but it must not be a shopper-facing product concept.

### Major clarity risk — Saved versus current Cart

`List`, `Shopping list`, `Save to list`, `Basket`, and `Cart` create too many overlapping container words.

For the next round, use two shopper-facing concepts only:

```text
Saved
Saved for later
Products you saved for a future cart.
Save for later
Remove from saved
```

and

```text
Cart
Your current cart
View cart
Cart updated
```

The underlying V1 scope remains one personal saved/shopping list. This is a terminology and mental-model revision, not feature expansion.

### Terminology rule for V1 research

- **Buy again** = products derived from previous orders.
- **Saved** = products the shopper intentionally keeps for a future purchase.
- **Cart** = products in the current purchase.
- Avoid mixing `Basket` and `Cart` in shopper-facing UI during the next test round.

## Decision

**REVISE, then continue human research.**

The pilot is useful enough to change the prototype, but it does not yet justify production WordPress/WooCommerce implementation because:

- only two captured sessions are mobile-sized;
- the fixed first-time/returning missions were not consistently followed;
- individual researcher observation notes are incomplete;
- three exports still use `anonymous` rather than unique participant IDs; and
- the sample is too small for a product-level proceed decision.

## Next test round

1. Update the prototype to use `Buy again`, `Saved for later`, and one consistent `Cart` term.
2. Test at approximately 390 × 844 or on real mobile devices.
3. Use a unique anonymous participant code (`S05`, `S06`, etc.) before every run.
4. Record the participant group before every run.
5. Run the fixed first-time and returning missions from `research/users/session-kit.md` instead of free exploration.
6. Create one concise observation note per participant; the JSON alone cannot capture hesitation or intent.
7. Before explaining anything, ask the shopper what **Buy again**, **Saved**, and **Cart** mean to them.
8. Reassess after at least five relevant mobile shopper sessions using the revised terminology.
