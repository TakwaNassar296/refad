<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\AboutUsController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\DelegateController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\ContributorController;
use App\Http\Controllers\Api\GovernorateController;
use App\Http\Controllers\Api\MissionPageController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RelationshipController;
use App\Http\Controllers\Api\AdminPositionController;
use App\Http\Controllers\Api\MaritalStatusController;
use App\Http\Controllers\Api\MedicalConditionController;


Route::middleware(SetLocale::class)->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
         ->middleware('custom.throttle:5,1');
        Route::post('login', [AuthController::class, 'login'])
        ->middleware('custom.throttle:5,1');

        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('verify-reset-code', [AuthController::class, 'verifyResetCode'])
         ->middleware('custom.throttle:3,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::delete('delete-account', [AuthController::class, 'destroy']);
        Route::post('/user/fcm-token', [AuthController::class, 'saveFcmToken']);
        Route::get('/my-activities', [ActivityLogController::class, 'userActivityLogs']);

    });


    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('users/pending', [AdminController::class, 'pendingUsers']);
        Route::post('users/{user}/approve', [AdminController::class, 'approveUser']);
        Route::post('users/{user}/reject', [AdminController::class, 'rejectUser']);

        Route::post('/projects/{id}/approve', [AdminController::class, 'approveProject']);
        Route::get('/admin/contributions', [AdminController::class, 'allContributions']);
        Route::post('/contributions/{contributionId}/status', [AdminController::class, 'updateContributionStatus']);

        Route::post('/users/{userId}/change-password', [AuthController::class, 'changeUserPassword']);


    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile', [ProfileController::class, 'updateProfile']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    });

   

    Route::get('camps', [CampController::class, 'index']);
    Route::get('/camps/statistics', [StatisticsController::class, 'getCampStatistics']);
    Route::get('camps/{slug}', [CampController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('camps', [CampController::class, 'store']);
        Route::delete('camps/{slug}', [CampController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('users', [AdminController::class, 'createUser']);
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
    });
   
    Route::middleware(['auth:sanctum', 'role:delegate,admin'])->group(function () {
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::get('/families/{family}', [FamilyController::class, 'show']);
        Route::post('/families/{family}', [FamilyController::class, 'update']);
        Route::delete('/families/{family}', [FamilyController::class, 'destroy']);

        Route::get('/families/{id}/statistics', [FamilyController::class, 'statistics']);

        Route::get('/families/export', [FamilyController::class, 'exportFamilies']);


        Route::get('/families/{family}/members', [FamilyMemberController::class, 'index']);
        Route::post('/families/{family}/members', [FamilyMemberController::class, 'store']);
        Route::get('/families/{family}/members/{member}', [FamilyMemberController::class, 'show']);
        Route::post('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);
        Route::delete('/families/{family}/members/{member}', [FamilyMemberController::class, 'destroy']);
    });

    Route::get('/list-projects', [ProjectController::class, 'listProjects']);

    Route::middleware(['auth:sanctum', 'role:delegate,admin'])->group(function () {
    
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::post('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/projects/export/data', [ProjectController::class, 'export']);


        Route::post('camps/{slug}', [CampController::class, 'update']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/contributor/camps/families/{campId?}', [ContributorController::class, 'campFamilies']);
        Route::post('/contributor/projects/{projectId}/contribute', [ContributorController::class, 'contribute']);
        Route::get('/contributor/history', [ContributorController::class, 'history']);
    });


    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'show'])->name('settings.show');
        Route::post('/', [SettingsController::class, 'store'])->name('settings.store');
        Route::post('/{setting}', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::prefix('homepage')->group(function () {
        Route::get('/', [HomepageController::class, 'show'])->name('homepage.show');
        Route::post('/', [HomepageController::class, 'update'])->name('homepage.update');
        Route::post('/slides', [HomepageController::class, 'createSlide']);
        Route::post('/sections', [HomepageController::class, 'createSection']);
        Route::delete('/slides/{id}', [HomepageController::class, 'deleteSlide']);


    });


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    });

    Route::middleware(['auth:sanctum', 'role:delegate'])->get('user/statistics', [StatisticsController::class, 'statistics']);
    Route::middleware(['auth:sanctum', 'role:delegate'])->get('/camp/statistics', [StatisticsController::class, 'DelegateStatistics']);
    Route::get('camp/family-statistics', [CampController::class, 'getCampFamilyStatistics']);
    Route::middleware(['auth:sanctum', 'role:delegate'])->get('/camp-statistics/export', [StatisticsController::class, 'exportCampStatistics']);
    Route::middleware(['auth:sanctum', 'role:contributor'])->get('/contributor-statistics', [StatisticsController::class, 'ContributorStatistics']);

    Route::middleware(['auth:sanctum', 'role:delegate'])->group(function () {
        Route::get('/delegate/contributions', [DelegateController::class, 'contributions']);
        Route::post('/delegate/contributions/{contributionId}/confirm', [DelegateController::class, 'confirmContribution']);
        Route::post('/delegate/contributions/{contributionId}/add-families', [DelegateController::class, 'addFamiliesToContribution']);
        Route::post('contributions/{contribution}/families/{family}/quantity', [DelegateController::class, 'updateFamilyQuantity']);
        Route::delete('contributions/{contribution}/families/{family}', [DelegateController::class, 'removeFamilyFromContribution']);
    });
   
    
    Route::prefix('pages')->group(function () {
        Route::get('/', [PageController::class, 'index']); 
        Route::get('{type}', [PageController::class, 'show']);
        Route::post('{type}', [PageController::class, 'update']); 
    });


    Route::prefix('partners')->group(function () {
        Route::get('/', [PartnerController::class, 'index']);
        Route::get('/{id}', [PartnerController::class, 'show']);
        Route::post('/', [PartnerController::class, 'store']);
        Route::post('/{id}', [PartnerController::class, 'update']);
        Route::delete('/{id}', [PartnerController::class, 'destroy']);
    });
    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
        Route::get('/{id}', [TestimonialController::class, 'show']);
        Route::post('/', [TestimonialController::class, 'store']);
        Route::post('/{id}', [TestimonialController::class, 'update']);
        Route::delete('/{id}', [TestimonialController::class, 'destroy']);
    });

    Route::prefix('contact-us')->group(function () {
        Route::get('/', [ContactUsController::class, 'index']);
        Route::get('/{id}', [ContactUsController::class, 'show']);
        Route::post('/', [ContactUsController::class, 'store']);
        Route::delete('/{id}', [ContactUsController::class, 'destroy']);
    });
    
    Route::prefix('complaints')->group(function () {
        Route::get('/', [ComplaintController::class, 'index']);
        Route::get('/{id}', [ComplaintController::class, 'show']);
        Route::post('/', [ComplaintController::class, 'store']);
        Route::delete('/{id}', [ComplaintController::class, 'destroy']);
    });


    Route::prefix('about-us')->group(function () {
        Route::get('/', [AboutUsController::class, 'index']);
        Route::post('/{type}', [AboutUsController::class, 'update']);

    });

    Route::get('/stats', [StatisticsController::class, 'getStats']);

    Route::prefix('governorates')->group(function () {
        Route::get('/', [GovernorateController::class, 'index']);
        Route::post('/', [GovernorateController::class, 'store']);
        Route::get('{governorate}', [GovernorateController::class, 'show']);
        Route::post('{governorate}', [GovernorateController::class, 'update']);
        Route::delete('{governorate}', [GovernorateController::class, 'destroy']);
    });

    Route::prefix('marital-statuses')->group(function () {
        Route::get('/', [MaritalStatusController::class, 'index']);
        Route::post('/', [MaritalStatusController::class, 'store']);
        Route::get('{maritalStatus}', [MaritalStatusController::class, 'show']);
        Route::post('{maritalStatus}', [MaritalStatusController::class, 'update']);
        Route::delete('{maritalStatus}', [MaritalStatusController::class, 'destroy']);
    });

    Route::prefix('medical-conditions')->group(function () {
        Route::get('/', [MedicalConditionController::class, 'index']);
        Route::post('/', [MedicalConditionController::class, 'store']);
        Route::get('{medicalCondition}', [MedicalConditionController::class, 'show']);
        Route::post('{medicalCondition}', [MedicalConditionController::class, 'update']);
        Route::delete('{medicalCondition}', [MedicalConditionController::class, 'destroy']);
    });


    Route::prefix('admin-positions')->group(function () {
        Route::get('/', [AdminPositionController::class, 'index']);
        Route::post('/', [AdminPositionController::class, 'store']);
        Route::get('{adminPosition}', [AdminPositionController::class, 'show']);
        Route::post('{adminPosition}', [AdminPositionController::class, 'update']);
        Route::delete('{adminPosition}', [AdminPositionController::class, 'destroy']);
    });

    Route::prefix('relationships')->group(function () {
        Route::get('/', [RelationshipController::class, 'index']);
        Route::post('/', [RelationshipController::class, 'store']);
        Route::get('{relationship}', [RelationshipController::class, 'show']);
        Route::post('{relationship}', [RelationshipController::class, 'update']);
        Route::delete('{relationship}', [RelationshipController::class, 'destroy']);
    });
});
