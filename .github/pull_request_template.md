## Deskripsi
<!-- Jelaskan secara singkat apa yang dilakukan pada PR ini dan alasannya -->

## Rincian Perubahan
<!-- Tuliskan poin-poin perubahan atau fitur yang ditambahkan -->
- 

## Tipe Perubahan
Centang yang sesuai:
- [ ] `feat`: Penambahan fitur baru
- [ ] `fix`: Perbaikan bug atau temuan review
- [ ] `refactor`: Perubahan struktur kode tanpa mengubah fungsionalitas
- [ ] `docs`: Penambahan atau pembaruan dokumentasi
- [ ] `test`: Penambahan pengujian unit / feature

## Cara Pengujian
<!-- Tuliskan perintah untuk menguji perubahan ini secara lokal -->
1. Jalankan migrasi dan seeder: `php artisan migrate:fresh --seed`
2. Jalankan pengujian: `php artisan test`

## Checklist Sebelum Penggabungan (Merge)
- [ ] Seluruh pengujian lokal lolos (hijau)
- [ ] Tidak ada kode debug yang tertinggal (seperti `dd()` atau `dump()`)
- [ ] CI GitHub Actions (`laravel-tests`) berhasil (hijau)
- [ ] Mendapatkan minimal satu approval dari reviewer
