#!/usr/bin/env python3
"""Validate third-party/demo asset licensing records for redistributable product content.

The verifier is intentionally conservative. Customer package roots may contain
first-party source files, but font/media/archive assets, vendored-path files and
minified vendor-like files require an explicit approved inventory record.
Remote runtime asset hotlinks are rejected so customer storefronts do not gain
an undocumented external dependency.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path
from typing import Iterable

import build_packages as package_builder

DEFAULT_REPO_ROOT = Path(__file__).resolve().parents[2]
DEFAULT_MANIFEST = Path("release/third-party-assets.json")
ALLOWED_TYPES = {
    "library",
    "font",
    "icon",
    "image",
    "illustration",
    "video",
    "audio",
    "demo-content",
    "other",
}
ALLOWED_SURFACES = {"theme", "core", "starter-demo", "docs", "sales-demo"}
ALLOWED_REDISTRIBUTION = {"approved", "review_required", "blocked"}
TEXT_EXTENSIONS = {".css", ".html", ".htm", ".js", ".json", ".php"}
REMOTE_ASSET_PATTERNS = (
    re.compile(r"url\(\s*['\"]?https?://", re.IGNORECASE),
    re.compile(r"@import\s+(?:url\()?\s*['\"]?https?://", re.IGNORECASE),
    re.compile(r"<(?:img|script|source|video|audio)\b[^>]*(?:src|srcset)=['\"]https?://", re.IGNORECASE),
)


class InventoryError(RuntimeError):
    pass


def fail(message: str) -> None:
    raise InventoryError(message)


def load_json(path: Path) -> dict:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise InventoryError(f"inventory is missing: {path}") from exc
    except json.JSONDecodeError as exc:
        raise InventoryError(f"inventory JSON is invalid: {exc}") from exc
    if not isinstance(data, dict):
        fail("inventory root must be an object")
    return data


def normalize_repo_path(value: str) -> str:
    value = value.replace("\\", "/").strip("/")
    if not value or value.startswith("../") or "/../" in f"/{value}/":
        fail(f"unsafe inventory path: {value!r}")
    return value


def validate_inventory(data: dict) -> None:
    if data.get("schema") != 1:
        fail("inventory schema must be 1")

    policy = data.get("policy")
    if not isinstance(policy, dict):
        fail("policy must be an object")

    roots = policy.get("customer_package_roots")
    if not isinstance(roots, dict) or set(roots) != {"theme", "core"}:
        fail("policy.customer_package_roots must define exactly theme and core")
    for surface, path in roots.items():
        if not isinstance(path, str):
            fail(f"package root for {surface} must be a string")
        normalize_repo_path(path)

    demo_roots = policy.get("demo_distribution_roots")
    if not isinstance(demo_roots, list) or not all(isinstance(item, str) for item in demo_roots):
        fail("policy.demo_distribution_roots must be a string array")
    for path in demo_roots:
        normalize_repo_path(path)

    extensions = policy.get("license_sensitive_extensions")
    if not isinstance(extensions, list) or not extensions:
        fail("policy.license_sensitive_extensions must be a non-empty array")
    for extension in extensions:
        if not isinstance(extension, str) or not extension.startswith(".") or extension != extension.lower():
            fail(f"invalid license-sensitive extension: {extension!r}")

    vendor_names = policy.get("vendor_directory_names")
    if not isinstance(vendor_names, list) or not vendor_names:
        fail("policy.vendor_directory_names must be a non-empty array")

    items = data.get("items")
    if not isinstance(items, list):
        fail("items must be an array")

    seen_ids: set[str] = set()
    seen_paths: set[str] = set()
    required_fields = {
        "id",
        "name",
        "version",
        "type",
        "author",
        "source_url",
        "license",
        "evidence",
        "paths",
        "surfaces",
        "modified",
        "notice_required",
        "redistribution_status",
        "reviewer",
        "reviewed_on",
    }

    for item in items:
        if not isinstance(item, dict):
            fail("each inventory item must be an object")
        missing = required_fields - set(item)
        if missing:
            fail(f"inventory item is missing fields: {sorted(missing)}")

        item_id = item["id"]
        if not isinstance(item_id, str) or not re.fullmatch(r"[a-z0-9][a-z0-9._-]*", item_id):
            fail(f"invalid inventory item id: {item_id!r}")
        if item_id in seen_ids:
            fail(f"duplicate inventory item id: {item_id}")
        seen_ids.add(item_id)

        if item["type"] not in ALLOWED_TYPES:
            fail(f"{item_id}: unsupported type {item['type']!r}")
        if item["redistribution_status"] not in ALLOWED_REDISTRIBUTION:
            fail(f"{item_id}: invalid redistribution_status")

        for field in ("name", "author", "source_url", "license", "evidence"):
            if not isinstance(item[field], str) or not item[field].strip():
                fail(f"{item_id}: {field} must be non-empty")

        if not re.match(r"https://", item["source_url"], re.IGNORECASE):
            fail(f"{item_id}: source_url must use https")

        if not isinstance(item["version"], str):
            fail(f"{item_id}: version must be a string")
        if not isinstance(item["modified"], bool) or not isinstance(item["notice_required"], bool):
            fail(f"{item_id}: modified and notice_required must be booleans")

        paths = item["paths"]
        if not isinstance(paths, list) or not paths:
            fail(f"{item_id}: paths must be a non-empty array")
        for path in paths:
            if not isinstance(path, str):
                fail(f"{item_id}: path must be a string")
            normalized = normalize_repo_path(path)
            if normalized in seen_paths:
                fail(f"inventory path is owned by multiple items: {normalized}")
            seen_paths.add(normalized)

        surfaces = item["surfaces"]
        if not isinstance(surfaces, list) or not surfaces:
            fail(f"{item_id}: surfaces must be a non-empty array")
        unknown = set(surfaces) - ALLOWED_SURFACES
        if unknown:
            fail(f"{item_id}: unsupported surfaces {sorted(unknown)}")

        if item["redistribution_status"] == "approved":
            if not isinstance(item["reviewer"], str) or not item["reviewer"].strip():
                fail(f"{item_id}: approved item requires reviewer")
            if not isinstance(item["reviewed_on"], str) or not re.fullmatch(r"\d{4}-\d{2}-\d{2}", item["reviewed_on"]):
                fail(f"{item_id}: approved item requires reviewed_on YYYY-MM-DD")


def iter_files(root: Path) -> Iterable[Path]:
    if not root.exists():
        return []
    return (path for path in root.rglob("*") if path.is_file())


def record_by_path(data: dict) -> dict[str, dict]:
    mapping: dict[str, dict] = {}
    for item in data["items"]:
        for path in item["paths"]:
            mapping[normalize_repo_path(path)] = item
    return mapping


def is_license_sensitive(relative: Path, sensitive_extensions: set[str], vendor_names: set[str]) -> bool:
    lowered_parts = {part.lower() for part in relative.parts}
    name = relative.name.lower()
    if relative.suffix.lower() in sensitive_extensions:
        return True
    if lowered_parts.intersection(vendor_names):
        return True
    if name.endswith(".min.js") or name.endswith(".min.css"):
        return True
    return False


def scan_remote_runtime_assets(path: Path) -> None:
    if path.suffix.lower() not in TEXT_EXTENSIONS:
        return
    try:
        text = path.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        fail(f"expected text runtime file is not UTF-8: {path}")
    for pattern in REMOTE_ASSET_PATTERNS:
        if pattern.search(text):
            fail(f"remote runtime asset/hotlink is not allowed in redistributable package source: {path}")


def scan_surface(repo_root: Path, surface: str, root_value: str, data: dict, mapping: dict[str, dict]) -> None:
    root_rel = normalize_repo_path(root_value)
    root = repo_root / root_rel
    if not root.is_dir():
        fail(f"configured {surface} root does not exist: {root_rel}")

    sensitive_extensions = {item.lower() for item in data["policy"]["license_sensitive_extensions"]}
    vendor_names = {item.lower() for item in data["policy"]["vendor_directory_names"]}

    for path in iter_files(root):
        repo_relative = path.relative_to(repo_root).as_posix()
        relative = path.relative_to(root)

        # Theme/Core scans must match the actual customer package boundary.
        # Tests, caches and other paths excluded by the package builder are not
        # redistributable inputs and therefore must not produce licensing noise.
        if surface in {"theme", "core"} and package_builder.is_excluded(relative):
            continue

        scan_remote_runtime_assets(path)
        if not is_license_sensitive(relative, sensitive_extensions, vendor_names):
            continue
        item = mapping.get(repo_relative)
        if item is None:
            fail(f"untracked license-sensitive redistributable file: {repo_relative}")
        if item["redistribution_status"] != "approved":
            fail(f"redistributable file is not approved: {repo_relative} ({item['redistribution_status']})")
        if surface not in item["surfaces"]:
            fail(f"{repo_relative}: inventory item does not declare {surface} surface")


def generate_notices(data: dict, surface: str) -> str:
    approved = [
        item
        for item in data["items"]
        if item["redistribution_status"] == "approved"
        and surface in item["surfaces"]
        and item["notice_required"]
    ]
    lines = [
        "# Third-Party Notices",
        "",
        "This file is generated from `release/third-party-assets.json`. Do not edit it manually.",
        "",
    ]
    if not approved:
        lines.extend(
            [
                "No third-party notices are currently required for the redistributed engineering-alpha package.",
                "",
                "The paid/public release remains separately blocked until final assets and package licensing are reviewed.",
                "",
            ]
        )
        return "\n".join(lines)

    for item in sorted(approved, key=lambda entry: entry["id"]):
        lines.extend(
            [
                f"## {item['name']}",
                "",
                f"- ID: `{item['id']}`",
                f"- Version: {item['version'] or 'not versioned'}",
                f"- Author/copyright: {item['author']}",
                f"- Source: {item['source_url']}",
                f"- License: {item['license']}",
                f"- Modified: {'yes' if item['modified'] else 'no'}",
                f"- Evidence: {item['evidence']}",
                "",
            ]
        )
    return "\n".join(lines)


def verify_notices(repo_root: Path, data: dict) -> None:
    for surface, root_value in data["policy"]["customer_package_roots"].items():
        notice_path = repo_root / normalize_repo_path(root_value) / "THIRD-PARTY-NOTICES.md"
        expected = generate_notices(data, surface)
        try:
            actual = notice_path.read_text(encoding="utf-8")
        except FileNotFoundError as exc:
            raise InventoryError(f"generated notice file is missing: {notice_path.relative_to(repo_root)}") from exc
        if actual != expected:
            fail(f"third-party notice drift: {notice_path.relative_to(repo_root)}")


def verify(repo_root: Path, manifest_path: Path) -> None:
    data = load_json(manifest_path)
    validate_inventory(data)
    mapping = record_by_path(data)

    for surface, root_value in data["policy"]["customer_package_roots"].items():
        scan_surface(repo_root, surface, root_value, data, mapping)

    for demo_root in data["policy"]["demo_distribution_roots"]:
        scan_surface(repo_root, "starter-demo", demo_root, data, mapping)

    verify_notices(repo_root, data)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--repo-root", default=str(DEFAULT_REPO_ROOT))
    parser.add_argument("--manifest", default=str(DEFAULT_MANIFEST))
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    repo_root = Path(args.repo_root).resolve()
    manifest = Path(args.manifest)
    if not manifest.is_absolute():
        manifest = repo_root / manifest
    try:
        verify(repo_root, manifest)
    except InventoryError as exc:
        print(f"asset-inventory: {exc}", file=sys.stderr)
        return 1
    print("asset-inventory: manifest, redistributable paths and notices are valid")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
