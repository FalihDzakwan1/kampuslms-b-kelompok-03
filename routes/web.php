<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TentangController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Auth;


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
| PASSWORD RESET
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password',
    [PasswordResetController::class, 'showForgotPasswordForm']
)->name('password.request');

Route::post('/forgot-password',
    [PasswordResetController::class, 'sendResetLinkEmail']
)->name('password.email');

Route::get('/reset-password/{token}',
    [PasswordResetController::class, 'showResetPasswordForm']
)->name('password.reset');

Route::post('/reset-password',
    [PasswordResetController::class, 'resetPassword']
)->name('password.update');



/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});


Route::get('/tentang',
    [TentangController::class, 'index']
)->name('tentang');



/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


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

            // Admin: kelola peserta semua MK
            Route::get('courses/{course}/enrollments', [CourseController::class, 'enrollments'])->name('courses.enrollments');
            Route::post('courses/{course}/enrollments', [CourseController::class, 'enrollStore'])->name('courses.enrollments.store');
            Route::delete('courses/{course}/enrollments/{student}', [CourseController::class, 'unenroll'])->name('courses.enrollments.destroy');

            Route::scopeBindings()
                ->resource(
                    'courses.assignments',
                    AssignmentController::class
                )
                ->only(['index', 'show'])
                ->shallow();

            Route::scopeBindings()
                ->resource(
                    'courses.materials',
                    MaterialController::class
                )
                ->only(['index', 'show'])
                ->shallow();

            Route::resource(
                'submissions',
                SubmissionController::class
            )->only(['show']);

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
            )->only(['index', 'show']);
            Route::get('courses/{course}/enrollments', [CourseController::class, 'enrollments'])->name('courses.enrollments');
            Route::post('courses/{course}/enrollments', [CourseController::class, 'enrollStore'])->name('courses.enrollments.store');
            Route::delete('courses/{course}/enrollments/{student}', [CourseController::class, 'unenroll'])->name('courses.enrollments.destroy');

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