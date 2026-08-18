# AisleFlow V0 — Tester Kit

**Purpose:** run repeatable research sessions on the grocery interaction model before production WordPress/WooCommerce implementation.

**Prototype:** research-only, temporary name, unfinished UI. Do not present it as the final product.

## 1. Who to test with

Use three groups separately:

- **Grocery shoppers:** people who buy groceries online on mobile.
- **WooCommerce builders:** freelancers/developers/designers who have built stores.
- **Store owners/operators:** people responsible for choosing, operating or maintaining ecommerce software.

Initial target:
- 5–8 grocery shoppers;
- 3–5 WooCommerce builders;
- 3–5 store owners/operators.

Do not recruit only friends who will be polite.

## 2. Researcher rules

Before each session:

1. Do not pitch the product.
2. Do not explain Aisle Rail, Basket Pulse, Buy Again or Shopping List before the participant uses them.
3. Say: **“We are testing the product, not you.”**
4. Ask the participant to speak when something surprises or confuses them.
5. Help only when they are genuinely blocked; record the help as a failure/research event.
6. Separate observation from interpretation.
7. Never invent or improve a participant quote.
8. Do not add a feature to the PRD because one person requests it.

## 3. Shopper session — first-time mission

Reset the prototype and keep it in **First-time** mode.

Give the participant this shopping list:

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

Then ask them to:

- confirm delivery to postcode `560001`;
- add the products and quantities;
- remove **Toor Dal** entirely after adding it;
- tell you the current basket count/total **before** opening Cart;
- open Cart and confirm what is there.

Do not tell them whether to use Search or Aisles.

## 4. Shopper session — returning household

Reset the prototype. The facilitator opens **Test controls**, switches to **Returning**, clicks **Reset run**, then closes Test controls before handing the device back.

Ask the participant to:

- use `This Week / Buy Again` to add five products they would repeat;
- add three different products from elsewhere in the store;
- change the quantity of one repeated product;
- open Shopping List;
- continue to Cart.

The research question is not “is returning mode faster?” The question is whether repeat-shopping actually reduces mental/search effort without adding confusing clutter.

## 5. What to record

For each mission record:

- time to first meaningful basket action;
- deliberate interactions from the facilitator research meter;
- surfaces/transitions;
- Search terms used;
- Aisle choices used;
- quantity mistakes/corrections;
- whether pack/unit information was noticed;
- whether delivery status was trusted/understood;
- whether Basket Pulse/cart count was noticed;
- whether `Aisles`, `List` and `This Week` meant what we expected;
- any product-detail page the participant expected to need;
- hesitation or rereading;
- places where the researcher had to help.

The automated lower bound currently recorded in the repo is **17 deliberate interactions** for the fixed first-time mission. A human score higher than 17 is expected. The important evidence is **why** the additional effort occurred.

## 6. Post-task questions

Ask after the task, not before:

1. What felt easiest?
2. Where did you have to stop and think?
3. What did you expect to happen that did not happen?
4. What was present but unnecessary?
5. How did you know an item was actually in the basket?
6. Did you trust the delivery result? Why or why not?
7. Before opening it, what did `Aisles` mean to you?
8. Before opening it, what did `List` mean to you?
9. What would you expect when a product has 500 ml and 1 L options?
10. If you used this store every week, what would you want to see first?

## 7. Builder / store-owner interview

Show the product proposition only after gathering background:

> Grocery-first WooCommerce theme + core plugin focused on fast mobile shopping, repeat purchasing, delivery certainty, simple setup and a small required plugin stack.

Ask:

- Which current theme/product would you compare this against?
- What would make you reject it immediately?
- What setup/import problems have you had with commercial themes?
- How many required plugins feels acceptable?
- Does “no mandatory Elementor” help, hurt or not matter?
- What normally breaks after WooCommerce updates?
- What proof would you need before paying for a new product with no review history?
- What should the seller maintain/support for a yearly fee?
- Which feature in our V1 sounds genuinely valuable and which sounds like marketing?

Do not lead with the `$59/year` hypothesis. Discuss pricing only after value/proof questions.

## 8. Accessibility subset

At least one dedicated pass must include:

- keyboard-only fixed mission;
- 200% browser zoom/reflow;
- a real screen reader on Search, quantity controls, delivery result, Shopping List, Cart and Basket Pulse;
- narrow mobile viewport/device;
- reduced-motion preference.

Automated browser smoke tests already exist, but they do not prove WCAG conformance.

## 9. Evidence handling

- Use anonymous participant IDs unless attribution is explicitly approved.
- Get permission before recording audio/video/screen.
- Do not collect real payment data/passwords.
- Do not store unnecessary personal information.
- Keep direct quotes separate from researcher interpretation.
- Record severe one-person failures even if they are not frequent; severity matters.

Use `research/users/session-note-template.md` for every session.

## 10. Decision rule after initial sessions

Create a synthesis table:

| Problem/observation | Participants | Severity | Frequency | V1 relevance | Action |
| --- | ---: | --- | --- | --- | --- |

Classify:
- **Critical:** blocks task/trust/security; fix before production.
- **Major:** repeated substantial friction; likely V1 change.
- **Moderate:** meaningful improvement; prioritize against scope.
- **Minor:** polish/individual preference.
- **Request:** requested feature without proven underlying problem yet.

The goal is not to make everyone happy. The goal is to discover whether our proposed product is materially easier and more valuable for its target users.
