<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'v1'], function () {

    /**
     * API routes authentication
     */
    Route::group(['prefix' => 'authentication'], function () {

        /** non-secured routes */
        Route::post('/login', [AuthenticationController::class, 'login']);
        Route::post('/password-reset-send', [AuthenticationController::class, 'sendPasswordReset']);
        Route::post('/register', [AuthenticationController::class, 'register']);
        Route::post('/check-reset-token', [AuthenticationController::class, 'checkResetToken']);
        Route::post('/password-reset', [AuthenticationController::class, 'resetPassword']);

        /** secured routes */
        Route::middleware("auth:sanctum")->get('/auth', [AuthenticationController::class, 'authenticate']);
        Route::middleware(["auth:sanctum"])->post('/logout', [AuthenticationController::class, 'logout']);
    });

    /**
     * API routes habits
     */
    Route::group(['prefix' => 'users'], function () {

        /** secured routes */
        Route::middleware("auth:sanctum")->get('/', [UsersController::class, 'index']);
        Route::middleware("auth:sanctum")->get('/{id}', [UsersController::class, 'get'])->where("id", "[0-9]+");
        Route::middleware("auth:sanctum")->post('/', [UsersController::class, 'create']);
        Route::middleware("auth:sanctum")->put('/{id}', [UsersController::class, 'update'])->where("id", "[0-9]+");;
        Route::middleware("auth:sanctum")->delete('/{id}', [UsersController::class, 'delete'])->where("id", "[0-9]+");
    });

    /**
     * API routes habits
     */
    Route::group(['prefix' => 'habits'], function () {

    });
});
