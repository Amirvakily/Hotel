#!/usr/bin/env bash
# Fresh demo install for tests (local dev only). Usage: tests/reset-install.sh [user] [pass]
set -e
cd "$(dirname "$0")/../public"
rm -f storage/config.php storage/database.sqlite storage/database.sqlite-wal storage/database.sqlite-shm
find uploads -mindepth 1 ! -name ".htaccess" ! -name ".gitkeep" ! -name "index.html" -delete
find storage/cache -name "*.html" -delete
php app/cli.php install --user="${1:-admin}" --pass="${2:-venus12345}" --name="Test"
sed -i "s/'debug' => false/'debug' => true/" storage/config.php
