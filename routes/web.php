<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TentangController;
use App\Http\Controllers\CourseController;


Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/tentang', [TentangController::class, 'index'])
    ->name('tentang');

Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

Route::get('/courses/create', [CourseController::class, 'create'])
    ->name('courses.create');

Route::post('/courses', [CourseController::class, 'store'])
    ->name('courses.store');

Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->name('courses.show');

Route::post('/courses', [CourseController::class, 'store'])
    ->name('courses.store');
