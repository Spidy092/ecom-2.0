#!/usr/bin/env python3
"""Validate committed store-owner onboarding research evidence without inventing it."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

RESULTS_DIR = Path("research/users/results/store-owner-onboarding-round1")
ALLOWED_ROLES = {"store_owner", "implementer", "agency_builder", "other_qualified"}
ALLOWED_OUTCOMES = {"pass", "assisted", "fail", "not_run"}
ALLOWED_CONFUSION = {
    "NAV_SETUP_DISCOVERY",
    "EXPECTS_THEME_OPTIONS",
    "EXPECTS_ELEMENTOR",
    "SITE_EDITOR_MENTAL_MODEL",
    "STYLE_VARIATION_MENTAL_MODEL",
    "HEADER_FOOTER_DISCOVERY",
    "DEMO_CONTENT_LOCKED",
    "COMMERCE_OWNERSHIP",
    "LAUNCH_CERTIFICATION",
    "WORDING_OTHER",
}
ALLOWED_CONFIDENCE = {"high", "medium", "low"}
ALLOWED_SITE_EDITOR_EXPERIENCE = {"none", "limited", "regular"}
FORBIDDEN_KEYS = {
    "name",
    "full_name",
    "email",
    "phone",
    "business_name",
    "company_name",
    "domain",
    "site_url",
    "store_url",
    "license_key",
    "password",
    "username",
    "customer_data",
    "order_data",
}
TASK_IDS = [f"T{i}" for i in range(1, 8)]
PARTICIPANT_RE = re.compile(r"^O\d{2,}$")


def fail(message: str) -> None:
    raise SystemExit(f"ERROR: {message}")


def collect_keys(value):
    keys = []
    if isinstance(value, dict):
        for key, item in value.items():
            keys.append(str(key).lower())
            keys.extend(collect_keys(item))
    elif isinstance(value, list):
        for item in value:
            keys.extend(collect_keys(item))
    return keys


def require_keys(obj: dict, required: set[str], context: str) -> None:
    missing = required.difference(obj)
    if missing:
        fail(f"{context} missing keys: {sorted(missing)}")


def validate_record(path: Path) -> str:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        fail(f"{path}: invalid JSON: {exc}")

    require_keys(
        data,
        {"schema_version", "participant_id", "session_date", "role", "environment", "tasks", "session_summary", "post_task"},
        str(path),
    )

    if data["schema_version"] != 1:
        fail(f"{path}: unsupported schema_version {data['schema_version']!r}")

    participant_id = data["participant_id"]
    if not isinstance(participant_id, str) or not PARTICIPANT_RE.fullmatch(participant_id):
        fail(f"{path}: participant_id must match O01/O02/... format")
    if path.stem != participant_id:
        fail(f"{path}: filename must equal participant_id ({participant_id}.json)")

    if data["role"] not in ALLOWED_ROLES:
        fail(f"{path}: invalid role {data['role']!r}")

    environment = data["environment"]
    require_keys(
        environment,
        {"device_class", "viewport", "wordpress_version", "woocommerce_version", "theme_version", "core_version", "site_editor_experience"},
        f"{path}: environment",
    )
    if environment["site_editor_experience"] not in ALLOWED_SITE_EDITOR_EXPERIENCE:
        fail(f"{path}: invalid site_editor_experience")

    tasks = data["tasks"]
    if set(tasks) != set(TASK_IDS):
        fail(f"{path}: tasks must be exactly {TASK_IDS}")

    passed = assisted = failed = 0
    for task_id in TASK_IDS:
        task = tasks[task_id]
        require_keys(task, {"outcome", "elapsed_seconds", "interventions", "confusion_codes", "note"}, f"{path}: {task_id}")
        if task["outcome"] not in ALLOWED_OUTCOMES:
            fail(f"{path}: {task_id} invalid outcome {task['outcome']!r}")
        if not isinstance(task["elapsed_seconds"], int) or task["elapsed_seconds"] < 0:
            fail(f"{path}: {task_id} elapsed_seconds must be a non-negative integer")
        if not isinstance(task["interventions"], int) or task["interventions"] < 0:
            fail(f"{path}: {task_id} interventions must be a non-negative integer")
        if not isinstance(task["confusion_codes"], list) or any(code not in ALLOWED_CONFUSION for code in task["confusion_codes"]):
            fail(f"{path}: {task_id} contains invalid confusion code")
        if task["outcome"] == "pass":
            passed += 1
        elif task["outcome"] == "assisted":
            assisted += 1
        elif task["outcome"] == "fail":
            failed += 1

    summary = data["session_summary"]
    require_keys(
        summary,
        {
            "seconds_to_first_personalization",
            "tasks_passed_unassisted",
            "tasks_assisted",
            "tasks_failed",
            "understands_demo_content_replaceable",
            "understands_commerce_ownership",
            "understands_launch_not_certification",
            "confidence",
        },
        f"{path}: session_summary",
    )
    if summary["tasks_passed_unassisted"] != passed:
        fail(f"{path}: summary pass count does not match task outcomes")
    if summary["tasks_assisted"] != assisted:
        fail(f"{path}: summary assisted count does not match task outcomes")
    if summary["tasks_failed"] != failed:
        fail(f"{path}: summary fail count does not match task outcomes")
    if summary["confidence"] not in ALLOWED_CONFIDENCE:
        fail(f"{path}: invalid confidence")

    forbidden = FORBIDDEN_KEYS.intersection(collect_keys(data))
    if forbidden:
        fail(f"{path}: forbidden PII/secret keys present: {sorted(forbidden)}")

    return participant_id


def commercial_gate(records: list[dict]) -> None:
    if len(records) < 5:
        fail(f"commercial gate requires at least 5 valid participant records; found {len(records)}")

    t1_find = sum(r["tasks"]["T1"]["outcome"] == "pass" for r in records)
    t2_to_t5_unassisted = sum(all(r["tasks"][task]["outcome"] == "pass" for task in ["T2", "T3", "T4", "T5"]) for r in records)
    demo_clear = sum(r["session_summary"]["understands_demo_content_replaceable"] is True for r in records)
    commerce_clear = sum(r["session_summary"]["understands_commerce_ownership"] is True for r in records)
    launch_clear = sum(r["session_summary"]["understands_launch_not_certification"] is True for r in records)

    if t1_find < 4:
        fail(f"commercial gate: only {t1_find}/{len(records)} independently passed T1")
    if t2_to_t5_unassisted < 4:
        fail(f"commercial gate: only {t2_to_t5_unassisted}/{len(records)} passed T2-T5 unassisted")
    if demo_clear < 4:
        fail(f"commercial gate: only {demo_clear}/{len(records)} understand demo content is replaceable")
    if commerce_clear < 4:
        fail(f"commercial gate: only {commerce_clear}/{len(records)} understand WooCommerce commerce ownership")
    if launch_clear != len(records):
        fail(f"commercial gate: {len(records)-launch_clear} participant(s) did not understand launch review is not certification")

    critical_codes = {"DEMO_CONTENT_LOCKED", "COMMERCE_OWNERSHIP", "LAUNCH_CERTIFICATION"}
    for code in critical_codes:
        affected = sum(any(code in task["confusion_codes"] for task in r["tasks"].values()) for r in records)
        if affected >= 2:
            fail(f"commercial gate: repeated critical confusion {code} in {affected} participants")

    print("COMMERCIAL GATE: PROCEED thresholds satisfied by committed participant evidence.")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--commercial-gate", action="store_true", help="Require >=5 sessions and evaluate Round-1 PROCEED thresholds")
    args = parser.parse_args()

    paths = sorted(RESULTS_DIR.glob("O*.json")) if RESULTS_DIR.exists() else []
    participant_ids = []
    records = []
    for path in paths:
        participant_id = validate_record(path)
        if participant_id in participant_ids:
            fail(f"duplicate participant_id {participant_id}")
        participant_ids.append(participant_id)
        records.append(json.loads(path.read_text(encoding="utf-8")))

    print(f"Validated {len(records)} store-owner onboarding participant record(s).")
    if args.commercial_gate:
        commercial_gate(records)
    else:
        print("Commercial gate not evaluated; pass --commercial-gate only after real participant evidence is committed.")


if __name__ == "__main__":
    main()
