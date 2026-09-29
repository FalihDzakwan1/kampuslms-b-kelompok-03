<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TentangController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
<<<<<<< Updated upstream
=======
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LoginController;


Route::get('/login',
    [LoginController::class, 'index']
)->name('login');


Route::post('/login',
    [LoginController::class, 'login']
);


Route::post('/logout',
    [LoginController::class, 'logout']
)->name('logout');


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
>>>>>>> Stashed changes

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/tentang', [TentangController::class, 'index'])
    ->name('tentang');

Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

Route::get('/courses/create', [CourseController::class, 'create'])
    ->name('courses.create');

<<<<<<< Updated upstream
Route::post('/courses', [CourseController::class, 'store'])
    ->name('courses.store');
=======
// AUTHENTICATED ROUTES

Route::middleware('auth')->group(function () {
>>>>>>> Stashed changes

Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->name('courses.show');

Route::resource('courses', CourseController::class);

Route::resource('users', UserController::class);