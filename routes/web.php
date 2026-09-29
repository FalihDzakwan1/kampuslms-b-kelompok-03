<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TentangController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubmissionController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/tentang', [TentangController::class, 'index'])
    ->name('tentang');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {

            Route::resource(
                'users',
                UserController::class
            );

            Route::resource(
                'courses',
                CourseController::class
            );

        });


    /*
    |--------------------------------------------------------------------------
    | DOSEN
    |--------------------------------------------------------------------------
    */

    Route::prefix('dosen')
        ->name('dosen.')
        ->middleware('role:dosen')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | CRUD Course Dosen
            |--------------------------------------------------------------------------
            |
            | URL:
            | /dosen/courses
            |
            */

            Route::resource(
                'courses',
                CourseController::class
            );


            /*
            |--------------------------------------------------------------------------
            | Nested Assignment
            |--------------------------------------------------------------------------
            |
            | URL:
            | /dosen/courses/{course}/assignments
            |
            | Detail:
            | /dosen/assignments/{assignment}
            |
            | Menggunakan:
            | - scopeBindings()
            | - shallow()
            |
            */

            Route::scopeBindings()
                ->resource(
                    'courses.assignments',
                    AssignmentController::class
                )
                ->shallow();


            /*
            |--------------------------------------------------------------------------
            | Nested Material
            |--------------------------------------------------------------------------
            |
            | URL:
            | /dosen/courses/{course}/materials
            |
            | Detail:
            | /dosen/materials/{material}
            |
            | Menggunakan:
            | - scopeBindings()
            | - shallow()
            |
            */

            Route::scopeBindings()
                ->resource(
                    'courses.materials',
                    MaterialController::class
                )
                ->shallow();

        });


    /*
    |--------------------------------------------------------------------------
    | MAHASISWA
    |--------------------------------------------------------------------------
    */

    Route::prefix('mahasiswa')
        ->name('mahasiswa.')
        ->middleware('role:mahasiswa')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Mahasiswa hanya melihat course
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'courses',
                CourseController::class
            )
            ->only([
                'index',
                'show'
            ]);


            /*
            |--------------------------------------------------------------------------
            | Submission mahasiswa
            |--------------------------------------------------------------------------
            |
            | Untuk mencegah IDOR
            | akan dilindungi di SubmissionController
            |
            */

            Route::resource(
                'submissions',
                SubmissionController::class
            )
            ->only([
                'index',
                'show',
                'store'
            ]);

        });

});