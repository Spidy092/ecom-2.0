# Grovia Commerce — working codename

> Commercial WordPress + WooCommerce product planning repository.

**Grovia is only an internal codename.** Preliminary brand research found existing Grovia/GroVia businesses and software-related naming conflicts. Do not treat it as the final public brand. See [`docs/BRAND-RESEARCH.md`](docs/BRAND-RESEARCH.md).

The first objective is deliberately small: create one exceptional grocery-first WooCommerce product, win the first paying customers, and expand only from real customer evidence.

## Current phase

**Engineering alpha — first core shopping vertical slice implemented and validated.**

The research and engineering gates have been reviewed. The repository now contains an installable block theme, a WooCommerce-dependent Core plugin, a disposable WordPress Playground, and automated REST/browser smoke coverage. It is an engineering alpha, not a paid beta: production compatibility, commercial operations, and broader tester evidence remain before launch.

## Non-generic rule

Before any meaningful screen, component or feature is built:

1. define the customer problem;
2. benchmark strong current competitors;
3. identify what they already do well;
4. identify a real gap/friction;
5. define Grovia's materially better/original approach;
6. define how we will measure the advantage;
7. only then implement.

Competitors are research inputs, never code/design/asset sources.

## V1 direction

- Grocery / organic WooCommerce niche
- Block-first WordPress architecture
- WooCommerce + Grovia Core as the only required product dependencies
- One exceptional Modern Grocery starter store
- Mobile-first grocery shopping UX
- Fast product discovery and quantity shopping
- Shopping List / Buy Again flows
- Delivery availability through WooCommerce Shipping Zones
- Guided setup wizard
- Performance, accessibility, security and maintainability by default

## Implemented alpha slice

- Modern Grocery block-theme templates, parts, tokens and mobile shopping dock;
- WooCommerce product search/product collection composition points;
- public Shipping-Zone-backed delivery checker with input validation;
- custom-table, user-scoped Shopping List REST routes and product-card Save to list action;
- HPOS-safe Buy Again service and authenticated endpoint;
- capability-protected three-step setup wizard;
- cart feedback reconciled from the WooCommerce Store API;
- disposable Playground Blueprint, authenticated Playwright smoke test, CI validation, release ZIPs and checksums;
- deterministic skill lockfile.

The remaining work is tracked in [`docs/ROADMAP.md`](docs/ROADMAP.md): quantity-control polish, deeper WooCommerce compatibility coverage, starter-site/importer hardening, tester evidence, and release/commercial operations.

## Documents

- [`docs/PRD.md`](docs/PRD.md) — V1 product requirements
- [`docs/TRD.md`](docs/TRD.md) — technical requirements
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — theme/plugin/platform boundaries
- [`docs/MARKET-RESEARCH.md`](docs/MARKET-RESEARCH.md) — living competitor research + research gate
- [`docs/DESIGN-PRINCIPLES.md`](docs/DESIGN-PRINCIPLES.md) — original UI/UX quality bar
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
  -> security/performance review
  -> release
```

Code first, justification later is explicitly not the Grovia workflow.
