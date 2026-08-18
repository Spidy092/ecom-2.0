# V1 User Validation Test Card

**Status:** Ready for real participant sessions; no results recorded yet  
**Date:** 2026-08-18  
**Issue:** #5

## Purpose

Validate the product with two different audiences before production implementation:

1. WooCommerce buyers/builders who choose and maintain themes.
2. Mobile grocery shoppers who use the storefront.

Do not treat opinions as proof. Observe tasks first, then ask questions.

## Participant targets

Initial research set:
- 3–5 WooCommerce freelancers/agency/store operators;
- 3–5 small ecommerce/store owners;
- 5–8 regular mobile grocery shoppers.

Avoid using only friends or people with no relevant experience.

## Builder / store-owner questions

Establish context:
- What stores have you built or managed?
- How do you choose a WooCommerce theme?
- Which themes/builders have you used recently?
- What setup, update, performance, compatibility, or support problems have cost you the most time?
- What makes a paid theme worth buying instead of a free one?

Show this proposition without pitching it:

> Grocery-first WooCommerce theme + core plugin focused on fast mobile shopping, repeat purchasing, delivery certainty, simple setup, and a small required-plugin stack.

Ask:
- Who is this for?
- What sounds valuable?
- What sounds unnecessary or risky?
- What would make you reject it?
- Does block-first / no mandatory Elementor help, hurt, or not matter?
- What proof would you need before paying for a new product with no review history?
- Which existing product would you compare it against?

Setup concept task:

> Starting from a clean WordPress install, describe what you expect to do until a local grocery store is ready to replace demo products with real products.

Observe expected steps such as WooCommerce setup, starter-site import, branding, delivery setup, editing, documentation and maintenance.

## Shopper task — first-time mode

Use `research/prototypes/aisleflow-v0/` on a mobile-sized screen.

Reset the prototype research meter and give this fixed basket mission:

```text
Amul Taaza Milk 1 L       ×2
Farm Eggs 6 pcs           ×2
Whole Wheat Bread 400 g   ×1
Sona Masoori Rice 5 kg    ×1
Toor Dal 1 kg             ×1
Fortune Sunflower Oil 1 L ×1
Fresh Tomato 1 kg         ×2
Banana Robusta 6 pcs      ×1
Bingo Potato Chips 90 g   ×2
Surf Excel Matic 1 kg     ×1
```

Also ask the participant to:
- confirm delivery for `560001`;
- remove one product after adding it;
- tell you the basket count/total before opening the basket;
- open the basket.

Record:
- time to first add;
- prototype interaction count;
- surface transitions;
- discovery method used first;
- search terms and aisle changes;
- quantity mistakes/recoveries;
- whether unit/pack is understood;
- whether Basket Pulse and mobile dock are understood;
- hesitation and unexpected behavior.

## Shopper task — returning mode

Reset, switch to Returning mode, then ask the participant to:
- add at least five items from This Week / Buy Again;
- add three different items using Search/Aisles;
- change one repeated item's quantity;
- open Shopping List;
- open basket.

The key question is whether Household Rhythm reduces work or simply adds clutter.

## Post-task questions

Ask only after the task:
- What felt easiest?
- Where did you have to think?
- What did you expect that was missing?
- What was present but unnecessary?
- How did you know an item was added?
- Did you trust the delivery message? Why?
- What did “Aisles” mean before using it?
- What did “List” mean before opening it?
- What would you expect Add to do for a product with multiple pack sizes?
- If you used this weekly, what would you want on the first screen?

## Observation record

Create one anonymous note per participant:

```text
Participant ID:
Group: Builder / Store owner / Shopper
Relevant experience:
Device/browser:
Mode:

Observed behaviors:
-

Metrics:
- First add:
- Interactions:
- Surfaces:
- Errors/recoveries:

Confusion / hesitation:
-

What worked unusually well:
-

Feature requests (not automatically accepted):
-

Researcher interpretation:
-

PRD implication:
Keep / Change / Remove / Needs more evidence
```

## Synthesis

Aggregate repeated evidence instead of writing from memory:

| Observation/problem | Participants | Severity | Frequency | V1 relevance | Action |
| --- | ---: | --- | --- | --- | --- |
| | | | | | |

Severity:
- Critical — prevents the task or trust; production blocker.
- Major — repeated substantial friction; likely V1 change.
- Moderate — useful improvement.
- Minor — polish/preferences.
- Request — feature request without proven underlying problem.

## Decisions this must answer

Before production code:
1. Is Aisle Rail clearer/faster than conventional category navigation?
2. Is Product Ledger density comfortable on mobile?
3. Is Basket Pulse useful or noisy?
4. Is early delivery certainty valuable?
5. Does Household Rhythm materially improve repeat shopping?
6. Is block-first/no mandatory Elementor commercially attractive to buyers?
7. Does the small dependency stack matter in purchase decisions?
8. Is the current V1 valuable enough to pay for?
9. What setup proof/documentation is required?
10. What must change before production implementation?

## Gate

This file is a research protocol, **not user evidence**. Issue #5 stays open until real sessions produce observations.
