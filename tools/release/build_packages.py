#!/usr/bin/env python3
"""Build and validate engineering-alpha WordPress Theme/Core release archives.

No external dependencies are required. The script deliberately packages only
runtime files from packages/storefront-theme and packages/storefront-core,
normalizes ZIP metadata, and emits SHA-256 release metadata.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import shutil
import sys
import zipfile
from datetime import datetime, timezone
from pathlib import Path, PurePosixPath
from typing import Iterable

REPO_ROOT = Path(__file__).resolve().parents[2]
THEME_SOURCE = REPO_ROOT / "packages" / "storefront-theme"
CORE_SOURCE = REPO_ROOT / "packages" / "storefront-core"

# These slugs are internal alpha package roots. Final public slugs are a
# separate brand/release decision and must not be changed accidentally here.
THEME_SLUG = "storefront-theme"
CORE_SLUG = "storefront-core"

ZIP_TIMESTAMP = (2020, 1, 1, 0, 0, 0)
RELEASE_SCHEMA = 1
PACKAGE_LICENSE_ID = "GPL-2.0-or-later"
PACKAGE_LICENSE_FILE = "LICENSE.txt"
PACKAGE_NOTICE_FILE = "NOTICE.md"
THIRD_PARTY_NOTICE_FILE = "THIRD-PARTY-NOTICES.md"

EXCLUDED_PARTS = {
    ".git",
    ".github",
    "node_modules",
    "tests",
    "__pycache__",
    ".pytest_cache",
    "coverage",
    "artifacts",
}
EXCLUDED_NAMES = {".DS_Store", "Thumbs.db"}
EXCLUDED_SUFFIXES = {".log", ".pem", ".key", ".p12", ".pfx"}


def fail(message: str) -> "NoReturn":
    raise SystemExit(f"package-contract: {message}")


def read_text(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8")
    except FileNotFoundError:
        fail(f"required file is missing: {path.relative_to(REPO_ROOT)}")


def parse_header(text: str, field: str) -> str:
    match = re.search(rf"^[ \t/*#@]*{re.escape(field)}:\s*(.+?)\s*$", text, re.MULTILINE | re.IGNORECASE)
    return match.group(1).strip() if match else ""


def require_header(text: str, field: str, source: Path) -> str:
    value = parse_header(text, field)
    if not value:
        fail(f"{source.relative_to(REPO_ROOT)} is missing required header: {field}")
    return value


def validate_package_legal_files(source: Path) -> None:
    for filename in (
        PACKAGE_LICENSE_FILE,
        PACKAGE_NOTICE_FILE,
        THIRD_PARTY_NOTICE_FILE,
    ):
        path = source / filename
        if not path.is_file():
            fail(f"required customer package legal file is missing: {path.relative_to(REPO_ROOT)}")


def validate_theme() -> dict[str, str]:
    style_path = THEME_SOURCE / "style.css"
    style = read_text(style_path)
    fields = {
        field: require_header(style, field, style_path)
        for field in (
            "Theme Name",
            "Version",
            "Requires at least",
            "Requires PHP",
            "License",
            "License URI",
            "Text Domain",
        )
    }

    theme_json = THEME_SOURCE / "theme.json"
    try:
        json.loads(read_text(theme_json))
    except json.JSONDecodeError as exc:
        fail(f"theme.json is invalid JSON: {exc}")

    for required in (
        THEME_SOURCE / "functions.php",
        THEME_SOURCE / "templates" / "front-page.html",
        THEME_SOURCE / "parts" / "footer.html",
    ):
        if not required.is_file():
            fail(f"required theme runtime file is missing: {required.relative_to(REPO_ROOT)}")

    validate_package_legal_files(THEME_SOURCE)
    return fields


def validate_core() -> dict[str, str]:
    plugin_path = CORE_SOURCE / "storefront-core.php"
    plugin = read_text(plugin_path)
    fields = {
        field: require_header(plugin, field, plugin_path)
        for field in (
            "Plugin Name",
            "Version",
            "Requires at least",
            "Requires PHP",
            "Requires Plugins",
            "License",
            "License URI",
        )
    }

    dependencies = {item.strip() for item in fields["Requires Plugins"].split(",") if item.strip()}
    if "woocommerce" not in dependencies:
        fail("Core Requires Plugins header must include woocommerce")

    constant = re.search(
        r"const\s+BHAIVATECH_STOREFRONT_CORE_VERSION\s*=\s*['\"]([^'\"]+)['\"]",
        plugin,
    )
    if not constant:
        fail("Core version constant BHAIVATECH_STOREFRONT_CORE_VERSION is missing")
    if constant.group(1) != fields["Version"]:
        fail(
            "Core plugin Version header and BHAIVATECH_STOREFRONT_CORE_VERSION "
            f"do not match ({fields['Version']} != {constant.group(1)})"
        )

    validate_package_legal_files(CORE_SOURCE)
    return fields


def is_excluded(relative: Path) -> bool:
    parts = set(relative.parts)
    if parts.intersection(EXCLUDED_PARTS):
        return True
    if relative.name in EXCLUDED_NAMES:
        return True
    if relative.name == ".env" or relative.name.startswith(".env."):
        return True
    if relative.suffix.lower() in EXCLUDED_SUFFIXES:
        return True
    return False


def package_files(source: Path) -> list[Path]:
    files: list[Path] = []
    for path in source.rglob("*"):
        if not path.is_file():
            continue
        relative = path.relative_to(source)
        if is_excluded(relative):
            continue
        files.append(path)
    return sorted(files, key=lambda p: p.relative_to(source).as_posix())


def validate_no_forbidden_source_files(source: Path) -> None:
    for path in source.rglob("*"):
        if not path.is_file():
            continue
        relative = path.relative_to(source)
        # Test files are allowed in source but are deliberately omitted from the
        # customer archive. Secret-like files are not allowed in package source.
        if relative.name == ".env" or relative.name.startswith(".env."):
            fail(f"secret-like environment file exists in package source: {relative}")
        if relative.suffix.lower() in {".pem", ".key", ".p12", ".pfx"}:
            fail(f"secret-like key/certificate file exists in package source: {relative}")


def write_deterministic_zip(source: Path, slug: str, destination: Path) -> None:
    files = package_files(source)
    if not files:
        fail(f"no runtime files found for {slug}")

    destination.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(destination, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for path in files:
            relative = path.relative_to(source).as_posix()
            archive_name = str(PurePosixPath(slug) / PurePosixPath(relative))
            info = zipfile.ZipInfo(archive_name, ZIP_TIMESTAMP)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            info.create_system = 3
            archive.writestr(info, path.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def inspect_zip(path: Path, expected_root: str, required_members: Iterable[str]) -> None:
    with zipfile.ZipFile(path) as archive:
        names = archive.namelist()

    if not names:
        fail(f"empty archive: {path.name}")

    roots = {PurePosixPath(name).parts[0] for name in names if PurePosixPath(name).parts}
    if roots != {expected_root}:
        fail(f"{path.name} must contain exactly one top-level root {expected_root!r}; got {sorted(roots)}")

    for member in required_members:
        wanted = str(PurePosixPath(expected_root) / member)
        if wanted not in names:
            fail(f"{path.name} is missing required member: {wanted}")

    forbidden_fragments = ("/.git/", "/.github/", "/node_modules/", "/tests/", "/__pycache__/", "/.pytest_cache/")
    for name in names:
        normalized = "/" + name.strip("/") + "/"
        if any(fragment in normalized for fragment in forbidden_fragments):
            fail(f"forbidden development path shipped in {path.name}: {name}")


def artifact_record(path: Path, slug: str, version: str, license_uri: str) -> dict[str, object]:
    return {
        "filename": path.name,
        "package_root": slug,
        "version": version,
        "sha256": sha256(path),
        "bytes": path.stat().st_size,
        "license": PACKAGE_LICENSE_ID,
        "license_uri": license_uri,
        "license_file": PACKAGE_LICENSE_FILE,
        "notice_file": PACKAGE_NOTICE_FILE,
        "third_party_notice_file": THIRD_PARTY_NOTICE_FILE,
    }


def build(output: Path, source_commit: str) -> None:
    theme = validate_theme()
    core = validate_core()
    validate_no_forbidden_source_files(THEME_SOURCE)
    validate_no_forbidden_source_files(CORE_SOURCE)

    if output.exists():
        shutil.rmtree(output)
    output.mkdir(parents=True)

    theme_zip = output / f"storefront-theme-{theme['Version']}.zip"
    core_zip = output / f"storefront-core-{core['Version']}.zip"

    write_deterministic_zip(THEME_SOURCE, THEME_SLUG, theme_zip)
    write_deterministic_zip(CORE_SOURCE, CORE_SLUG, core_zip)

    shared_legal_members = (
        PACKAGE_LICENSE_FILE,
        PACKAGE_NOTICE_FILE,
        THIRD_PARTY_NOTICE_FILE,
    )
    inspect_zip(
        theme_zip,
        THEME_SLUG,
        ("style.css", "theme.json", "functions.php", *shared_legal_members),
    )
    inspect_zip(
        core_zip,
        CORE_SLUG,
        ("storefront-core.php", *shared_legal_members),
    )

    theme_record = artifact_record(theme_zip, THEME_SLUG, theme["Version"], theme["License URI"])
    core_record = artifact_record(core_zip, CORE_SLUG, core["Version"], core["License URI"])

    manifest = {
        "schema": RELEASE_SCHEMA,
        "status": "engineering-alpha",
        "generated_at_utc": datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z"),
        "source_commit": source_commit,
        "artifacts": {
            "theme": theme_record,
            "core": core_record,
        },
        "paid_release_blockers": [
            "final package/product names approved",
            "final legal copyright holder/entity and non-code asset customer license reviewed",
            "third-party notices/assets reviewed",
            "commercial provider lifecycle proven",
            "install/activation smoke green for the exact release artifacts",
        ],
    }

    (output / "release-manifest.json").write_text(
        json.dumps(manifest, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
    )
    (output / "SHA256SUMS").write_text(
        f"{theme_record['sha256']}  {theme_zip.name}\n{core_record['sha256']}  {core_zip.name}\n",
        encoding="utf-8",
    )

    print(f"Built {theme_zip.relative_to(REPO_ROOT)}")
    print(f"Built {core_zip.relative_to(REPO_ROOT)}")
    print(f"Manifest: {(output / 'release-manifest.json').relative_to(REPO_ROOT)}")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--output",
        default=str(REPO_ROOT / "artifacts" / "release"),
        help="Output directory (default: artifacts/release)",
    )
    parser.add_argument(
        "--source-commit",
        default=os.environ.get("GITHUB_SHA", "unknown"),
        help="Source commit recorded in release-manifest.json",
    )
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    output = Path(args.output).resolve()
    try:
        output.relative_to(REPO_ROOT)
    except ValueError:
        fail("output directory must be inside the repository working tree")
    build(output, args.source_commit)


if __name__ == "__main__":
    main()
