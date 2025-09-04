<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EntriesController;
use App\Http\Controllers\HabitsController;
use App\Http\Controllers\RemindersController;
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
        Route::middleware("auth:sanctum")->get('/{id}', [HabitsController::class, 'get'])->where("id", "[0-9]+");
        Route::middleware("auth:sanctum")->post('/', [HabitsController::class, 'create']);
        Route::middleware("auth:sanctum")->put('/{id}', [HabitsController::class, 'update'])->where("id", "[0-9]+");;
        Route::middleware("auth:sanctum")->delete('/{id}', [HabitsController::class, 'delete'])->where("id", "[0-9]+");

    });

    /**
     * API routes habit entries
     */
    Route::group(['prefix' => 'entries'], function () {

        /** secured routes */
        Route::middleware("auth:sanctum")->get('/', [EntriesController::class, 'index']);
        Route::middleware("auth:sanctum")->get('/{id}', [EntriesController::class, 'get'])->where("entryId", "[0-9]+");
        Route::middleware("auth:sanctum")->post('/', [EntriesController::class, 'create']);
        Route::middleware("auth:sanctum")->put('/{id}', [EntriesController::class, 'update'])->where("entryId", "[0-9]+");
        Route::middleware("auth:sanctum")->delete('/{id}', [EntriesController::class, 'delete'])->where("entryId", "[0-9]+");
    });

    /**
     * API routes habit reminders
     */
    Route::group(['prefix' => 'reminders'], function () {

        /** secured routes */
        Route::middleware("auth:sanctum")->get('/', [RemindersController::class, 'index']);
        Route::middleware("auth:sanctum")->get('/{reminderId}', [RemindersController::class, 'get'])->where("entryId", "[0-9]+");
        Route::middleware("auth:sanctum")->post('/', [RemindersController::class, 'create']);
        Route::middleware("auth:sanctum")->put('/{reminderId}', [RemindersController::class, 'update'])->where("entryId", "[0-9]+");
        Route::middleware("auth:sanctum")->delete('/{reminderId}', [RemindersController::class, 'delete'])->where("entryId", "[0-9]+");
    });
});
