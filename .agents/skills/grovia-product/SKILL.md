---
name: grovia-product
description: Keeps Grovia product work aligned with the lean V1 PRD, customer journeys, exclusions, validation milestones, and evidence-driven scope. Use for product planning, issue creation, roadmap changes, acceptance criteria, and scope reviews.
---

# Grovia Product Skill

## Read first

- `docs/PRD.md`
- `docs/BUSINESS.md`
- `docs/ROADMAP.md`
- `docs/DECISIONS.md`
- `docs/MARKET-RESEARCH.md`

## Product principle

One excellent grocery-first product -> first paying customers -> evidence -> expansion.

Do not optimize for feature count, demo count, or marketplace screenshots.

## V1 protected outcome

A store owner can launch one excellent Modern Grocery storefront with a small dependency stack. A shopper can discover, add, adjust, confirm delivery, checkout, save/rebuy products, and navigate efficiently on mobile.

## Before accepting scope

Ask:
- Which V1 customer problem does this solve?
- What evidence shows the problem matters?
- What existing V1 feature could solve it more simply?
- Is it commodity or differentiating?
- What does it cost to maintain/security-test/support?
- Does adding it delay the first paying customer?

## V1 exclusions

Reject by default:
- multi-vendor marketplace;
- custom payment platform;
- AI chatbot;
- dozens of demos;
- multiple mandatory builders;
- generic product comparison;
- advanced fleet/logistics;
- broad SaaS back office;
- agency portal;
- unrelated marketing widgets.

Changing an exclusion requires an explicit product decision, not an incidental code change.

## Requirement format

Every major story should contain:

```text
User:
Problem:
Desired outcome:
Research/benchmark:
Uniqueness thesis:
Acceptance criteria:
Accessibility criteria:
Performance constraint:
Security/privacy notes:
Out of scope:
Measurement:
```

## Validation milestones

1. first unrelated paying customer;
2. 10 paying customers with onboarding/support evidence;
3. 50 customers with repeated issue patterns;
4. 100 customers before broad ecosystem expansion.

Prioritize work that moves the nearest milestone rather than speculative future breadth.
