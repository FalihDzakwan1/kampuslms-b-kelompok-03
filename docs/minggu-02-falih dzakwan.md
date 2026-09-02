1. Baris mana di routes/web.php yang menangkapnya?

    berikut baris yang menangkapnya
```php
Route::get('/tentang', function () {
    return view('tentang');
});
```
2. Kalau ditangani controller, berkas dan method mana?

3. View mana yang dikembalikan? Di path apa persisnya?
4. Layout apa yang membungkusnya?
5. Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?