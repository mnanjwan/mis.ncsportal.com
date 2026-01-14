<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\CommandController;
use App\Http\Controllers\Api\V1\DeceasedOfficerController;
use App\Http\Controllers\Api\V1\DutyRosterController;
use App\Http\Controllers\Api\V1\EmolumentController;
use App\Http\Controllers\Api\V1\EmolumentTimelineController;
use App\Http\Controllers\Api\V1\LeaveApplicationController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\ManningRequestController;
use App\Http\Controllers\Api\V1\MovementOrderController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OfficerController;
use App\Http\Controllers\Api\V1\OfficerCourseController;
use App\Http\Controllers\Api\V1\PassApplicationController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\QuarterController;
use App\Http\Controllers\Api\V1\RetirementController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\StaffOrderController;
use App\Http\Controllers\Api\V1\ZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Officers
    Route::get('/officers', [OfficerController::class, 'index']);
    Route::get('/officers/{id}', [OfficerController::class, 'show']);
    Route::patch('/officers/{id}', [OfficerController::class, 'update']);
    Route::patch('/officers/{id}/quartered-status', [OfficerController::class, 'updateQuarteredStatus']);
    Route::post('/officers/bulk-update-quartered-status', [OfficerController::class, 'bulkUpdateQuarteredStatus']);
    Route::get('/officers/{id}/emoluments', [EmolumentController::class, 'index']);
    Route::get('/officers/{id}/leave-applications', [LeaveApplicationController::class, 'index']);

    // Emolument Timelines
    Route::get('/emolument-timelines', [EmolumentTimelineController::class, 'index']);
    Route::get('/emolument-timelines/active', [EmolumentTimelineController::class, 'active']);
    Route::post('/emolument-timelines', [EmolumentTimelineController::class, 'store']);
    Route::patch('/emolument-timelines/{id}/extend', [EmolumentTimelineController::class, 'extend']);

    // Emoluments
    Route::get('/emoluments', [EmolumentController::class, 'index']);
    Route::get('/emoluments/my-emoluments', [EmolumentController::class, 'myEmoluments']);
    Route::post('/emoluments', [EmolumentController::class, 'store']);
    Route::get('/emoluments/{id}', [EmolumentController::class, 'show']);
    Route::post('/officers/{id}/emoluments', [EmolumentController::class, 'store']);
    Route::post('/emoluments/{id}/assess', [EmolumentController::class, 'assess']);
    Route::post('/emoluments/{id}/validate', [EmolumentController::class, 'validate']);
    Route::get('/emoluments/validated', [EmolumentController::class, 'validated']);

    // Leave Types
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);
    Route::post('/leave-types', [LeaveTypeController::class, 'store']);

    // Leave Applications
    Route::get('/leave-applications', [LeaveApplicationController::class, 'index']);
    Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show']);
    Route::post('/officers/{id}/leave-applications', [LeaveApplicationController::class, 'store']);
    Route::post('/leave-applications/{id}/minute', [LeaveApplicationController::class, 'minute']);
    Route::post('/leave-applications/{id}/approve', [LeaveApplicationController::class, 'approve']);
    Route::post('/leave-applications/{id}/print', [LeaveApplicationController::class, 'print']);

    // Zones
    Route::get('/zones', [ZoneController::class, 'index']);
    Route::get('/zones/{id}', [ZoneController::class, 'show']);

    // Commands
    Route::get('/commands', [CommandController::class, 'index']);
    Route::get('/commands/{id}', [CommandController::class, 'show']);

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Pass Applications
    Route::get('/pass-applications', [PassApplicationController::class, 'index']);
    Route::post('/officers/{id}/pass-applications', [PassApplicationController::class, 'store']);
    Route::post('/pass-applications/{id}/approve', [PassApplicationController::class, 'approve']);

    // Manning Requests
    Route::get('/manning-requests', [ManningRequestController::class, 'index']);
    Route::post('/manning-requests', [ManningRequestController::class, 'store']);
    Route::post('/manning-requests/{id}/submit', [ManningRequestController::class, 'submit']);
    Route::post('/manning-requests/{id}/approve', [ManningRequestController::class, 'approve']);

    // Staff Orders
    Route::get('/staff-orders', [StaffOrderController::class, 'index']);
    Route::post('/staff-orders', [StaffOrderController::class, 'store']);

    // Movement Orders
    Route::get('/movement-orders', [MovementOrderController::class, 'index']);
    Route::post('/movement-orders', [MovementOrderController::class, 'store']);
    Route::get('/movement-orders/{id}', [MovementOrderController::class, 'show']);

    // Promotions
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::get('/promotions/{id}', [PromotionController::class, 'show']);
    Route::get('/promotion-eligibility-lists', [PromotionController::class, 'eligibilityLists']);
    Route::get('/promotions/dashboard-stats', [PromotionController::class, 'dashboardStats']);
    Route::post('/promotions/eligibility-lists', [PromotionController::class, 'createEligibilityList']);
    Route::post('/promotions/eligibility-lists/{id}/generate', [PromotionController::class, 'generateEligibilityList']);
    Route::post('/promotions/{id}/approve', [PromotionController::class, 'approve']);

    // Retirement
    Route::get('/retirement-lists', [RetirementController::class, 'index']);
    Route::post('/retirement-lists/generate', [RetirementController::class, 'generateList']);
    Route::get('/retirement-lists/{id}', [RetirementController::class, 'show']);
    Route::post('/retirement-items/{id}/process', [RetirementController::class, 'processRetirement']);

    // Duty Roster
    Route::get('/duty-rosters', [DutyRosterController::class, 'index']);
    Route::post('/duty-rosters', [DutyRosterController::class, 'store']);
    Route::get('/duty-rosters/{id}', [DutyRosterController::class, 'show']);
    Route::get('/officers/{id}/duty-schedule', [DutyRosterController::class, 'officerSchedule']);

    // Deceased Officers
    Route::get('/deceased-officers', [DeceasedOfficerController::class, 'index']);
    Route::post('/deceased-officers', [DeceasedOfficerController::class, 'store']);
    Route::get('/deceased-officers/{id}', [DeceasedOfficerController::class, 'show']);

    // Officer Courses
    Route::get('/officer-courses', [OfficerCourseController::class, 'index']);
    Route::post('/officer-courses', [OfficerCourseController::class, 'store']);
    Route::get('/officer-courses/{id}', [OfficerCourseController::class, 'show']);

    // Quarters
    Route::get('/quarters', [QuarterController::class, 'index']);
    Route::get('/quarters/statistics', [QuarterController::class, 'statistics']);
    Route::post('/quarters', [QuarterController::class, 'store']);
    Route::post('/quarters/allocate', [QuarterController::class, 'allocate']);
    Route::post('/quarters/{id}/deallocate', [QuarterController::class, 'deallocate']);
    
    // Quarter Requests (Officers)
    Route::post('/quarters/request', [QuarterController::class, 'submitRequest']);
    Route::get('/quarters/my-requests', [QuarterController::class, 'myRequests']);
    
    // Quarter Allocations (Officers)
    Route::get('/quarters/my-allocations', [QuarterController::class, 'myAllocations']);
    Route::post('/quarters/allocations/{id}/accept', [QuarterController::class, 'acceptAllocation']);
    Route::post('/quarters/allocations/{id}/reject', [QuarterController::class, 'rejectAllocation']);
    
    // Quarter Requests (Building Unit)
    Route::get('/quarters/requests', [QuarterController::class, 'requests']);
    Route::post('/quarters/requests/{id}/approve', [QuarterController::class, 'approveRequest']);
    Route::post('/quarters/requests/{id}/reject', [QuarterController::class, 'rejectRequest']);
    
    // Pending Allocations (Building Unit)
    Route::get('/quarters/pending-allocations', [QuarterController::class, 'pendingAllocations']);
    
    // Rejected Allocations (Building Unit)
    Route::get('/quarters/rejected-allocations', [QuarterController::class, 'rejectedAllocations']);

    // Chat
    Route::get('/chat/rooms', [ChatController::class, 'rooms']);
    Route::post('/chat/rooms', [ChatController::class, 'createRoom']);
    Route::get('/chat/rooms/{id}/messages', [ChatController::class, 'messages']);
    Route::post('/chat/rooms/{id}/messages', [ChatController::class, 'sendMessage']);
});
