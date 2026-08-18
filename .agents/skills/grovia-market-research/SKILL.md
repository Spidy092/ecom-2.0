---
name: grovia-market-research
description: Researches the WooCommerce theme market before Grovia product or UX decisions. Use before proposing or implementing a major Grovia feature, screen, positioning claim, pricing change, or competitor response.
---

# Grovia Market Research

## Mission

Prevent Grovia from becoming generic. Establish what the market already offers, what users actually struggle with, and the specific advantage Grovia intends to deliver before implementation begins.

## Required inputs

Read:
- `docs/PRD.md`
- `docs/MARKET-RESEARCH.md`
- `docs/DESIGN-PRINCIPLES.md`
- `docs/DECISIONS.md`

Use current primary marketplace/product sources and official WordPress/WooCommerce sources where possible. Use community/support evidence to discover pain, but distinguish anecdote from broad fact.

## Workflow

For the requested feature/screen/decision:

1. Define the customer and task in one sentence.
2. Benchmark at least three relevant current competitors when the decision is market-facing.
3. Capture what each does well — not only weaknesses.
4. Identify commodity behavior that Grovia must not mistake for differentiation.
5. Mine credible support/review/community evidence for repeated friction where useful.
6. Check current WordPress/WooCommerce direction so Grovia does not build functionality the platform already solves or is replacing.
7. Produce 2–3 alternative Grovia approaches.
8. Choose the approach with the best combination of customer value, originality, accessibility, performance, implementation cost and maintenance/security cost.
9. Define at least one measurable success criterion.
10. Update `docs/MARKET-RESEARCH.md` or a dedicated research note when the result materially changes product direction.

## Required output for implementation issues

```text
Customer problem:
Competitors benchmarked:
What they do well:
Commodity expectations:
Observed gap:
Grovia uniqueness thesis:
Alternative approaches considered:
Measurable success criterion:
Why this belongs in V1:
Performance/security/maintenance implications:
Sources/date:
```

## Reject implementation when

- the feature exists only because competitors list it;
- the proposal is a visual clone with different colors;
- no customer problem is documented;
- no meaningful advantage is proposed;
- the advantage cannot plausibly be tested;
- the maintenance/security cost is greater than the V1 value;
- research is stale for a fast-changing dependency/market claim.

## Research integrity

Never fabricate sales, ratings, benchmarks, customer quotes, vulnerability claims, or performance results. Clearly label hypotheses. Do not describe an absence of known vulnerabilities as proof of security.
