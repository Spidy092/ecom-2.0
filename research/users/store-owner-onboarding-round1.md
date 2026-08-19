# Store-owner onboarding validation — Round 1

Status: READY TO RUN — no participant evidence recorded yet
Issue: #64
Engineering surface: draft PR #66

## Research question

Can a relevant WordPress/WooCommerce store owner or implementer personalize the starter into a recognizable store and identify the remaining launch work without developer intervention or believing the product silently changed their content?

This round evaluates comprehension and task completion. It does **not** prove willingness to pay, legal compliance, performance superiority, or universal plugin compatibility.

## Participant criteria

Run at least five sessions with unique participant codes `O01` through `O05` or higher.

A qualifying participant must have at least one of:

- currently owns/administers a WooCommerce store;
- has launched or maintained a WooCommerce site for a client;
- is actively evaluating a WooCommerce theme/storefront for a real store.

Record role category only. Do not commit names, email addresses, phone numbers, business names, domains, customer/order data or other unnecessary PII.

Prefer a mix of:

- store owner / operator;
- freelance implementer;
- agency / developer / builder.

## Test environment

Use the same product state represented by the tested integration branch/package. Record:

- participant code;
- date in ISO format;
- viewport/device class;
- WordPress version;
- WooCommerce version;
- Theme version;
- Core version;
- whether the participant has used the WordPress Site Editor before.

Do not coach the participant through the UI unless the intervention rule below is triggered.

## Moderator opening

Tell the participant only:

> Imagine you bought this WooCommerce storefront for a real grocery/retail store. The Theme and Core are installed. Please make the starter feel like your store and tell me when you believe it is ready for a launch review. I will mostly stay quiet. Think aloud when something is unclear.

Do not explain where controls live before the participant attempts the tasks.

## Fixed tasks

### T1 — Find the guided setup surface

Prompt: “Start from the WordPress admin. Find where you would begin setting up or personalizing this product.”

Success:
- participant independently reaches `WooCommerce > Store Setup` or `WooCommerce > Personalize Store`;
- can explain the difference between technical readiness and personalization after seeing the two surfaces.

Major failure:
- cannot find either surface without intervention;
- assumes Elementor or another builder is required.

### T2 — Brand identity

Prompt: “Change the store identity so it no longer looks like the starter brand.”

Success:
- finds the native Site Editor path from onboarding;
- identifies Site Logo and/or Site Title as normal WordPress controls;
- does not believe the product locks the starter branding.

Observe whether the participant expects a proprietary theme-options panel.

### T3 — Visual starting point

Prompt: “Choose a different supplied visual style, then show where you would change a brand color.”

Success:
- reaches Styles;
- identifies a supplied style variation;
- finds Global Styles color controls;
- understands the variation is a starting point, not a separate theme install.

### T4 — Store shell

Prompt: “Change something in the header or footer and show where you would edit the store navigation.”

Success:
- reaches the registered template-part/navigation editing surface;
- understands Header/Footer are customer-editable WordPress state.

### T5 — Replace starter catalog content

Prompt: “Replace one starter product image and show where you would edit its price and stock.”

Success:
- reaches WooCommerce Products;
- identifies product image/media, price and stock controls;
- understands generated demo images/products are replaceable defaults rather than runtime-locked theme assets.

Critical failure:
- believes the generated demo images cannot be replaced;
- attempts to edit price/stock in Theme/Core settings rather than WooCommerce.

### T6 — Commerce ownership

Prompt: “Where would you configure shipping or delivery, payments and taxes?”

Success:
- identifies WooCommerce settings or relevant WooCommerce-owned extension surfaces;
- does not believe Theme/Core are the source of truth for payments/tax/order state.

### T7 — Launch review

Prompt: “Assume the design looks right. What would you check before calling the store ready to launch?”

Success requires the participant to identify at least four relevant checks, including at least one commerce check and one content/policy/review check.

Possible answers include:
- products/prices/stock/images;
- shipping/delivery coverage;
- cart/checkout;
- payment configuration;
- tax configuration where applicable;
- policies/legal copy;
- mobile/desktop review;
- technical status/HTTPS.

Do not score specific legal advice as correct. We are testing awareness that presentation completion is not equivalent to production readiness.

## Intervention rule

Remain silent during a task until one of these occurs:

- participant explicitly gives up;
- participant has made no forward progress for 90 seconds;
- participant is about to perform an unsafe/destructive action unrelated to the task.

Record every intervention with a short neutral code. Do not hide assisted completions.

## Measurements

For every task record:

- outcome: `pass`, `assisted`, `fail`, or `not_run`;
- elapsed seconds;
- intervention count;
- confusion codes;
- concise observation note.

At session level record:

- seconds to first successful personalization action;
- total tasks passed unassisted;
- total tasks assisted;
- total tasks failed;
- whether participant correctly explains replaceable demo content;
- whether participant correctly explains WordPress/WooCommerce/Core ownership;
- whether participant understands launch review is not automatic certification;
- overall confidence: `high`, `medium`, or `low` based on observed behavior, not politeness.

## Confusion codes

Use only these stable codes in the JSON export. Add new codes through review rather than free-form category drift.

- `NAV_SETUP_DISCOVERY` — could not find Store Setup/Personalize Store;
- `EXPECTS_THEME_OPTIONS` — expected a proprietary theme settings panel;
- `EXPECTS_ELEMENTOR` — believed Elementor/page builder was required;
- `SITE_EDITOR_MENTAL_MODEL` — did not understand Site Editor/Global Styles/template parts;
- `STYLE_VARIATION_MENTAL_MODEL` — did not understand supplied visual styles;
- `HEADER_FOOTER_DISCOVERY` — could not locate shell editing;
- `DEMO_CONTENT_LOCKED` — believed starter products/images were locked;
- `COMMERCE_OWNERSHIP` — confused Theme/Core with WooCommerce price/stock/payment/tax/shipping ownership;
- `LAUNCH_CERTIFICATION` — interpreted presentation/setup completion as automatic production certification;
- `WORDING_OTHER` — wording confusion not covered above; explain in note.

## Post-task questions

Ask only after all fixed tasks:

1. “What do you think this product includes?”
2. “What part of setup felt easiest?”
3. “What part felt most confusing?”
4. “Did anything make you worry the product would overwrite your work?”
5. “Are the demo products and images clearly replaceable?”
6. “Would you expect Elementor to be required? Why?”
7. “What would stop you from using this on a real store?”
8. “If you were comparing this with another WooCommerce theme, what would you need to see before paying?”

The last question is discovery evidence only. Do not convert a hypothetical answer into willingness-to-pay proof.

## Round decision rules

After at least five qualifying sessions:

### PROCEED

All of the following:
- at least 4/5 independently find the product setup/personalization surface;
- at least 4/5 complete T2–T5 without moderator intervention on the first attempt;
- at least 4/5 correctly understand demo products/images are replaceable;
- at least 4/5 correctly identify WooCommerce ownership of product/commerce truth;
- zero participants believe successful personalization means legal/payment/shipping/tax certification after using the launch-review section;
- no repeated critical usability failure appears in 2+ participants.

### REVISE

Any threshold above misses but the core mental model remains recoverable without architectural change.

### REJECT / CHANGE APPROACH

Use only if repeated evidence shows the native customization/onboarding model itself is not understandable enough for the intended buyer and small copy/navigation changes are unlikely to fix it.

## Evidence handling

Commit one JSON record per participant under:

`research/users/results/store-owner-onboarding-round1/OXX.json`

Do not commit recordings, names, emails, domains, credentials, store data or license keys. Keep raw recordings/PII outside the repository if separately consented and needed.

After five valid records, synthesize them into a separate results document. Automated validation may check schema/IDs/completeness, but it must never fabricate participant evidence or mark the commercial gate passed by itself.
