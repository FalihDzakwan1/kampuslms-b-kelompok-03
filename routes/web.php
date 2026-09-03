<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
});

use App\Http\Controllers\TentangController;

Route::get('/tentang', [TentangController::class, 'index']);

use App\Http\Controllers\CourseController;

Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->name('courses.show');