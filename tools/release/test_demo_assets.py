#!/usr/bin/env python3
"""Adversarial tests for tools/release/verify_demo_assets.py."""

from __future__ import annotations

import copy
import json
import tempfile
from pathlib import Path

from verify_demo_assets import DemoAssetError, verify


def write_json(path: Path, data: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(data, indent=2) + "\n", encoding="utf-8")


def base_demo_manifest() -> dict:
    return {
        "schema": 1,
        "status": "engineering-alpha",
        "policy": {
            "distribution_root": "packages/storefront-core/starter-assets/modern-grocery",
            "allowed_provenance": [
                "first-party-generated",
                "first-party-created",
                "third-party-licensed",
            ],
            "allowed_roles": ["product", "department", "campaign", "placeholder"],
            "allowed_extensions": [".webp"],
            "allowed_surfaces": ["starter", "live-demo", "sales-screenshot"],
            "canonical_first_party_must_exclude": [
                "identifiable_people",
                "third_party_logos",
                "readable_branded_packaging",
            ],
            "max_bytes_by_role": {
                "product": 200000,
                "department": 300000,
                "campaign": 400000,
                "placeholder": 100000,
            },
        },
        "planned_assets": [
            {
                "id": "modern-grocery.product.apple",
                "role": "product",
                "fixture": "Alpha Apple",
                "target_width": 960,
                "target_height": 960,
                "notes": "fixture",
            }
        ],
        "items": [],
        "commercial_blockers": [],
        "notes": [],
    }


def base_third_party_manifest() -> dict:
    return {
        "schema": 1,
        "status": "engineering-alpha",
        "policy": {
            "customer_package_roots": {
                "theme": "packages/storefront-theme",
                "core": "packages/storefront-core",
            },
            "demo_distribution_roots": [],
            "license_sensitive_extensions": [".webp"],
            "vendor_directory_names": ["vendor"],
        },
        "items": [],
        "paid_release_blockers": [],
        "notes": [],
    }


def approved_first_party_item() -> dict:
    return {
        "id": "modern-grocery.product.apple",
        "role": "product",
        "fixture": "Alpha Apple",
        "path": "packages/storefront-core/starter-assets/modern-grocery/apple.webp",
        "provenance": "first-party-generated",
        "source_reference": "generation-batch:test-001",
        "rights_evidence": "provider terms reviewed for this test fixture",
        "surfaces": ["starter", "live-demo", "sales-screenshot"],
        "width": 960,
        "height": 960,
        "status": "approved",
        "reviewer": "release-test",
        "reviewed_on": "2026-08-19",
        "alt_guidance": "Apple product image",
        "identifiable_people": False,
        "third_party_logos": False,
        "readable_branded_packaging": False,
        "third_party_item_id": "",
    }


def expect_failure(repo: Path, demo: dict, third_party: dict, expected: str) -> None:
    demo_path = repo / "release/demo-assets.json"
    third_party_path = repo / "release/third-party-assets.json"
    write_json(demo_path, demo)
    write_json(third_party_path, third_party)
    try:
        verify(repo, demo_path, third_party_path)
    except DemoAssetError as exc:
        assert expected in str(exc), (expected, str(exc))
        return
    raise AssertionError(f"expected failure containing {expected!r}")


def main() -> None:
    with tempfile.TemporaryDirectory() as temp:
        repo = Path(temp)
        root = repo / "packages/storefront-core/starter-assets/modern-grocery"
        root.mkdir(parents=True)

        demo = base_demo_manifest()
        third_party = base_third_party_manifest()
        write_json(repo / "release/demo-assets.json", demo)
        write_json(repo / "release/third-party-assets.json", third_party)
        verify(repo, repo / "release/demo-assets.json", repo / "release/third-party-assets.json")

        (root / "apple.webp").write_bytes(b"fake-webp")
        expect_failure(repo, demo, third_party, "untracked canonical demo image")

        draft = copy.deepcopy(demo)
        item = approved_first_party_item()
        item["status"] = "review_required"
        item["reviewer"] = ""
        item["reviewed_on"] = ""
        draft["items"] = [item]
        expect_failure(repo, draft, third_party, "exists before approval")

        approved = copy.deepcopy(demo)
        approved["items"] = [approved_first_party_item()]
        write_json(repo / "release/demo-assets.json", approved)
        first_party = verify(repo, repo / "release/demo-assets.json", repo / "release/third-party-assets.json")
        assert "packages/storefront-core/starter-assets/modern-grocery/apple.webp" in first_party

        branded = copy.deepcopy(approved)
        branded["items"][0]["third_party_logos"] = True
        expect_failure(repo, branded, third_party, "blocked visual-rights flag")

        external = copy.deepcopy(approved)
        external_item = external["items"][0]
        external_item["provenance"] = "third-party-licensed"
        external_item["third_party_item_id"] = "stock.apple"
        expect_failure(repo, external, third_party, "linked third-party item is not approved")

        third_party_ok = copy.deepcopy(third_party)
        third_party_ok["items"] = [
            {
                "id": "stock.apple",
                "name": "Stock apple",
                "version": "",
                "type": "image",
                "author": "Example",
                "source_url": "https://example.test/apple",
                "license": "Test-License",
                "evidence": "test evidence",
                "paths": ["packages/storefront-core/starter-assets/modern-grocery/apple.webp"],
                "surfaces": ["core", "starter-demo"],
                "modified": False,
                "notice_required": False,
                "redistribution_status": "approved",
                "reviewer": "release-test",
                "reviewed_on": "2026-08-19",
            }
        ]
        write_json(repo / "release/demo-assets.json", external)
        write_json(repo / "release/third-party-assets.json", third_party_ok)
        verify(repo, repo / "release/demo-assets.json", repo / "release/third-party-assets.json")

    print("demo-assets tests: untracked/review/first-party/third-party gates passed")


if __name__ == "__main__":
    main()
