# Store-owner validation Playground

Status: research-only, disposable environment

## Purpose

This WordPress Playground Blueprint exists so moderated participants in Issue #71 can evaluate the real Store Setup / Personalize Store flow without installing WordPress, Docker or local development tools.

It is a **research environment**, not a customer-download substitute. Customer ZIP packaging and clean install remain separately validated by the Package contract workflow.

## Share link

Open a fresh temporary Playground with:

`https://playground.wordpress.net/?storage=temp&blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2FSpidy092%2Fecom-2.0%2Fagent%2Fgrovia-playground-validation%2Fresearch%2Fplayground%2Fstore-owner-validation.blueprint.json`

The Blueprint should:

1. boot WordPress 7.0.2 on PHP 8.3;
2. install and activate WooCommerce 11.0.0;
3. install and activate the validation-branch Storefront Core;
4. install and activate the validation-branch Storefront Theme;
5. seed deterministic grocery products and canonical demo images;
6. log in as the Playground administrator;
7. land on `WooCommerce > Personalize Store`.

## Moderator start procedure

For every participant:

1. open the share link in a fresh browser tab;
2. wait until Playground finishes all Blueprint steps;
3. verify the participant can see **Personalize your store**;
4. start the fixed protocol in `research/users/store-owner-onboarding-round1.md`;
5. do not explain where controls live unless the protocol intervention rule requires it;
6. do not enter any real store credentials, customer data or production settings;
7. after the session, close the tab;
8. start the next participant from the original share link rather than reusing the prior Playground.

`storage=temp` is intentional: the research instance is disposable and should not become a participant's real store.

## Browser guidance

WordPress Playground runs WordPress locally in the browser. For moderated sessions prefer a current Chromium-based desktop browser unless the protocol specifically calls for another environment. Record the actual browser and viewport in the participant record.

If a participant's device is low-memory or the Playground takes unusually long to boot, record that as environment friction rather than silently excluding it from the observation.

## Research boundaries

- Use only the seeded demo products and images.
- Never ask a participant to connect a live payment account.
- Never ask a participant to import customer/order data.
- Never ask for a production WordPress password, license key, domain login or API credential.
- The Playground session does not prove the Theme/Core ZIP install experience; that has its own release gate.
- The Playground session does not prove production hosting performance.

## Reset

The clean reset procedure is to close the session and reopen the original `storage=temp` share link. Do not try to manually undo participant changes between sessions.
