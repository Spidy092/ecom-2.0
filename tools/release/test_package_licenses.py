#!/usr/bin/env python3
"""Adversarial tests for the Theme/Core package license contract."""

from __future__ import annotations

import shutil
import tempfile
from pathlib import Path

import verify_package_licenses as contract

REPO_ROOT = Path(__file__).resolve().parents[2]


def make_fixture(destination: Path) -> None:
    for spec in contract.PACKAGE_SPECS.values():
        relative_root = Path(spec["root"])
        source_root = REPO_ROOT / relative_root
        target_root = destination / relative_root
        target_root.mkdir(parents=True, exist_ok=True)
        for filename in (
            str(spec["header"]),
            "LICENSE.txt",
            "NOTICE.md",
            "THIRD-PARTY-NOTICES.md",
        ):
            shutil.copy2(source_root / filename, target_root / filename)


def expect_failure(repo_root: Path, expected_fragment: str) -> None:
    try:
        contract.verify_repository(repo_root)
    except contract.LicenseContractError as exc:
        if expected_fragment not in str(exc):
            raise AssertionError(
                f"Expected failure containing {expected_fragment!r}, got {str(exc)!r}"
            ) from exc
        return
    raise AssertionError(f"Expected license-contract failure containing {expected_fragment!r}")


def main() -> None:
    with tempfile.TemporaryDirectory(prefix="package-license-contract-") as temp_dir:
        fixture = Path(temp_dir)
        make_fixture(fixture)

        # Reviewed baseline must pass.
        contract.verify_repository(fixture)

        core_root = fixture / "packages/storefront-core"
        theme_root = fixture / "packages/storefront-theme"

        # A missing full license copy must fail.
        core_license = core_root / "LICENSE.txt"
        core_license.unlink()
        expect_failure(fixture, "required package file is missing")
        shutil.copy2(REPO_ROOT / "packages/storefront-core/LICENSE.txt", core_license)

        # Editing the reviewed GPL text must fail the pinned fingerprint.
        theme_license = theme_root / "LICENSE.txt"
        theme_license.write_text(theme_license.read_text(encoding="utf-8") + "\nchanged\n", encoding="utf-8")
        expect_failure(fixture, "does not match the reviewed GNU GPL v2 text")
        shutil.copy2(REPO_ROOT / "packages/storefront-theme/LICENSE.txt", theme_license)

        # Header/URI drift must fail independently from the full license copy.
        core_header = core_root / "storefront-core.php"
        core_header.write_text(
            core_header.read_text(encoding="utf-8").replace(
                contract.EXPECTED_LICENSE_URI,
                "https://example.invalid/license",
            ),
            encoding="utf-8",
        )
        expect_failure(fixture, "License URI drifted")
        shutil.copy2(REPO_ROOT / "packages/storefront-core/storefront-core.php", core_header)

        # Customer archives need their first-party and third-party notice surfaces.
        theme_notice = theme_root / "NOTICE.md"
        theme_notice.write_text("# Package notice\n", encoding="utf-8")
        expect_failure(fixture, "NOTICE.md is missing required policy marker")
        shutil.copy2(REPO_ROOT / "packages/storefront-theme/NOTICE.md", theme_notice)

        third_party = core_root / "THIRD-PARTY-NOTICES.md"
        third_party.unlink()
        expect_failure(fixture, "required package file is missing")

    print(
        "package-license tests: baseline/missing-license/altered-license/header-drift/"
        "notice-drift/missing-third-party-notice gates passed"
    )


if __name__ == "__main__":
    main()
