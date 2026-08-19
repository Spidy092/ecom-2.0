# Grovia Commerce — working codename

> Commercial WordPress + WooCommerce product repository.

**Grovia is only an internal codename.** Preliminary brand research found existing Grovia/GroVia businesses and software-related naming conflicts. Do not treat it as the final public brand. See [`docs/BRAND-RESEARCH.md`](docs/BRAND-RESEARCH.md).

The first objective is deliberately small: create one excellent grocery-first WooCommerce product, win the first paying customers, and expand only from real customer evidence.

## Current phase

**Engineering alpha + buyer/commercial validation.**

The repository now contains an active WordPress/WooCommerce engineering alpha. Shopper-facing implementation may continue only inside the current evidence gates; broad feature expansion is secondary to reliability, one polished Modern Grocery experience, buyer/store-owner validation, setup proof, and commercial readiness.

## Commercial product rule

The goal is **not to be different for its own sake**. The goal is to make relevant buyers understand the product, trust it, choose it, pay for it, and successfully launch/use it.

Before meaningful product work:

1. define the customer or buyer problem;
2. benchmark strong current alternatives;
3. identify what buyers already expect and what competitors already do well;
4. identify a real friction, trust gap, implementation burden, or purchase blocker;
5. decide whether the best answer is familiar, materially better, or genuinely original;
6. define evidence that would show the change matters;
7. only then implement.

Commercial decision rule:

> Familiar where familiarity improves comprehension. Better where evidence shows a problem. Original where originality strengthens the product. Never different just to be different.

Competitors are research inputs, never code/design/asset sources.

## V1 direction

- Grocery / local-food WooCommerce niche
- Block-first WordPress architecture
- WooCommerce + product Core as the only required product dependencies
- One exceptional Modern Grocery starter store
- Mobile-first grocery shopping UX
- Fast product discovery and quantity shopping
- Saved for later / Buy Again flows
- Delivery availability by zone/postcode
- Guided setup/import/system-status experience
- Performance, accessibility, security and maintainability by default
- Clear documentation, compatibility, updates, support, licensing and refund terms before paid launch

## V1 business objective

The success metric is not feature count, demo count, visual novelty, GitHub activity, or social attention.

The V1 objective is:

> The first 10 relevant, unrelated customers choose to pay, can successfully launch/use the product, and give us enough evidence to know what should improve next.

One unrelated paying customer is the first paid-validation milestone. Until that happens, willingness-to-pay interviews and beta feedback are evidence, not commercial validation.

## Documents

- [`docs/PRD.md`](docs/PRD.md) — V1 product requirements
- [`docs/TRD.md`](docs/TRD.md) — technical requirements
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — theme/plugin/platform boundaries
- [`docs/MARKET-RESEARCH.md`](docs/MARKET-RESEARCH.md) — living competitor research + research gate
- [`docs/DESIGN-PRINCIPLES.md`](docs/DESIGN-PRINCIPLES.md) — UI/UX quality bar
- [`docs/STOREFRONT-DESIGN-SYSTEM.md`](docs/STOREFRONT-DESIGN-SYSTEM.md) — current Modern Grocery visual-system contract
- [`docs/COMMERCIAL-CONVERSION-GATE.md`](docs/COMMERCIAL-CONVERSION-GATE.md) — buyer comprehension, trust, willingness-to-pay and paid-launch gate
- [`docs/SECURITY.md`](docs/SECURITY.md) — security baseline
- [`docs/BUSINESS.md`](docs/BUSINESS.md) — lean business/launch model
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — evidence-driven roadmap
- [`docs/RELEASE.md`](docs/RELEASE.md) — CI/build/release plan
- [`docs/LICENSING.md`](docs/LICENSING.md) — licensing/distribution strategy
- [`docs/BRAND-RESEARCH.md`](docs/BRAND-RESEARCH.md) — naming constraints
- [`docs/SKILLS.md`](docs/SKILLS.md) — agent skill inventory/policy
- [`docs/DECISIONS.md`](docs/DECISIONS.md) — accepted/open decisions

## Agent / Codex workflow

Read [`AGENTS.md`](AGENTS.md) before making changes.

Project skills live in `.agents/skills/`:
- `find-skills` — reviewed Vercel Labs skill, installed project-level;
- `grovia-market-research`;
- `grovia-product`;
- `grovia-wordpress-engineering`;
- `grovia-security-review`;
- `grovia-design-critic`.

The required sequence is:

```text
research
  -> product thesis
  -> UX/design
  -> architecture
  -> implementation
  -> tests
  -> buyer/store-owner validation
  -> commercial proof
  -> security/performance/release review
  -> release
```

Code first, justification later is explicitly not the product workflow. A feature that does not improve a customer outcome, buyer decision, support burden, or defensibility should not enter V1 merely because a competitor has it.
