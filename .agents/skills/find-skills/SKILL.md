---
name: find-skills
description: Helps users discover and install agent skills when they ask questions like "how do I do X", "find a skill for X", "is there a skill that can...", or express interest in extending capabilities. This skill should be used when the user is looking for functionality that might exist as an installable skill.
---

# Find Skills

This skill helps you discover and install skills from the open agent skills ecosystem.

## When to Use This Skill

Use this skill when the user:

- Asks "how do I do X" where X might be a common task with an existing skill
- Says "find a skill for X" or "is there a skill for X"
- Asks "can you do X" where X is a specialized capability
- Expresses interest in extending agent capabilities
- Wants to search for tools, templates, or workflows
- Mentions they wish they had help with a specific domain (design, testing, deployment, etc.)

## What is the Skills CLI?

The Skills CLI (`npx skills`) is the package manager for the open agent skills ecosystem. Skills are modular packages that extend agent capabilities with specialized knowledge, workflows, and tools.

**Key commands:**

- `npx skills find [query] [--owner <owner>]` - Search for skills interactively or by keyword, optionally scoped to a GitHub owner
- `npx skills add <package>` - Install a skill from GitHub or other sources
- `npx skills update` - Update all installed skills

**Browse skills at:** https://skills.sh/

## How to Help Users Find Skills

### Step 1: Understand What They Need

When a user asks for help with something, identify:

1. The domain (e.g., React, testing, design, deployment)
2. The specific task (e.g., writing tests, creating animations, reviewing PRs)
3. Whether this is a common enough task that a skill likely exists

### Step 2: Check the Leaderboard First

Before running a CLI search, check the skills.sh leaderboard to see if a well-known skill already exists for the domain. The leaderboard ranks skills by total installs, surfacing the most popular and battle-tested options.

### Step 3: Search for Skills

If the leaderboard doesn't cover the user's need, run:

```bash
npx skills find [query] [--owner <owner>]
```

Use specific search terms, and try alternative terms when needed.

### Step 4: Verify Quality Before Recommending

**Do not recommend a skill based solely on search results.** Always verify:

1. **Install count** — Prefer skills with meaningful adoption. Be cautious with extremely low adoption.
2. **Source reputation** — Prefer established/official sources where possible.
3. **Repository health** — Inspect the source repository, current contents, scripts, and maintenance signals.
4. **Security** — Review what the skill instructs the agent to execute and any scripts/tools it invokes.
5. **License** — Ensure redistribution/use is compatible with the project.

### Step 5: Present Options

When relevant skills are found, explain:

1. skill name and purpose;
2. source/reputation signals;
3. install command;
4. important permissions/scripts/security considerations.

### Step 6: Install Deliberately

If approved, install with the Skills CLI or vendor project-level content according to repository policy. For Grovia, third-party skills are dependency changes and must follow `docs/SKILLS.md`.

## Common Skill Categories

| Category | Example Queries |
| --- | --- |
| WordPress | wordpress, woocommerce, gutenberg, blocks |
| Testing | testing, playwright, e2e, tdd |
| DevOps | deploy, docker, ci-cd, release |
| Documentation | docs, readme, changelog, api-docs |
| Code Quality | review, lint, refactor, best-practices |
| Design | ui, ux, design-system, accessibility |
| Security | wordpress security, php security, code security |

## Grovia-specific note

Do not install a candidate merely because it ranks highly. Read `docs/SKILLS.md`, avoid overlapping instructions, and prefer the smallest reviewed skill set that directly improves the task.
