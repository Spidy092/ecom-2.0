#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
VERSION="${1:-0.0.1-alpha}"
OUTPUT_DIR="${2:-$ROOT_DIR/dist}"
STAGING_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGING_DIR"' EXIT

if [[ "$OUTPUT_DIR" != /* ]]; then
	OUTPUT_DIR="$ROOT_DIR/$OUTPUT_DIR"
fi

if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+([.-][0-9A-Za-z.-]+)?$ ]]; then
	echo "Invalid version: $VERSION" >&2
	exit 1
fi

rm -rf "$OUTPUT_DIR"
mkdir -p "$OUTPUT_DIR"

THEME_STAGE="$STAGING_DIR/bhaivatech-grocery-alpha"
CORE_STAGE="$STAGING_DIR/bhaivatech-storefront-core"
mkdir -p "$THEME_STAGE" "$CORE_STAGE"
cp -R "$ROOT_DIR/packages/storefront-theme/." "$THEME_STAGE/"
cp -R "$ROOT_DIR/packages/storefront-core/." "$CORE_STAGE/"

find "$THEME_STAGE" "$CORE_STAGE" -type d \( -name tests -o -name node_modules -o -name vendor \) -prune -exec rm -rf {} +
find "$THEME_STAGE" "$CORE_STAGE" -type f \( -name '.gitkeep' -o -name '*.log' \) -delete
find "$THEME_STAGE" "$CORE_STAGE" -name '.DS_Store' -delete

if find "$THEME_STAGE" "$CORE_STAGE" -type f \( -name '.env' -o -name '.env.*' -o -name '*secret*' \) -print -quit | grep -q .; then
	echo 'Refusing to package secret-like files.' >&2
	exit 1
fi

( cd "$STAGING_DIR" && zip -qr "$OUTPUT_DIR/bhaivatech-grocery-alpha-$VERSION.zip" bhaivatech-grocery-alpha )
( cd "$STAGING_DIR" && zip -qr "$OUTPUT_DIR/bhaivatech-storefront-core-$VERSION.zip" bhaivatech-storefront-core )

COMMIT="$(git -C "$ROOT_DIR" rev-parse HEAD 2>/dev/null || echo unknown)"
{
	echo "version=$VERSION"
	echo "commit=$COMMIT"
	echo "theme=bhaivatech-grocery-alpha-$VERSION.zip"
	echo "core=bhaivatech-storefront-core-$VERSION.zip"
} > "$OUTPUT_DIR/manifest.txt"

( cd "$OUTPUT_DIR" && sha256sum ./*.zip > SHA256SUMS )
echo "Release artifacts written to $OUTPUT_DIR"
