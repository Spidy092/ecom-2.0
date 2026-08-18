# Grovia Agent Operating Guide

This repository is a commercial WordPress/WooCommerce product. Agents (Codex or other coding agents) must optimize for correctness, maintainability, security, performance, and a small V1 scope — not feature count.

## Read before changing code

1. `docs/PRD.md` — what V1 is and is not.
2. `docs/TRD.md` — technical boundaries and quality requirements.
3. `docs/ARCHITECTURE.md` — ownership between theme, core plugin, WooCommerce, and WordPress.
4. `docs/SECURITY.md` — mandatory security rules.
5. `docs/DECISIONS.md` — accepted architectural/product decisions.

## Product rule

Grovia V1 is a grocery-first WooCommerce product. Do not add generic marketplace features merely because competitors have them. Prefer the smallest feature that improves grocery shopping, store setup, performance, or maintainability.

## Engineering rules

- Never modify WordPress or WooCommerce core.
- Prefer public WordPress/WooCommerce APIs, hooks, blocks, and extension points.
- Keep presentation in the theme and reusable business/product functionality in `grovia-core`.
- Treat all request, form, REST, AJAX, URL, cookie, and database input as untrusted.
- Verify authorization before state-changing operations.
- Escape output at the final rendering boundary.
- Do not add a third-party runtime dependency without documenting why it is needed, its license, update path, and security impact.
- Do not make Elementor or another page builder mandatory for V1.
- Do not add telemetry unless it is explicitly opt-in and documented.
- Do not add AI-generated code that cannot be explained, tested, and maintained.

## Agent workflow

1. Read the relevant requirements.
2. State the behavior being implemented and the public seam to test.
3. Make the smallest coherent change.
4. Add/update tests for observable behavior.
5. Run available lint, static analysis, unit/integration tests, and build checks.
6. Update documentation when public behavior, architecture, setup, compatibility, or security changes.
7. Never silently broaden scope.

## V1 protected scope

V1 focuses on:
- instant grocery-oriented product discovery;
- fast quantity shopping from product cards;
- focused filters;
- postcode/zone delivery availability;
- mobile bottom navigation and sticky cart feedback;
- shopping list;
- buy-again experience;
- styled WooCommerce cart/checkout blocks;
- guided store setup;
- one Modern Grocery starter site.

Explicitly defer multi-vendor marketplace, AI chatbot, dozens of demos, multiple builders, advanced logistics, custom payments, and generic feature bloat.

## Skills

Project skills live under `.agents/skills/`. Third-party skills must be reviewed before they are added. See `docs/SKILLS.md`.
