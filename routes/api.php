<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\ProjectFamilyController;

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

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::get('/families/{family}', [FamilyController::class, 'show']);
        Route::post('/families/{family}', [FamilyController::class, 'update']);
        Route::delete('/families/{family}', [FamilyController::class, 'destroy']);


        Route::get('/families/{family}/members', [FamilyMemberController::class, 'index']);
        Route::post('/families/{family}/members', [FamilyMemberController::class, 'store']);
        Route::get('/families/{family}/members/{member}', [FamilyMemberController::class, 'show']);
        Route::post('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);
        Route::delete('/families/{family}/members/{member}', [FamilyMemberController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/projects/export/data', [ProjectController::class, 'export']);


        Route::get('/projects/{project}/families', [ProjectFamilyController::class, 'index']);
        Route::post('/projects/{project}/families', [ProjectFamilyController::class, 'store']);
        Route::delete('/projects/{project}/families/{family}', [ProjectFamilyController::class, 'destroy']);
        Route::post('/projects/{project}/families/sync', [ProjectFamilyController::class, 'syncFamilies']);
        Route::get('/projects/{project}/families/available', [ProjectFamilyController::class, 'availableFamilies']);
    });


});
