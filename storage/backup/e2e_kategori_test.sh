#!/usr/bin/env bash
# E2E: halaman admin Kategori — CRUD database + jumlah menu per kategori sinkron dengan menus
set -u
BASE=http://127.0.0.1:8899
DIR="$(dirname "$0")"
PASS=0; FAIL=0
MYSQL="D:/xampp/mysql/bin/mysql.exe"

get_token() { curl -s -b "$1" -c "$1" "$BASE$2" | grep -oE 'name="_token"[^>]*value="[^"]+"' | head -1 | sed -E 's/.*value="//; s/"$//'; }

login() {
  local jar=$1 u=$2 p=$3 t
  t=$(get_token "$jar" /login)
  curl -s -b "$jar" -c "$jar" -o /dev/null -X POST "$BASE/login" --data-urlencode "_token=$t" --data-urlencode "username=$u" --data-urlencode "password=$p"
}

expect() { # $1 label, $2 needle, $3 haystack-file
  if grep -q "$2" "$3"; then echo "PASS | $1"; PASS=$((PASS+1)); else echo "FAIL | $1 (tidak ditemukan '$2')"; FAIL=$((FAIL+1)); fi
}

count_db() { $MYSQL -u root -N -e "$1" | tr -d ' \r'; }

J="$DIR/kategori_admin.jar"; rm -f "$J" "$DIR/kategori_user.jar"
login "$J" admin admin123

# 1. halaman kategori: dari DB, jumlah per kategori = hitungan menus
curl -s -b "$J" "$BASE/admin/kategori" > "$DIR/.k1"
expect "halaman kategori OK" "Kategori Menu" "$DIR/.k1"
expect "kategori Makanan ada" "Makanan" "$DIR/.k1"
expect "kategori Minuman ada" "Minuman" "$DIR/.k1"
MK=$(count_db "SELECT COUNT(*) FROM kasiro_db.menus WHERE category='Makanan';")
expect "jumlah menu Makanan sinkron ($MK)" "$MK menu" "$DIR/.k1"
MN=$(count_db "SELECT COUNT(*) FROM kasiro_db.menus WHERE category='Minuman';")
expect "jumlah menu Minuman sinkron ($MN)" "$MN menu" "$DIR/.k1"
# tidak ada dummy lagi (array JS dummy lama punya field 'created' + tanggal Jan/Mar 2024)
if grep -q "Jan 2024\|Mar 2024\|created: '" "$DIR/.k1"; then echo "FAIL | masih ada data dummy"; FAIL=$((FAIL+1)); else echo "PASS | data dummy hilang"; PASS=$((PASS+1)); fi

# 2. tambah kategori baru via POST → muncul di halaman
T=$(get_token "$J" /admin/kategori)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/kategori" --data-urlencode "_token=$T" --data-urlencode "name=Cemilan" --data-urlencode "status=Aktif"
DBN=$(count_db "SELECT name FROM kasiro_db.categories WHERE name='Cemilan';")
[ "$DBN" = "Cemilan" ] && { echo "PASS | kategori 'Cemilan' tersimpan di DB"; PASS=$((PASS+1)); } || { echo "FAIL | tidak ada di DB: '$DBN'"; FAIL=$((FAIL+1)); }
curl -s -b "$J" "$BASE/admin/kategori" > "$DIR/.k2"
expect "kategori baru tampil + 0 menu" "Cemilan" "$DIR/.k2"
expect "jumlah kategori header = 3" "3 kategori" "$DIR/.k2"

# 3. duplikat ditolak
T=$(get_token "$J" /admin/kategori)
curl -s -b "$J" -c "$J" -o /dev/null -X POST "$BASE/admin/kategori" --data-urlencode "_token=$T" --data-urlencode "name=Cemilan" --data-urlencode "status=Aktif"
N=$(count_db "SELECT COUNT(*) FROM kasiro_db.categories WHERE name='Cemilan';")
[ "$N" = "1" ] && { echo "PASS | duplikat ditolak (masih 1 baris)"; PASS=$((PASS+1)); } || { echo "FAIL | duplikat tersimpan ($N baris)"; FAIL=$((FAIL+1)); }

# 4. tambah menu dengan kategori baru → jumlah di halaman bertambah
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu" --data-urlencode "_token=$T" \
  --data-urlencode "name=Pisang Goreng E2E" --data-urlencode "category=Cemilan" \
  --data-urlencode "price=6000" --data-urlencode "cost=2500" --data-urlencode "status=Aktif"
curl -s -b "$J" "$BASE/admin/kategori" > "$DIR/.k3"
expect "jumlah Cemilan jadi 1 setelah menu ditambah" "1 menu" "$DIR/.k3"

# 5. menu dengan kategori ngawur ditolak (exists:categories)
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -c "$J" -o /dev/null -X POST "$BASE/admin/menu" --data-urlencode "_token=$T" \
  --data-urlencode "name=Menu Ngawur" --data-urlencode "category=Ngawur" \
  --data-urlencode "price=1000" --data-urlencode "status=Aktif"
N=$(count_db "SELECT COUNT(*) FROM kasiro_db.menus WHERE name='Menu Ngawur';")
[ "$N" = "0" ] && { echo "PASS | menu kategori tak terdaftar ditolak"; PASS=$((PASS+1)); } || { echo "FAIL | menu ngawur tersimpan"; FAIL=$((FAIL+1)); }

# 6. hapus kategori yang masih dipakai menu → ditolak + pesan error flash
CID=$(count_db "SELECT id FROM kasiro_db.categories WHERE name='Cemilan';")
T=$(get_token "$J" /admin/kategori)
curl -s -b "$J" -c "$J" -o /dev/null -X POST "$BASE/admin/kategori/$CID" --data-urlencode "_token=$T" --data-urlencode "_method=DELETE"
N=$(count_db "SELECT COUNT(*) FROM kasiro_db.categories WHERE id=$CID;")
[ "$N" = "1" ] && { echo "PASS | hapus kategori terpakai ditolak DB"; PASS=$((PASS+1)); } || { echo "FAIL | kategori terpakai terhapus"; FAIL=$((FAIL+1)); }
curl -s -b "$J" "$BASE/admin/kategori" > "$DIR/.k4"
expect "flash error tampil" "masih dipakai" "$DIR/.k4"

# 7. rename kategori → menu ikut berpindah (cascade)
T=$(get_token "$J" /admin/kategori)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/kategori/$CID" --data-urlencode "_token=$T" --data-urlencode "_method=PUT" \
  --data-urlencode "name=Cemilan Pedas" --data-urlencode "status=Aktif"
N=$(count_db "SELECT COUNT(*) FROM kasiro_db.menus WHERE category='Cemilan Pedas';")
[ "$N" = "1" ] && { echo "PASS | rename cascade: menu ikut pindah"; PASS=$((PASS+1)); } || { echo "FAIL | menu tidak ikut ($N)"; FAIL=$((FAIL+1)); }
N2=$(count_db "SELECT COUNT(*) FROM kasiro_db.categories WHERE name='Cemilan';")
[ "$N2" = "0" ] && { echo "PASS | nama lama hilang"; PASS=$((PASS+1)); } || { echo "FAIL | nama lama masih ada"; FAIL=$((FAIL+1)); }

# 8. hapus menu → hapus kategori (sekarang kosong) → berhasil
MID=$(count_db "SELECT id FROM kasiro_db.menus WHERE name='Pisang Goreng E2E';")
T=$(get_token "$J" /admin/menu)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/menu/$MID" --data-urlencode "_token=$T" --data-urlencode "_method=DELETE"
T=$(get_token "$J" /admin/kategori)
curl -s -b "$J" -o /dev/null -X POST "$BASE/admin/kategori/$CID" --data-urlencode "_token=$T" --data-urlencode "_method=DELETE"
N=$(count_db "SELECT COUNT(*) FROM kasiro_db.categories WHERE id=$CID;")
[ "$N" = "0" ] && { echo "PASS | kategori kosong berhasil dihapus"; PASS=$((PASS+1)); } || { echo "FAIL | kategori masih ada"; FAIL=$((FAIL+1)); }

# 9. halaman kasir: chip kategori dari DB; filter menu admin memuat kategori dari DB
JU="$DIR/kategori_user.jar"; login "$JU" kasir kasir123
curl -s -b "$JU" "$BASE/kasir/transaksi" > "$DIR/.kt"
expect "kasir: chip Makanan" ">Makanan<" "$DIR/.kt"
expect "kasir: chip Minuman" ">Minuman<" "$DIR/.kt"
if grep -q ">Cemilan<" "$DIR/.kt"; then echo "FAIL | kategori terhapus masih jadi chip"; FAIL=$((FAIL+1)); else echo "PASS | kategori terhapus tidak jadi chip"; PASS=$((PASS+1)); fi
curl -s -b "$J" "$BASE/admin/menu" > "$DIR/.km"
expect "filter menu: opsi Makanan dari DB" 'value="Makanan"' "$DIR/.km"
expect "filter menu: opsi Minuman dari DB" 'value="Minuman"' "$DIR/.km"

rm -f "$DIR/.k1" "$DIR/.k2" "$DIR/.k3" "$DIR/.k4" "$DIR/.kt" "$DIR/.km"
echo "----"
echo "E2E KATEGORI: PASS=$PASS FAIL=$FAIL"
