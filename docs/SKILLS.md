# Agent Skills Inventory & Policy

## Policy

Agent skills can change how a coding agent reasons, executes commands, or modifies the repository. Treat them as development dependencies.

Before adding a third-party skill:
1. verify the exact upstream repository/skill;
2. read the skill and any scripts/references it invokes;
3. review source reputation and maintenance;
4. check available security-audit signals;
5. check license/redistribution requirements;
6. confirm the skill is actually needed for Grovia;
7. pin/document the upstream source and review date.

Do not install dozens of overlapping skills. A small coherent set is easier to reason about and safer.

## Installed project skills

### `find-skills`

- Source: `vercel-labs/skills`, `skills/find-skills`
- Purpose: discover candidate skills for specialized tasks.
- Upstream license: MIT.
- Review date: 2026-08-18.
- Status: **installed project-level**.

The normal CLI installation is:

```bash
npx skills add https://github.com/vercel-labs/skills --skill find-skills
```

The CLI could not complete in the current execution environment, so the reviewed upstream skill is vendored at `.agents/skills/find-skills/` with its license notice.

### `grovia-product`

- Source: this repository.
- Purpose: keep Codex aligned to the V1 PRD, exclusions, product journey, and evidence-driven scope.
- Status: **installed**.

### `grovia-wordpress-engineering`

- Source: this repository.
- Purpose: enforce Grovia's WordPress/WooCommerce architecture and dependency rules during implementation.
- Status: **installed**.

### `grovia-security-review`

- Source: this repository.
- Purpose: threat-model Grovia changes and review authorization/input/output/data/dependency risks before merge/release.
- Status: **installed**.

## Approved candidates to evaluate when their task begins

These are **not automatically installed** merely because they are popular.

### Anthropic `frontend-design`

Useful during the V1 prototype/design phase. Current skills directory signals show a very large install base and a highly established upstream repository. Install only when we start visual prototype work, after re-reading the current upstream skill/license.

```bash
npx skills add https://github.com/anthropics/skills --skill frontend-design
```

### Anthropic `webapp-testing`

Useful when the local WordPress demo can run and we need browser-level Playwright workflows. It includes helper scripts, so scripts must be reviewed before project installation.

```bash
npx skills add https://github.com/anthropics/skills --skill webapp-testing
```

### Matt Pocock `tdd`

Potentially useful once public seams/test harnesses are established. Do not let it override Grovia's WordPress/WooCommerce testing constraints or force inappropriate testing architecture.

```bash
npx skills add https://github.com/mattpocock/skills --skill tdd
```

### Automattic `wpds`

Potentially useful for WordPress-related interface work. It is close to the WordPress ecosystem but may expect additional tooling/MCP context. Evaluate only if its current prerequisites match our environment.

```bash
npx skills add https://github.com/automattic/agent-skills --skill wpds
```

## Skills deliberately not approved yet

- Unknown WordPress/WooCommerce skills with weak maintenance/reputation signals.
- Skills that require broad shell/network/write permissions without a clear benefit.
- Duplicate frontend-design/TDD/review skills that create conflicting agent instructions.
- Security skills with unresolved security-audit failures until the cause is inspected.
- Skills that encourage modifying WordPress/WooCommerce core.

## Installation ownership

Every added project skill must update this file with:
- upstream;
- purpose;
- license;
- review date;
- installed version/commit where practical;
- scripts/permissions of concern.

## Update rule

Do not run blind `npx skills update` in release automation. Skill updates are reviewed dependency changes and should arrive through a pull request.
