# Modern Grocery Starter Import Contract

**Status:** provider-independent engineering-alpha contract  
**Issue:** #18  
**Customer-facing destructive import:** disabled until the commercial package/update provider and content-operation verification are complete.

## 1. Purpose

The starter-store importer must make setup easier without turning retries into duplicate pages, menus, products, settings or support incidents.

The importer is therefore treated as a transaction with explicit business-level phases rather than a long sequence of unrelated WordPress writes.

```text
Preflight
  -> Content
  -> Configuration
  -> Verification
  -> Complete
```

A failure must stop at a known phase, preserve already-completed phase markers, expose a bounded diagnostic code, and allow the same manifest to resume safely after the underlying problem is fixed.

## 2. Provider boundary

The eventual commercial provider may control:

- entitlement;
- package/download authorization;
- update delivery;
- release artifact retrieval.

It must **not** control the internal setup transaction semantics.

Storefront Core owns:

- manifest validation;
- transaction identity;
- ordered phase state;
- atomic begin ownership;
- retry/resume semantics;
- idempotency markers;
- verification state;
- privacy-safe diagnostic status.

This keeps Lemon Squeezy, Freemius or another provider replaceable without rewriting the starter-store setup model.

## 3. Manifest identity

The transaction identity currently uses these immutable fields:

- schema version;
- starter ID;
- starter version;
- ordered transaction phases.

The identity is hashed into a digest used only internally for idempotency checks. The digest is intentionally excluded from the downloadable support report because support does not need it.

Current internal starter identity:

```text
id: modern-grocery
version: 0.1.0-alpha
schema: 1
```

This is not a public release/version promise.

## 4. State machine

Allowed top-level states:

```text
idle
running
failed
complete
```

Stored technical state is intentionally small:

- manifest ID/version/digest;
- attempt count;
- current phase;
- completed phases;
- failed phase;
- bounded error code;
- last update timestamp.

Never persist customer content, exception dumps, credentials, request payloads, license secrets, order data or PII inside transaction state.

## 5. Atomic begin lock

A status flag alone is not enough because two HTTP requests can read the same idle state before either writes `running`.

Before a transaction changes state, Core acquires a database-backed WordPress option lock using a unique option key. `add_option()` provides the uniqueness guard so only one begin request can own the lock.

Rules:

- a second lock owner is rejected;
- state is re-read after lock acquisition so a stale pre-lock read cannot overwrite a transaction that changed in between;
- the lock remains held across intermediate phases;
- failure releases the lock so the same manifest can retry;
- completion releases the lock;
- explicit internal reset releases the lock;
- an `already_complete` check does not retain a lock.

A customer-facing recovery action for a genuinely abandoned/stale lock is **not** exposed yet. That behavior must be designed with the real importer runtime rather than inventing an arbitrary timeout.

## 6. Begin rules

Starting a transaction must:

1. validate the manifest schema and immutable identity;
2. reject another currently running transaction;
3. reject another request that already owns the atomic begin lock;
4. reject silent reuse of state belonging to a different manifest version;
5. start at `preflight` for a new transaction;
6. resume at the first incomplete phase for the same failed manifest, including a failure at the first `preflight` phase;
7. increment the attempt counter on a real retry;
8. return `already_complete` for the same completed manifest instead of running writes again.

## 7. Ordered phase rules

A phase can only be completed when it is the current phase.

For example:

```text
preflight -> content        allowed
preflight -> verification   rejected
```

This prevents callers from accidentally marking a partially configured store as verified/complete.

## 8. Failure and retry rules

When a phase fails:

- transaction becomes `failed`;
- failed phase is recorded;
- completed phases remain recorded;
- only a sanitized machine-readable error code is persisted;
- the atomic transaction lock is released;
- same manifest can resume from the failed/incomplete phase;
- retry reacquires the lock;
- a different manifest version cannot silently inherit the previous state.

Example:

```text
attempt 1
preflight ✓
content  ✕ network-timeout

attempt 2
preflight already complete
content resumes
configuration
verification
complete
```

## 9. Verification-only starter resource manifest

Before destructive content operations exist, the alpha already has a machine-checkable list of resources the current product expects.

Current verification targets:

```text
modern-grocery/woocommerce/page/shop
modern-grocery/woocommerce/page/cart
modern-grocery/woocommerce/page/checkout
modern-grocery/woocommerce/page/myaccount
modern-grocery/theme/template/front-page
modern-grocery/theme/part/footer
modern-grocery/core/block/product-workspace
modern-grocery/core/block/mobile-shopping-nav
```

This manifest is verification-only. It does not authorize Core to create or overwrite any of those resources.

The current preflight checks:

- WooCommerce page IDs resolve to published pages;
- expected product-theme files exist under the active stylesheet directory;
- required Storefront Core blocks are registered;
- resource keys are namespaced and unique;
- only bounded result codes are exported to support diagnostics.

Setup/System Status reports a simple ready/total result and exact stable keys for failed checks instead of a generic "import failed" message.

## 10. Content idempotency contract — required before UI enablement

The transaction state alone is not enough. Every future destructive content operation must also have a stable resource identity.

Before the customer-facing importer is enabled, each managed resource must define:

- stable starter resource key;
- resource type;
- ownership marker/meta;
- create/update/skip decision;
- conflict behavior when a customer-owned resource already exists;
- verification condition;
- rollback/recovery behavior where safe.

Do **not** use title-only matching as ownership proof.

Example future resource key shape:

```text
modern-grocery/page/home
modern-grocery/page/about
modern-grocery/menu/primary
modern-grocery/pattern/home-intro
```

A rerun must resolve the same managed resource rather than creating `Home (2)` or another duplicate.

## 11. Customer-owned content rule

The importer must not silently overwrite unrelated customer content merely because a slug/title matches the starter manifest.

When an existing resource is not positively identified as product-managed, the importer should choose one of:

- preserve + report conflict;
- ask for an explicit decision;
- create a safely namespaced alternative during an approved migration flow.

Silent destructive replacement is not acceptable.

## 12. Preflight requirements before destructive work

The final preflight should verify only requirements that materially affect the import, including:

- supported WordPress/PHP/WooCommerce versions;
- Core + intended product theme active;
- required capabilities;
- package/manifest integrity;
- verification-only resource readiness;
- required REST/filesystem/network behavior where actually needed;
- enough environment resources for the tested starter package;
- no conflicting active transaction;
- current starter state and any recoverable previous attempt.

Do not invent arbitrary minimum memory/time thresholds without measuring the real package.

## 13. Verification contract

`complete` must mean more than “no exception happened.”

Before completion, verification should prove the resources the importer owns reached the expected state, for example:

- intended front page/template assignment exists;
- required WooCommerce pages remain valid;
- product-managed navigation/pattern resources exist once;
- starter styles/settings are applied where expected;
- no duplicate managed resource keys exist;
- storefront routes needed by the demo resolve;
- verification does not depend on a fake screenshot or hard-coded post IDs.

Exact destructive-resource checks will be added with the real content manifest.

## 14. Rollback boundary

A universal database rollback is not promised.

Instead, the importer should prefer:

- deterministic, individually identifiable writes;
- phase-local recovery;
- safe retry;
- deletion/reversion only for resources positively known to be created by the failed transaction and only when doing so cannot destroy later customer edits.

If automatic rollback is unsafe, preserve the state and explain the recovery action rather than guessing.

## 15. Privacy and support reporting

The downloadable System Status report may include bounded transaction fields useful to support:

- status;
- manifest ID/version;
- attempts;
- current phase;
- failed phase;
- last error code.

It may also include bounded verification-preflight fields:

- stable resource key;
- resource type;
- ready/not-ready boolean;
- bounded result code.

It must not include the internal digest, lock contents, package URL/token, license key, filesystem path, customer/order data, credentials or raw exception traces.

## 16. Current automated contract

CI exercises:

- resource-manifest validation and unique stable keys;
- verification-preflight structure;
- product-theme/Core verification targets;
- manifest validation;
- unsupported schema rejection;
- atomic begin-lock rejection;
- first start and lock ownership;
- parallel-running rejection;
- out-of-order phase rejection;
- failure + lock release;
- retry after first-phase failure;
- same-manifest resume;
- attempt increment;
- completed-phase preservation;
- lock retention across intermediate phases;
- full completion + lock release;
- completed rerun becoming `already_complete`;
- changed-manifest rejection;
- state/lock reset back to `idle`.

The browser setup/status test separately verifies that no destructive import action is exposed yet and that the downloadable report stays privacy-bounded.

## 17. Remaining gate before real import

Do not expose `Import Modern Grocery` until:

- Issue #14 records the commercial package/update provider decision;
- the actual starter content manifest exists;
- each content operation has a stable resource identity and conflict policy;
- retry is tested against deliberately interrupted content/configuration writes;
- verification checks the resulting real storefront;
- failed setup gives an actionable recovery path;
- a rerun demonstrably creates zero duplicate managed resources;
- target store owners/builders complete setup without developer intervention.
