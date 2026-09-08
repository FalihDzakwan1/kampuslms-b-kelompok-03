## Elsya Nur Aulia Handayani 
## 10241026 

READ — Telusuri satu request penuh (30 menit)
Ambil route /tentang yang Anda buat minggu lalu. Tanpa AI, tulis di catatan Anda:
---
1. Baris mana di routes/web.php yang menangkapnya?
   Jawab:
2. Kalau ditangani controller, berkas dan method mana?
   Jawab:
3. View mana yang dikembalikan? Di path apa persisnya?
   Jawab:
4. Layout apa yang membungkusnya?
   Jawab:
5. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?
   jawab:










---
BREAK — Delapan kerusakan (40 menit)
---

| No | Yang dirusak | Prediksi sebelum mencoba | Pesan error sebenarnya |
|----|--------------|--------------------------|------------------------|
| 1 | Mengubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah | Halaman tidak dapat dibuka karena browser mengirim request GET, sedangkan Laravel hanya menerima method POST. | `The GET method is not supported for route courses. Supported methods: POST.` |
| 2 | Mengubah nama view pada `return view(...)` menjadi view yang tidak tersedia | Laravel tidak menemukan file Blade yang dipanggil sehingga halaman gagal ditampilkan. | `View [indri] not found.` |
| 3 | Menghapus `->name('courses.show')`, lalu membuka halaman yang menggunakan `route('courses.show')` | Laravel tidak dapat membuat URL karena route dengan nama tersebut sudah tidak terdaftar. | `Route [courses.show] not defined.` |
| 4 | Memindahkan `/courses/{course}` ke atas `/courses/create`, lalu membuka `/courses/create` | Laravel akan membaca `create` sebagai nilai parameter `{course}` karena route parameter berada lebih dahulu. | 404 Not Found |
| 5 | Mengganti `{{ $nama }}` menjadi `{!! $nama !!}`, kemudian mengisi `$nama` dengan `<script>alert('XSS')</script>` | HTML akan ditampilkan tanpa proses escaping sehingga script JavaScript dapat dijalankan di browser. | Muncul pop-up pada browser yang menunjukkan kerentanan **XSS (Cross-Site Scripting)**. |
| 6 | Menghapus `@vite(...)` dari layout Blade | File CSS dan JavaScript tidak akan dimuat karena koneksi dengan Vite dihilangkan. | Tampilan halaman kehilangan styling|
| 7 | Menghentikan proses `npm run dev`, kemudian memuat ulang halaman | Laravel tidak dapat mengambil asset dari Vite development server yang sedang berhenti. | Asset CSS/JavaScript gagal dimuat atau muncul pesan `Vite manifest not found at: C:\laragon\www\Pemroweb\kampuslms-b-kelompok-03\public\build/manifest.json |
| 8 | Memanggil `route('courses.show')` tanpa memberikan parameter | Laravel membutuhkan nilai `{course}` untuk membentuk URL route tersebut. | Missing required parameter for [Route: courses.show] [URI: courses/{course}] [Missing parameter: course]. |
