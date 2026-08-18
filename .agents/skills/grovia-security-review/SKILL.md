---
name: grovia-security-review
description: Reviews Grovia WordPress/WooCommerce changes for authorization, CSRF, XSS, injection, file/import, privacy, supply-chain, performance-abuse, and release risks. Use before merging security-sensitive endpoints, customer data features, importer/setup code, dependencies, or releases.
---

# Grovia Security Review Skill

## Read first

- `docs/SECURITY.md`
- `docs/TRD.md`
- `docs/ARCHITECTURE.md`

## Review method

For each change identify:
1. assets/data being protected;
2. attacker-controlled inputs;
3. authentication identity source;
4. required capabilities/ownership;
5. state-changing operations and CSRF protection;
6. database/files/network sinks;
7. output contexts and escaping;
8. data stored/logged/shared;
9. expensive operations that can be abused;
10. new dependency/supply-chain exposure;
11. failure behavior (fail-open vs fail-closed);
12. tests that prove the boundary.

## Grovia high-risk areas

### Shopping List

Ensure every read/write is scoped to the authenticated customer and no predictable public list identifier bypasses ownership.

### Buy Again

Never trust client-supplied order/customer IDs. Query only orders the current customer is authorized to access and expose only the minimum information required.

### Delivery checker

Normalize/bound postcode input, avoid expensive wildcard queries, and do not disclose private administrative configuration beyond the availability result.

### Search

Bound query length/result size, debounce/cancel client requests where appropriate, and prevent expensive unbounded/meta queries.

### Setup/importer

Require administrative capability, validate archives/content, prevent path traversal/zip-slip, never import executable code, and constrain remote downloads.

## WordPress security rules

- Nonces/CSRF tokens do not replace capability/ownership checks.
- Validate expected type/domain; sanitize as appropriate.
- Escape at final output context.
- Prefer WordPress/WooCommerce query APIs and prepared queries.
- Do not accept arbitrary filenames, callbacks, class names, SQL fragments or server-side URLs from users.
- Use minimum privilege for admin actions and CI tokens.

## Release blockers

Stop release for known exploitable critical/high issues, authorization bypasses, stored/reflected XSS in Grovia-owned surfaces, SQL injection, arbitrary file/path execution, leaked secrets, or vulnerable required dependencies without an accepted mitigation.

## Output

Provide findings by severity:
- BLOCKER
- HIGH
- MEDIUM
- LOW
- NOTE

Each finding must include evidence, affected boundary, impact, and concrete remediation. Do not claim "secure" because an automated scan is clean.
