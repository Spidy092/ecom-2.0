# AisleFlow V0 — Static Accessibility Audit

**Status:** Static/code review complete; manual assistive-technology validation still required  
**Date:** 2026-08-18  
**Issue:** #10

## 1. Scope

This audit reviews the low-fidelity prototype interaction model before production WordPress/WooCommerce implementation.

It is **not** a WCAG conformance report. No WCAG 2.2 AA claim is permitted from this document.

Manual tests still required:
- full 10-item keyboard-only mission;
- 200% zoom/reflow in a real browser;
- screen-reader passes on search, quantity, delivery, Shopping List, Cart and Basket Pulse;
- mobile/touch device checks;
- reduced-motion behavior in a real browser;
- production implementation retest after the prototype is translated into WordPress/WooCommerce code.

## 2. Standards baseline

Primary references:
- WCAG 2.2 — W3C Recommendation: https://www.w3.org/TR/WCAG22/
- WCAG 2.4.11 Focus Not Obscured (Minimum): https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum
- WCAG 2.5.8 Target Size (Minimum): https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html
- ARIA APG Button Pattern: https://www.w3.org/WAI/ARIA/apg/patterns/button/

## 3. Findings and prototype revisions

### A11Y-01 — Keyboard focus could be lost after quantity/save re-render

**Severity:** High for prototype validation  
**Status:** Patched in `a11y-normalize.js`

The prototype re-renders product rows after Add, increment/decrement and Save actions. The activated button is therefore removed from the DOM. Without focus restoration, keyboard users can lose their interaction position.

Revision:
- capture product/action/surface before the app re-renders;
- after re-render, focus the equivalent replacement action for the same product;
- when removing an item from Shopping List causes the row to disappear, move focus to the Shopping List surface.

Production requirement:
- prefer DOM/state updates that preserve the focused control where feasible instead of relying on a repair layer.

### A11Y-02 — Aisle Rail used incorrect tab semantics

**Severity:** Medium  
**Status:** Patched in prototype normalization layer

Aisles act as category/filter/navigation choices, not tab widgets with associated tabpanels. The initial prototype used `tablist` / `tab` semantics.

Revision:
- remove `tablist` and `tab` roles;
- expose aisle choices as normal buttons with selected state represented by `aria-pressed`;
- preserve focus on the newly rendered selected aisle button.

Production requirement:
- use ordinary buttons/navigation/filter semantics unless the final interaction truly implements the complete ARIA tabs pattern.

### A11Y-03 — Save/Saved mixed changing labels with toggle semantics

**Severity:** Medium  
**Status:** Patched

The initial UI changed the visible label between “Save to list” and “Saved” while also using `aria-pressed`. APG toggle-button guidance recommends keeping the label stable when `aria-pressed` communicates the state.

Revision:
- model the control as a command whose label describes the next action:
  - `Save to list`
  - `Remove from list`
- remove `aria-pressed` from this command model.

Production decision:
- either keep this command-label model, or use a true toggle button with a stable label and explicit pressed state; do not mix both patterns.

### A11Y-04 — Quantity and Basket Pulse created duplicate live announcements

**Severity:** Medium/High for frequent grocery interaction  
**Status:** Patched for prototype testing

Each quantity value had a polite live region while Basket Pulse also had `role=status`. Repeated rapid Add/quantity changes could produce duplicated/noisy announcements.

Revision:
- remove live behavior from individual quantity values;
- remove live-region semantics from the visible Basket Pulse container;
- create one hidden, debounced basket status region;
- announce the resulting product action plus current basket count/total.

Why this matters:
- grocery shopping can involve dozens of quantity actions, so an announcement pattern that is acceptable for one purchase can become unusable during a large basket mission.

Manual validation needed:
- NVDA/JAWS/VoiceOver/TalkBack behavior can differ; test the cadence before production.

### A11Y-05 — Search results could announce on every keystroke and unrelated re-render

**Severity:** Medium  
**Status:** Patched for prototype testing

The result summary was directly live. Product-list re-rendering also occurs after basket updates.

Revision:
- remove direct live semantics from the visible result summary;
- announce changed result count/context through a dedicated debounced status region;
- suppress duplicate identical announcements.

Manual validation needed:
- verify the 450 ms debounce is understandable rather than delayed/confusing.

### A11Y-06 — Cart/List surfaces did not intentionally move/return focus

**Severity:** High for keyboard flow  
**Status:** Patched

Opening a Cart or Shopping List surface changed/scrolls context but did not move focus. Closing also did not return to the launcher.

Revision:
- make Cart/List surfaces programmatically focusable;
- after opening, move focus to the surface;
- after Close/Escape, return focus to the launcher when it still exists.

Production requirement:
- if the final product uses a modal/drawer instead of an inline surface, implement the appropriate dialog focus-management pattern rather than copying this prototype behavior.

### A11Y-07 — Fixed dock and Basket Pulse can obscure focused controls

**Severity:** High at high zoom/narrow viewports  
**Status:** Prototype mitigation added; manual verification required

WCAG 2.2 adds Focus Not Obscured (Minimum), and fixed/sticky author UI is a common cause of failure.

Revision:
- on focus changes, measure the focused target against sticky header + mobile dock + visible Basket Pulse reserved regions;
- adjust scroll position when needed so the focused control remains visible.

Manual validation needed:
- 200% zoom;
- 320 CSS px-ish narrow viewport;
- browser UI/safe-area differences;
- focus on controls near the end of long lists.

Production preference:
- solve this through layout/scroll-padding and reduced overlapping fixed UI where possible, with JavaScript as a last-resort guard.

### A11Y-08 — Small secondary Save target

**Severity:** Medium  
**Status:** Prototype mitigation added

The Save command originally used very small vertical padding. WCAG 2.2 Target Size (Minimum) requires at least 24 × 24 CSS px unless an exception applies; larger touch targets remain a better usability target for a grocery/mobile product.

Revision:
- prototype normalization enforces at least 32 px height for the Save command;
- primary quantity controls remain approximately 40–48 px high depending on viewport.

Production target:
- aim for ~44 px touch targets on primary mobile grocery actions even though WCAG AA minimum is 24 px.

### A11Y-09 — Quantity groups need explicit grouping semantics

**Severity:** Low/Medium  
**Status:** Patched

The quantity wrapper had an accessible label but no semantic role.

Revision:
- add `role=group` while preserving product-specific labels on decrement/increment controls.

Manual validation needed:
- verify screen readers do not over-announce group context when rapidly traversing many products.

### A11Y-10 — Reduced motion

**Severity:** Medium  
**Status:** Present before this audit

The prototype stylesheet already changes smooth scrolling to automatic and collapses animation/transition durations under `prefers-reduced-motion: reduce`.

Manual validation needed:
- confirm no scripted interaction reintroduces smooth motion in the final implementation.

### A11Y-11 — Delivery status is not color-only

**Severity:** Pass in static review  
**Status:** Keep

Available/unavailable states use different colors **and explicit text** (“delivery available today” / “delivery is not available for this postcode”).

Production requirement:
- keep the textual state and avoid icon/color-only serviceability messages.

## 4. Items that static review cannot prove

Do not close Issue #10 yet. We still need real execution evidence for:
- tab sequence and focus restoration across the full 10-item mission;
- screen-reader announcement frequency/quality;
- whether horizontal Aisle Rail is understandable and operable with keyboard/switch input;
- 200% zoom and mobile-dock overlap;
- browser differences in `focus({preventScroll:true})` + scripted visibility correction;
- touch target usability on physical mobile devices;
- color contrast of final visual system (current prototype colors are temporary);
- accessible name quality after final branding/copy changes.

## 5. Production acceptance criteria derived from this audit

When the WordPress implementation begins, each interactive grocery component must demonstrate:

1. no focus loss after asynchronous state updates;
2. no required hover/drag-only action;
3. explicit product context in quantity accessible names;
4. one intentional announcement channel for high-frequency cart changes;
5. serviceability communicated in text;
6. focus not obscured by sticky/fixed Grovia UI at 200% zoom;
7. primary touch targets designed for comfortable mobile use;
8. reduced-motion support;
9. Cart/List/drawer/dialog focus management appropriate to the final pattern;
10. keyboard-only completion of the fixed grocery mission before release.

## 6. Current conclusion

The prototype had meaningful accessibility issues, especially **focus preservation and live-region noise**, but those issues were found early enough that they can shape the production interaction model rather than become retrofit bugs.

Issue #10 remains open pending manual execution evidence.
