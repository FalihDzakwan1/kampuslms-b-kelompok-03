# Dokumentasi REST API - Kampus LMS

Dokumentasi lengkap REST API Kampus LMS mencakup spesifikasi endpoint, mekanisme autentikasi, skema rate limiting, kode respons, format request/response, dan contoh perintah **cURL** untuk setiap endpoint.

---

## 1. Ikhtisar & Konfigurasi Dasar

- **Base URL**: `http://localhost:8000/api/v1`
- **Header Standar**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Autentikasi**: Menggunakan **Laravel Sanctum**. Token dikirim melalui header `Authorization`:
  ```http
  Authorization: Bearer <TOKEN>
  ```

---

## 2. Kebijakan Rate Limiting

API Kampus LMS dilengkapi dengan proteksi pembatasan laju permintaan (*rate limiting*) untuk menjaga kestabilan dan keamanan sistem dari serangan brute-force maupun spamming:

| Scope | Batas Maksimum | Identifier Kunci | Middleware |
|---|---|---|---|
| **Umum (Semua Endpoint API)** | **60 request / menit** | ID Pengguna (jika login) atau Alamat IP | `throttle:api` |
| **Login (`/auth/login`)** | **5 request / menit** | Email pengguna + Alamat IP | `throttle:login` |

### Respons Header Rate Limiting
Setiap request yang diproses akan mengembalikan header informasi laju:
- `X-RateLimit-Limit`: Batas maksimum permintaan dalam jendela waktu (misal: 60 atau 5).
- `X-RateLimit-Remaining`: Sisa kuota permintaan yang tersedia dalam periode saat ini.
- `Retry-After`: Jumlah detik yang harus ditunggu sebelum dapat mengirim request kembali (hanya jika terkena limit).

### Respons Melebihi Batas (HTTP 429)
Jika batas permintaan terlampaui, server akan mengembalikan respons HTTP `429 Too Many Requests`:
```json
{
  "message": "Too Many Attempts."
}
```

---

## 3. Format Kode Status HTTP

| Status Code | Deskripsi |
|---|---|
| `200 OK` | Permintaan berhasil diproses |
| `201 Created` | Data baru berhasil dibuat |
| `204 No Content` | Berhasil dihapus / tidak ada konten yang dikembalikan |
| `401 Unauthorized` | Token Bearer tidak valid atau belum login |
| `403 Forbidden` | Tidak memiliki hak akses (role tidak sesuai atau bukan pemilik data) |
| `404 Not Found` | Data atau rute tidak ditemukan |
| `422 Unprocessable Content` | Validasi input gagal / pelanggaran aturan bisnis |
| `429 Too Many Requests` | Melebihi ambang batas *rate limiting* |

---

## 4. Daftar Endpoint & Contoh cURL

---

### A. Modul Autentikasi

#### 1. Login
* **Method**: `POST`
* **Endpoint**: `/auth/login`
* **Akses**: Publik
* **Rate Limit**: **5 request / menit**
* **Request Body**:
  ```json
  {
    "email": "dosen@example.com",
    "password": "password"
  }
  ```
* **Contoh cURL**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{
      "email": "dosen@example.com",
      "password": "password"
    }'
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": {
      "token": "1|AbCdEf123456...",
      "user": {
        "id": 2,
        "name": "Budi Santoso, M.Kom.",
        "email": "dosen@example.com",
        "nim_nip": "198501012010121001",
        "role": "dosen",
        "created_at": "2026-01-10T08:00:00.000000Z",
        "updated_at": "2026-01-10T08:00:00.000000Z"
      }
    }
  }
  ```

---

#### 2. Profil Pengguna Saat Ini (Me)
* **Method**: `GET`
* **Endpoint**: `/me`
* **Akses**: Autentikasi (Semua Role)
* **Contoh cURL**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/me \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": {
      "id": 2,
      "name": "Budi Santoso, M.Kom.",
      "email": "dosen@example.com",
      "nim_nip": "198501012010121001",
      "role": "dosen",
      "created_at": "2026-01-10T08:00:00.000000Z",
      "updated_at": "2026-01-10T08:00:00.000000Z"
    }
  }
  ```

---

#### 3. Logout
* **Method**: `POST`
* **Endpoint**: `/auth/logout`
* **Akses**: Autentikasi (Semua Role)
* **Contoh cURL**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/logout \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "message": "Berhasil logout."
  }
  ```

---

### B. Modul Mata Kuliah (Courses)

#### 4. Daftar Mata Kuliah
* **Method**: `GET`
* **Endpoint**: `/courses`
* **Akses**: Autentikasi (Admin: semua MK; Dosen: MK yang diampu; Mahasiswa: MK yang diikuti)
* **Query Parameters**:
  - `per_page` (integer, default: 15)
  - `page` (integer, default: 1)
* **Contoh cURL**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/courses?per_page=10&page=1" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "code": "IF101",
        "name": "Pemrograman Web",
        "description": "Dasar pemrograman web modern",
        "sks": 3,
        "status": "active",
        "lecturer_id": 2,
        "lecturer": {
          "id": 2,
          "name": "Budi Santoso, M.Kom.",
          "email": "dosen@example.com",
          "nim_nip": "198501012010121001",
          "role": "dosen"
        },
        "created_at": "2026-01-15T09:00:00.000000Z",
        "updated_at": "2026-01-15T09:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "total": 47
    }
  }
  ```

---

#### 5. Detail Mata Kuliah
* **Method**: `GET`
* **Endpoint**: `/courses/{course_id}`
* **Akses**: Autentikasi (Pengajar MK, Mahasiswa terdaftar, atau Admin)
* **Contoh cURL**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/courses/1 \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": {
      "id": 1,
      "code": "IF101",
      "name": "Pemrograman Web",
      "description": "Dasar pemrograman web modern",
      "sks": 3,
      "status": "active",
      "lecturer_id": 2,
      "lecturer": {
        "id": 2,
        "name": "Budi Santoso, M.Kom.",
        "email": "dosen@example.com",
        "nim_nip": "198501012010121001",
        "role": "dosen"
      },
      "materials_count": 5,
      "assignments_count": 3,
      "created_at": "2026-01-15T09:00:00.000000Z",
      "updated_at": "2026-01-15T09:00:00.000000Z"
    }
  }
  ```

---

#### 6. Daftar Materi Mata Kuliah
* **Method**: `GET`
* **Endpoint**: `/courses/{course_id}/materials`
* **Akses**: Autentikasi (Pengajar MK, Mahasiswa terdaftar, atau Admin)
* **Contoh cURL**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/courses/1/materials?per_page=10" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "course_id": 1,
        "title": "Pengantar HTML5 & CSS3",
        "description": "Slide presentasi pertemuan 1",
        "file_path": "materials/pertemuan-1.pdf",
        "file_type": "pdf",
        "file_size": 2048576,
        "uploader": {
          "id": 2,
          "name": "Budi Santoso, M.Kom.",
          "email": "dosen@example.com",
          "nim_nip": "198501012010121001",
          "role": "dosen"
        },
        "created_at": "2026-01-16T10:00:00.000000Z",
        "updated_at": "2026-01-16T10:00:00.000000Z"
      }
    ]
  }
  ```

---

#### 7. Daftar Tugas pada Mata Kuliah
* **Method**: `GET`
* **Endpoint**: `/courses/{course_id}/assignments`
* **Akses**: Autentikasi (Pengajar MK, Mahasiswa terdaftar, atau Admin)
* **Query Parameters**:
  - `status` (string, optional: `draft`, `published`, `archived`)
  - `per_page` (integer, default: 15)
* **Contoh cURL**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/courses/1/assignments?status=published" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "course_id": 1,
        "created_by": 2,
        "title": "Tugas 1: Membuat Halaman Portofolio",
        "instructions": "Buatlah landing page profil pribadi dengan HTML & CSS.",
        "due_at": "2026-02-28 23:59:00",
        "max_score": 100,
        "allow_late": false,
        "status": "published",
        "creator": {
          "id": 2,
          "name": "Budi Santoso, M.Kom."
        }
      }
    ]
  }
  ```

---

### C. Modul Tugas (Assignments)

#### 8. Membuat Tugas Baru
* **Method**: `POST`
* **Endpoint**: `/assignments`
* **Akses**: Khusus Dosen pengampu mata kuliah terkait
* **Request Body**:
  ```json
  {
    "course_id": 1,
    "title": "Tugas 2: RESTful API Laravel",
    "instructions": "Implementasikan rate limiting dan buat dokumentasi lengkap.",
    "due_at": "2026-03-15 23:59:00",
    "max_score": 100,
    "allow_late": true
  }
  ```
* **Contoh cURL**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/assignments \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <TOKEN>" \
    -d '{
      "course_id": 1,
      "title": "Tugas 2: RESTful API Laravel",
      "instructions": "Implementasikan rate limiting dan buat dokumentasi lengkap.",
      "due_at": "2026-03-15 23:59:00",
      "max_score": 100,
      "allow_late": true
    }'
  ```
* **Contoh Respons (201 Created)**:
  ```json
  {
    "data": {
      "id": 2,
      "course_id": 1,
      "created_by": 2,
      "title": "Tugas 2: RESTful API Laravel",
      "instructions": "Implementasikan rate limiting dan buat dokumentasi lengkap.",
      "due_at": "2026-03-15 23:59:00",
      "max_score": 100,
      "allow_late": true,
      "status": "draft",
      "created_at": "2026-02-01T10:00:00.000000Z",
      "updated_at": "2026-02-01T10:00:00.000000Z"
    }
  }
  ```

---

#### 9. Memperbarui Tugas
* **Method**: `PUT` / `PATCH`
* **Endpoint**: `/assignments/{assignment_id}`
* **Akses**: Khusus Dosen pengampu
* **Request Body** (semua field bersifat opsional pada update):
  ```json
  {
    "title": "Tugas 2: RESTful API Laravel (Revisi Deadline)",
    "due_at": "2026-03-20 23:59:00",
    "status": "published"
  }
  ```
* **Contoh cURL**:
  ```bash
  curl -X PUT http://localhost:8000/api/v1/assignments/2 \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <TOKEN>" \
    -d '{
      "title": "Tugas 2: RESTful API Laravel (Revisi Deadline)",
      "due_at": "2026-03-20 23:59:00",
      "status": "published"
    }'
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": {
      "id": 2,
      "course_id": 1,
      "title": "Tugas 2: RESTful API Laravel (Revisi Deadline)",
      "due_at": "2026-03-20 23:59:00",
      "status": "published",
      "updated_at": "2026-02-02T11:00:00.000000Z"
    }
  }
  ```

---

#### 10. Menghapus Tugas
* **Method**: `DELETE`
* **Endpoint**: `/assignments/{assignment_id}`
* **Akses**: Khusus Dosen pengampu
* **Contoh cURL**:
  ```bash
  curl -X DELETE http://localhost:8000/api/v1/assignments/2 \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons**: `204 No Content` (tanpa body).

---

#### 11. Melihat Daftar Pengumpulan Tugas (Submissions)
* **Method**: `GET`
* **Endpoint**: `/assignments/{assignment_id}/submissions`
* **Akses**: Khusus Dosen pengampu
* **Contoh cURL**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/assignments/1/submissions?per_page=15" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 10,
        "assignment_id": 1,
        "user_id": 5,
        "file_path": "submissions/abc123xyz.zip",
        "original_name": "tugas1_10241028.zip",
        "file_size": 1048576,
        "note": "Catatan tugas",
        "submitted_at": "2026-02-25 14:00:00",
        "is_late": false,
        "student": {
          "id": 5,
          "name": "Falih Dzakwan",
          "nim_nip": "10241028"
        },
        "grade": {
          "id": 1,
          "score": 95,
          "feedback": "Pekerjaan sangat rapi dan lengkap."
        }
      }
    ]
  }
  ```

---

### D. Modul Pengumpulan & Penilaian (Submissions & Grades)

#### 12. Mengumpulkan Tugas (Submit Assignment)
* **Method**: `POST`
* **Endpoint**: `/assignments/{assignment_id}/submissions`
* **Akses**: Khusus Mahasiswa yang terdaftar pada mata kuliah tersebut
* **Content-Type**: `multipart/form-data`
* **Form Data**:
  - `file`: File tugas (wajib, max 10MB)
  - `note`: Catatan tambahan pengumpulan (opsional, max 1000 karakter)
* **Contoh cURL**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/assignments/1/submissions \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>" \
    -F "file=@/path/to/tugas_kelompok.zip" \
    -F "note=Tugas pemrograman web pertemuan 3"
  ```
* **Contoh Respons (201 Created)**:
  ```json
  {
    "data": {
      "id": 10,
      "assignment_id": 1,
      "user_id": 5,
      "file_path": "submissions/wertyuio123.zip",
      "original_name": "tugas_kelompok.zip",
      "file_size": 2457600,
      "note": "Tugas pemrograman web pertemuan 3",
      "submitted_at": "2026-02-25T14:30:00.000000Z",
      "is_late": false,
      "created_at": "2026-02-25T14:30:00.000000Z",
      "updated_at": "2026-02-25T14:30:00.000000Z"
    }
  }
  ```

---

#### 13. Memberikan / Memperbarui Nilai (Upsert Grade)
* **Method**: `PUT`
* **Endpoint**: `/submissions/{submission_id}/grade`
* **Akses**: Khusus Dosen pengampu
* **Request Body**:
  ```json
  {
    "score": 90.5,
    "feedback": "Hasil implementasi sangat baik, rate limiting bekerja optimal."
  }
  ```
* **Contoh cURL**:
  ```bash
  curl -X PUT http://localhost:8000/api/v1/submissions/10/grade \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <TOKEN>" \
    -d '{
      "score": 90.5,
      "feedback": "Hasil implementasi sangat baik, rate limiting bekerja optimal."
    }'
  ```
* **Contoh Respons**:
  - `201 Created` (jika penilaian pertama kali dibuat)
  - `200 OK` (jika nilai diperbarui)
  ```json
  {
    "data": {
      "id": 1,
      "submission_id": 10,
      "graded_by": 2,
      "score": 90.5,
      "feedback": "Hasil implementasi sangat baik, rate limiting bekerja optimal.",
      "graded_at": "2026-02-26T09:00:00.000000Z",
      "created_at": "2026-02-26T09:00:00.000000Z",
      "updated_at": "2026-02-26T09:00:00.000000Z"
    }
  }
  ```

---

### E. Modul Notifikasi (Notifications)

#### 14. Daftar Notifikasi Pengguna
* **Method**: `GET`
* **Endpoint**: `/notifications`
* **Akses**: Autentikasi (Semua Role)
* **Query Parameters**:
  - `per_page` (integer, default: 15)
  - `page` (integer, default: 1)
* **Contoh cURL**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/notifications?per_page=10" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": "e445b23d-bcf8-4991-8869-7db724b025d5",
        "type": "NewAssignmentNotification",
        "data": {
          "title": "Tugas Baru: RESTful API",
          "course_name": "Pemrograman Web"
        },
        "read_at": null,
        "created_at": "2026-02-20T08:00:00Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "total": 1
    }
  }
  ```

---

#### 15. Menandai Notifikasi Telah Dibaca
* **Method**: `POST`
* **Endpoint**: `/notifications/{notification_id}/read`
* **Akses**: Autentikasi (Pemilik Notifikasi)
* **Contoh cURL**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/notifications/e445b23d-bcf8-4991-8869-7db724b025d5/read \
    -H "Accept: application/json" \
    -H "Authorization: Bearer <TOKEN>"
  ```
* **Contoh Respons (200 OK)**:
  ```json
  {
    "message": "Notifikasi berhasil ditandai telah dibaca."
  }
  ```
