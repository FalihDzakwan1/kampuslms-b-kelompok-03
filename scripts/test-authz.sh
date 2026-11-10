#!/bin/bash

# ==============================================================================
# SCRIPT PENGUJIAN OTORISASI (test-authz.sh) - KAMPUSLMS MINGGU 7
# Tujuan: Menguji IDOR dan Bypass Otorisasi sesuai rancangan Role & Policy
# ==============================================================================

# Ganti domain/port ini atau teruskan sebagai argumen: ./scripts/test-authz.sh "https://domain-anda.com/api/v1"
BASE_URL="${1:-${BASE_URL:-https://kampuslms-b-kelompok-03-production-03.up.railway.app/api/v1}}"

echo "Target Endpoint: $BASE_URL"
echo "--- MENDAPATKAN TOKEN ---"
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email": "admin@kampuslms.test", "password": "password"}' | sed -E 's/.*"token":"([^"]+)".*/\1/')

DOSEN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email": "dosen@kampuslms.test", "password": "password"}' | sed -E 's/.*"token":"([^"]+)".*/\1/')

MHS_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email": "mahasiswa@kampuslms.test", "password": "password"}' | sed -E 's/.*"token":"([^"]+)".*/\1/')

echo "Token berhasil didapatkan."
echo "========================================================"

run_test() {
  TEST_NAME=$1
  EXPECTED_CODE=$2
  METHOD=$3
  ENDPOINT=$4
  TOKEN=$5
  DATA=$6

  echo -e "\n[TEST] $TEST_NAME"
  echo "Ekspektasi: $EXPECTED_CODE"
  
  if [ -z "$DATA" ]; then
    ACTUAL_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X $METHOD "$BASE_URL/$ENDPOINT" \
      -H "Authorization: Bearer $TOKEN" -H "Accept: application/json")
  else
    ACTUAL_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X $METHOD "$BASE_URL/$ENDPOINT" \
      -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" -d "$DATA")
  fi

  echo "Hasil Aktual: $ACTUAL_CODE"
  
  if [ "$ACTUAL_CODE" == "$EXPECTED_CODE" ]; then
    echo "✅ LOLOS"
  else
    echo "❌ GAGAL (Kode tidak sesuai)"
  fi
}

# --- JALANKAN PENGUJIAN ---

# Asumsi: ID 2 milik Mahasiswa/Dosen lain, ID 3 tidak di-enroll
run_test "Mahasiswa A mengakses submission mahasiswa lain (IDOR)" "403" "GET" "submissions/2" "$MHS_TOKEN"
run_test "Dosen A mengakses mata kuliah Dosen lain" "403" "GET" "courses/2" "$DOSEN_TOKEN"
run_test "Mahasiswa A mengakses mata kuliah yang tidak diikutinya" "403" "GET" "courses/3" "$MHS_TOKEN"
run_test "Mahasiswa A mencoba edit data dengan Role Admin (Privilege Escalation)" "403" "PUT" "users/4" "$MHS_TOKEN" '{"name":"Hacker","email":"mahasiswa1@kampuslms.test","nim_nip":"111","role":"admin"}'

echo -e "\n========================================================"
echo "PENGUJIAN SELESAI"