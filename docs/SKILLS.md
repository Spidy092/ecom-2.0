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

### `webapp-testing`

- Source: `anthropics/skills`, `skills/webapp-testing`.
- Purpose: browser-level Playwright testing for the AisleFlow prototype and later WooCommerce storefront regression tests.
- Upstream license: Apache-2.0.
- Reviewed upstream skill SHA: `4726215301db64a0cc4d41fc3219c61f37a30f4a`.
- Reviewed helper SHA: `431f2eba16b268b7f3e2ae4daae9db41c0289b6d`.
- Review date: 2026-08-18.
- Status: **installed project-level** with upstream skill, helper, examples and license.

Security note: `scripts/with_server.py` intentionally invokes configured server commands using Python `subprocess.Popen(..., shell=True)` so commands such as `cd ... && ...` work. For this repository:

- only run repository-owned/trusted local server commands;
- never interpolate issue text, customer input, model-generated unreviewed strings, branch names, filenames, environment values, or other untrusted data into `--server`;
- prefer a direct static `file://` Playwright path when a server is unnecessary;
- run the helper with `--help` before use, as the upstream skill instructs.

### `frontend-design`

- Source: `anthropics/skills`, `skills/frontend-design`.
- Purpose: support the V1 visual-design phase with a deliberate, distinctive design process instead of generic template aesthetics.
- Upstream license: Apache-2.0.
- Reviewed upstream repository commit: `0a64e398ec6bb34a494f0c347e8ccae53a862f8e`.
- Reviewed skill blob SHA: `8ed6b320fadba34688070f008f2e187419e115e4` as fetched from the reviewed upstream tree.
- Review date: 2026-08-21.
- Status: **installed project-level** at `.agents/skills/frontend-design/` with its upstream license.

Security/overlap note:
- the reviewed skill is instruction-only and adds no scripts, shell commands, runtime packages, credentials or network services;
- it complements `grovia-design-critic`: the upstream skill gives a visual exploration/critique workflow, while the Grovia skill keeps grocery task speed, accessibility, performance and product originality as the deciding constraints;
- its warning about generic AI design defaults is directly relevant to the current visual-system phase, so visual directions must be tested against Grovia's subject and not adopted mechanically.

Normal CLI installation if needed elsewhere:

```bash
npx skills add https://github.com/anthropics/skills --skill frontend-design
```

### `grovia-market-research`

- Source: this repository.
- Purpose: require current market/competitor/customer evidence and a uniqueness thesis before implementation.
- Status: **installed**.

### `grovia-product`

- Source: this repository.
- Purpose: keep Codex aligned to the V1 PRD, exclusions, product journey, and evidence-driven scope.
- Status: **installed**.

### `grovia-design-critic`

- Source: this repository.
- Purpose: reject generic visual/interaction work and critique grocery task speed, originality, mobile UX, accessibility and performance.
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
