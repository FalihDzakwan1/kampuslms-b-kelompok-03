<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TentangController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LoginController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

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
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');


Route::get('/tentang',
    [TentangController::class, 'index']
)->name('tentang');



/*
|--------------------------------------------------------------------------
| AUTHENTICATED
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


            Route::resource(
                'courses',
                CourseController::class
            );


            Route::scopeBindings()
                ->resource(
                    'courses.assignments',
                    AssignmentController::class
                )
                ->shallow();



            Route::scopeBindings()
                ->resource(
                    'courses.materials',
                    MaterialController::class
                )
                ->shallow();


            Route::resource(
                'submissions',
                SubmissionController::class
            )->only(['show']);

            Route::put(
                'submissions/{submission}/grade',
                [SubmissionController::class, 'grade']
            )->name('submissions.grade');

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


            Route::resource(
                'courses',
                CourseController::class
            )
            ->only([
                'index',
                'show'
            ]);

            Route::post(
                'courses/{course}/enroll',
                [CourseController::class, 'enroll']
            )->name('courses.enroll');


            Route::scopeBindings()
                ->resource(
                    'courses.assignments',
                    AssignmentController::class
                )
                ->only([
                    'index',
                    'show'
                ])
                ->shallow();


            Route::scopeBindings()
                ->resource(
                    'courses.materials',
                    MaterialController::class
                )
                ->only([
                    'index',
                    'show'
                ])
                ->shallow();


            Route::post(
                'assignments/{assignment}/submissions',
                [SubmissionController::class, 'store']
            )->name('assignments.submissions.store');


            Route::resource(
                'submissions',
                SubmissionController::class
            )
            ->only([
                'index',
                'show',
                'destroy'
            ]);


        });


});