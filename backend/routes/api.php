<?php

use App\Http\Controllers\Ai\AiHabitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\HabitLogController;
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
        Route::delete('/habit/{habit}', [HabitController::class, 'destroy']);

        Route::get('/habit/log/stats', [HabitLogController::class, 'getStats']);
        Route::get('/habit/{habit}/log', [HabitLogController::class, 'index']);
        Route::post('/habit/{habit}/log', [HabitLogController::class, 'store']);

        Route::prefix('/ai')->group(function () {
            Route::get('/habit/analysis', [AiHabitController::class, 'analyzeHabits']);
            Route::get('/habit/suggestions', [AiHabitController::class, 'suggestHabits']);
            Route::get('/habit/create-habit', [AiHabitController::class, 'createSmartHabits']);
        });
    });
});
