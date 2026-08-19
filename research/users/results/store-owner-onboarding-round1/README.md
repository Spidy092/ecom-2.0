# Store-owner onboarding Round 1 results

This directory stores **de-identified structured evidence only**.

Before each session, copy:

`research/users/templates/store-owner-onboarding-session.json`

to a unique filename such as:

`O01.json`

The filename and `participant_id` must match.

Do not commit names, emails, phone numbers, business/company names, domains, store URLs, credentials, customer/order data, license keys, recordings or screenshots containing private store information.

Validate records with:

```bash
python scripts/validate-store-owner-onboarding.py
```

After at least five real qualifying sessions are committed, evaluate the documented PROCEED thresholds with:

```bash
python scripts/validate-store-owner-onboarding.py --commercial-gate
```

A green validator means the committed evidence is structurally complete and the stated thresholds are satisfied. It does not prove participant authenticity by itself and must never be populated with synthetic sessions.
