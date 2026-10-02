#!/usr/bin/env bash

# ==============================================================================
# Script Pengujian Otorisasi & Keamanan API (Prompt C) - Kampus LMS
# ==============================================================================
# Script ini melakukan automated testing terhadap:
# 1. Unauthenticated Access (401 Unauthorized)
# 2. Role-Based Access Control (403 Forbidden)
# 3. Insecure Direct Object Reference / IDOR Prevention (403 Forbidden)
# 4. Authorized Access (200 OK / 201 Created)
# 5. Rate Limiting Protection (429 Too Many Requests)
# ==============================================================================

BASE_URL="${BASE_URL:-http://127.0.0.1:8000/api/v1}"

# Warna Output Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

PASSED_COUNT=0
FAILED_COUNT=0
TOTAL_TESTS=0

echo -e "${CYAN}==================================================================${NC}"
echo -e "${CYAN}     KAMPUS LMS - AUTOMATED API AUTHORIZATION TEST (PROMPT C)     ${NC}"
echo -e "${CYAN}==================================================================${NC}"
echo -e "Target Base URL: ${YELLOW}${BASE_URL}${NC}\n"

# Helper function untuk parse JSON string tanpa dependency jq
extract_json_val() {
    local key="$1"
    grep -o "\"${key}\":\"[^\"]*" | head -n 1 | cut -d'"' -f4
}

# Helper assert response code
assert_status() {
    local test_name="$1"
    local expected_code="$2"
    local actual_code="$3"
    local response_body="$4"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo -n "Test $TOTAL_TESTS: $test_name ... "

    if [ "$actual_code" -eq "$expected_code" ]; then
        echo -e "${GREEN}[PASSED] (HTTP $actual_code)${NC}"
        PASSED_COUNT=$((PASSED_COUNT + 1))
    else
        echo -e "${RED}[FAILED] (Ekspektasi: $expected_code, Didapat: $actual_code)${NC}"
        echo -e "   ${YELLOW}Response Body: ${response_body:0:150}...${NC}"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
}

# ------------------------------------------------------------------------------
# 1. OTENTIKASI & PENGAMBILAN TOKEN
# ------------------------------------------------------------------------------
echo -e "${BLUE}=== [1/5] Autentikasi Pengguna & Penyiapan Token ===${NC}"

# Login Dosen 1 (Pemilik Course 1 & 4)
DOSEN1_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"dosen@kampuslms.test","password":"password"}')
DOSEN1_CODE=$(echo "$DOSEN1_RESP" | tail -n1)
DOSEN1_BODY=$(echo "$DOSEN1_RESP" | sed '$d')
DOSEN1_TOKEN=$(echo "$DOSEN1_BODY" | extract_json_val "token")

# Fallback ke dosen@test.com jika dosen@kampuslms.test belum diseed
if [ -z "$DOSEN1_TOKEN" ]; then
    DOSEN1_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -d '{"email":"dosen@test.com","password":"password"}')
    DOSEN1_BODY=$(echo "$DOSEN1_RESP" | sed '$d')
    DOSEN1_TOKEN=$(echo "$DOSEN1_BODY" | extract_json_val "token")
fi

if [ -n "$DOSEN1_TOKEN" ]; then
    echo -e "✓ Login Dosen 1 : ${GREEN}Berhasil${NC}"
else
    echo -e "✗ Login Dosen 1 : ${RED}Gagal (Pastikan seeder telah dijalankan)${NC}"
fi

# Login Dosen 2 (Pemilik Course 2 & 5)
DOSEN2_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"dosen2@kampuslms.test","password":"password"}')
DOSEN2_CODE=$(echo "$DOSEN2_RESP" | tail -n1)
DOSEN2_BODY=$(echo "$DOSEN2_RESP" | sed '$d')
DOSEN2_TOKEN=$(echo "$DOSEN2_BODY" | extract_json_val "token")

if [ -n "$DOSEN2_TOKEN" ]; then
    echo -e "✓ Login Dosen 2 : ${GREEN}Berhasil${NC}"
else
    echo -e "⚠ Login Dosen 2 : ${YELLOW}Tidak ditemukan (menggunakan fallback dummy token)${NC}"
    DOSEN2_TOKEN="invalid_or_missing_dosen2_token"
fi

# Login Mahasiswa 1
MHS_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"mahasiswa@kampuslms.test","password":"password"}')
MHS_CODE=$(echo "$MHS_RESP" | tail -n1)
MHS_BODY=$(echo "$MHS_RESP" | sed '$d')
MHS_TOKEN=$(echo "$MHS_BODY" | extract_json_val "token")

if [ -z "$MHS_TOKEN" ]; then
    MHS_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -d '{"email":"mhs@test.com","password":"password"}')
    MHS_BODY=$(echo "$MHS_RESP" | sed '$d')
    MHS_TOKEN=$(echo "$MHS_BODY" | extract_json_val "token")
fi

if [ -n "$MHS_TOKEN" ]; then
    echo -e "✓ Login Mahasiswa: ${GREEN}Berhasil${NC}"
else
    echo -e "✗ Login Mahasiswa: ${RED}Gagal (Pastikan seeder telah dijalankan)${NC}"
fi

echo ""

# ------------------------------------------------------------------------------
# 2. PENGUJIAN AKSES TANPA AUTENTIKASI (401 UNAUTHORIZED)
# ------------------------------------------------------------------------------
echo -e "${BLUE}=== [2/5] Uji Akses Tanpa Autentikasi (401 Unauthorized) ===${NC}"

# Test 1: GET /me tanpa token
RESP=$(curl -s -w "\n%{http_code}" -X GET "${BASE_URL}/me" -H "Accept: application/json")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "GET /me tanpa Bearer Token" 401 "$CODE" "$BODY"

# Test 2: GET /courses tanpa token
RESP=$(curl -s -w "\n%{http_code}" -X GET "${BASE_URL}/courses" -H "Accept: application/json")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "GET /courses tanpa Bearer Token" 401 "$CODE" "$BODY"

# Test 3: POST /assignments tanpa token
RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"title":"Tugas Ilegal"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "POST /assignments tanpa Bearer Token" 401 "$CODE" "$BODY"

echo ""

# ------------------------------------------------------------------------------
# 3. PENGUJIAN ROLE-BASED ACCESS CONTROL (403 FORBIDDEN)
# ------------------------------------------------------------------------------
echo -e "${BLUE}=== [3/5] Uji Pembatasan Hak Akses Berdasarkan Role (403 Forbidden) ===${NC}"

# Test 4: Mahasiswa mencoba membuat tugas baru (Hanya Dosen yang boleh)
RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${MHS_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"course_id":1,"title":"Tugas Palsu dari Mahasiswa","due_at":"2026-12-31 23:59:00"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Mahasiswa dilarang membuat tugas (POST /assignments)" 403 "$CODE" "$BODY"

# Test 5: Mahasiswa mencoba memberi nilai pada submission
RESP=$(curl -s -w "\n%{http_code}" -X PUT "${BASE_URL}/submissions/1/grade" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${MHS_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"score":100,"feedback":"Nilai sendiri"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Mahasiswa dilarang memberi nilai (PUT /submissions/1/grade)" 403 "$CODE" "$BODY"

# Test 6: Mahasiswa mencoba menghapus tugas
RESP=$(curl -s -w "\n%{http_code}" -X DELETE "${BASE_URL}/assignments/1" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${MHS_TOKEN}")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Mahasiswa dilarang menghapus tugas (DELETE /assignments/1)" 403 "$CODE" "$BODY"

# Test 7: Mahasiswa mencoba melihat seluruh submission tugas dosen
RESP=$(curl -s -w "\n%{http_code}" -X GET "${BASE_URL}/assignments/1/submissions" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${MHS_TOKEN}")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Mahasiswa dilarang melihat daftar submissions dosen" 403 "$CODE" "$BODY"

echo ""

# ------------------------------------------------------------------------------
# 4. PENGUJIAN IDOR (INSECURE DIRECT OBJECT REFERENCE) ANTAR DOSEN (403 FORBIDDEN)
# ------------------------------------------------------------------------------
echo -e "${BLUE}=== [4/5] Uji Proteksi IDOR Antar Dosen (403 Forbidden) ===${NC}"

# Test 8: Dosen 2 mencoba membuat tugas pada Course 1 (Course 1 milik Dosen 1)
RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN2_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"course_id":1,"title":"Tugas Ilegal Dosen 2","due_at":"2026-12-31 23:59:00"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 2 dilarang membuat tugas di Course milik Dosen 1" 403 "$CODE" "$BODY"

# Test 9: Dosen 2 mencoba mengedit Assignment 1 (milik Dosen 1)
RESP=$(curl -s -w "\n%{http_code}" -X PUT "${BASE_URL}/assignments/1" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN2_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"title":"Judul Diubah Hacker Dosen 2"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 2 dilarang mengedit tugas milik Dosen 1 (PUT /assignments/1)" 403 "$CODE" "$BODY"

# Test 10: Dosen 2 mencoba menghapus Assignment 1 (milik Dosen 1)
RESP=$(curl -s -w "\n%{http_code}" -X DELETE "${BASE_URL}/assignments/1" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN2_TOKEN}")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 2 dilarang menghapus tugas milik Dosen 1 (DELETE /assignments/1)" 403 "$CODE" "$BODY"

# Test 11: Dosen 2 mencoba memberi nilai pada submission Course milik Dosen 1
RESP=$(curl -s -w "\n%{http_code}" -X PUT "${BASE_URL}/submissions/1/grade" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN2_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"score":50,"feedback":"Disabotase Dosen 2"}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 2 dilarang memberi nilai submission pada Course Dosen 1" 403 "$CODE" "$BODY"

echo ""

# ------------------------------------------------------------------------------
# 5. PENGUJIAN AKSES RESMI & RATE LIMITING
# ------------------------------------------------------------------------------
echo -e "${BLUE}=== [5/5] Uji Akses Sah (200/201) & Rate Limiting (429) ===${NC}"

# Test 12: Profil user sah (GET /me)
RESP=$(curl -s -w "\n%{http_code}" -X GET "${BASE_URL}/me" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN1_TOKEN}")
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 1 mengakses profil diri sendiri (GET /me)" 200 "$CODE" "$BODY"

# Test 13: Dosen 1 membuat tugas baru di Course 1 miliknya
RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${DOSEN1_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{"course_id":1,"title":"Tugas Uji Otomatis","due_at":"2026-12-31 23:59:00","max_score":100}')
CODE=$(echo "$RESP" | tail -n1)
BODY=$(echo "$RESP" | sed '$d')
assert_status "Dosen 1 berhasil membuat tugas pada Course miliknya" 201 "$CODE" "$BODY"

# Test 14: Rate limiting login (5 request per menit)
echo -n "Test Rate Limiting: Melakukan 6 request beruntun ke /auth/login ... "
RATE_LIMITED=0
for i in {1..7}; do
    RL_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/auth/login" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -d '{"email":"spam_test@kampuslms.test","password":"wrongpassword"}')
    RL_CODE=$(echo "$RL_RESP" | tail -n1)
    if [ "$RL_CODE" -eq 429 ]; then
        RATE_LIMITED=1
        break
    fi
done

TOTAL_TESTS=$((TOTAL_TESTS + 1))
if [ "$RATE_LIMITED" -eq 1 ]; then
    echo -e "${GREEN}[PASSED] (HTTP 429 Too Many Requests terdeteksi)${NC}"
    PASSED_COUNT=$((PASSED_COUNT + 1))
else
    echo -e "${RED}[FAILED] (Rate limiter tidak memblokir pada request > 5)${NC}"
    FAILED_COUNT=$((FAILED_COUNT + 1))
fi

# ------------------------------------------------------------------------------
# RINGKASAN HASIL
# ------------------------------------------------------------------------------
echo ""
echo -e "${CYAN}==================================================================${NC}"
echo -e "${CYAN}                      RINGKASAN HASIL PENGUJIAN                   ${NC}"
echo -e "${CYAN}==================================================================${NC}"
echo -e "Total Pengujian  : ${TOTAL_TESTS}"
echo -e "Lulus (Passed)   : ${GREEN}${PASSED_COUNT}${NC}"
echo -e "Gagal (Failed)   : ${RED}${FAILED_COUNT}${NC}"
echo -e "${CYAN}==================================================================${NC}"

if [ "$FAILED_COUNT" -eq 0 ]; then
    echo -e "${GREEN}SELURUH PENGUJIAN OTORISASI & KEAMANAN BERHASIL (LULUS 100%)!${NC}\n"
    exit 0
else
    echo -e "${RED}ADA PENGUJIAN YANG GAGAL. SILAKAN CEK KONFIGURASI / LOG SISTEM.${NC}\n"
    exit 1
fi
