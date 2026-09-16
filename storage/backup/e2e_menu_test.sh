#!/usr/bin/env bash
# E2E: tabel menu admin (sort + paginasi 5/halaman) & halaman kasir dari DB
set -u
BASE=http://127.0.0.1:8899
DIR="$(dirname "$0")"
PASS=0; FAIL=0

get_token() { curl -s -b "$1" -c "$1" "$BASE$2" | grep -oE 'name="_token"[^>]*value="[^"]+"' | head -1 | sed -E 's/.*value="//; s/"$//'; }

login() {
  local jar=$1 u=$2 p=$3 t
  t=$(get_token "$jar" /login)
  curl -s -b "$jar" -c "$jar" -o /dev/null -X POST "$BASE/login" --data-urlencode "_token=$t" --data-urlencode "username=$u" --data-urlencode "password=$p"
}

expect() { # $1 label, $2 needle, $3 haystack-file
  if grep -q "$2" "$3"; then echo "PASS | $1"; PASS=$((PASS+1)); else echo "FAIL | $1 (tidak ditemukan '$2')"; FAIL=$((FAIL+1)); fi
}

count_db() { "D:/xampp/mysql/bin/mysql.exe" -u root -N -e "$1" | tr -d '\r'; } # pertahankan spasi dalam nama

J="$DIR/menu_admin.jar"; rm -f "$J" "$DIR/menu_user.jar"
login "$J" admin admin123

# 1. halaman menu: 5 baris, info paginasi — ekspektasi dihitung dari DB (data user berubah-ubah)
curl -s -b "$J" -c "$J" "$BASE/admin/menu" > "$DIR/.p1"
expect "halaman menu OK (200 baris tabel)" "Daftar Menu" "$DIR/.p1"
TOTAL=$(count_db "SELECT COUNT(*) FROM kasiro_db.menus;")
P1END=$(( TOTAL < 5 ? TOTAL : 5 ))
expect "paginasi halaman-1: 1–$P1END dari $TOTAL" "1–$P1END dari $TOTAL menu" "$DIR/.p1"
# nomor baris hanya 1..5
expect "No 5 ada" ">5<" "$DIR/.p1"
N6=$(grep -oE '<td class="px-4 py-3 font-mono text-\[12px\] text-inkmuted tnum">[0-9]+</td>' "$DIR/.p1" | wc -l)
[ "$N6" -eq 5 ] && { echo "PASS | tepat 5 baris tabel (No)"; PASS=$((PASS+1)); } || { echo "FAIL | jumlah baris $N6 != 5"; FAIL=$((FAIL+1)); }
expect "ada dropdown urutan" 'name="sort"' "$DIR/.p1"
expect "opsi abjad" "Abjad (A → Z)" "$DIR/.p1"
expect "opsi harga kecil" "Harga terkecil → terbesar" "$DIR/.p1"
expect "opsi harga besar" "Harga terbesar → terkecil" "$DIR/.p1"
expect "ada link Berikutnya" "Berikutnya" "$DIR/.p1"
expect "halaman-1 aktif" ">1<" "$DIR/.p1"

# 2. sort abjad: baris pertama harus nama menu pertama menurut DB
FIRSTDB=$(count_db "SELECT name FROM kasiro_db.menus ORDER BY name, id LIMIT 1;")
curl -s -b "$J" -c "$J" "$BASE/admin/menu?sort=abjad" > "$DIR/.s1"
FIRST=$(grep -oE '<span class="font-medium text-ink">[^<]+' "$DIR/.s1" | head -1 | sed 's/.*>//')
[ "$FIRST" = "$FIRSTDB" ] && { echo "PASS | sort abjad: nama pertama '$FIRSTDB'"; PASS=$((PASS+1)); } || { echo "FAIL | sort abjad: '$FIRST' != '$FIRSTDB'"; FAIL=$((FAIL+1)); }

# 3. sort harga terkecil->terbesar: menu harga termurah menurut DB
CHEAP=$(count_db "SELECT name FROM kasiro_db.menus ORDER BY price ASC, id LIMIT 1;")
curl -s -b "$J" -c "$J" "$BASE/admin/menu?sort=harga-kecil" > "$DIR/.s2"
FIRST=$(grep -oE '<span class="font-medium text-ink">[^<]+' "$DIR/.s2" | head -1 | sed 's/.*>//')
[ "$FIRST" = "$CHEAP" ] && { echo "PASS | sort harga terkecil: '$CHEAP'"; PASS=$((PASS+1)); } || { echo "FAIL | '$FIRST' != '$CHEAP'"; FAIL=$((FAIL+1)); }

# 4. sort harga terbesar->terkecil: menu harga termahal menurut DB
PRICY=$(count_db "SELECT name FROM kasiro_db.menus ORDER BY price DESC, id LIMIT 1;")
curl -s -b "$J" -c "$J" "$BASE/admin/menu?sort=harga-besar" > "$DIR/.s3"
FIRST=$(grep -oE '<span class="font-medium text-ink">[^<]+' "$DIR/.s3" | head -1 | sed 's/.*>//')
[ "$FIRST" = "$PRICY" ] && { echo "PASS | sort harga terbesar: '$PRICY'"; PASS=$((PASS+1)); } || { echo "FAIL | '$FIRST' != '$PRICY'"; FAIL=$((FAIL+1)); }

# 5. halaman 2 & 3 (halaman 3 hanya bila ada lebih dari 10 menu)
curl -s -b "$J" -c "$J" "$BASE/admin/menu?sort=abjad&page=2" > "$DIR/.s4"
if [ "$TOTAL" -gt 5 ]; then
  P2END=$(( TOTAL < 10 ? TOTAL : 10 ))
  expect "halaman 2: menampilkan 6–$P2END" "6–$P2END dari $TOTAL" "$DIR/.s4"
else
  expect "halaman 2: menampilkan seluruh $TOTAL" "1–$TOTAL dari $TOTAL" "$DIR/.s4"
fi
if [ "$TOTAL" -gt 10 ]; then
  curl -s -b "$J" -c "$J" "$BASE/admin/menu?sort=abjad&page=3" > "$DIR/.s5"
  expect "halaman 3: menampilkan 11–$TOTAL" "11–$TOTAL dari $TOTAL" "$DIR/.s5"
fi

# 6. CRUD via POST: tambah -> toggle -> hapus
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -c "$J" -o /dev/null -X POST "$BASE/admin/menu" \
  --data-urlencode "_token=$T" --data-urlencode "name=Test E2E" --data-urlencode "category=Makanan" \
  --data-urlencode "price=9999" --data-urlencode "cost=5000" --data-urlencode "status=Aktif"
curl -s -b "$J" "$BASE/admin/menu?sort=abjad&q=Test" > "$DIR/.s6"
expect "menu baru 'Test E2E' muncul + flash success" "Test E2E" "$DIR/.s6"
expect "flash message tampil" "berhasil ditambahkan" "$DIR/.s6"
# cari id dari DB lewat list: pakai route edit form hidden -> pakai mysql langsung
ID=$("D:/xampp/mysql/bin/mysql.exe" -u root -N -e "SELECT id FROM kasiro_db.menus WHERE name='Test E2E';" | tr -d ' \r')
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$ID/toggle" --data-urlencode "_token=$T" --data-urlencode "_method=PATCH"
STATUS=$("D:/xampp/mysql/bin/mysql.exe" -u root -N -e "SELECT status FROM kasiro_db.menus WHERE id=$ID;" | tr -d ' \r')
[ "$STATUS" = "Nonaktif" ] && { echo "PASS | toggle -> Nonaktif di database"; PASS=$((PASS+1)); } || { echo "FAIL | toggle: $STATUS"; FAIL=$((FAIL+1)); }
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$ID" --data-urlencode "_token=$T" --data-urlencode "_method=DELETE"
LEFT=$("D:/xampp/mysql/bin/mysql.exe" -u root -N -e "SELECT COUNT(*) FROM kasiro_db.menus WHERE id=$ID;" | tr -d ' \r')
[ "$LEFT" = "0" ] && { echo "PASS | hapus permanen dari database"; PASS=$((PASS+1)); } || { echo "FAIL | masih ada $LEFT baris"; FAIL=$((FAIL+1)); }

# 7. halaman kasir: dari DB, nonaktif tidak tampil, tanpa localStorage, gambar lokal
JU="$DIR/menu_user.jar"; login "$JU" kasir kasir123
curl -s -b "$JU" "$BASE/kasir/transaksi" > "$DIR/.k1"
expect "kasir: serverMenus dari DB" "const serverMenus =" "$DIR/.k1"
# nama menu Aktif apa pun dari DB harus tampil (data user berubah-ubah)
SAMPLE=$(count_db "SELECT name FROM kasiro_db.menus WHERE status='Aktif' ORDER BY id LIMIT 1;")
expect "kasir: menu Aktif dari DB tampil ('$SAMPLE')" "$SAMPLE" "$DIR/.k1"
if grep -q "kasiro-menus" "$DIR/.k1"; then echo "FAIL | masih pakai localStorage"; FAIL=$((FAIL+1)); else echo "PASS | localStorage dibuang"; PASS=$((PASS+1)); fi
# gambar lokal: folder public/images/menu dikelola manual (boleh kosong) —
# buktikan mekanismenya dengan file sementara milik script ini, lalu hapus.
USVG="uji-gambar-e2e"
printf '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"/>' > "C:/Users/LENOVO/PJBL/PJBL/public/images/menu/$USVG.svg"
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu" --data-urlencode "_token=$T" \
  --data-urlencode "name=Uji Gambar E2E" --data-urlencode "category=Makanan" \
  --data-urlencode "price=1000" --data-urlencode "cost=500" --data-urlencode "status=Aktif"
curl -s -b "$JU" "$BASE/kasir/transaksi" > "$DIR/.k2"
expect "kasir: gambar lokal ter-resolve (file temp)" "images..menu..$USVG\\.svg" "$DIR/.k2"
# bersihkan menu + file sementara
EID=$("D:/xampp/mysql/bin/mysql.exe" -u root -N -e "SELECT id FROM kasiro_db.menus WHERE name='Uji Gambar E2E';" | tr -d ' \r')
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$EID" --data-urlencode "_token=$T" --data-urlencode "_method=DELETE"
rm -f "C:/Users/LENOVO/PJBL/PJBL/public/images/menu/$USVG.svg"

# 8. sinkronisasi: toggle 'Es Teh' -> nonaktif -> hilang dari kasir -> aktifkan lagi
T=$(get_token "$J" /admin/menu)
ETE=$("D:/xampp/mysql/bin/mysql.exe" -u root -N -e "SELECT id FROM kasiro_db.menus WHERE name='Es Teh';" | tr -d ' \r')
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$ETE/toggle" --data-urlencode "_token=$T" --data-urlencode "_method=PATCH"
curl -s -b "$JU" "$BASE/kasir/transaksi" | grep -q '"Es Teh"' && { echo "FAIL | Es Teh masih di kasir setelah toggle"; FAIL=$((FAIL+1)); } || { echo "PASS | toggle admin langsung berefek ke kasir"; PASS=$((PASS+1)); }
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$ETE/toggle" --data-urlencode "_token=$T" --data-urlencode "_method=PATCH"

rm -f "$DIR/.p1" "$DIR/.s1" "$DIR/.s2" "$DIR/.s3" "$DIR/.s4" "$DIR/.s5" "$DIR/.s6" "$DIR/.k1"
echo "----"
echo "E2E MENU: PASS=$PASS FAIL=$FAIL"
