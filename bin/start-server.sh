#!/bin/bash

# Get the directory of this script regardless from where it is called
BIN="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
APP="$BIN/../"

# Start the PHP built-in http server
php -S 0.0.0.0:10000 -t "$APP" "$APP/app/index.php"
