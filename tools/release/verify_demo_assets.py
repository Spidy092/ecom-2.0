#!/usr/bin/env python3
"""Validate canonical Modern Grocery demo/starter image provenance.

The verifier is dependency-free and deliberately separates first-party demo
assets from third-party redistribution. An image may exist in the canonical
starter root only when it has an approved manifest record. Third-party assets
also require an approved record in release/third-party-assets.json.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
DEFAULT_MANIFEST = Path("release/demo-assets.json")
DEFAULT_THIRD_PARTY = Path("release/third-party-assets.json")
ALLOWED_PROVENANCE = {
    "first-party-generated",
    "first-party-created",
    "third-party-licensed",
}
ALLOWED_ROLES = {"product", "department", "campaign", "placeholder"}
ALLOWED_SURFACES = {"starter", "live-demo", "sales-screenshot"}
ALLOWED_STATUS = {"draft", "review_required", "approved", "blocked"}
FIRST_PARTY = {"first-party-generated", "first-party-created"}


class DemoAssetError(RuntimeError):
    pass


def fail(message: str) -> None:
    raise DemoAssetError(message)


def load_json(path: Path) -> dict:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise DemoAssetError(f"manifest is missing: {path}") from exc
    except json.JSONDecodeError as exc:
        raise DemoAssetError(f"invalid JSON in {path}: {exc}") from exc
    if not isinstance(data, dict):
        fail(f"manifest root must be an object: {path}")
    return data


def normalize_repo_path(value: str) -> str:
    value = value.replace("\\", "/").strip("/")
    if not value or value.startswith("../") or "/../" in f"/{value}/":
        fail(f"unsafe repository path: {value!r}")
    return value


def validate_id(value: object, label: str) -> str:
    if not isinstance(value, str) or not re.fullmatch(r"[a-z0-9][a-z0-9._-]*", value):
        fail(f"{label} has invalid id: {value!r}")
    return value


def validate_policy(data: dict) -> tuple[str, set[str], dict[str, int]]:
    if data.get("schema") != 1:
        fail("demo asset schema must be 1")
    policy = data.get("policy")
    if not isinstance(policy, dict):
        fail("policy must be an object")

    root = policy.get("distribution_root")
    if not isinstance(root, str):
        fail("policy.distribution_root must be a string")
    root = normalize_repo_path(root)

    if set(policy.get("allowed_provenance", [])) != ALLOWED_PROVENANCE:
        fail("policy.allowed_provenance does not match verifier contract")
    if set(policy.get("allowed_roles", [])) != ALLOWED_ROLES:
        fail("policy.allowed_roles does not match verifier contract")
    if set(policy.get("allowed_surfaces", [])) != ALLOWED_SURFACES:
        fail("policy.allowed_surfaces does not match verifier contract")

    extensions = policy.get("allowed_extensions")
    if not isinstance(extensions, list) or not extensions:
        fail("policy.allowed_extensions must be a non-empty array")
    extension_set: set[str] = set()
    for extension in extensions:
        if not isinstance(extension, str) or not re.fullmatch(r"\.[a-z0-9]+", extension):
            fail(f"invalid allowed extension: {extension!r}")
        extension_set.add(extension)

    budgets = policy.get("max_bytes_by_role")
    if not isinstance(budgets, dict) or set(budgets) != ALLOWED_ROLES:
        fail("policy.max_bytes_by_role must define every allowed role")
    normalized_budgets: dict[str, int] = {}
    for role, value in budgets.items():
        if not isinstance(value, int) or value <= 0:
            fail(f"invalid byte budget for {role}")
        normalized_budgets[role] = value

    return root, extension_set, normalized_budgets


def validate_planned_assets(data: dict) -> set[str]:
    planned = data.get("planned_assets")
    if not isinstance(planned, list):
        fail("planned_assets must be an array")
    seen: set[str] = set()
    required = {"id", "role", "fixture", "target_width", "target_height", "notes"}
    for entry in planned:
        if not isinstance(entry, dict):
            fail("each planned asset must be an object")
        missing = required - set(entry)
        if missing:
            fail(f"planned asset is missing fields: {sorted(missing)}")
        asset_id = validate_id(entry["id"], "planned asset")
        if asset_id in seen:
            fail(f"duplicate planned asset id: {asset_id}")
        seen.add(asset_id)
        if entry["role"] not in ALLOWED_ROLES:
            fail(f"{asset_id}: invalid role")
        if not isinstance(entry["fixture"], str) or not entry["fixture"].strip():
            fail(f"{asset_id}: fixture must be non-empty")
        for field in ("target_width", "target_height"):
            if not isinstance(entry[field], int) or entry[field] <= 0:
                fail(f"{asset_id}: {field} must be a positive integer")
        if not isinstance(entry["notes"], str) or not entry["notes"].strip():
            fail(f"{asset_id}: notes must be non-empty")
    return seen


def approved_third_party_items(path: Path) -> dict[str, dict]:
    data = load_json(path)
    items = data.get("items")
    if not isinstance(items, list):
        fail("third-party items must be an array")
    result: dict[str, dict] = {}
    for item in items:
        if not isinstance(item, dict):
            continue
        item_id = item.get("id")
        if isinstance(item_id, str) and item.get("redistribution_status") == "approved":
            result[item_id] = item
    return result


def validate_items(
    repo_root: Path,
    data: dict,
    distribution_root: str,
    extensions: set[str],
    budgets: dict[str, int],
    planned_ids: set[str],
    third_party: dict[str, dict],
) -> tuple[dict[str, dict], set[str]]:
    items = data.get("items")
    if not isinstance(items, list):
        fail("items must be an array")

    required = {
        "id",
        "role",
        "fixture",
        "path",
        "provenance",
        "source_reference",
        "rights_evidence",
        "surfaces",
        "width",
        "height",
        "status",
        "reviewer",
        "reviewed_on",
        "alt_guidance",
        "identifiable_people",
        "third_party_logos",
        "readable_branded_packaging",
        "third_party_item_id",
    }
    by_path: dict[str, dict] = {}
    first_party_approved: set[str] = set()
    seen_ids: set[str] = set()

    for item in items:
        if not isinstance(item, dict):
            fail("each demo item must be an object")
        missing = required - set(item)
        if missing:
            fail(f"demo item is missing fields: {sorted(missing)}")
        asset_id = validate_id(item["id"], "demo item")
        if asset_id in seen_ids:
            fail(f"duplicate demo item id: {asset_id}")
        seen_ids.add(asset_id)
        if planned_ids and asset_id not in planned_ids:
            fail(f"{asset_id}: item is not present in planned_assets")

        role = item["role"]
        provenance = item["provenance"]
        status = item["status"]
        if role not in ALLOWED_ROLES:
            fail(f"{asset_id}: invalid role")
        if provenance not in ALLOWED_PROVENANCE:
            fail(f"{asset_id}: invalid provenance")
        if status not in ALLOWED_STATUS:
            fail(f"{asset_id}: invalid status")

        path_value = normalize_repo_path(item["path"])
        root_prefix = distribution_root.rstrip("/") + "/"
        if not path_value.startswith(root_prefix):
            fail(f"{asset_id}: path must be inside {distribution_root}")
        if Path(path_value).suffix.lower() not in extensions:
            fail(f"{asset_id}: file extension is not allowed")
        if path_value in by_path:
            fail(f"demo file is owned by multiple entries: {path_value}")
        by_path[path_value] = item

        for field in ("fixture", "source_reference", "rights_evidence", "alt_guidance"):
            if not isinstance(item[field], str) or not item[field].strip():
                fail(f"{asset_id}: {field} must be non-empty")

        surfaces = item["surfaces"]
        if not isinstance(surfaces, list) or not surfaces or set(surfaces) - ALLOWED_SURFACES:
            fail(f"{asset_id}: invalid surfaces")
        for field in ("width", "height"):
            if not isinstance(item[field], int) or item[field] <= 0:
                fail(f"{asset_id}: {field} must be a positive integer")
        for field in ("identifiable_people", "third_party_logos", "readable_branded_packaging"):
            if not isinstance(item[field], bool):
                fail(f"{asset_id}: {field} must be boolean")

        file_path = repo_root / path_value
        if status == "approved":
            if not isinstance(item["reviewer"], str) or not item["reviewer"].strip():
                fail(f"{asset_id}: approved asset requires reviewer")
            if not isinstance(item["reviewed_on"], str) or not re.fullmatch(r"\d{4}-\d{2}-\d{2}", item["reviewed_on"]):
                fail(f"{asset_id}: approved asset requires reviewed_on YYYY-MM-DD")
            if not file_path.is_file():
                fail(f"{asset_id}: approved asset file is missing: {path_value}")
            if file_path.stat().st_size > budgets[role]:
                fail(f"{asset_id}: file exceeds {role} byte budget ({file_path.stat().st_size} > {budgets[role]})")

            if provenance in FIRST_PARTY:
                if item["identifiable_people"] or item["third_party_logos"] or item["readable_branded_packaging"]:
                    fail(f"{asset_id}: canonical first-party asset contains a blocked visual-rights flag")
                if item["third_party_item_id"] not in (None, ""):
                    fail(f"{asset_id}: first-party asset must not declare third_party_item_id")
                first_party_approved.add(path_value)
            else:
                third_party_item_id = item["third_party_item_id"]
                if not isinstance(third_party_item_id, str) or not third_party_item_id:
                    fail(f"{asset_id}: third-party asset requires third_party_item_id")
                record = third_party.get(third_party_item_id)
                if record is None:
                    fail(f"{asset_id}: linked third-party item is not approved: {third_party_item_id}")
                declared_paths = {normalize_repo_path(value) for value in record.get("paths", []) if isinstance(value, str)}
                if path_value not in declared_paths:
                    fail(f"{asset_id}: linked third-party item does not own {path_value}")
        elif file_path.exists():
            fail(f"{asset_id}: demo file exists before approval ({status}): {path_value}")

    return by_path, first_party_approved


def scan_distribution_root(
    repo_root: Path,
    distribution_root: str,
    extensions: set[str],
    by_path: dict[str, dict],
) -> None:
    root = repo_root / distribution_root
    if not root.is_dir():
        fail(f"distribution root does not exist: {distribution_root}")
    for path in root.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in extensions:
            continue
        repo_relative = path.relative_to(repo_root).as_posix()
        item = by_path.get(repo_relative)
        if item is None:
            fail(f"untracked canonical demo image: {repo_relative}")
        if item["status"] != "approved":
            fail(f"canonical demo image is not approved: {repo_relative} ({item['status']})")


def verify(repo_root: Path, manifest_path: Path, third_party_path: Path) -> set[str]:
    data = load_json(manifest_path)
    distribution_root, extensions, budgets = validate_policy(data)
    planned_ids = validate_planned_assets(data)
    third_party = approved_third_party_items(third_party_path)
    by_path, first_party_approved = validate_items(
        repo_root,
        data,
        distribution_root,
        extensions,
        budgets,
        planned_ids,
        third_party,
    )
    scan_distribution_root(repo_root, distribution_root, extensions, by_path)
    return first_party_approved


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--repo-root", default=str(REPO_ROOT))
    parser.add_argument("--manifest", default=str(DEFAULT_MANIFEST))
    parser.add_argument("--third-party", default=str(DEFAULT_THIRD_PARTY))
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    repo_root = Path(args.repo_root).resolve()
    manifest = Path(args.manifest)
    third_party = Path(args.third_party)
    if not manifest.is_absolute():
        manifest = repo_root / manifest
    if not third_party.is_absolute():
        third_party = repo_root / third_party
    try:
        first_party = verify(repo_root, manifest, third_party)
    except DemoAssetError as exc:
        print(f"demo-assets: {exc}", file=sys.stderr)
        return 1
    print(f"demo-assets: manifest and canonical distribution root are valid ({len(first_party)} approved first-party files)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
