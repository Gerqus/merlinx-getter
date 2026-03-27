#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

status=0
for file in *_test.php; do
  echo "Running $file"
  if ! php "$file"; then
    status=1
  fi
done

exit "$status"
