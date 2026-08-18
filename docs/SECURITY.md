# Grovia Security Baseline

Security is a release requirement. This document applies to the theme, Grovia Core, build/release automation, demo importer, admin UI, and public endpoints.

## 1. Threat model priorities

V1 particularly protects against:
- unauthorized access to another customer's order/history/Shopping List;
- CSRF on state-changing admin/customer actions;
- reflected/stored XSS in settings, product-adjacent UI, notices, search, and demo content;
- SQL injection and unsafe dynamic query construction;
- arbitrary file/path inclusion or upload handling errors;
- SSRF/open redirect from user-controlled URLs;
- privilege escalation through missing capability checks;
- insecure object references;
- dependency/supply-chain compromise;
- leaked credentials/secrets in repository/build artifacts;
- denial-of-service through unbounded public search or expensive endpoints.

## 2. Trust-boundary rule

Treat as untrusted:
- query parameters;
- request bodies;
- REST/AJAX payloads;
- headers/cookies;
- shortcode/block attributes that can be influenced by lower-privileged users;
- database content before it is rendered into a new context;
- imported demo content;
- third-party API responses;
- filenames/URLs supplied by users.

## 3. Authentication and authorization

Authentication alone is not authorization.

For protected actions:
1. determine the current authenticated identity;
2. check WordPress capability/ownership appropriate to the resource;
3. validate the requested resource belongs to/is accessible by that identity;
4. only then read or mutate protected data.

Buy Again must never accept a client-supplied order/customer ID and trust it without ownership validation.

Admin settings require explicit capabilities; never rely only on hiding UI.

## 4. CSRF / nonce handling

State-changing WordPress admin/AJAX actions must use the appropriate nonce/CSRF mechanism and authorization check. Nonces are not a replacement for capabilities.

REST/Store API extension patterns must follow the selected WordPress/WooCommerce authentication/nonce contract.

## 5. Input validation and sanitization

Validate for the expected type/domain first, sanitize as a secondary normalization step.

Examples:
- IDs -> positive integer + resource existence/access check;
- postcode -> length/character/locale rule;
- enum -> explicit allowlist;
- boolean -> strict conversion;
- URL -> validated URL with scheme/host constraints when used server-side;
- arrays -> bound maximum size and validate every element.

Never accept arbitrary callback/class/function names from a request.

## 6. Output escaping

Escape at the final output context:
- HTML text;
- HTML attributes;
- URLs;
- JavaScript/JSON;
- CSS/style contexts.

Do not assume previously sanitized database content is safe for every rendering context.

## 7. Database safety

- Prefer WordPress/WooCommerce query APIs.
- Parameterize dynamic SQL using supported prepared-query mechanisms.
- Never interpolate request values into SQL identifiers/fragments.
- Bound queries and pagination.
- Review joins/queries used by search and Buy Again for predictable cost.

## 8. File and importer safety

The starter-site importer is high risk.

Requirements:
- imports only expected structured content;
- no arbitrary PHP/executable files;
- validate file type and archive paths;
- prevent path traversal/zip-slip;
- do not download executable code from arbitrary URLs;
- imported remote media must use safe HTTP APIs and URL validation;
- importing requires privileged admin capability;
- provide rollback/clear failure behavior where practical.

## 9. HTTP / SSRF

If server-side HTTP is added:
- use WordPress HTTP APIs;
- constrain destination where possible;
- never fetch arbitrary private-network/metadata URLs from user input;
- set timeouts and response-size expectations;
- fail closed for security-sensitive checks.

## 10. XSS-sensitive surfaces

Extra review required for:
- product search result highlighting;
- admin-configured notices/content;
- delivery messages;
- SVG/icon handling;
- starter-site import content;
- block attributes/render callbacks;
- WooCommerce notices;
- error messages that echo user input.

Prefer static trusted icon assets. Do not allow untrusted raw SVG markup by default.

## 11. Shopping List privacy

Shopping List data is private customer data.
- namespace/store it by authenticated identity;
- every read/write enforces ownership;
- no public predictable endpoint exposing list identifiers;
- uninstall/privacy behavior must be documented before release.

## 12. Buy Again privacy

Purchase history is sensitive.
- query only current customer's authorized orders;
- do not expose raw order details when only product IDs are needed;
- never log full order/customer payloads for debugging by default.

## 13. Logging

Production logging must not contain:
- passwords;
- API/payment secrets;
- authentication cookies/nonces;
- full payment details;
- unnecessary customer PII;
- complete order payloads unless explicitly required and protected.

Debug mode may add context but must still avoid secrets.

## 14. Dependency policy

Before adding a dependency:
- identify source/maintainer;
- license check;
- current maintenance health;
- known vulnerability check;
- runtime surface/permissions;
- update ownership;
- whether the dependency ships to customers.

Lock build dependencies. Do not commit secrets or package-manager auth tokens.

## 15. Secrets

No production secrets in Git, example configs, demo exports, screenshots, CI logs, release ZIPs, or support bundles.

Use GitHub secrets/appropriate deployment secret stores when automation begins.

## 16. Security testing gates

As code arrives, CI should include appropriate combinations of:
- dependency audit;
- secret scanning;
- PHP static analysis;
- PHPCS/WordPress security-oriented rules;
- targeted unit/integration tests for authorization;
- browser tests for privilege/user-boundary flows;
- manual security review for new public/state-changing endpoints.

A generic scanner passing does not prove safety.

## 17. Vulnerability reporting

Before public launch create `SECURITY.md` at repository/product level containing:
- supported versions;
- private reporting channel;
- expected response process;
- coordinated disclosure expectations.

Do not direct researchers to publish exploitable issues publicly before triage.

## 18. Release blockers

Do not release with:
- known exploitable high/critical vulnerability;
- missing authorization on protected data/action;
- reproducible stored/reflected XSS in Grovia-owned code;
- SQL injection/arbitrary file execution/path traversal;
- secret in release artifact;
- known vulnerable required dependency without an approved mitigation;
- unsupported WordPress/WooCommerce/PHP compatibility claim.

## 19. Security review checklist for every feature

- What is the attacker-controlled input?
- Where does identity come from?
- What authorization is required?
- What can be enumerated/guessed?
- What expensive operation can be spammed?
- What output context receives data?
- What is stored, and for how long?
- What happens if the dependency/upstream API lies or fails?
- Does failure become permissive?
- Are logs/support diagnostics exposing customer data?
