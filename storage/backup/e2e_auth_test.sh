#!/usr/bin/env bash
# E2E auth test melawan server nyata (MySQL + SESSION_DRIVER=file)
set -u
BASE=http://127.0.0.1:8899
DIR="$(dirname "$0")"

get_token() { # $1=jar $2=path
  curl -s -b "$1" -c "$1" "$BASE$2" \
    | grep -oE 'name="_token"[^>]*value="[^"]+"' \
    | head -1 | sed -E 's/.*value="//; s/"$//'
}

req() { # $1=jar $2=method $3=path [$4..=data]  -> prints "CODE|LOCATION"
  local jar=$1 m=$2 p=$3; shift 3
  local hdr="$DIR/.hdr.$$"
  local args=(-s -D "$hdr" -o /dev/null -b "$jar" -c "$jar" -X "$m" "$BASE$p")
  for kv in "$@"; do args+=(--data-urlencode "$kv"); done
  local code; code=$(curl "${args[@]}" -w '%{http_code}')
  local loc; loc=$(grep -i '^location:' "$hdr" | head -1 | sed -E 's/^[Ll]ocation:[[:space:]]*//; s/\r$//')
  rm -f "$hdr"
  echo "$code|$loc"
}

login() { # $1=jar $2=user $3=pass
  local jar=$1 u=$2 p=$3 t
  t=$(get_token "$jar" /login)
  req "$jar" POST /login "_token=$t" "username=$u" "password=$p"
}

check() { # $1=label $2=expected( substring "code|loc") $3=actual
  if [[ "$3" == "$2" ]]; then echo "PASS | $1 | $3"; else echo "FAIL | $1 | expected '$2' got '$3'"; fi
}

rm -f "$DIR/admin.jar" "$DIR/user.jar" "$DIR/bad.jar"
touch "$DIR/guest.jar"

echo '=== ADMIN (admin / admin123) ==='
check "login admin -> 302 /admin/dashboard" "302|$BASE/admin/dashboard" "$(login "$DIR/admin.jar" admin admin123)"
check "GET /admin/dashboard (session admin)"  "200|" "$(req "$DIR/admin.jar" GET /admin/dashboard)"
check "GET /admin/kasir (CRUD list)"          "200|" "$(req "$DIR/admin.jar" GET /admin/kasir)"
check "admin diblokir dari /kasir/transaksi"  "302|$BASE/admin/dashboard" "$(req "$DIR/admin.jar" GET /kasir/transaksi)"
check "root / redirect sesuai role"           "302|$BASE/admin/dashboard" "$(req "$DIR/admin.jar" GET /)"
check "session bertahan (GET /admin/menu)"    "200|" "$(req "$DIR/admin.jar" GET /admin/menu)"

echo '=== USER (kasir / kasir123 — username lama, role kini user) ==='
check "login user -> 302 /kasir/transaksi"    "302|$BASE/kasir/transaksi" "$(login "$DIR/user.jar" kasir kasir123)"
check "GET /kasir/transaksi (session user)"   "200|" "$(req "$DIR/user.jar" GET /kasir/transaksi)"
check "user diblokir dari /admin/dashboard"   "302|$BASE/kasir/transaksi" "$(req "$DIR/user.jar" GET /admin/dashboard)"
check "user diblokir dari /admin/kasir"       "302|$BASE/kasir/transaksi" "$(req "$DIR/user.jar" GET /admin/kasir)"

echo '=== LOGIN GAGAL ==='
check "password salah -> 302 /login"          "302|$BASE/login" "$(login "$DIR/bad.jar" admin salah123)"
check "bad jar /admin/dashboard -> login"     "302|$BASE/login" "$(req "$DIR/bad.jar" GET /admin/dashboard)"
# pastikan memang tidak login: POST lalu baca kembali /login (flash error), tanpa -L
t=$(get_token "$DIR/bad.jar" /login)
curl -s -b "$DIR/bad.jar" -c "$DIR/bad.jar" -o /dev/null -X POST "$BASE/login" --data-urlencode "_token=$t" --data-urlencode "username=admin" --data-urlencode "password=***"
if curl -s -b "$DIR/bad.jar" -c "$DIR/bad.jar" "$BASE/login" | grep -q "Username atau password salah"; then
  echo "PASS | pesan error login salah muncul"
else
  echo "FAIL | pesan error login salah tidak muncul"
fi

echo '=== LOGOUT ==='
t=$(get_token "$DIR/user.jar" /kasir/transaksi)
check "logout user -> 302 /login"             "302|$BASE/login" "$(req "$DIR/user.jar" POST /logout "_token=$t")"
check "setelah logout /kasir/transaksi -> login" "302|$BASE/login" "$(req "$DIR/user.jar" GET /kasir/transaksi)"

echo '=== TAMU ==='
check "tamu /admin/dashboard -> login"        "302|$BASE/login" "$(req "$DIR/guest.jar" GET /admin/dashboard)"
check "tamu /kasir/riwayat -> login"          "302|$BASE/login" "$(req "$DIR/guest.jar" GET /kasir/riwayat)"
check "/up health 200"                        "200|" "$(req "$DIR/guest.jar" GET /up)"
