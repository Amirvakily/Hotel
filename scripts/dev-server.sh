#!/usr/bin/env bash
# Local preview with PHP's built-in server: http://localhost:8000
# First run: php public/app/cli.php install --user=admin --pass=your-password
# OPcache writes a lock file to /tmp by default, which Android (Termux) does not allow,
# so it uses the system's temp folder ($TMPDIR) when there is one.
cd "$(dirname "$0")/../public" && exec php -d opcache.lockfile_path="${TMPDIR:-/tmp}" -S "${HOST:-127.0.0.1}:${PORT:-8000}" router.php
