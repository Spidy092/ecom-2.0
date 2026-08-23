#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

node --check packages/storefront-core/assets/js/storefront.js
node -e "JSON.parse(require('fs').readFileSync('packages/storefront-theme/theme.json','utf8')); console.log('theme.json valid')"

python3 - <<'PY'
from pathlib import Path
import re

paths = list(Path('packages/storefront-theme/templates').glob('*.html'))
paths += list(Path('packages/storefront-theme/parts').glob('*.html'))
for path in paths:
    content = path.read_text()
    opens = len(re.findall(r'<!-- wp:', content))
    closes = len(re.findall(r'<!-- /wp:', content))
    singles = len(re.findall(r'<!-- wp:[^>]+/-->', content))
    if opens != closes + singles:
        raise SystemExit(f'Block marker mismatch in {path}')
print(f'Validated {len(paths)} block templates')
PY

if command -v php >/dev/null 2>&1; then
	while IFS= read -r -d '' file; do
		php -l "$file" >/dev/null
	done < <(find packages -type f -name '*.php' -print0)
	echo 'PHP syntax passed'
else
	echo 'PHP not installed; PHP syntax check deferred to CI' >&2
fi

git diff --check
echo 'Project validation passed'
