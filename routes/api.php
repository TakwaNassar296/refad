<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\API\HomepageController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\ContributorController;
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
        Route::post('/user/fcm-token', [AuthController::class, 'saveFcmToken']);
        Route::get('/my-activities', [ActivityLogController::class, 'userActivityLogs']);

    });


    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('delegates/pending', [AdminController::class, 'pendingDelegates']);
        Route::post('delegates/{delegate}/approve', [AdminController::class, 'approveDelegate']);
        Route::post('delegates/{delegate}/reject', [AdminController::class, 'rejectDelegate']);
        Route::delete('/families/{family}', [FamilyController::class, 'destroy']);
        Route::delete('/families/{family}/members/{member}', [FamilyMemberController::class, 'destroy']);
        Route::delete('/projects/{project}/families/{family}', [ProjectFamilyController::class, 'destroy']);

        Route::get('/admin/contributions', [AdminController::class, 'allContributions']);
        Route::post('/contributions/{contributionId}/status', [AdminController::class, 'updateContributionStatus']);

    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile', [ProfileController::class, 'updateProfile']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    });

   

    Route::get('camps', [CampController::class, 'index']);
    Route::get('camps/{slug}', [CampController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('camps', [CampController::class, 'store']);
        Route::post('camps/{slug}', [CampController::class, 'update']);
        Route::delete('camps/{slug}', [CampController::class, 'destroy']);
    });
   

    Route::middleware(['auth:sanctum', 'role:delegate'])->group(function () {
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::get('/families/{family}', [FamilyController::class, 'show']);
        Route::post('/families/{family}', [FamilyController::class, 'update']);


        Route::get('/families/{family}/members', [FamilyMemberController::class, 'index']);
        Route::post('/families/{family}/members', [FamilyMemberController::class, 'store']);
        Route::get('/families/{family}/members/{member}', [FamilyMemberController::class, 'show']);
        Route::post('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);

        Route::get('/delegate/contributions', [ProjectController::class, 'delegateContributions']);
    });

    Route::middleware(['auth:sanctum', 'role:delegate'])->group(function () {
    
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::post('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/projects/export/data', [ProjectController::class, 'export']);


        Route::get('/projects/{project}/families', [ProjectFamilyController::class, 'index']);
        Route::post('/projects/{project}/families', [ProjectFamilyController::class, 'store']);
        Route::post('projects/{project}/families/mark-beneficial', [ProjectFamilyController::class, 'markAsBeneficial']);
    });

    Route::middleware(['auth:sanctum', 'role:contributor'])->group(function () {
        Route::get('contributor/camps', [ContributorController::class, 'index']);
        Route::get('contributor/camps/{campId}/projects', [ContributorController::class, 'projects']);
        Route::get('/contributor/camps/{campId}/families', [ContributorController::class, 'campFamilies']);
        Route::post('/contributor/projects/{projectId}/contribute', [ContributorController::class, 'contribute']);
        Route::get('/contributor/history', [ContributorController::class, 'history']);
        Route::post('/contributor/contributions/{id}', [ContributorController::class, 'update']);
        Route::delete('/contributor/contributions/{id}', [ContributorController::class, 'destroy']);
    });


    Route::middleware('auth:sanctum')->get('user/statistics', [StatisticsController::class, 'statistics']);


    Route::get('/homepage', [HomepageController::class, 'index']);
    Route::get('/settings', [HomepageController::class, 'setting']);

    
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{type}', [PageController::class, 'show']);
    Route::get('/partners', [PageController::class, 'partner']);
    Route::get('/testimonials', [PageController::class, 'testimonial']);

    Route::post('/contact-us', [ContactUsController::class, 'store']);
    Route::post('/complaints', [ContactUsController::class, 'complaints']);
    Route::get('/stats', [StatisticsController::class, 'getStats']);


});
