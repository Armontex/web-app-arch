#!/usr/bin/env bash
set -euo pipefail

TOKEN=$(./Lab/Lab14/pkce-curl.sh | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p')
API_URL="http://127.0.0.1:8001/api"

echo "GET /posts/1/comments"
curl -sS -i "$API_URL/posts/1/comments" | sed -n '1,10p'

echo
echo "POST /posts/1/comments"
CREATE_RESPONSE=$(curl -sS -i -X POST "$API_URL/posts/1/comments" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"body":"CRUD create from task 7","author_name":"Task 7 User"}')
printf '%s\n' "$CREATE_RESPONSE" | sed -n '1,14p'

COMMENT_ID=$(printf '%s\n' "$CREATE_RESPONSE" | sed -n 's/.*"id":\([0-9][0-9]*\).*/\1/p' | head -1)

echo
echo "PUT /comments/$COMMENT_ID"
curl -sS -i -X PUT "$API_URL/comments/$COMMENT_ID" \
    -H "Content-Type: application/json" \
    -d '{"body":"CRUD update from task 7"}' \
    | sed -n '1,12p'

echo
echo "DELETE /comments/$COMMENT_ID"
curl -sS -i -X DELETE "$API_URL/comments/$COMMENT_ID" \
    | sed -n '1,10p'
