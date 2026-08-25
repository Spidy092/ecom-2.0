#!/usr/bin/env python3
"""Verify the customer Theme/Core package license and notice contract."""

from __future__ import annotations

import argparse
import hashlib
import re
from pathlib import Path

EXPECTED_LICENSE_HEADER = "GNU General Public License v2 or later"
EXPECTED_LICENSE_URI = "https://www.gnu.org/licenses/gpl-2.0.html"
EXPECTED_GPL2_SHA256 = "edaef632cbb643e4e7a221717a6c441a4c1a7c918e6e4d56debc3d8739b233f6"

PACKAGE_SPECS = {
    "theme": {
        "root": Path("packages/storefront-theme"),
        "header": "style.css",
    },
    "core": {
        "root": Path("packages/storefront-core"),
        "header": "storefront-core.php",
    },
}


class LicenseContractError(RuntimeError):
    """Raised when a customer package violates the license contract."""


def parse_header(text: str, field: str) -> str:
    match = re.search(
        rf"^[ \t/*#@]*{re.escape(field)}:\s*(.+?)\s*$",
        text,
        re.MULTILINE | re.IGNORECASE,
    )
    return match.group(1).strip() if match else ""


def read_required(path: Path) -> bytes:
    if not path.is_file():
        raise LicenseContractError(f"required package file is missing: {path}")
    return path.read_bytes()


def verify_package(repo_root: Path, package_name: str, spec: dict[str, object]) -> None:
    package_root = repo_root / Path(spec["root"])
    header_path = package_root / str(spec["header"])
    license_path = package_root / "LICENSE.txt"
    notice_path = package_root / "NOTICE.md"
    third_party_path = package_root / "THIRD-PARTY-NOTICES.md"

    header = read_required(header_path).decode("utf-8")
    declared_license = parse_header(header, "License")
    declared_uri = parse_header(header, "License URI")

    if declared_license != EXPECTED_LICENSE_HEADER:
        raise LicenseContractError(
            f"{package_name} License header drifted: {declared_license!r}; "
            f"expected {EXPECTED_LICENSE_HEADER!r}"
        )
    if declared_uri != EXPECTED_LICENSE_URI:
        raise LicenseContractError(
            f"{package_name} License URI drifted: {declared_uri!r}; "
            f"expected {EXPECTED_LICENSE_URI!r}"
        )

    license_bytes = read_required(license_path)
    digest = hashlib.sha256(license_bytes).hexdigest()
    if digest != EXPECTED_GPL2_SHA256:
        raise LicenseContractError(
            f"{package_name} LICENSE.txt does not match the reviewed GNU GPL v2 text "
            f"(sha256 {digest})"
        )

    notice = read_required(notice_path).decode("utf-8")
    for marker in (
        "GPL-2.0-or-later",
        "LICENSE.txt",
        "THIRD-PARTY-NOTICES.md",
        "final legal copyright holder",
    ):
        if marker not in notice:
            raise LicenseContractError(
                f"{package_name} NOTICE.md is missing required policy marker: {marker!r}"
            )

    third_party = read_required(third_party_path).decode("utf-8")
    if "Third-Party Notices" not in third_party:
        raise LicenseContractError(
            f"{package_name} THIRD-PARTY-NOTICES.md is not the expected generated notice file"
        )


def verify_repository(repo_root: Path) -> None:
    license_copies: list[bytes] = []
    for package_name, spec in PACKAGE_SPECS.items():
        verify_package(repo_root, package_name, spec)
        license_copies.append((repo_root / Path(spec["root"]) / "LICENSE.txt").read_bytes())

    if license_copies[0] != license_copies[1]:
        raise LicenseContractError("Theme/Core LICENSE.txt copies are not byte-identical")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--repo-root",
        default=str(Path(__file__).resolve().parents[2]),
        help="Repository root to verify (defaults to this checkout)",
    )
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    repo_root = Path(args.repo_root).resolve()
    try:
        verify_repository(repo_root)
    except LicenseContractError as exc:
        raise SystemExit(f"package-license-contract: {exc}") from exc

    print(
        "package-license-contract: Theme/Core headers, GPLv2 copies, NOTICE files "
        "and third-party notice presence verified"
    )


if __name__ == "__main__":
    main()
