#!/usr/bin/env bash
# Builds dist/venus-hotel-<version>.zip – the file you upload to cPanel (public_html).
# The zip contains the contents of public/ without your local database, config or uploads.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VERSION="$(grep -oE "VENUS_VERSION', '[^']+" "$ROOT/public/app/bootstrap.php" | cut -d"'" -f3)"
OUT="$ROOT/dist"
STAGE="$(mktemp -d)"
mkdir -p "$OUT"
# Refresh the minified CSS/JS when Node.js is available (the site falls back to the
# full files if a minified copy is out of date, so this step is optional).
if command -v npx >/dev/null 2>&1 || [ -n "${ESBUILD:-}" ]; then
  bash "$ROOT/scripts/minify.sh" || echo "warning: minify failed – the unminified files will be used"
fi
cp -a "$ROOT/public/." "$STAGE/"
# remove local data
rm -f "$STAGE/storage/config.php" "$STAGE"/storage/database.sqlite* "$STAGE/router.php"
find "$STAGE/storage" -mindepth 2 -type f ! -name ".gitkeep" ! -name ".htaccess" ! -name "index.html" -delete
find "$STAGE/uploads" -mindepth 1 ! -name ".htaccess" ! -name ".gitkeep" ! -name "index.html" -exec rm -rf {} + 2>/dev/null || true
mkdir -p "$STAGE/storage/cache/pages" "$STAGE/storage/logs" "$STAGE/storage/backups" "$STAGE/storage/sessions"
cp "$ROOT/INSTALL.md" "$ROOT/INSTALL.fa.md" "$STAGE/" 2>/dev/null || true
ZIP="$OUT/venus-hotel-$VERSION.zip"
rm -f "$ZIP"
(cd "$STAGE" && zip -qr9 "$ZIP" . -x "*.DS_Store")
rm -rf "$STAGE"
echo "Created $ZIP ($(du -h "$ZIP" | cut -f1))"
