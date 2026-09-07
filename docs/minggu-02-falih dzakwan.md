1. Baris mana di routes/web.php yang menangkapnya?

    berikut baris yang menangkapnya
```php
Route::get('/tentang', [TentangController::class, 'index'])
    ->name('tentang');
```
2. Kalau ditangani controller, berkas dan method mana?
Ada di dalam file app/Http/Controlles/TentangController.php
berikut methodnya:
```php
<?php

namespace App\Http\Controllers;

class TentangController extends Controller
{
    public function index()
    {
        return view('tentang');
    }
}
```

3. View mana yang dikembalikan? Di path apa persisnya?
4. Layout apa yang membungkusnya?
5. Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?