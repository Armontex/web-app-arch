#!/usr/bin/env bash
set -euo pipefail

TOKEN=$(./Lab/Lab14/pkce-curl.sh | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p')

echo "TOKEN_PREFIX=$(printf '%s' "$TOKEN" | cut -c 1-48)..."
echo
echo "VALID TOKEN REQUEST:"
curl -sS -i -X POST http://127.0.0.1:8001/api/posts/1/comments \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"body":"RS256 check","author_name":"RS256 User"}' \
    | sed -n '1,12p'

echo
echo "TAMPERED TOKEN REQUEST:"
curl -sS -i -X POST http://127.0.0.1:8001/api/posts/1/comments \
    -H "Authorization: Bearer ${TOKEN}x" \
    -H "Content-Type: application/json" \
    -d '{"body":"RS256 check fail","author_name":"RS256 User"}' \
    | sed -n '1,12p'
