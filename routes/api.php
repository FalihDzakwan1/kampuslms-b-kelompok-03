<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Resources\UserResource;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [
        AuthController::class,
        'login'
    ])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        Route::post('/auth/logout', [
            AuthController::class,
            'logout'
        ]);

        Route::get('/me', [
            AuthController::class,
            'me'
        ]);

        Route::apiResource(
            'courses',
            CourseController::class
        );

        Route::apiResource(
            'assignments',
            AssignmentController::class
        );

        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });

    });

});

