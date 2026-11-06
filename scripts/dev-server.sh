#!/usr/bin/env bash
# Local preview with PHP's built-in server: http://localhost:8000
# First run: php public/app/cli.php install --user=admin --pass=your-password
# OPcache is turned off: a local preview doesn't need it, and on Android (Termux)
# it stops the server with "Cannot create lock - Permission denied".
cd "$(dirname "$0")/../public" && exec php -d opcache.enable=0 -S "${HOST:-127.0.0.1}:${PORT:-8000}" router.php
