#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-}"
DIRECTORY_MODE="${DIRECTORY_MODE:-dark}"
PENIA_SLUG="${PENIA_SLUG:-}"
RADIO_SLUG="${RADIO_SLUG:-}"
PROGRAM_SLUG="${PROGRAM_SLUG:-}"

if [[ -z "$BASE_URL" ]]; then
  echo "Falta BASE_URL. Ejemplo: BASE_URL=https://staging.example.test DIRECTORY_MODE=dark bash scripts/smoke-directories.sh" >&2
  exit 2
fi

if [[ "$DIRECTORY_MODE" != "dark" && "$DIRECTORY_MODE" != "light" ]]; then
  echo "DIRECTORY_MODE debe ser dark o light." >&2
  exit 2
fi

if [[ ! "$BASE_URL" =~ ^https://[A-Za-z0-9.-]+(:[0-9]+)?$ ]]; then
  echo "BASE_URL debe ser un origen HTTPS público, sin path, query ni credenciales." >&2
  exit 2
fi

BASE_URL="${BASE_URL%/}"

status_for() {
  curl --silent --show-error --output /dev/null --write-out '%{http_code}' "$BASE_URL$1"
}

expect_status() {
  local path="$1"
  local expected="$2"
  local actual
  actual="$(status_for "$path")"
  if [[ "$actual" != "$expected" ]]; then
    echo "FAIL $path: esperaba HTTP $expected y recibió $actual" >&2
    exit 1
  fi
  echo "OK   $path -> HTTP $actual"
}

expect_status "/" "200"

if [[ "$DIRECTORY_MODE" == "dark" ]]; then
  expect_status "/penias" "404"
  expect_status "/radios-de-folklore-argentino" "404"
  expect_status "/radios-de-folklore-argentino/programas" "404"
  expect_status "/sitemap-penias.xml" "404"
  expect_status "/sitemap-radios.xml" "404"
else
  expect_status "/penias" "200"
  expect_status "/radios-de-folklore-argentino" "200"
  expect_status "/radios-de-folklore-argentino/programas" "200"
  expect_status "/sitemap-penias.xml" "200"
  expect_status "/sitemap-radios.xml" "200"

  [[ -n "$PENIA_SLUG" ]] && expect_status "/penias/$PENIA_SLUG" "200"
  [[ -n "$RADIO_SLUG" ]] && expect_status "/radios-de-folklore-argentino/$RADIO_SLUG" "200"
  [[ -n "$PROGRAM_SLUG" ]] && expect_status "/radios-de-folklore-argentino/programas/$PROGRAM_SLUG" "200"
fi

echo "Smoke de directorios completado en modo $DIRECTORY_MODE sobre $BASE_URL."
