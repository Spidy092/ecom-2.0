#!/usr/bin/env python3
"""Deterministic tests for the third-party asset inventory release gate."""

from __future__ import annotations

import json
import tempfile
from pathlib import Path

import verify_asset_inventory as verifier


BASE_POLICY = {
    "customer_package_roots": {
        "theme": "packages/storefront-theme",
        "core": "packages/storefront-core",
    },
    "demo_distribution_roots": [],
    "license_sensitive_extensions": [".png", ".svg", ".woff2"],
    "vendor_directory_names": ["third-party", "third_party", "vendor", "vendors"],
}


def write(path: Path, content: str | bytes) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    if isinstance(content, bytes):
        path.write_bytes(content)
    else:
        path.write_text(content, encoding="utf-8")


def manifest(items: list[dict]) -> dict:
    return {
        "schema": 1,
        "status": "test",
        "policy": BASE_POLICY,
        "items": items,
        "paid_release_blockers": [],
        "notes": [],
    }


def approved_font() -> dict:
    return {
        "id": "fixture-font",
        "name": "Fixture Font",
        "version": "1.0.0",
        "type": "font",
        "author": "Fixture Author",
        "source_url": "https://example.test/fixture-font",
        "license": "OFL-1.1",
        "evidence": "fixture-only test evidence",
        "paths": ["packages/storefront-theme/assets/fonts/fixture.woff2"],
        "surfaces": ["theme"],
        "modified": False,
        "notice_required": True,
        "redistribution_status": "approved",
        "reviewer": "fixture-reviewer",
        "reviewed_on": "2026-08-19",
    }


def set_notices(root: Path, data: dict) -> None:
    for surface, package_root in data["policy"]["customer_package_roots"].items():
        write(root / package_root / "THIRD-PARTY-NOTICES.md", verifier.generate_notices(data, surface))


def write_manifest(root: Path, data: dict) -> Path:
    path = root / "release/third-party-assets.json"
    write(path, json.dumps(data, indent=2) + "\n")
    return path


def expect_failure(root: Path, manifest_path: Path, expected: str) -> None:
    try:
        verifier.verify(root, manifest_path)
    except verifier.InventoryError as exc:
        assert expected in str(exc), (expected, str(exc))
    else:
        raise AssertionError(f"Expected InventoryError containing: {expected}")


def main() -> None:
    with tempfile.TemporaryDirectory() as tmp:
        root = Path(tmp)
        write(root / "packages/storefront-theme/style.css", "/* first-party fixture */\n")
        write(root / "packages/storefront-core/storefront-core.php", "<?php // first-party fixture\n")

        empty = manifest([])
        set_notices(root, empty)
        manifest_path = write_manifest(root, empty)
        verifier.verify(root, manifest_path)

        font_path = root / "packages/storefront-theme/assets/fonts/fixture.woff2"
        write(font_path, b"fixture-font")
        expect_failure(root, manifest_path, "untracked license-sensitive redistributable file")

        review_item = approved_font()
        review_item["redistribution_status"] = "review_required"
        review_item["reviewer"] = ""
        review_item["reviewed_on"] = ""
        under_review = manifest([review_item])
        set_notices(root, under_review)
        manifest_path = write_manifest(root, under_review)
        expect_failure(root, manifest_path, "redistributable file is not approved")

        approved = manifest([approved_font()])
        set_notices(root, approved)
        manifest_path = write_manifest(root, approved)
        verifier.verify(root, manifest_path)

        write(root / "packages/storefront-theme/assets/css/remote.css", "body{background:url(https://cdn.example.test/a.png)}\n")
        expect_failure(root, manifest_path, "remote runtime asset/hotlink is not allowed")

        (root / "packages/storefront-theme/assets/css/remote.css").unlink()
        write(root / "packages/storefront-core/vendor/helper.js", "window.fixture=true;\n")
        expect_failure(root, manifest_path, "untracked license-sensitive redistributable file")

    print("asset-inventory tests: untracked/review-only/hotlink/vendor gates passed")


if __name__ == "__main__":
    main()
