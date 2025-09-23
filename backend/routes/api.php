<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::post('/auth', [AuthController::class, 'store']);

    Route::post('/user', [UserController::class, 'store']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/auth', [AuthController::class, 'index']);
        Route::put('/auth', [AuthController::class, 'update']);
        Route::delete('/auth', [AuthController::class, 'destroy']);

        Route::get('/habit', [HabitController::class, 'index']);
        Route::get('/habit/{habit}', [HabitController::class, 'show']);
        Route::post('/habit', [HabitController::class, 'store']);
        Route::put('/habit/{habit}', [HabitController::class, 'update']);
    });
});
