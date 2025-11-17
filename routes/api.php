<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProfileController;

Route::middleware(SetLocale::class)->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('verify-reset-code', [AuthController::class, 'verifyResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::delete('delete-account', [AuthController::class, 'destroy']);
    });


    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('delegates/pending', [AdminController::class, 'pendingDelegates']);
        Route::post('delegates/{delegate}/approve', [AdminController::class, 'approveDelegate']);
        Route::post('delegates/{delegate}/reject', [AdminController::class, 'rejectDelegate']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile', [ProfileController::class, 'updateProfile']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('camps', [CampController::class, 'index']);
        Route::get('camps/{slug}', [CampController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::post('camps', [CampController::class, 'store']);
            Route::post('camps/{slug}', [CampController::class, 'update']);
            Route::delete('camps/{slug}', [CampController::class, 'destroy']);
        });
    });


});
