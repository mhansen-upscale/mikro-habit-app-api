<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\HabitEntriesController;
use App\Http\Controllers\HabitsController;
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

        /** secured routes */
        Route::middleware("auth:sanctum")->get('/', [HabitsController::class, 'index']);
        Route::middleware("auth:sanctum")->post('/', [HabitsController::class, 'create']);
        Route::middleware("auth:sanctum")->put('/{id}', [HabitsController::class, 'update'])->where("id", "[0-9]+");;
        Route::middleware("auth:sanctum")->delete('/{id}', [HabitsController::class, 'delete'])->where("id", "[0-9]+");

        /**
         * API routes habits
         */
        Route::group(['prefix' => '{id}'], function () {

            Route::middleware("auth:sanctum")->get('/', [HabitsController::class, 'get'])->where("id", "[0-9]+");

            /**
             * API routes habits
             */
            Route::group(['prefix' => 'entries'], function () {

                /** secured routes */
                Route::middleware("auth:sanctum")->get('/', [HabitEntriesController::class, 'index']);
                Route::middleware("auth:sanctum")->get('/{entryId}', [HabitEntriesController::class, 'get'])->where("entryId", "[0-9]+");
                Route::middleware("auth:sanctum")->post('/', [HabitEntriesController::class, 'create']);
                Route::middleware("auth:sanctum")->put('/{entryId}', [HabitEntriesController::class, 'update'])->where("entryId", "[0-9]+");;
                Route::middleware("auth:sanctum")->delete('/{entryId}', [HabitEntriesController::class, 'delete'])->where("entryId", "[0-9]+");
            });
        });
    });

    /**
     * API routes habits
     */
    Route::group(['prefix' => 'habits'], function () {

    });
});
