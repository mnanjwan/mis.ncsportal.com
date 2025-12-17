<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeceasedOfficerController;
use App\Http\Controllers\DutyRosterController;
use App\Http\Controllers\EmolumentController;
use App\Http\Controllers\EmolumentTimelineController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\LeavePassController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\ManningRequestController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\MovementOrderController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\PassApplicationController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\QuarterController;
use App\Http\Controllers\RetirementController;
use App\Http\Controllers\StaffOrderController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\CommandController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard Routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Officer Routes
    Route::prefix('officer')->name('officer.')->group(function () {
        Route::get('/dashboard', [OfficerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [OfficerController::class, 'profile'])->name('profile');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/leave-applications', [LeaveApplicationController::class, 'index'])->name('leave-applications');
        Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show'])->name('leave-applications.show');
        Route::get('/pass-applications', [PassApplicationController::class, 'index'])->name('pass-applications');
        Route::get('/pass-applications/{id}', [PassApplicationController::class, 'show'])->name('pass-applications.show');
        Route::get('/application-history', [OfficerController::class, 'applicationHistory'])->name('application-history');
    });

    // HRD Routes
    Route::prefix('hrd')->name('hrd.')->middleware('role:HRD')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hrd'])->name('dashboard');
        Route::get('/officers', [OfficerController::class, 'index'])->name('officers');
        Route::get('/officers/{id}', [OfficerController::class, 'show'])->name('officers.show');
        Route::get('/officers/{id}/edit', [OfficerController::class, 'edit'])->name('officers.edit');

        Route::get('/emolument-timeline', [EmolumentTimelineController::class, 'index'])->name('emolument-timeline');
        Route::get('/emolument-timeline/create', [EmolumentTimelineController::class, 'create'])->name('emolument-timeline.create');
        Route::post('/emolument-timeline', [EmolumentTimelineController::class, 'store'])->name('emolument-timeline.store');
        Route::get('/emolument-timeline/{id}/extend', [EmolumentTimelineController::class, 'extend'])->name('emolument-timeline.extend');
        Route::post('/emolument-timeline/{id}/extend', [EmolumentTimelineController::class, 'extendStore'])->name('emolument-timeline.extend.store');

        // Staff Orders - accessible to HRD (Zone Coordinators handled in controller)
        Route::get('/staff-orders', [StaffOrderController::class, 'index'])->name('staff-orders');
        Route::get('/staff-orders/create', [StaffOrderController::class, 'create'])->name('staff-orders.create');
        Route::post('/staff-orders', [StaffOrderController::class, 'store'])->name('staff-orders.store');
        Route::get('/staff-orders/{id}', [StaffOrderController::class, 'show'])->name('staff-orders.show');
        Route::get('/staff-orders/{id}/edit', [StaffOrderController::class, 'edit'])->name('staff-orders.edit');
        Route::put('/staff-orders/{id}', [StaffOrderController::class, 'update'])->name('staff-orders.update');

        Route::get('/movement-orders', [MovementOrderController::class, 'index'])->name('movement-orders');
        Route::get('/movement-orders/create', [MovementOrderController::class, 'create'])->name('movement-orders.create');
        Route::post('/movement-orders', [MovementOrderController::class, 'store'])->name('movement-orders.store');
        Route::get('/movement-orders/{id}', [MovementOrderController::class, 'show'])->name('movement-orders.show');

        Route::get('/promotion-eligibility', [PromotionController::class, 'index'])->name('promotion-eligibility');
        Route::get('/promotion-eligibility/create', [PromotionController::class, 'createEligibilityList'])->name('promotion-eligibility.create');
        Route::post('/promotion-eligibility', [PromotionController::class, 'storeEligibilityList'])->name('promotion-eligibility.store');
        Route::get('/promotion-eligibility/{id}', [PromotionController::class, 'showEligibilityList'])->name('promotion-eligibility.show');
        Route::delete('/promotion-eligibility/{id}', [PromotionController::class, 'destroyEligibilityList'])->name('promotion-eligibility.destroy');

        Route::get('/promotion-criteria', [PromotionController::class, 'criteria'])->name('promotion-criteria');
        Route::get('/promotion-criteria/create', [PromotionController::class, 'createCriteria'])->name('promotion-criteria.create');
        Route::post('/promotion-criteria', [PromotionController::class, 'storeCriteria'])->name('promotion-criteria.store');
        Route::get('/promotion-criteria/{id}/edit', [PromotionController::class, 'editCriteria'])->name('promotion-criteria.edit');
        Route::put('/promotion-criteria/{id}', [PromotionController::class, 'updateCriteria'])->name('promotion-criteria.update');

        Route::get('/retirement-list', [RetirementController::class, 'index'])->name('retirement-list');
        Route::get('/retirement-list/generate', [RetirementController::class, 'generateList'])->name('retirement-list.generate');
        Route::post('/retirement-list', [RetirementController::class, 'store'])->name('retirement-list.store');
        Route::get('/retirement-list/{id}', [RetirementController::class, 'show'])->name('retirement-list.show');
        Route::delete('/retirement-list/{id}', [RetirementController::class, 'destroy'])->name('retirement-list.destroy');

        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::post('/reports/generate', [DashboardController::class, 'generateReport'])->name('reports.generate');

        Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('leave-types');
        
        Route::get('/manning-requests', [ManningRequestController::class, 'hrdIndex'])->name('manning-requests');
        Route::get('/manning-requests/{id}', [ManningRequestController::class, 'hrdShow'])->name('manning-requests.show');
        Route::get('/manning-requests/{id}/match', [ManningRequestController::class, 'hrdMatch'])->name('manning-requests.match');
        Route::post('/manning-requests/{id}/generate-order', [ManningRequestController::class, 'hrdGenerateOrder'])->name('manning-requests.generate-order');
        Route::get('/leave-types/create', [LeaveTypeController::class, 'create'])->name('leave-types.create');
        Route::post('/leave-types', [LeaveTypeController::class, 'store'])->name('leave-types.store');
        Route::get('/leave-types/{id}/edit', [LeaveTypeController::class, 'edit'])->name('leave-types.edit');
        Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update'])->name('leave-types.update');
        Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy'])->name('leave-types.destroy');

        Route::get('/courses', [CourseController::class, 'index'])->name('courses');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
        Route::post('/courses/{id}/complete', [CourseController::class, 'markComplete'])->name('courses.complete');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('system-settings');
        Route::put('/system-settings', [SystemSettingController::class, 'update'])->name('system-settings.update');

        Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
        Route::post('/onboarding/initiate', [OnboardingController::class, 'initiate'])->name('onboarding.initiate');
        Route::post('/onboarding/bulk-initiate', [OnboardingController::class, 'bulkInitiate'])->name('onboarding.bulk-initiate');
        Route::post('/onboarding/csv-upload', [OnboardingController::class, 'csvUpload'])->name('onboarding.csv-upload');
        Route::post('/onboarding/{id}/resend-link', [OnboardingController::class, 'resendLink'])->name('onboarding.resend-link');

        Route::get('/role-assignments', [\App\Http\Controllers\RoleAssignmentController::class, 'index'])->name('role-assignments');
        Route::get('/role-assignments/create', [\App\Http\Controllers\RoleAssignmentController::class, 'create'])->name('role-assignments.create');
        Route::post('/role-assignments', [\App\Http\Controllers\RoleAssignmentController::class, 'store'])->name('role-assignments.store');
        Route::get('/role-assignments/officers-by-command', [\App\Http\Controllers\RoleAssignmentController::class, 'getOfficersByCommand'])->name('role-assignments.officers-by-command');
        Route::put('/role-assignments/{userId}/{roleId}', [\App\Http\Controllers\RoleAssignmentController::class, 'update'])->name('role-assignments.update');
        Route::delete('/role-assignments/{userId}/{roleId}', [\App\Http\Controllers\RoleAssignmentController::class, 'destroy'])->name('role-assignments.destroy');

        // Zone Management Routes
        Route::get('/zones', [ZoneController::class, 'index'])->name('zones.index');
        Route::get('/zones/create', [ZoneController::class, 'create'])->name('zones.create');
        Route::post('/zones', [ZoneController::class, 'store'])->name('zones.store');
        Route::get('/zones/{id}', [ZoneController::class, 'show'])->name('zones.show');
        Route::get('/zones/{id}/edit', [ZoneController::class, 'edit'])->name('zones.edit');
        Route::put('/zones/{id}', [ZoneController::class, 'update'])->name('zones.update');

        // Command Management Routes
        Route::get('/commands', [CommandController::class, 'index'])->name('commands.index');
        Route::get('/commands/create', [CommandController::class, 'create'])->name('commands.create');
        Route::post('/commands', [CommandController::class, 'store'])->name('commands.store');
        Route::get('/commands/{id}', [CommandController::class, 'show'])->name('commands.show');
        Route::get('/commands/{id}/edit', [CommandController::class, 'edit'])->name('commands.edit');
        Route::put('/commands/{id}', [CommandController::class, 'update'])->name('commands.update');
    });

    // Staff Officer Routes
    Route::prefix('staff-officer')->name('staff-officer.')->middleware('role:Staff Officer')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staffOfficer'])->name('dashboard');

        Route::get('/leave-pass', [LeavePassController::class, 'staffOfficerIndex'])->name('leave-pass');
        Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show'])->name('leave-applications.show');
        Route::post('/leave-applications/{id}/minute', [LeaveApplicationController::class, 'minute'])->name('leave-applications.minute');
        Route::get('/leave-applications/{id}/print', [LeaveApplicationController::class, 'print'])->name('leave-applications.print');

        Route::get('/pass-applications/{id}', [PassApplicationController::class, 'show'])->name('pass-applications.show');
        Route::post('/pass-applications/{id}/minute', [PassApplicationController::class, 'minute'])->name('pass-applications.minute');
        Route::get('/pass-applications/{id}/print', [PassApplicationController::class, 'print'])->name('pass-applications.print');

        Route::get('/manning-level', [ManningRequestController::class, 'index'])->name('manning-level');
        Route::get('/manning-level/create', [ManningRequestController::class, 'create'])->name('manning-level.create');
        Route::post('/manning-level', [ManningRequestController::class, 'store'])->name('manning-level.store');
        Route::get('/manning-level/{id}', [ManningRequestController::class, 'show'])->name('manning-level.show');
        Route::get('/manning-level/{id}/edit', [ManningRequestController::class, 'edit'])->name('manning-level.edit');
        Route::put('/manning-level/{id}', [ManningRequestController::class, 'update'])->name('manning-level.update');
        Route::post('/manning-level/{id}/submit', [ManningRequestController::class, 'submit'])->name('manning-level.submit');

        Route::get('/roster', [DutyRosterController::class, 'index'])->name('roster');
        Route::get('/roster/create', [DutyRosterController::class, 'create'])->name('roster.create');
        Route::post('/roster', [DutyRosterController::class, 'store'])->name('roster.store');
        Route::get('/roster/{id}', [DutyRosterController::class, 'show'])->name('roster.show');
        Route::get('/roster/{id}/edit', [DutyRosterController::class, 'edit'])->name('roster.edit');
        Route::put('/roster/{id}', [DutyRosterController::class, 'update'])->name('roster.update');
        Route::post('/roster/{id}/submit', [DutyRosterController::class, 'submit'])->name('roster.submit');

        Route::get('/officers', [OfficerController::class, 'index'])->name('officers');
        Route::get('/officers/{id}', [OfficerController::class, 'show'])->name('officers.show');
        Route::post('/officers/{id}/document', [OfficerController::class, 'document'])->name('officers.document');
    });

    // Assessor Routes
    Route::prefix('assessor')->name('assessor.')->middleware('role:Assessor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'assessor'])->name('dashboard');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/emoluments/{id}/assess', [EmolumentController::class, 'assess'])->name('emoluments.assess');
    });

    // Validator Routes
    Route::prefix('validator')->name('validator.')->middleware('role:Validator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'validator'])->name('dashboard');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/emoluments/{id}/validate', [EmolumentController::class, 'validateForm'])->name('emoluments.validate');
    });

    // Area Controller Routes
    Route::prefix('area-controller')->name('area-controller.')->middleware('role:Area Controller')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'areaController'])->name('dashboard');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/emoluments/{id}/validate', [EmolumentController::class, 'validateForm'])->name('emoluments.validate');
        Route::post('/emoluments/{id}/validate', [EmolumentController::class, 'processValidation'])->name('emoluments.process-validation');
        Route::get('/leave-pass', [LeavePassController::class, 'areaControllerIndex'])->name('leave-pass');
        Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show'])->name('leave-applications.show');
        Route::get('/leave-applications/{id}/print', [LeaveApplicationController::class, 'print'])->name('leave-applications.print');
        Route::get('/pass-applications/{id}', [PassApplicationController::class, 'show'])->name('pass-applications.show');
        Route::get('/pass-applications/{id}/print', [PassApplicationController::class, 'print'])->name('pass-applications.print');
        Route::get('/manning-level', [ManningRequestController::class, 'areaControllerIndex'])->name('manning-level');
        Route::get('/manning-level/{id}', [ManningRequestController::class, 'areaControllerShow'])->name('manning-level.show');
        Route::post('/manning-level/{id}/approve', [ManningRequestController::class, 'areaControllerApprove'])->name('manning-level.approve');
        Route::post('/manning-level/{id}/reject', [ManningRequestController::class, 'areaControllerReject'])->name('manning-level.reject');
        Route::get('/roster', [DutyRosterController::class, 'areaControllerIndex'])->name('roster');
        Route::get('/roster/{id}', [DutyRosterController::class, 'areaControllerShow'])->name('roster.show');
        Route::post('/roster/{id}/approve', [DutyRosterController::class, 'areaControllerApprove'])->name('roster.approve');
        Route::post('/roster/{id}/reject', [DutyRosterController::class, 'areaControllerReject'])->name('roster.reject');
    });

    // Zone Coordinator Routes
    Route::prefix('zone-coordinator')->name('zone-coordinator.')->middleware('role:Zone Coordinator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'zoneCoordinator'])->name('dashboard');
        Route::get('/officers', [OfficerController::class, 'zoneOfficers'])->name('officers');
        // Staff Orders
        Route::get('/staff-orders', [StaffOrderController::class, 'index'])->name('staff-orders');
        Route::get('/staff-orders/create', [StaffOrderController::class, 'create'])->name('staff-orders.create');
        Route::post('/staff-orders', [StaffOrderController::class, 'store'])->name('staff-orders.store');
        Route::get('/staff-orders/{id}', [StaffOrderController::class, 'show'])->name('staff-orders.show');
    });

    // DC Admin Routes
    Route::prefix('dc-admin')->name('dc-admin.')->middleware('role:DC Admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dcAdmin'])->name('dashboard');
        Route::get('/leave-pass', [DashboardController::class, 'dcAdminLeavePass'])->name('leave-pass');
        Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show'])->name('leave-applications.show');
        Route::post('/leave-applications/{id}/approve', [LeaveApplicationController::class, 'approve'])->name('leave-applications.approve');
        Route::post('/leave-applications/{id}/reject', [LeaveApplicationController::class, 'reject'])->name('leave-applications.reject');
        Route::get('/pass-applications/{id}', [PassApplicationController::class, 'show'])->name('pass-applications.show');
        Route::post('/pass-applications/{id}/approve', [PassApplicationController::class, 'approve'])->name('pass-applications.approve');
        Route::post('/pass-applications/{id}/reject', [PassApplicationController::class, 'reject'])->name('pass-applications.reject');
    });

    // Accounts Routes
    Route::prefix('accounts')->name('accounts.')->middleware('role:Accounts')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'accounts'])->name('dashboard');
        Route::get('/validated-officers', [EmolumentController::class, 'validated'])->name('validated-officers');
        Route::get('/processed-history', [EmolumentController::class, 'processedHistory'])->name('processed-history');
        Route::get('/processed-history/export', [EmolumentController::class, 'exportProcessedReport'])->name('processed-history.export');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::post('/emoluments/{id}/process', [EmolumentController::class, 'processPayment'])->name('emoluments.process');
        Route::post('/emoluments/bulk-process', [EmolumentController::class, 'bulkProcess'])->name('emoluments.bulk-process');
        Route::get('/deceased-officers', [DeceasedOfficerController::class, 'index'])->name('deceased-officers');
        Route::get('/deceased-officers/{id}', [DeceasedOfficerController::class, 'show'])->name('deceased-officers.show');
    });

    // Board Routes
    Route::prefix('board')->name('board.')->middleware('role:Board')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'board'])->name('dashboard');
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions');
        Route::get('/promotions/{id}', [PromotionController::class, 'show'])->name('promotions.show');
        Route::get('/promotions/{id}/approve', [PromotionController::class, 'approve'])->name('promotions.approve');
    });

    // Building Routes
    Route::prefix('building')->name('building.')->middleware('role:Building Unit')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'building'])->name('dashboard');
        Route::get('/quarters', [QuarterController::class, 'index'])->name('quarters');
        Route::get('/quarters/create', [QuarterController::class, 'create'])->name('quarters.create');
        Route::get('/quarters/allocate', [QuarterController::class, 'allocate'])->name('quarters.allocate');
    });

    // Establishment Routes
    Route::prefix('establishment')->name('establishment.')->middleware('role:Establishment')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'establishment'])->name('dashboard');
        Route::get('/service-numbers', [DashboardController::class, 'serviceNumbers'])->name('service-numbers');
        Route::get('/new-recruits', [DashboardController::class, 'newRecruits'])->name('new-recruits');
    });

    // Welfare Routes
    Route::prefix('welfare')->name('welfare.')->middleware('role:Welfare')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'welfare'])->name('dashboard');
        Route::get('/deceased-officers', [DeceasedOfficerController::class, 'index'])->name('deceased-officers');
        Route::get('/deceased-officers/create', [DeceasedOfficerController::class, 'create'])->name('deceased-officers.create');
        Route::get('/deceased-officers/{id}', [DeceasedOfficerController::class, 'show'])->name('deceased-officers.show');
    });

    // Form Routes (Public within auth)
    Route::get('/emolument/raise', [EmolumentController::class, 'create'])->name('emolument.raise');
    Route::post('/emolument/raise', [EmolumentController::class, 'store'])->name('emolument.store');
    Route::post('/emolument/{id}/assess', [EmolumentController::class, 'processAssessment'])->name('emolument.process-assessment');
    Route::post('/emolument/{id}/validate', [EmolumentController::class, 'processValidation'])->name('emolument.process-validation');
    Route::post('/emolument/{id}/process-payment', [EmolumentController::class, 'processPayment'])->name('emolument.process-payment');

    Route::get('/leave/apply', [LeaveApplicationController::class, 'create'])->name('leave.apply');
    Route::post('/leave/apply', [LeaveApplicationController::class, 'store'])->name('leave.store');
    Route::get('/pass/apply', [PassApplicationController::class, 'create'])->name('pass.apply');
    Route::post('/pass/apply', [PassApplicationController::class, 'store'])->name('pass.store');
});

// Onboarding Routes (token-based authentication, no auth middleware required)
Route::prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/step1', [DashboardController::class, 'onboardingStep1'])->name('step1');
    Route::post('/step1', [DashboardController::class, 'saveOnboardingStep1'])->name('step1.save');
    Route::get('/step2', [DashboardController::class, 'onboardingStep2'])->name('step2');
    Route::post('/step2', [DashboardController::class, 'saveOnboardingStep2'])->name('step2.save');
    Route::get('/step3', [DashboardController::class, 'onboardingStep3'])->name('step3');
    Route::post('/step3', [DashboardController::class, 'saveOnboardingStep3'])->name('step3.save');
    Route::get('/step4', [DashboardController::class, 'onboardingStep4'])->name('step4');
    Route::post('/step4', [DashboardController::class, 'saveOnboardingStep4'])->name('step4.save');
    Route::post('/submit', [DashboardController::class, 'submitOnboarding'])->name('submit');
});
