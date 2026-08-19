# Store-owner onboarding research — recruitment kit

Use this only to recruit qualifying participants for Issue #71. Do not describe the expected navigation path, style system, demo-content replaceability or architecture before the session; revealing those answers would bias the test.

## Short outreach message

> We are testing an early WooCommerce storefront product and need feedback from people who actually run or build WooCommerce stores. The session is a practical setup/personalization test, not a sales call. You will use a test store, complete a few normal store-owner tasks, and tell us what is confusing. We are testing the product, not you. If you currently run a WooCommerce store, have launched one for a client, or are actively evaluating WooCommerce for a real store, would you be willing to participate?

## Direct-message version

> Hi — I’m doing a small usability study for an early WooCommerce storefront product. I’m looking for store owners or implementers with real WooCommerce experience. The session is hands-on: you’ll use a test WordPress admin and try normal branding/product/launch-review tasks while I observe where the product is confusing. It is research, not a sales demo, and I do not need access to your real store. Interested?

## Builder/agency version

> We are validating the setup experience of a new WooCommerce storefront package. I’m looking for freelancers, agencies, or developers who have actually launched or maintained WooCommerce sites. I want to see whether the product’s setup and personalization model is understandable without developer guidance. The test uses our disposable environment; no client credentials or production-store access are needed. Would you be open to a research session?

## Screener

Ask these before scheduling. Keep only the de-identified role result in the repository.

1. Which best describes you?
   - own/administer a WooCommerce store now;
   - have built/maintained WooCommerce stores for clients;
   - actively evaluating WooCommerce for a real store;
   - none of these.

2. Roughly how many WooCommerce stores have you personally configured or maintained?
   - 0;
   - 1;
   - 2–5;
   - 6+.

3. Have you used the WordPress Site Editor / block theme editing before?
   - never;
   - a little;
   - regularly.

4. Are you comfortable doing a hands-on session in a disposable WordPress test site while thinking aloud?
   - yes;
   - no.

Qualify participants who satisfy at least one real-store criterion in question 1 and answer yes to question 4.

## Do not pre-screen for agreement

Do **not** ask questions such as:

- “Do you prefer block themes over Elementor?”
- “Do you like products with fewer required plugins?”
- “Would you pay for a theme that uses the Site Editor?”
- “Do you think demo images should be replaceable?”

Those questions expose hypotheses and can prime the participant.

## Scheduling note

Tell participants:

- no access to their real store is required;
- they should not share passwords, license keys, customer/order data or private client information;
- the test is about the product, not their WordPress skill;
- they can stop at any time;
- if recording is desired, obtain explicit consent outside the repository and keep the recording out of Git unless it contains no private information and there is a reviewed reason to retain it.

## Participant code assignment

Assign the code before the session:

`O01`, `O02`, `O03`, ...

Never use the participant's name, company, email or domain as the filename or participant ID.

## After the session

1. Complete the JSON record immediately while observations are fresh.
2. Run `python scripts/validate-store-owner-onboarding.py`.
3. Commit only the de-identified JSON evidence.
4. Do not change product copy after one isolated comment unless severity warrants immediate safety correction; synthesize repeated patterns across sessions.
5. After five valid sessions, run the commercial-gate command and write a human synthesis before changing Issue #64 to PROCEED/REVISE/CHANGE APPROACH.
