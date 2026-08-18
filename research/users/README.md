# Human Research — Current Workflow

This folder contains V1 user-validation material.

## Current decision

The first AisleFlow pilot produced real human evidence and the current product decision is:

> **REVISE before production.**

See:
- `v1-validation.md` — master evidence/synthesis and production gate;
- `aisleflow-v0-pilot-results-2026-08-18.md` — first pilot synthesis;
- `results/aisleflow-v0/` — raw anonymized pilot exports.

## Canonical next-round files

Use these for all new sessions:

1. **`session-kit.md`** — exact current facilitator protocol and fixed tasks.
2. **`session-note-template.md`** — duplicate once per participant.
3. **AisleFlow V0 prototype** — `../prototypes/aisleflow-v0/`.
4. **`v1-validation.md`** — synthesis file; do not edit conclusions from one participant alone.

Older research planning/tester documents may remain for history, but where wording conflicts, **`session-kit.md` is authoritative**.

## Revised terminology under test

The first pilot showed confusion between List, Basket and Cart, and around the previous returning-shopping language.

The next round tests:

```text
Buy again
Products you bought before

Saved
Saved for later
Save for later
Remove from saved

Cart
Your current cart
```

Do not explain these labels to the participant before measuring what they think they mean.

## Next participant IDs

For the next mobile shopper round use unique IDs:

```text
S05
S06
S07
S08
S09
```

Do not use real names and do not reuse `anonymous`.

## Where to push evidence

### Observation notes

```text
research/users/participants/S05.md
research/users/participants/S06.md
...
```

Create each file by copying `session-note-template.md`.

### Anonymous JSON exports

```text
research/users/results/aisleflow-v0-round2/S05.json
research/users/results/aisleflow-v0-round2/S06.json
...
```

The JSON must come from the prototype's **Export anonymous JSON** action.

## Public-repository privacy rule

Never commit:
- real names;
- email addresses;
- phone numbers;
- real addresses/postcodes;
- payment information;
- passwords/tokens;
- consent forms containing identity;
- identifiable recordings;
- private customer/store data.

If a participant gives sensitive information, keep it out of GitHub entirely.

## Device rule for round 2

Shopper sessions must use:
- a real mobile device; or
- approximately 390 × 844 / 400 × 874 CSS pixels.

Do not mix desktop sessions into the round-2 mobile task comparison.

## Evidence rule

JSON tells us **what controls were activated**. Observation notes tell us **why, where they hesitated, and what they understood**. We need both.

After the five revised mobile sessions are pushed, the master synthesis determines one of:

- `PROCEED`;
- `REVISE`;
- `REJECT / CHANGE THESIS`.
