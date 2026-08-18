# V1 Real-User Session Kit

**Status:** Ready to use  
**Issue:** #5  
**Rule:** observe behavior before asking for opinions.

## 1. Who to recruit first

We do not need hundreds of people for the first prototype decision.

Initial target:
- 5–8 people who regularly buy groceries on mobile;
- 3–5 people who have built WooCommerce stores for clients;
- 3–5 small ecommerce/store owners or operators.

Avoid using only friends or people who want to be polite.

## 2. Short recruitment messages

### Grocery shopper

> I’m testing a very early mobile grocery-shopping concept. This is not a finished website and I’m not testing you — I’m testing the design. I need about 15–20 minutes while you complete a shopping task and tell me what confused you. No payment, address, account, or real order is involved.

### WooCommerce builder / freelancer

> I’m researching a new grocery-first WooCommerce product before development starts. I want critical feedback from people who actually build or maintain WooCommerce sites — especially setup pain, plugin dependencies, performance, updates, customization, and what would make you reject a new theme. I’m looking for candid feedback, not promotion.

### Store owner / operator

> I’m validating a grocery ecommerce product before we build it. I’d like to understand what makes store setup, daily management, mobile shopping and delivery configuration difficult. I’ll show a rough prototype, not a finished sales pitch, and I specifically want to know what would stop you from paying for it.

## 3. Privacy and session setup

Before the session:
- use a participant code such as `S01`, `B01`, or `O01`; do not put names in the exported research file;
- tell the participant this is a prototype and no real purchase will happen;
- do not ask them to enter a real address, phone, email, password, payment method, or personal postcode;
- use the fixture postcode `560001`;
- do not record audio/video without explicit permission;
- if the participant says something sensitive, do not copy it into GitHub notes.

The prototype session exporter is local-only. It intentionally does not record search terms or postcode values and sends no telemetry automatically.

## 4. Facilitator rules

Do **not**:
- explain where Search/Aisles/List/Cart are before the task;
- tell the participant which category to choose;
- say “this is faster” or mention competitor weaknesses;
- rescue them immediately when they hesitate;
- ask “do you like it?” during the task;
- count your own facilitator setup clicks as shopper actions.

Do:
- note where they stop, backtrack, misread, or ask a question;
- ask “What are you looking for?” only after a meaningful hesitation;
- separate observation from interpretation;
- let mistakes happen unless the participant is completely blocked.

## 5. First-time shopper session

### Setup

1. Open the prototype at a mobile-sized viewport/device.
2. Open **Test controls**.
3. Select **First-time**.
4. Enter an anonymous participant code.
5. Select `Shopper`.
6. Press **Reset run**.
7. Close Test controls.

### Give only this task

> You are buying groceries for home. Confirm that delivery is available to postcode **560001**, then add the following items. Afterward remove Toor Dal completely, tell me how many items and what total you think are in the basket without opening it first, then open the basket.

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
- any wrong product/pack selection;
- quantity corrections;
- whether they notice Basket Pulse;
- whether they understand the cart count/total before opening Cart;
- whether “List” and “Aisles” mean what they expect;
- any point where they ask for help;
- whether the bottom dock blocks content or feels useful.

### Export

After the task:
1. Open **Test controls**.
2. Press **Export anonymous JSON**.
3. Store the file with the participant note; do not commit personal information.

## 6. Returning-shopper session

Reset the run and switch to **Returning**.

Task:

> Add at least five products you would recognize from “This week”, then add three different products using any other method. Change the quantity of one repeated item, open Shopping List, then open the basket.

Observe whether:
- This Week is immediately understood;
- repeat items genuinely reduce searching/browsing;
- the section feels helpful or like clutter;
- shoppers know when to use Search versus Aisles;
- returning content pushes important shopping controls too far down.

## 7. Post-task shopper questions

Only after the task:
- What was the easiest part?
- Where did you have to think?
- What did “Aisles” mean to you before you used it?
- What did “List” mean to you before you opened it?
- How did you know an item was added?
- Did you trust the delivery message? What would make you trust it more?
- Was the basket bar useful or annoying?
- If you used this every week, what would you want to see first?
- What would you expect if an item had 500 ml and 1 L options?
- What felt unnecessary?

Do not ask “Would you buy this theme?” to shoppers; they are validating the storefront UX, not the B2B purchase decision.

## 8. Builder/store-owner interview

Start with their experience before showing the concept.

Ask:
- Which WooCommerce themes/builders have you used recently?
- What has caused the most setup or maintenance pain?
- How many plugins do you normally need for a grocery/client store?
- What breaks most often after WooCommerce/theme/plugin updates?
- What makes you choose a paid theme rather than a free one?
- What makes you refund or reject a theme?
- How important is Elementor to your workflow?
- Would a block-first product with no mandatory Elementor be a benefit, drawback, or irrelevant?
- What proof would you require before trusting a new product with no marketplace review history?

Then show the current proposition:

> Grocery-first WooCommerce theme + core plugin focused on fast mobile multi-item shopping, repeat purchasing, delivery certainty, simple setup, and a small required-plugin stack.

Ask them to critique:
- positioning;
- feature value;
- missing essentials;
- maintenance risks;
- support expectations;
- documentation expectations;
- price expectation only after discussing value.

## 9. One-participant note template

```text
Participant ID:
Group: Shopper / Builder / Store owner
Date:
Device/browser:
Relevant experience:
Mode: First-time / Returning / Interview

OBSERVED BEHAVIOR
- 

MEASUREMENTS
- First add:
- Deliberate interactions:
- Surfaces:
- Product-detail transitions:
- Quantity/product mistakes:
- Needed facilitator help? Yes/No

BASKET COMPREHENSION
- Count understood before Cart? Yes/No
- Total understood before Cart? Yes/No

CONFUSION / HESITATION
- 

WHAT WORKED UNUSUALLY WELL
- 

DIRECT QUOTES (only if safe/anonymous)
- 

FEATURE REQUESTS (not automatically accepted)
- 

RESEARCHER INTERPRETATION
- 

V1 IMPLICATION
Keep / Change / Remove / Needs more evidence
```

## 10. Evidence threshold

One person's preference does not change the PRD.

Escalate when:
- a task is blocked for any participant -> investigate immediately;
- the same major confusion appears in 2+ early participants -> prototype change candidate;
- 3+ relevant participants independently identify the same pain/value -> strong product signal;
- builders repeatedly reject block-first/no-Elementor -> revisit builder strategy;
- shoppers repeatedly ignore or misunderstand an “innovative” element -> simplify/remove it rather than defending it.

## 11. The decision we need from these sessions

After the first round, we should be able to choose one of three outcomes:

### PROCEED
Core interaction is understood and valuable; only normal iteration remains.

### REVISE
The concept has value, but Aisle Rail, delivery placement, Product Ledger, Basket Pulse or Household Rhythm needs material redesign before WordPress implementation.

### REJECT / CHANGE THESIS
Users do not gain meaningful value over conventional grocery ecommerce flows; return to product research instead of forcing the concept into production.
