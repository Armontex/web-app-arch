#!/usr/bin/env bash
set -euo pipefail

TOKEN=$(./Lab/Lab14/pkce-curl.sh | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p')
API_URL="http://127.0.0.1:8001/api"

echo "CREATE comment by user from Passport token"
CREATE_RESPONSE=$(curl -sS -i -X POST "$API_URL/posts/1/comments" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"body":"Owner check source","author_name":"Owner User"}')
printf '%s\n' "$CREATE_RESPONSE" | sed -n '1,12p'

COMMENT_ID=$(printf '%s\n' "$CREATE_RESPONSE" | sed -n 's/.*"id":\([0-9][0-9]*\).*/\1/p' | head -1)

docker compose exec -T db mysql -uboardy -pboardy -e \
    "UPDATE boardy_api.comments SET author_id = 999, author_name = 'Other User' WHERE id = $COMMENT_ID;" >/dev/null

echo
echo "PUT чужого комментария /comments/$COMMENT_ID"
curl -sS -i -X PUT "$API_URL/comments/$COMMENT_ID" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"body":"Should be forbidden"}' \
    | sed -n '1,12p'

echo
echo "DELETE чужого комментария /comments/$COMMENT_ID"
curl -sS -i -X DELETE "$API_URL/comments/$COMMENT_ID" \
    -H "Authorization: Bearer $TOKEN" \
    | sed -n '1,12p'
