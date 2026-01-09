#!/usr/bin/env bash
set -euo pipefail

URL="${1:-http://localhost/JPLY_BBS/server_side/graffiti_wall_read.php}"
NICK="${NICK:-}"
PASSWORD="${PASSWORD:-}"

if [[ -z "$NICK" || -z "$PASSWORD" ]]; then
  echo "Usage: NICK=your_nick PASSWORD=your_password $0 [url]" >&2
  echo "Example: NICK=alice PASSWORD=secret $0 http://localhost/JPLY_BBS/server_side/graffiti_wall_read.php" >&2
  exit 1
fi

curl -sS -X POST "$URL" \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode "NICK=$NICK" \
  --data-urlencode "PASSWORD=$PASSWORD" \
  | sed -e 's/^/RESPONSE: /'
