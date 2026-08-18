# V1 Real-User Session Kit

**Status:** Revised after first pilot evidence  
**Issue:** #5  
**Rule:** observe behavior before asking for opinions.

## 1. Who to recruit next

For the next decision round, prioritize:
- at least **5 mobile grocery shoppers** using the revised terminology;
- then 3–5 WooCommerce builders/freelancers;
- then 3–5 small ecommerce/store owners/operators.

The first pilot already produced useful evidence, but the task was not followed consistently. The next round must be more disciplined.

Avoid using only friends or people who want to be polite.

## 2. Short recruitment messages

### Grocery shopper

> I’m testing a very early mobile grocery-shopping concept. This is not a finished website and I’m not testing you — I’m testing the design. I need about 15–20 minutes while you complete a shopping task and tell me what confused you. No payment, address, account, or real order is involved.

### WooCommerce builder / freelancer

> I’m researching a new grocery-first WooCommerce product before development starts. I want critical feedback from people who actually build or maintain WooCommerce sites — especially setup pain, plugin dependencies, performance, updates, customization, and what would make you reject a new theme. I’m looking for candid feedback, not promotion.

### Store owner / operator

> I’m validating a grocery ecommerce product before we build it. I’d like to understand what makes store setup, daily management, mobile shopping and delivery configuration difficult. I’ll show a rough prototype, not a finished sales pitch, and I specifically want to know what would stop you from paying for it.

## 3. Privacy and session setup

Before every session:
- use a unique participant code such as `S05`, `S06`, `B03`, or `O02`;
- never use `anonymous` when a unique anonymous ID can be assigned;
- do not put names/contact details in the exported research file;
- tell the participant this is a prototype and no real purchase will happen;
- do not ask for a real address, phone, email, password, payment method, or personal postcode;
- use fixture postcode `560001`;
- do not record audio/video without explicit permission;
- if the participant says something sensitive, do not copy it into GitHub notes.

The prototype session exporter is local-only. It intentionally does not record search terms or postcode values and sends no telemetry automatically.

## 4. Device requirement for shopper round

Use:
- a real mobile phone where possible; or
- approximately **390 × 844** / **400 × 874** CSS-pixel viewport.

Do not mix desktop traces into the next mobile shopper efficiency comparison.

Record the viewport/device in the participant note.

## 5. Facilitator rules

Do **not**:
- explain Search, Aisles, Buy again, Saved, or Cart before the task;
- tell the participant which category to choose;
- say “this is faster” or mention competitor weaknesses;
- rescue them immediately when they hesitate;
- ask “do you like it?” during the task;
- count facilitator setup clicks as shopper actions.

Do:
- note where they stop, backtrack, misread, or ask a question;
- ask “What are you looking for?” only after a meaningful hesitation;
- separate observation from interpretation;
- let mistakes happen unless the participant is completely blocked.

## 6. First-time shopper session

### Setup

1. Open the prototype on the mobile device/viewport.
2. Open **Test controls**.
3. Select **First-time test**.
4. Enter a unique anonymous participant code.
5. Select `Shopper`.
6. Press **Reset run**.
7. Close Test controls.

### Give only this task

> You are buying groceries for home. Confirm that delivery is available to postcode **560001**, then add the following items. Afterward remove Toor Dal completely, tell me how many items and what total you think are in the cart without opening it first, then open the cart.

```text
Amul Taaza Milk 1 L       ×2
Farm Eggs 6 pcs           ×2
Whole Wheat Bread 400 g   ×1
Sona Masoori Rice 5 kg    ×1
Toor Dal 1 kg             ×1
Fortune Sunflower Oil 1 L ×1
Fresh Tomato 1 kg         ×2
Banana Robusta 6 pcs      ×1
Bingo Potato Chips 90 g   ×2
Surf Excel Matic 1 kg     ×1
```

### Observe

Record:
- first action;
- time to first Add;
- whether delivery is understood without explanation;
- whether they use Search, Aisles, scrolling, or a combination;
- wrong product/pack selection;
- quantity corrections;
- whether persistent Cart feedback is noticed;
- whether cart count/total is understood before opening Cart;
- what `Aisles` means to them;
- whether the bottom dock blocks content or feels useful;
- every point where facilitator help is required.

### Mental-model check before explaining

After the task, point to the navigation/labels and ask:
- What would you expect **Saved** to contain?
- What would you expect **Cart** to contain?
- Are Saved and Cart clearly different to you? Why?

Do not explain the intended answer until they respond.

### Export

After the task:
1. Open **Test controls**.
2. Press **Export anonymous JSON**.
3. Save the JSON beside the participant note.
4. Push only anonymized/sanitized data to the public repository.

## 7. Repeat-shopper session

Reset the run and select **Repeat-shopper test**.

### Before the task

Without explaining the section, ask:

> What do you think **Buy again** means here?

Record the answer verbatim/near-verbatim where safe.

### Task

> Use **Buy again** to add at least five products you recognize from previous purchases. Then add three different products using any other method. Change the quantity of one repeated item, save one product for later, open **Saved**, then open the **Cart**.

Observe whether:
- `Buy again` is immediately understood as previous purchases;
- repeated products genuinely reduce searching/browsing;
- `Saved` is understood as future intent, not current cart contents;
- `Save for later` / `Remove from saved` are understood;
- shoppers know when to use Search versus Aisles;
- repeat content pushes important shopping controls too far down;
- Cart remains clearly the current purchase.

## 8. Post-task shopper questions

Only after the task:
- What was the easiest part?
- Where did you have to think?
- What did `Aisles` mean to you before you used it?
- What did `Buy again` mean before you used it?
- What did `Saved` mean before you opened it?
- How is Saved different from Cart?
- How did you know an item was added to Cart?
- Did you trust the delivery message? What would make you trust it more?
- Was the persistent cart bar useful or annoying?
- If you used this every week, what would you want to see first?
- What would you expect if an item had 500 ml and 1 L options?
- What felt unnecessary?

Do not ask “Would you buy this theme?” to shoppers; they validate storefront UX, not the B2B purchase decision.

## 9. Builder/store-owner interview

Start with their current experience before showing our proposition.

Ask:
- Which WooCommerce themes/builders have you used recently?
- What causes the most setup or maintenance pain?
- How many plugins do you normally need for a grocery/client store?
- What breaks most often after WooCommerce/theme/plugin updates?
- What makes you choose a paid theme rather than a free one?
- What makes you refund or reject a theme?
- How important is Elementor to your workflow?
- Would block-first/no mandatory Elementor be a benefit, drawback, or irrelevant?
- What proof would you require before trusting a new product with no marketplace review history?

Then show the current proposition:

> Grocery-first WooCommerce theme + core plugin focused on fast mobile multi-item shopping, Buy Again, Saved for later, delivery certainty, simple setup, and a small required-plugin stack.

Ask them to critique:
- positioning;
- feature value;
- missing essentials;
- maintenance risks;
- support expectations;
- documentation expectations;
- price expectation only after discussing value.

## 10. One-participant note template

Use `research/users/session-note-template.md` and create one file per person.

At minimum record:

```text
Participant ID:
Group: Shopper / Builder / Store owner
Date:
Device/browser + viewport:
Relevant experience:
Mode: First-time / Repeat-shopper / Interview

OBSERVED BEHAVIOR
-

MEASUREMENTS
- First add:
- Deliberate interactions:
- Surfaces:
- Product-detail transitions:
- Quantity/product mistakes:
- Needed facilitator help? Yes/No

MENTAL MODEL
- Buy again meant:
- Saved meant:
- Cart meant:
- Saved vs Cart distinction clear? Yes/No + why

CART COMPREHENSION
- Count understood before Cart? Yes/No
- Total understood before Cart? Yes/No

CONFUSION / HESITATION
-

WHAT WORKED UNUSUALLY WELL
-

DIRECT QUOTES (safe/anonymous only)
-

FEATURE REQUESTS (not automatically accepted)
-

RESEARCHER INTERPRETATION
-

V1 IMPLICATION
Keep / Change / Remove / Needs more evidence
```

## 11. Evidence threshold

One person's preference does not change the PRD.

Escalate when:
- a task is blocked for any participant -> investigate immediately;
- the same major confusion appears in 2+ early participants -> prototype change candidate;
- 3+ relevant participants independently identify the same pain/value -> strong product signal;
- builders repeatedly reject block-first/no-Elementor -> revisit builder strategy;
- shoppers repeatedly ignore or misunderstand an innovative element -> simplify/remove it rather than defending it.

## 12. Decision after the next round

### PROCEED
Core interaction and revised terminology are understood and valuable enough to begin a clean production engineering phase.

### REVISE
The concept has value, but Aisle Rail, delivery placement, Product Ledger, Cart feedback, Buy Again, or Saved still needs material redesign.

### REJECT / CHANGE THESIS
Users do not gain meaningful value over conventional grocery ecommerce flows; return to product research instead of forcing the concept into production.

The current decision from the first pilot is **REVISE**.
