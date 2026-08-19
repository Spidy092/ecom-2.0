# Store-owner recruitment plan — Round 1

Status: execution-ready
Goal: recruit at least five qualifying real participants for `research/users/store-owner-onboarding-round1.md` and Issue #71.

## Sample target

Aim for the first five completed sessions to include:

- at least 2 people directly responsible for a real WooCommerce store;
- at least 2 WooCommerce freelancers, implementers or agency builders;
- 1 additional qualified participant from either group.

This mix is a sampling target, not a reason to alter, exclude or fabricate evidence.

## Current recruitment channels verified 2026-08-19

### 1. Official WooCommerce Community Slack — highest priority for implementers

Official source: https://woocommerce.com/community-slack/
Official community overview: https://woocommerce.com/community/

WooCommerce currently describes its Community Slack as a community of 25,000+ WooCommerce developers. The official community page also directs store owners/builders to Slack, local meetups and the official support forum.

Use only channels where a small research request is appropriate; read current channel rules before posting. Do not mass-DM members.

### 2. WooCommerce / WordPress meetups in India — highest priority for owner/operator mix

Current Meetup discovery: https://www.meetup.com/topics/woocommerce/in/

The current India WooCommerce topic page lists active groups including Mumbai, Bangalore, Malappuram and Kolkata. Recruitment should prioritize organizers/community posts rather than scraping member lists.

Useful current communities:

- Bangalore WooCommerce Meetup: https://www.meetup.com/bangalore-woocommerce-meetup/
- Kolkata WooCommerce Meetup: https://www.meetup.com/kolkata-woocommerce-group/
- Ahmedabad WooCommerce Meetup: https://www.meetup.com/woocommerce-ahmedabad-meetup/
- Bengaluru WordPress Meetup: https://www.meetup.com/bengaluruwordpress/

The Kolkata WooCommerce group explicitly describes its audience as WordPress users, startup/business owners, entrepreneurs and seasoned ecommerce users, which is relevant to the owner/operator side of this study.

### 3. Official WooCommerce support/community surfaces — secondary

Official source: https://woocommerce.com/community/

The official Woo community page points users to WooCommerce meetups, Slack, GitHub and the official support forum. Before posting a recruitment request in any forum, confirm the forum's current self-promotion/research rules. Do not disguise recruitment as a support question.

### 4. Direct professional outreach — controlled fallback

Use only public professional profiles that clearly indicate current WooCommerce implementation/store work. Prefer a small number of relevant messages over bulk outreach.

Do not scrape personal emails or phone numbers. Do not commit recipient identities or contact information to Git.

## Recruitment funnel

1. Post/ask organizer permission in 1–2 relevant communities.
2. Screen interested people using the neutral screener below.
3. Assign the next de-identified participant code only after qualification (`O01`, `O02`, ...).
4. Send the disposable Playground link immediately before the moderated session.
5. Run the fixed protocol without revealing the intended navigation path.
6. Commit only the de-identified JSON session record after the session.
7. Update only aggregate recruitment counts in Git.

## Neutral screener

Ask only what is needed to qualify the participant:

1. Which best describes you today?
   - I own/operate a WooCommerce store.
   - I build/manage WooCommerce stores for clients.
   - I am actively evaluating WooCommerce for a real store/project.
   - None of the above.
2. Have you personally used the WordPress admin area for a WooCommerce store?
   - Yes, regularly.
   - Yes, a few times.
   - Not yet, but I am evaluating it for a real project.
   - No.
3. Are you comfortable sharing your screen during a short browser-based usability session using demo data only?
   - Yes.
   - No.

Do **not** ask whether they know where Site Editor, Global Styles, products, shipping or payment settings are before the test. Those are observed tasks.

## Qualification

Qualifies if they currently:

- own/administer a WooCommerce store; or
- have launched/maintained one for a client; or
- are actively evaluating WooCommerce for a real store/project.

Exclude people with no real WooCommerce/store context from the commercial-validation sample. They may still provide informal feedback, but it must not be counted toward O01–O05.

## Session environment

Use the validated temporary Playground from Issue #74 / PR #75.

Moderator link:

`https://playground.wordpress.net/?storage=temp&blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2FSpidy092%2Fecom-2.0%2Fagent%2Fgrovia-playground-validation%2Fresearch%2Fplayground%2Fstore-owner-validation.blueprint.json`

Open the original link for every participant so each session starts with a fresh temporary site.

## Privacy

Never commit:

- participant name;
- email/phone;
- employer/business name;
- store domain/URL;
- production credentials;
- order/customer records;
- payment data;
- license keys;
- private recording links.

Git may contain only de-identified participant IDs and aggregate funnel counts.

## Research integrity

- Do not recruit only friends/team members.
- Do not offer leading descriptions such as “easy”, “premium” or “better than Elementor”.
- Do not show the seven tasks before the moderated session.
- Do not count a polite positive comment as willingness to pay.
- Do not create synthetic participant records.
- Keep the commercial gate closed until five qualifying real sessions exist.