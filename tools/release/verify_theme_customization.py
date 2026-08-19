#!/usr/bin/env python3
"""Validate the V1 WordPress-native customization contract.

This is deliberately a structural gate. It proves that the shipped block theme
contains the native primitives promised to buyers without inventing a second
proprietary theme-options system. Browser behavior is covered separately by the
engineering E2E suite.
"""

from __future__ import annotations

import json
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
THEME_ROOT = REPO_ROOT / "packages/storefront-theme"
BASE_THEME_JSON = THEME_ROOT / "theme.json"
STYLE_ROOT = THEME_ROOT / "styles"
HEADER = THEME_ROOT / "parts/header.html"
FOOTER = THEME_ROOT / "parts/footer.html"
FRONT_PAGE = THEME_ROOT / "templates/front-page.html"
PRODUCT_CSS = THEME_ROOT / "assets/css/product-workspace.css"

REQUIRED_PALETTE_SLUGS = {
    "paper",
    "ink",
    "surface",
    "soft",
    "muted",
    "line",
    "copper",
    "copper-dark",
    "success",
    "danger",
    "info",
}
REQUIRED_STYLE_VARIATIONS = {
    "fresh-grove.json": "Fresh Grove",
    "minimal-market.json": "Minimal Market",
}


class CustomizationError(RuntimeError):
    pass


def fail(message: str) -> None:
    raise CustomizationError(message)


def load_json(path: Path) -> dict:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise CustomizationError(f"missing JSON file: {path.relative_to(REPO_ROOT)}") from exc
    except json.JSONDecodeError as exc:
        raise CustomizationError(f"invalid JSON in {path.relative_to(REPO_ROOT)}: {exc}") from exc
    if not isinstance(data, dict):
        fail(f"JSON root must be an object: {path.relative_to(REPO_ROOT)}")
    return data


def read_text(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8")
    except FileNotFoundError as exc:
        raise CustomizationError(f"missing theme file: {path.relative_to(REPO_ROOT)}") from exc


def palette_slugs(data: dict, label: str) -> set[str]:
    settings = data.get("settings")
    if not isinstance(settings, dict):
        fail(f"{label}: settings must be an object")
    color = settings.get("color")
    if not isinstance(color, dict):
        fail(f"{label}: settings.color must be an object")
    palette = color.get("palette")
    if not isinstance(palette, list):
        fail(f"{label}: color.palette must be an array")

    slugs: set[str] = set()
    for entry in palette:
        if not isinstance(entry, dict) or not isinstance(entry.get("slug"), str):
            fail(f"{label}: every palette entry needs a string slug")
        slug = entry["slug"]
        if slug in slugs:
            fail(f"{label}: duplicate palette slug {slug}")
        slugs.add(slug)
    return slugs


def verify_base_theme(data: dict) -> None:
    if data.get("version") != 3:
        fail("theme.json must use schema version 3")

    settings = data.get("settings")
    if not isinstance(settings, dict):
        fail("theme.json settings must be an object")
    if settings.get("appearanceTools") is not True:
        fail("theme.json must keep appearanceTools enabled")

    color = settings.get("color")
    if not isinstance(color, dict) or color.get("custom") is not True:
        fail("theme.json must allow buyer custom colors")

    typography = settings.get("typography")
    if not isinstance(typography, dict):
        fail("theme.json typography must be an object")
    if typography.get("customFontSize") is not False:
        fail("V1 must keep arbitrary custom font sizes disabled")

    families = typography.get("fontFamilies")
    if not isinstance(families, list):
        fail("theme.json typography.fontFamilies must be an array")
    family_slugs = {entry.get("slug") for entry in families if isinstance(entry, dict)}
    if family_slugs != {"system-sans", "system-serif"}:
        fail("V1 typography must expose exactly the approved system sans/serif families")

    if palette_slugs(data, "theme.json") != REQUIRED_PALETTE_SLUGS:
        fail("theme.json semantic palette contract changed")

    parts = data.get("templateParts")
    if not isinstance(parts, list):
        fail("theme.json must register templateParts")
    normalized = {
        entry.get("name"): (entry.get("area"), entry.get("title"))
        for entry in parts
        if isinstance(entry, dict)
    }
    expected = {
        "header": ("header", "Store Header"),
        "footer": ("footer", "Store Footer"),
    }
    if normalized != expected:
        fail(f"theme.json templateParts must be exactly {expected!r}")


def verify_variations(base_palette: set[str]) -> None:
    if not STYLE_ROOT.is_dir():
        fail("theme styles directory is missing")

    files = {path.name: path for path in STYLE_ROOT.glob("*.json")}
    missing = set(REQUIRED_STYLE_VARIATIONS) - set(files)
    if missing:
        fail(f"missing required style variations: {sorted(missing)}")

    for filename, title in REQUIRED_STYLE_VARIATIONS.items():
        data = load_json(files[filename])
        label = f"styles/{filename}"
        if data.get("version") != 3:
            fail(f"{label}: must use schema version 3")
        if data.get("title") != title:
            fail(f"{label}: expected title {title!r}")
        if palette_slugs(data, label) != base_palette:
            fail(f"{label}: must preserve every semantic palette slug")

        serialized = json.dumps(data)
        if "fontFace" in serialized or "src\"" in serialized:
            fail(f"{label}: bundled/remote font sources are outside the V1 contract")


def require_tokens(text: str, label: str, tokens: tuple[str, ...]) -> None:
    for token in tokens:
        if token not in text:
            fail(f"{label}: missing required customization primitive {token}")


def verify_templates() -> None:
    header = read_text(HEADER)
    footer = read_text(FOOTER)
    front = read_text(FRONT_PAGE)

    require_tokens(
        header,
        "parts/header.html",
        ("wp:site-logo", "wp:site-title", "wp:navigation", "wp:page-list", "bt-site-header"),
    )
    require_tokens(
        footer,
        "parts/footer.html",
        ("wp:site-title", "wp:bhaivatech-storefront/mobile-shopping-nav", "bt-site-footer"),
    )
    require_tokens(
        front,
        "templates/front-page.html",
        (
            'wp:template-part {"slug":"header"}',
            'wp:template-part {"slug":"footer"}',
            "wp:bhaivatech-storefront/product-workspace",
        ),
    )

    if "<img" in header.lower():
        fail("header must not hard-code a brand image; use Site Logo")
    if "starter-assets/modern-grocery" in front:
        fail("front page must not hard-code canonical starter image paths")


def verify_semantic_css() -> None:
    css = read_text(PRODUCT_CSS)
    required = (
        "--bt-paper: var(--wp--preset--color--paper",
        "--bt-ink: var(--wp--preset--color--ink",
        "--bt-surface: var(--wp--preset--color--surface",
        "--bt-copper: var(--wp--preset--color--copper",
        "--bt-copper-dark: var(--wp--preset--color--copper-dark",
    )
    require_tokens(css, "product-workspace.css", required)


def verify() -> None:
    base = load_json(BASE_THEME_JSON)
    verify_base_theme(base)
    base_palette = palette_slugs(base, "theme.json")
    verify_variations(base_palette)
    verify_templates()
    verify_semantic_css()


def main() -> int:
    try:
        verify()
    except CustomizationError as exc:
        print(f"theme-customization: {exc}", file=sys.stderr)
        return 1
    print("theme-customization: native customization contract is valid")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
