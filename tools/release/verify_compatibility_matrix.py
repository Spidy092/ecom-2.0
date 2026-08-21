#!/usr/bin/env python3
"""Validate the machine-readable V1 WordPress/WooCommerce/PHP support matrix."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
MATRIX_PATH = ROOT / "release" / "compatibility-matrix.json"
REFERENCE_ENV = ROOT / ".wp-env.json"
PACKAGE_ENV = ROOT / ".wp-env.package.json"
THEME_HEADER = ROOT / "packages" / "storefront-theme" / "style.css"
CORE_HEADER = ROOT / "packages" / "storefront-core" / "storefront-core.php"


class MatrixError(RuntimeError):
    pass


def parse_header(text: str, field: str) -> str:
    match = re.search(
        rf"^[ \t/*#@]*{re.escape(field)}:\s*(.+?)\s*$",
        text,
        re.MULTILINE | re.IGNORECASE,
    )
    return match.group(1).strip() if match else ""


def version_tuple(value: str) -> tuple[int, ...]:
    if not re.fullmatch(r"\d+(?:\.\d+)+", value):
        raise MatrixError(f"invalid dotted version: {value}")
    return tuple(int(part) for part in value.split("."))


def load_json(path: Path) -> dict:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise MatrixError(f"missing required file: {path.relative_to(ROOT)}") from exc
    except json.JSONDecodeError as exc:
        raise MatrixError(f"invalid JSON in {path.relative_to(ROOT)}: {exc}") from exc


def validate() -> dict:
    matrix = load_json(MATRIX_PATH)
    if matrix.get("schema") != 1:
        raise MatrixError("compatibility matrix schema must be 1")

    rows = matrix.get("combinations")
    if not isinstance(rows, list) or not rows:
        raise MatrixError("compatibility matrix must contain combinations")

    ids: set[str] = set()
    reference_rows = []
    for row in rows:
        if not isinstance(row, dict):
            raise MatrixError("every compatibility row must be an object")
        for key in ("id", "wordpress", "woocommerce", "php", "tier"):
            if not isinstance(row.get(key), str) or not row[key].strip():
                raise MatrixError(f"compatibility row missing {key}")
        if row["id"] in ids:
            raise MatrixError(f"duplicate compatibility id: {row['id']}")
        ids.add(row["id"])
        if row["tier"] not in {"reference", "validated"}:
            raise MatrixError(f"unsupported compatibility tier: {row['tier']}")
        if row["tier"] == "reference":
            reference_rows.append(row)
        version_tuple(row["wordpress"])
        version_tuple(row["woocommerce"])
        version_tuple(row["php"])

    if len(reference_rows) != 1:
        raise MatrixError("exactly one compatibility row must be reference")

    reference = matrix.get("reference")
    if not isinstance(reference, dict) or reference.get("id") != reference_rows[0]["id"]:
        raise MatrixError("reference object must match the single reference row")
    if reference != reference_rows[0]:
        raise MatrixError("reference object and reference row must be byte-for-value equivalent")

    reference_env = load_json(REFERENCE_ENV)
    package_env = load_json(PACKAGE_ENV)
    expected_core = f"https://wordpress.org/wordpress-{reference['wordpress']}.zip"
    expected_woo = f"https://downloads.wordpress.org/plugin/woocommerce.{reference['woocommerce']}.zip"

    for label, env in ((".wp-env.json", reference_env), (".wp-env.package.json", package_env)):
        if env.get("core") != expected_core:
            raise MatrixError(f"{label} WordPress pin does not match reference row")
        if env.get("phpVersion") != reference["php"]:
            raise MatrixError(f"{label} PHP pin does not match reference row")
        if expected_woo not in env.get("plugins", []):
            raise MatrixError(f"{label} WooCommerce pin does not match reference row")

    min_wp = min((version_tuple(row["wordpress"]) for row in rows))
    min_php = min((version_tuple(row["php"]) for row in rows))
    theme = THEME_HEADER.read_text(encoding="utf-8")
    core = CORE_HEADER.read_text(encoding="utf-8")

    for label, header in (("theme", theme), ("core", core)):
        required_wp = parse_header(header, "Requires at least")
        required_php = parse_header(header, "Requires PHP")
        if not required_wp or not required_php:
            raise MatrixError(f"{label} must declare Requires at least and Requires PHP")
        if version_tuple(required_wp) > min_wp:
            raise MatrixError(f"{label} Requires at least excludes a claimed WordPress row")
        if version_tuple(required_php) > min_php:
            raise MatrixError(f"{label} Requires PHP excludes a claimed PHP row")

    required_checks = {
        "exact WordPress version",
        "exact WooCommerce version",
        "exact PHP major.minor",
        "WooCommerce active",
        "Storefront Core active",
        "Storefront Theme active",
        "HPOS enabled",
        "Woo Store API products route available",
        "Core serviceability REST route available",
        "front-end storefront returns HTTP 200",
    }
    if set(matrix.get("runtime_checks", [])) != required_checks:
        raise MatrixError("runtime_checks must exactly match the V1 compatibility smoke contract")

    return matrix


def github_matrix(matrix: dict) -> str:
    return json.dumps({"include": matrix["combinations"]}, separators=(",", ":"))


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--github-matrix", action="store_true")
    args = parser.parse_args()
    try:
        matrix = validate()
    except MatrixError as exc:
        raise SystemExit(f"compatibility-matrix: {exc}") from exc

    if args.github_matrix:
        print(github_matrix(matrix))
    else:
        print(
            "compatibility-matrix: verified "
            f"{len(matrix['combinations'])} exact rows; reference={matrix['reference']['id']}"
        )


if __name__ == "__main__":
    main()
