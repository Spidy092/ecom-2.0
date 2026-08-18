# AisleFlow V0 — Round 2 Anonymous Exports

Use this folder only for the revised mobile shopper round after the first pilot terminology changes.

Expected files:

```text
S05.json
S06.json
S07.json
S08.json
S09.json
```

Before committing, verify each JSON:
- uses only the anonymous participant code;
- records the participant group as `shopper`;
- uses a mobile-sized viewport/device for this round;
- has `network_telemetry: false`;
- has `search_terms_recorded: false`;
- has `postcode_recorded: false`;
- contains no personal information.

Each export must have a corresponding observation note under `research/users/participants/`.
