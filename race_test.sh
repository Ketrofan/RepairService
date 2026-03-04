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
trap 'rm -f "$COOKIE_JAR" "$LOGIN_HTML" "$MASTER_HTML"' EXIT

echo "==> 1) GET /login (получаем HTML с CSRF токеном + cookies)"
GET_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -c "$COOKIE_JAR" \
  -X GET "$BASE_URL/login" \
  -o "$LOGIN_HTML" -w "%{http_code}")

echo "GET /login HTTP: $GET_CODE"
if [ "$GET_CODE" != "200" ]; then
  echo "ERROR: /login не 200. Проверь доступность $BASE_URL"
  exit 1
fi

echo "==> 2) Парсим CSRF token из HTML формы логина"
LOGIN_TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$LOGIN_HTML" | head -n 1 | sed 's/.*value="//;s/"$//')

if [ -z "${LOGIN_TOKEN:-}" ]; then
  echo "ERROR: не нашли _token в HTML /login"
  echo "---- login html head ----"
  sed -n '1,120p' "$LOGIN_HTML"
  echo "-------------------------"
  exit 1
fi

echo "CSRF(login) length: ${#LOGIN_TOKEN}"

echo "==> 3) POST /login с _token (ожидаемо 302)"
POST_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -X POST "$BASE_URL/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "email=master1@example.com&password=password&_token=$LOGIN_TOKEN" \
  -o /dev/null -w "%{http_code}")

echo "POST /login HTTP: $POST_CODE"
if [ "$POST_CODE" != "302" ] && [ "$POST_CODE" != "200" ]; then
  echo "ERROR: login не прошёл (ожидали 302/200)."
  echo "---- cookie jar dump ----"
  sed -n '1,200p' "$COOKIE_JAR"
  echo "-------------------------"
  exit 1
fi

echo "==> 4) GET /master/requests (получаем CSRF токен уже после логина)"
MASTER_CODE=$(curl -sS --max-time 15 --connect-timeout 7 \
  -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -X GET "$BASE_URL/master/requests" \
  -o "$MASTER_HTML" -w "%{http_code}")

echo "GET /master/requests HTTP: $MASTER_CODE"
if [ "$MASTER_CODE" != "200" ]; then
  echo "ERROR: мастерская панель не 200. Возможно, логин не мастер/сессия не сохранилась."
  echo "---- master html head ----"
  sed -n '1,120p' "$MASTER_HTML"
  echo "--------------------------"
  exit 1
fi

MASTER_TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$MASTER_HTML" | head -n 1 | sed 's/.*value="//;s/"$//')
if [ -z "${MASTER_TOKEN:-}" ]; then
  # fallback на login token
  MASTER_TOKEN="$LOGIN_TOKEN"
fi

echo "CSRF(master) length: ${#MASTER_TOKEN}"

echo "==> 5) 2 параллельных take для REQUEST_ID=$REQUEST_ID (ожидаемо: один 200, второй 409)"
echo

run_take () {
  local label="$1"
  local tmp_body
  tmp_body="$(mktemp)"

  local code
  code=$(curl -sS --max-time 15 --connect-timeout 7 \
    -b "$COOKIE_JAR" \
    -X POST "$BASE_URL/master/requests/$REQUEST_ID/take" \
    -H "Accept: application/json" \
    -H "X-CSRF-TOKEN: $MASTER_TOKEN" \
    --data "" \
    -o "$tmp_body" -w "%{http_code}")

  echo "---- request $label ----"
  echo "HTTP: $code"
  echo "BODY:"
  cat "$tmp_body"
  echo
  rm -f "$tmp_body"
}

run_take "A" &
PID1=$!
run_take "B" &
PID2=$!

wait $PID1 || true
wait $PID2 || true

echo "DONE. Корректно: ровно один HTTP: 200 и ровно один HTTP: 409."