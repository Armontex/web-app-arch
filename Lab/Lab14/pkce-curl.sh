#!/usr/bin/env bash
set -euo pipefail

CLIENT_ID="a1dffde3-6309-4cd8-b89e-9be5292c6d2a"
REDIRECT_URI="http://localhost/oauth/callback"

docker compose exec -T php sh -s -- "$CLIENT_ID" "$REDIRECT_URI" <<'SH'
set -eu

CLIENT_ID="$1"
REDIRECT_URI="$2"
PORT=$((8100 + ($$ % 500)))

php artisan serve --host=127.0.0.1 --port="$PORT" >/tmp/lab14-artisan-serve.log 2>&1 &
SERVER_PID=$!
trap 'kill "$SERVER_PID" 2>/dev/null || true' EXIT
sleep 1

VERIFIER=$(openssl rand -base64 96 | tr -d '\n' | tr '+/' '-_' | tr -d '=' | cut -c 1-64)
CHALLENGE=$(printf '%s' "$VERIFIER" | openssl dgst -sha256 -binary | openssl base64 -A | tr '+/' '-_' | tr -d '=')
STATE=$(openssl rand -hex 16)

COOKIE=/tmp/boardy-oauth.cookies
LOGIN_HTML=/tmp/boardy-login.html
AUTH_HTML=/tmp/boardy-authorize.html
AUTH_HEADERS=/tmp/boardy-authorize.headers
APPROVE_HEADERS=/tmp/boardy-approve.headers
TOKEN_JSON=/tmp/boardy-token.json

rm -f "$COOKIE" "$LOGIN_HTML" "$AUTH_HTML" "$AUTH_HEADERS" "$APPROVE_HEADERS" "$TOKEN_JSON"

curl -sS -c "$COOKIE" -b "$COOKIE" \
    "http://127.0.0.1:$PORT/login" \
    -o "$LOGIN_HTML"

CSRF=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_HTML" | head -1)

curl -sS -c "$COOKIE" -b "$COOKIE" -X POST \
    "http://127.0.0.1:$PORT/login" \
    -d "_token=$CSRF" \
    -d "email=abagail37@example.net" \
    -d "password=password" \
    -o /tmp/boardy-login-response.html

curl -sS -c "$COOKIE" -b "$COOKIE" -G \
    "http://127.0.0.1:$PORT/oauth/authorize" \
    --data-urlencode "client_id=$CLIENT_ID" \
    --data-urlencode "response_type=code" \
    --data-urlencode "redirect_uri=$REDIRECT_URI" \
    --data-urlencode "code_challenge=$CHALLENGE" \
    --data-urlencode "code_challenge_method=S256" \
    --data-urlencode "state=$STATE" \
    --data-urlencode "scope=*" \
    --data-urlencode "prompt=consent" \
    -D "$AUTH_HEADERS" \
    -o "$AUTH_HTML"

LOCATION=$(grep -i '^Location:' "$AUTH_HEADERS" | tr -d '\r' | sed 's/^Location: //I' || true)

if [ -z "$LOCATION" ]; then
    CSRF=$(grep -oP 'name="_token" value="\K[^"]+' "$AUTH_HTML" | head -1)
    AUTH_TOKEN=$(grep -oP 'name="auth_token" value="\K[^"]+' "$AUTH_HTML" | head -1)

    curl -sS -c "$COOKIE" -b "$COOKIE" -X POST \
        "http://127.0.0.1:$PORT/oauth/authorize" \
        -d "_token=$CSRF" \
        -d "auth_token=$AUTH_TOKEN" \
        -D "$APPROVE_HEADERS" \
        -o /tmp/boardy-approve.html

    LOCATION=$(grep -i '^Location:' "$APPROVE_HEADERS" | tr -d '\r' | sed 's/^Location: //I')
fi

CODE=$(printf '%s' "$LOCATION" | sed -n 's/.*[?&]code=\([^&]*\).*/\1/p')

if [ -z "$CODE" ]; then
    echo "OAuth authorization code was not returned." >&2
    echo "Authorization headers:" >&2
    sed -n '1,40p' "$AUTH_HEADERS" >&2
    echo "Approve headers:" >&2
    sed -n '1,40p' "$APPROVE_HEADERS" >&2 || true
    exit 1
fi

curl -sS -X POST "http://127.0.0.1:$PORT/oauth/token" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "grant_type=authorization_code" \
    -d "client_id=$CLIENT_ID" \
    -d "redirect_uri=$REDIRECT_URI" \
    -d "code_verifier=$VERIFIER" \
    -d "code=$CODE" \
    -o "$TOKEN_JSON"

echo "CLIENT_ID=$CLIENT_ID"
echo "REDIRECT_URI=$REDIRECT_URI"
echo "CODE_VERIFIER=$VERIFIER"
echo "CODE_CHALLENGE=$CHALLENGE"
echo "STATE=$STATE"
echo
echo "AUTHORIZATION_REDIRECT=$LOCATION"
echo
echo "TOKEN_RESPONSE:"
cat "$TOKEN_JSON"
echo
SH
