# AisleFlow V0 — Round 2 terminology results

**Status:** Two mobile shopper sessions recorded; continue the planned round before production.
**Date:** 2026-08-18
**Exports:** `research/users/results/aisleflow-v0-round2/`
**Observation notes:** `research/users/participants/S05.md`, `research/users/participants/S06.md`

## Evidence boundary

This synthesis covers two revised-terminology sessions only. Both exports use anonymous codes, record the group as `shopper`, use mobile-sized viewports, and declare that no network telemetry, search text, or postcode values were recorded.

| Participant | Mode | Viewport | First add | Direct terminology result |
| --- | --- | --- | ---: | --- |
| S05 | First-time | 440 × 956 | 42s | Cart and Saved were understood without guidance. |
| S06 | First-time | 412 × 924 | 70s | Cart and Saved for later were understood without guidance. |

The participants did not run identical fixed missions, so timings and interaction counts are not efficiency comparisons.

## Findings

### Revised Cart and Saved terminology

**Positive signal — two mobile shoppers.** Both participants reported that Cart and Saved for later were understandable without an explanation. S06 also preferred that Saved remains outside the main shopping surface until explicitly opened, because this prevents visual crowding.

**Decision:** Keep the revised shopper-facing terminology and separate on-demand Saved surface for the remaining Round 2 sessions.

### Search recovery

S06 typed `tomoto` while looking for Tomato and initially interpreted the absence of a result as product unavailability.

**Decision:** Retest this problem. The smallest candidate improvement is clear no-result recovery guidance, such as checking spelling or browsing the relevant aisle. Do not add a broad fuzzy-search or AI search dependency from one observation.

## Production gate

The production gate remains closed. The planned requirement is five relevant mobile shopper sessions with the revised language, plus fixed-mission observations and buyer/store-owner evidence. This result raises confidence in the Cart/Saved revision but does not prove the full grocery interaction system yet.
