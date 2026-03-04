#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8000}"

if [ -z "${REQUEST_ID:-}" ]; then
  echo "Usage: REQUEST_ID=ID ./race_test.sh"
  exit 1
fi

COOKIE_JAR="$(mktemp)"
LOGIN_HTML="$(mktemp)"
MASTER_HTML="$(mktemp)"
trap 'rm -f "$COOKIE_JAR" "$LOGIN_HTML" "$MASTER_HTML" codeA.txt codeB.txt bodyA.json bodyB.json' EXIT

echo "==> 1) GET /login (получаем HTML с CSRF токеном + cookies)"
GET_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -c "$COOKIE_JAR" \
  -X GET "$BASE_URL/login" \
  -o "$LOGIN_HTML" -w "%{http_code}")
echo "GET /login HTTP: $GET_CODE"
[ "$GET_CODE" = "200" ] || { echo "ERROR: /login not 200"; exit 1; }

echo "==> 2) Парсим CSRF token из HTML формы логина"
LOGIN_TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$LOGIN_HTML" | head -n 1 | sed 's/.*value="//;s/"$//')
[ -n "${LOGIN_TOKEN:-}" ] || { echo "ERROR: CSRF token not found"; exit 1; }
echo "CSRF(login) length: ${#LOGIN_TOKEN}"

echo "==> 3) POST /login с _token (ожидаемо 302/200)"
POST_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -X POST "$BASE_URL/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "email=master1@example.com&password=password&_token=$LOGIN_TOKEN" \
  -o /dev/null -w "%{http_code}")
echo "POST /login HTTP: $POST_CODE"
if [ "$POST_CODE" != "302" ] && [ "$POST_CODE" != "200" ]; then
  echo "ERROR: login failed (expected 302/200)"
  exit 1
fi

echo "==> 4) GET /master/requests (получаем CSRF токен уже после логина)"
MASTER_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -X GET "$BASE_URL/master/requests" \
  -o "$MASTER_HTML" -w "%{http_code}")
echo "GET /master/requests HTTP: $MASTER_CODE"
[ "$MASTER_CODE" = "200" ] || { echo "ERROR: /master/requests not 200"; exit 1; }

MASTER_TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$MASTER_HTML" | head -n 1 | sed 's/.*value="//;s/"$//')
[ -n "${MASTER_TOKEN:-}" ] || MASTER_TOKEN="$LOGIN_TOKEN"
echo "CSRF(master) length: ${#MASTER_TOKEN}"

echo "==> 5) 2 параллельных take для REQUEST_ID=$REQUEST_ID"
echo

run_take () {
  local label="$1"
  local bodyfile="$2"
  local codefile="$3"

  code=$(curl -sS --max-time 15 --connect-timeout 7 \
    -b "$COOKIE_JAR" \
    -X POST "$BASE_URL/master/requests/$REQUEST_ID/take" \
    -H "Accept: application/json" \
    -H "X-CSRF-TOKEN: $MASTER_TOKEN" \
    --data "" \
    -o "$bodyfile" -w "%{http_code}")

  echo "$code" > "$codefile"

  echo "---- request $label ----"
  echo "HTTP: $code"
  echo "BODY:"
  cat "$bodyfile"
  echo
}

run_take "A" "bodyA.json" "codeA.txt" &
PID1=$!
run_take "B" "bodyB.json" "codeB.txt" &
PID2=$!

wait $PID1 || true
wait $PID2 || true

CODE_A="$(cat codeA.txt 2>/dev/null || echo "")"
CODE_B="$(cat codeB.txt 2>/dev/null || echo "")"

echo "SUMMARY: A=$CODE_A, B=$CODE_B"

# Нормализуем (порядок не важен)
if { [ "$CODE_A" = "200" ] && [ "$CODE_B" = "409" ]; } || { [ "$CODE_A" = "409" ] && [ "$CODE_B" = "200" ]; }; then
  echo "OK: got 200 + 409 (race protection works)"
elif [ "$CODE_A" = "409" ] && [ "$CODE_B" = "409" ]; then
  echo "OK: got 409 + 409 (request already taken / not in assigned state)"
else
  echo "WARN: unexpected codes (expected 200+409 on first run, or 409+409 on repeat run)"
  exit 2
fi
