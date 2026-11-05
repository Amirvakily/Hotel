#!/usr/bin/env bash
# Local preview with PHP's built-in server: http://localhost:8000
# First run: php public/app/cli.php install --user=admin --pass=your-password
cd "$(dirname "$0")/../public" && exec php -S "${HOST:-127.0.0.1}:${PORT:-8000}" router.php
