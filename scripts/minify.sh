#!/usr/bin/env bash
# Builds the minified copies the site serves (site.min.css, site.min.js, admin.min.*).
# Needs Node.js (uses esbuild through npx). Run it after editing the CSS/JS files.
# Each .min file starts with the md5 of its source; the site only uses it while they
# match, so an edited source file is never hidden by an old minified copy.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ESBUILD="${ESBUILD:-npx --yes esbuild@0.24.0}"
for f in assets/css/site.css assets/js/site.js assets/admin/admin.css assets/admin/admin.js; do
  src="$ROOT/public/$f"
  out="${src%.*}.min.${src##*.}"
  hash="$(md5sum "$src" | cut -d' ' -f1)"
  case "$src" in
    *.js) body="$($ESBUILD "$src" --minify --target=es2017 --charset=utf8 --log-level=warning)" ;;
    *)    body="$($ESBUILD "$src" --minify --charset=utf8 --log-level=warning)" ;;
  esac
  printf '/*src:%s*/\n%s\n' "$hash" "$body" > "$out"
  echo "$(basename "$out"): $(wc -c < "$src") -> $(wc -c < "$out") bytes"
done
