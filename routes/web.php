<?php

use App\Http\Controllers\AccountChangeRequestController;
use App\Http\Controllers\NextOfKinChangeRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
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
use App\Http\Controllers\CourseManagementController;
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
use App\Http\Controllers\TRADOCController;
use App\Http\Controllers\ICTController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\RecruitOnboardingController;
use App\Http\Controllers\CGCPreretirementLeaveController;
use App\Http\Controllers\APERTimelineController;
use App\Http\Controllers\APERFormController;
use App\Http\Controllers\AdminRoleAssignmentController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\OfficerQueryController;
use App\Http\Controllers\InvestigationController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\InternalStaffOrderController;
use App\Http\Controllers\OfficerDeletionController;
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

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Public Recruit Onboarding Routes (Token-based, no auth required)
Route::prefix('recruit/onboarding')->name('recruit.onboarding.')->group(function () {
    Route::get('/step1', [RecruitOnboardingController::class, 'step1'])->name('step1');
    Route::post('/step1', [RecruitOnboardingController::class, 'saveStep1'])->name('step1.save');
    Route::get('/step2', [RecruitOnboardingController::class, 'step2'])->name('step2');
    Route::post('/step2', [RecruitOnboardingController::class, 'saveStep2'])->name('step2.save');
    Route::get('/step3', [RecruitOnboardingController::class, 'step3'])->name('step3');
    Route::post('/step3', [RecruitOnboardingController::class, 'saveStep3'])->name('step3.save');
    Route::get('/step4', [RecruitOnboardingController::class, 'step4'])->name('step4');
    Route::post('/step4', [RecruitOnboardingController::class, 'saveStep4'])->name('step4.save');
    Route::get('/preview', [RecruitOnboardingController::class, 'preview'])->name('preview');
    Route::get('/document-preview', [RecruitOnboardingController::class, 'documentPreview'])->name('document-preview');
    Route::post('/final-submit', [RecruitOnboardingController::class, 'finalSubmit'])->name('final-submit');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard Routes (with onboarding check)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('onboarding.complete');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('onboarding.complete');

    // Officer Routes
    Route::prefix('officer')->name('officer.')->middleware('onboarding.complete')->group(function () {
        Route::get('/dashboard', [OfficerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [OfficerController::class, 'profile'])->name('profile');
        Route::post('/profile/update-picture', [OfficerController::class, 'updateProfilePicture'])->name('profile.update-picture');
        Route::get('/settings', [OfficerController::class, 'settings'])->name('settings');
        Route::post('/settings/change-password', [OfficerController::class, 'changePassword'])->name('settings.change-password');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/account-change', [AccountChangeRequestController::class, 'index'])->name('account-change.index');
        Route::get('/account-change/create', [AccountChangeRequestController::class, 'create'])->name('account-change.create');
        Route::post('/account-change', [AccountChangeRequestController::class, 'store'])->name('account-change.store');
        Route::get('/account-change/{id}', [AccountChangeRequestController::class, 'show'])->name('account-change.show');
        Route::get('/leave-applications', [LeaveApplicationController::class, 'index'])->name('leave-applications');
        Route::get('/leave-applications/{id}', [LeaveApplicationController::class, 'show'])->name('leave-applications.show');
        Route::get('/pass-applications', [PassApplicationController::class, 'index'])->name('pass-applications');
        Route::get('/pass-applications/{id}', [PassApplicationController::class, 'show'])->name('pass-applications.show');
        Route::get('/application-history', [OfficerController::class, 'applicationHistory'])->name('application-history');
        Route::get('/course-nominations', [OfficerController::class, 'courseNominations'])->name('course-nominations');
        Route::get('/next-of-kin', [NextOfKinChangeRequestController::class, 'index'])->name('next-of-kin.index');
        Route::get('/next-of-kin/create', [NextOfKinChangeRequestController::class, 'create'])->name('next-of-kin.create');
        Route::post('/next-of-kin', [NextOfKinChangeRequestController::class, 'store'])->name('next-of-kin.store');
        Route::get('/next-of-kin/{id}/edit', [NextOfKinChangeRequestController::class, 'edit'])->name('next-of-kin.edit');
        Route::put('/next-of-kin/{id}', [NextOfKinChangeRequestController::class, 'update'])->name('next-of-kin.update');
        Route::delete('/next-of-kin/{id}', [NextOfKinChangeRequestController::class, 'destroy'])->name('next-of-kin.destroy');
        Route::get('/retirement', [RetirementController::class, 'myRetirement'])->name('retirement');

        // Query Routes
        Route::get('/queries', [OfficerQueryController::class, 'index'])->name('queries.index');
        Route::get('/queries/{id}', [OfficerQueryController::class, 'show'])->name('queries.show');
        Route::post('/queries/{id}/respond', [OfficerQueryController::class, 'respond'])->name('queries.respond');

        Route::get('/quarter-requests', [QuarterController::class, 'myRequests'])->name('quarter-requests');
        Route::get('/quarter-requests/create', [QuarterController::class, 'createRequest'])->name('quarter-requests.create');

        // Quarter Allocation Accept/Reject
        Route::post('/quarters/allocations/{id}/accept', [QuarterController::class, 'acceptAllocation'])->name('quarters.allocations.accept');
        Route::post('/quarters/allocations/{id}/reject', [QuarterController::class, 'rejectAllocation'])->name('quarters.allocations.reject');

        // APER Forms
        Route::get('/aper-forms', [APERFormController::class, 'index'])->name('aper-forms');
        Route::get('/aper-forms/create', [APERFormController::class, 'create'])->name('aper-forms.create');
        Route::post('/aper-forms', [APERFormController::class, 'store'])->name('aper-forms.store');

        // APER Forms - OIC/2IC: Search for officers to create APER forms (must come before {id} routes)
        Route::get('/aper-forms/search-officers', [APERFormController::class, 'searchOfficers'])->name('aper-forms.search-officers');
        Route::get('/aper-forms/access/{officerId}', [APERFormController::class, 'accessForm'])->name('aper-forms.access');

        Route::get('/aper-forms/{id}/edit', [APERFormController::class, 'edit'])->name('aper-forms.edit');
        Route::put('/aper-forms/{id}', [APERFormController::class, 'update'])->name('aper-forms.update');
        Route::get('/aper-forms/{id}', [APERFormController::class, 'show'])->name('aper-forms.show');
        Route::get('/aper-forms/{id}/export', [APERFormController::class, 'exportPDF'])->name('aper-forms.export');
        Route::post('/aper-forms/{id}/submit', [APERFormController::class, 'submit'])->name('aper-forms.submit');
        Route::post('/aper-forms/{id}/update-comments', [APERFormController::class, 'updateComments'])->name('aper-forms.update-comments');
        Route::post('/aper-forms/{id}/accept', [APERFormController::class, 'accept'])->name('aper-forms.accept');
        Route::post('/aper-forms/{id}/reject', [APERFormController::class, 'reject'])->name('aper-forms.reject');
        Route::post('/aper-forms/{id}/reporting-officer', [APERFormController::class, 'updateReportingOfficer'])->name('aper-forms.update-reporting-officer');
        Route::post('/aper-forms/{id}/complete-reporting-officer', [APERFormController::class, 'completeReportingOfficer'])->name('aper-forms.complete-reporting-officer');

        // APER Forms - Countersigning Officer Search (Pool)
        Route::get('/aper-forms/countersigning/search', [APERFormController::class, 'searchCountersigningForms'])->name('aper-forms.countersigning.search');

        // APER Forms - Countersigning Officer
        Route::get('/aper-forms/countersigning/{id}', [APERFormController::class, 'accessCountersigningForm'])->name('aper-forms.countersigning');
        Route::post('/aper-forms/{id}/countersigning-officer', [APERFormController::class, 'updateCountersigningOfficer'])->name('aper-forms.update-countersigning-officer');
        Route::post('/aper-forms/{id}/complete-countersigning-officer', [APERFormController::class, 'completeCountersigningOfficer'])->name('aper-forms.complete-countersigning-officer');
    });

    // HRD Routes
    Route::prefix('hrd')->name('hrd.')->middleware('role:HRD')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hrd'])->name('dashboard');
        
        // Officer Deletion Routes (must come before /officers/{id} to avoid route conflict)
        Route::prefix('officers/delete')->name('officers.delete.')->group(function () {
            Route::get('/', [OfficerDeletionController::class, 'index'])->name('index');
            Route::get('/{id}', [OfficerDeletionController::class, 'show'])->name('show');
            Route::delete('/{id}', [OfficerDeletionController::class, 'destroy'])->name('destroy');
        });
        
        Route::get('/officers', [OfficerController::class, 'index'])->name('officers');
        Route::get('/officers/search', [OfficerController::class, 'search'])->name('officers.search');
        Route::get('/officers/{id}', [OfficerController::class, 'show'])->name('officers.show');
        Route::get('/officers/{id}/edit', [OfficerController::class, 'edit'])->name('officers.edit');
        Route::put('/officers/{id}', [OfficerController::class, 'update'])->name('officers.update');

        // Query Management Routes
        Route::prefix('queries')->name('queries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\HrdQueryController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\HrdQueryController::class, 'show'])->name('show');
        });

        Route::get('/emolument-timeline', [EmolumentTimelineController::class, 'index'])->name('emolument-timeline');
        Route::get('/emolument-timeline/create', [EmolumentTimelineController::class, 'create'])->name('emolument-timeline.create');
        Route::post('/emolument-timeline', [EmolumentTimelineController::class, 'store'])->name('emolument-timeline.store');
        Route::get('/emolument-timeline/{id}/extend', [EmolumentTimelineController::class, 'extend'])->name('emolument-timeline.extend');
        Route::post('/emolument-timeline/{id}/extend', [EmolumentTimelineController::class, 'extendStore'])->name('emolument-timeline.extend.store');

        // APER Timeline Management
        Route::get('/aper-timeline', [APERTimelineController::class, 'index'])->name('aper-timeline');
        Route::get('/aper-timeline/create', [APERTimelineController::class, 'create'])->name('aper-timeline.create');
        Route::post('/aper-timeline', [APERTimelineController::class, 'store'])->name('aper-timeline.store');
        Route::get('/aper-timeline/{id}/extend', [APERTimelineController::class, 'extend'])->name('aper-timeline.extend');
        Route::post('/aper-timeline/{id}/extend', [APERTimelineController::class, 'extendStore'])->name('aper-timeline.extend.store');

        // APER Forms Management
        Route::get('/aper-forms', [APERFormController::class, 'hrdIndex'])->name('aper-forms');
        Route::get('/aper-forms/kpi/print', [APERFormController::class, 'kpiReport'])->name('aper-forms.kpi.print');
        Route::get('/aper-forms/{id}', [APERFormController::class, 'show'])->name('aper-forms.show');
        Route::get('/aper-forms/{id}/grade', [APERFormController::class, 'hrdGrade'])->name('aper-forms.grade');
        Route::post('/aper-forms/{id}/grade', [APERFormController::class, 'hrdGradeSubmit'])->name('aper-forms.grade.submit');
        Route::get('/aper-forms/{id}/export', [APERFormController::class, 'exportPDF'])->name('aper-forms.export');
        Route::post('/aper-forms/{id}/reassign-reporting-officer', [APERFormController::class, 'reassignReportingOfficer'])->name('aper-forms.reassign-reporting-officer');
        Route::post('/aper-forms/{id}/reassign-countersigning-officer', [APERFormController::class, 'reassignCountersigningOfficer'])->name('aper-forms.reassign-countersigning-officer');

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
        Route::get('/movement-orders/{id}/edit', [MovementOrderController::class, 'edit'])->name('movement-orders.edit');
        Route::put('/movement-orders/{id}', [MovementOrderController::class, 'update'])->name('movement-orders.update');
        Route::get('/movement-orders/{id}/eligible-officers', [MovementOrderController::class, 'eligibleOfficers'])->name('movement-orders.eligible-officers');
        Route::post('/movement-orders/{id}/post-officers', [MovementOrderController::class, 'postOfficers'])->name('movement-orders.post-officers');
        Route::post('/movement-orders/{id}/publish', [MovementOrderController::class, 'publish'])->name('movement-orders.publish');

        Route::get('/promotion-eligibility', [PromotionController::class, 'index'])->name('promotion-eligibility');
        Route::get('/promotion-eligibility/create', [PromotionController::class, 'createEligibilityList'])->name('promotion-eligibility.create');
        Route::post('/promotion-eligibility', [PromotionController::class, 'storeEligibilityList'])->name('promotion-eligibility.store');
        Route::get('/promotion-eligibility/{id}', [PromotionController::class, 'showEligibilityList'])->name('promotion-eligibility.show');
        Route::get('/promotion-eligibility/{id}/export', [PromotionController::class, 'exportEligibilityList'])->name('promotion-eligibility.export');
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
        Route::get('/manning-requests/print-selected', [ManningRequestController::class, 'hrdPrintSelected'])->name('manning-requests.print-selected');
        Route::get('/manning-requests/{id}', [ManningRequestController::class, 'hrdShow'])->name('manning-requests.show');
        Route::get('/manning-requests/{id}/print', [ManningRequestController::class, 'hrdPrint'])->name('manning-requests.print');
        Route::get('/manning-requests/{id}/match', [ManningRequestController::class, 'hrdMatch'])->name('manning-requests.match');
        Route::post('/manning-requests/{id}/match-all', [ManningRequestController::class, 'hrdMatchAll'])->name('manning-requests.match-all');

        // Command Duration Routes
        Route::prefix('command-duration')->name('command-duration.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CommandDurationController::class, 'index'])->name('index');
            Route::match(['get', 'post'], '/search', [\App\Http\Controllers\CommandDurationController::class, 'search'])->name('search');
            Route::post('/add-to-draft', [\App\Http\Controllers\CommandDurationController::class, 'addToDraft'])->name('add-to-draft');
            Route::get('/print', [\App\Http\Controllers\CommandDurationController::class, 'print'])->name('print');
        });
        Route::get('/manning-requests/{id}/draft', [ManningRequestController::class, 'hrdViewDraft'])->name('manning-requests.draft');
        Route::post('/manning-requests/{id}/generate-order', [ManningRequestController::class, 'hrdGenerateOrder'])->name('manning-requests.generate-order');
        Route::post('/manning-requests/{id}/add-to-draft', [ManningRequestController::class, 'hrdAddToDraft'])->name('manning-requests.add-to-draft');
        
        // Draft Deployment Management
        Route::get('/manning-deployments/draft', [ManningRequestController::class, 'hrdDraftIndex'])->name('manning-deployments.draft');
        Route::delete('/manning-deployments/{deploymentId}/remove-officer/{assignmentId}', [ManningRequestController::class, 'hrdDraftRemoveOfficer'])->name('manning-deployments.draft.remove-officer');
        Route::post('/manning-deployments/{deploymentId}/swap-officer/{assignmentId}', [ManningRequestController::class, 'hrdDraftSwapOfficer'])->name('manning-deployments.draft.swap-officer');
        Route::post('/manning-deployments/{deploymentId}/update-destination/{assignmentId}', [ManningRequestController::class, 'hrdDraftUpdateDestination'])->name('manning-deployments.draft.update-destination');
        Route::post('/manning-deployments/{id}/publish', [ManningRequestController::class, 'hrdDraftPublish'])->name('manning-deployments.publish');
        Route::get('/manning-deployments/{id}/print', [ManningRequestController::class, 'hrdDraftPrint'])->name('manning-deployments.print');
        Route::get('/manning-deployments/published', [ManningRequestController::class, 'hrdPublishedIndex'])->name('manning-deployments.published');
        Route::get('/leave-types/create', [LeaveTypeController::class, 'create'])->name('leave-types.create');
        Route::post('/leave-types', [LeaveTypeController::class, 'store'])->name('leave-types.store');
        Route::get('/leave-types/{id}/edit', [LeaveTypeController::class, 'edit'])->name('leave-types.edit');
        Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update'])->name('leave-types.update');
        Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy'])->name('leave-types.destroy');

        Route::get('/courses', [CourseController::class, 'index'])->name('courses');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/print', [CourseController::class, 'print'])->name('courses.print');
        Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
        Route::post('/courses/{id}/complete', [CourseController::class, 'markComplete'])->name('courses.complete');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

        // Course Management (CRUD for Course master data)
        Route::resource('course-management', CourseManagementController::class)->names([
            'index' => 'course-management.index',
            'create' => 'course-management.create',
            'store' => 'course-management.store',
            'show' => 'course-management.show',
            'edit' => 'course-management.edit',
            'update' => 'course-management.update',
            'destroy' => 'course-management.destroy',
        ]);

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

    // CGC Routes
    Route::prefix('cgc')->name('cgc.')->middleware('role:CGC')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'cgc'])->name('dashboard');

        // Preretirement Leave Management
        Route::get('/preretirement-leave', [CGCPreretirementLeaveController::class, 'index'])->name('preretirement-leave.index');
        Route::get('/preretirement-leave/approaching', [CGCPreretirementLeaveController::class, 'approaching'])->name('preretirement-leave.approaching');
        Route::get('/preretirement-leave/{id}', [CGCPreretirementLeaveController::class, 'show'])->name('preretirement-leave.show');
        Route::post('/preretirement-leave/{id}/approve-in-office', [CGCPreretirementLeaveController::class, 'approveInOffice'])->name('preretirement-leave.approve-in-office');
        Route::post('/preretirement-leave/{id}/cancel-approval', [CGCPreretirementLeaveController::class, 'cancelApproval'])->name('preretirement-leave.cancel-approval');
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
        Route::get('/deceased-officers/create', [DeceasedOfficerController::class, 'create'])->name('deceased-officers.create');
        Route::post('/deceased-officers', [DeceasedOfficerController::class, 'store'])->name('deceased-officers.store');

        Route::get('/roster', [DutyRosterController::class, 'index'])->name('roster');
        Route::get('/roster/create', [DutyRosterController::class, 'create'])->name('roster.create');
        Route::post('/roster', [DutyRosterController::class, 'store'])->name('roster.store');
        Route::get('/roster/officers-by-command', [DutyRosterController::class, 'getOfficersByCommand'])->name('roster.officers-by-command');
        Route::get('/roster/{id}/edit', [DutyRosterController::class, 'edit'])->name('roster.edit');
        Route::put('/roster/{id}', [DutyRosterController::class, 'update'])->name('roster.update');
        Route::post('/roster/{id}/submit', [DutyRosterController::class, 'submit'])->name('roster.submit');
        Route::get('/roster/{id}', [DutyRosterController::class, 'show'])->name('roster.show');

        // Posting Management (Release Letters & Acceptance)
        Route::prefix('postings')->name('postings.')->group(function () {
            Route::get('/pending-release-letters', [\App\Http\Controllers\StaffOfficer\PostingController::class, 'pendingReleaseLetters'])->name('pending-release-letters');
            Route::get('/{postingId}/print-release-letter', [\App\Http\Controllers\StaffOfficer\PostingController::class, 'printReleaseLetter'])->name('print-release-letter');
            Route::post('/{postingId}/mark-release-letter-printed', [\App\Http\Controllers\StaffOfficer\PostingController::class, 'markReleaseLetterPrinted'])->name('mark-release-letter-printed');
            Route::get('/pending-arrivals', [\App\Http\Controllers\StaffOfficer\PostingController::class, 'pendingArrivals'])->name('pending-arrivals');
            Route::post('/{postingId}/accept', [\App\Http\Controllers\StaffOfficer\PostingController::class, 'acceptOfficer'])->name('accept');
        });
        Route::get('/roster/print/all', [PrintController::class, 'printAllRosters'])->name('roster.print-all');
        Route::get('/roster/{id}/print', [PrintController::class, 'printRoster'])->name('roster.print');

        // Internal Staff Orders Routes
        Route::get('/internal-staff-orders', [InternalStaffOrderController::class, 'index'])->name('internal-staff-orders.index');
        Route::get('/internal-staff-orders/create', [InternalStaffOrderController::class, 'create'])->name('internal-staff-orders.create');
        Route::post('/internal-staff-orders', [InternalStaffOrderController::class, 'store'])->name('internal-staff-orders.store');
        Route::get('/internal-staff-orders/{id}', [InternalStaffOrderController::class, 'show'])->name('internal-staff-orders.show');
        Route::post('/internal-staff-orders/{id}/submit', [InternalStaffOrderController::class, 'submit'])->name('internal-staff-orders.submit');
        Route::get('/internal-staff-orders/{id}/edit', [InternalStaffOrderController::class, 'edit'])->name('internal-staff-orders.edit');
        Route::put('/internal-staff-orders/{id}', [InternalStaffOrderController::class, 'update'])->name('internal-staff-orders.update');
        Route::delete('/internal-staff-orders/{id}', [InternalStaffOrderController::class, 'destroy'])->name('internal-staff-orders.destroy');
        
        // AJAX endpoints for Internal Staff Orders
        Route::post('/internal-staff-orders/get-officer-assignment', [InternalStaffOrderController::class, 'getOfficerAssignment'])->name('internal-staff-orders.get-officer-assignment');
        Route::post('/internal-staff-orders/check-conflicts', [InternalStaffOrderController::class, 'checkConflicts'])->name('internal-staff-orders.check-conflicts');

        Route::get('/officers', [OfficerController::class, 'index'])->name('officers');
        Route::get('/officers/{id}', [OfficerController::class, 'show'])->name('officers.show');
        Route::post('/officers/{id}/document', [OfficerController::class, 'document'])->name('officers.document');
        Route::post('/officers/{id}/release', [OfficerController::class, 'release'])->name('officers.release');

        // APER Forms - Reporting Officer
        Route::get('/aper-forms/search', [APERFormController::class, 'searchOfficers'])->name('aper-forms.search');

        // APER Forms - Reporting Officer (also accessible to Staff Officer)
        Route::get('/aper-forms/reporting-officer/search', [APERFormController::class, 'searchOfficers'])->name('aper-forms.reporting-officer.search');
        Route::get('/aper-forms/access/{officerId}', [APERFormController::class, 'accessForm'])->name('aper-forms.access');
        Route::post('/aper-forms/{id}/reporting-officer', [APERFormController::class, 'updateReportingOfficer'])->name('aper-forms.update-reporting-officer');
        Route::post('/aper-forms/{id}/complete-reporting-officer', [APERFormController::class, 'completeReportingOfficer'])->name('aper-forms.complete-reporting-officer');

        // APER Forms - Countersigning Officer Search (Pool)
        Route::get('/aper-forms/countersigning/search', [APERFormController::class, 'searchCountersigningForms'])->name('aper-forms.countersigning.search');

        // APER Forms - Countersigning Officer
        Route::get('/aper-forms/countersigning/{id}', [APERFormController::class, 'accessCountersigningForm'])->name('aper-forms.countersigning');
        Route::post('/aper-forms/{id}/countersigning-officer', [APERFormController::class, 'updateCountersigningOfficer'])->name('aper-forms.update-countersigning-officer');
        Route::post('/aper-forms/{id}/complete-countersigning-officer', [APERFormController::class, 'completeCountersigningOfficer'])->name('aper-forms.complete-countersigning-officer');

        // APER Forms - Export (Staff Officer)
        Route::get('/aper-forms/{id}/export', [APERFormController::class, 'exportPDF'])->name('aper-forms.export');

        // APER Forms - Staff Officer Review (rejection/reassignment)
        Route::get('/aper-forms/review', [APERFormController::class, 'staffOfficerReviewIndex'])->name('aper-forms.review');
        Route::get('/aper-forms/review/{id}', [APERFormController::class, 'staffOfficerReviewShow'])->name('aper-forms.review.show');
        Route::get('/aper-forms/search-users', [APERFormController::class, 'searchUsersForReassignment'])->name('aper-forms.search-users');
        Route::post('/aper-forms/{id}/staff-officer-reject', [APERFormController::class, 'staffOfficerReject'])->name('aper-forms.staff-officer-reject');
        Route::post('/aper-forms/{id}/reassign-reporting-officer', [APERFormController::class, 'reassignReportingOfficer'])->name('aper-forms.reassign-reporting-officer');
        Route::post('/aper-forms/{id}/reassign-countersigning-officer', [APERFormController::class, 'reassignCountersigningOfficer'])->name('aper-forms.reassign-countersigning-officer');

        // Query Management Routes
        Route::get('/queries', [QueryController::class, 'index'])->name('queries.index');
        Route::get('/queries/create', [QueryController::class, 'create'])->name('queries.create');
        Route::post('/queries', [QueryController::class, 'store'])->name('queries.store');
        Route::get('/queries/{id}', [QueryController::class, 'show'])->name('queries.show');
        Route::post('/queries/{id}/accept', [QueryController::class, 'accept'])->name('queries.accept');
        Route::post('/queries/{id}/reject', [QueryController::class, 'reject'])->name('queries.reject');
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

    // Auditor Routes
    Route::prefix('auditor')->name('auditor.')->middleware('role:Auditor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'auditor'])->name('dashboard');
        Route::get('/emoluments', [EmolumentController::class, 'index'])->name('emoluments');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::get('/emoluments/{id}/audit', [EmolumentController::class, 'audit'])->name('emoluments.audit');
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
        Route::get('/deceased-officers/create', [DeceasedOfficerController::class, 'create'])->name('deceased-officers.create');
        Route::post('/deceased-officers', [DeceasedOfficerController::class, 'store'])->name('deceased-officers.store');
        Route::get('/deceased-officers/create', [DeceasedOfficerController::class, 'create'])->name('deceased-officers.create');
        Route::post('/deceased-officers', [DeceasedOfficerController::class, 'store'])->name('deceased-officers.store');
        Route::post('/manning-level/{id}/reject', [ManningRequestController::class, 'areaControllerReject'])->name('manning-level.reject');
        Route::get('/roster', [DutyRosterController::class, 'areaControllerIndex'])->name('roster');
        Route::get('/roster/{id}', [DutyRosterController::class, 'areaControllerShow'])->name('roster.show');
        Route::post('/roster/{id}/approve', [DutyRosterController::class, 'areaControllerApprove'])->name('roster.approve');
        Route::post('/roster/{id}/reject', [DutyRosterController::class, 'areaControllerReject'])->name('roster.reject');

        // Query Management Routes
        Route::prefix('queries')->name('queries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\AreaControllerQueryController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\AreaControllerQueryController::class, 'show'])->name('show');
        });
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

        // Duty Roster Routes
        Route::get('/roster', [DutyRosterController::class, 'dcAdminIndex'])->name('roster');
        Route::get('/roster/{id}', [DutyRosterController::class, 'dcAdminShow'])->name('roster.show');
        Route::post('/roster/{id}/approve', [DutyRosterController::class, 'dcAdminApprove'])->name('roster.approve');
        Route::post('/roster/{id}/reject', [DutyRosterController::class, 'dcAdminReject'])->name('roster.reject');

        // Internal Staff Order Routes
        Route::get('/internal-staff-orders', [\App\Http\Controllers\DcAdminInternalStaffOrderController::class, 'index'])->name('internal-staff-orders');
        Route::get('/internal-staff-orders/{id}', [\App\Http\Controllers\DcAdminInternalStaffOrderController::class, 'show'])->name('internal-staff-orders.show');
        Route::post('/internal-staff-orders/{id}/approve', [\App\Http\Controllers\DcAdminInternalStaffOrderController::class, 'approve'])->name('internal-staff-orders.approve');
        Route::post('/internal-staff-orders/{id}/reject', [\App\Http\Controllers\DcAdminInternalStaffOrderController::class, 'reject'])->name('internal-staff-orders.reject');

        // Manning Request Routes
        Route::get('/manning-level', [ManningRequestController::class, 'dcAdminIndex'])->name('manning-level');
        Route::get('/manning-level/{id}', [ManningRequestController::class, 'dcAdminShow'])->name('manning-level.show');
        Route::post('/manning-level/{id}/approve', [ManningRequestController::class, 'dcAdminApprove'])->name('manning-level.approve');
        Route::post('/manning-level/{id}/reject', [ManningRequestController::class, 'dcAdminReject'])->name('manning-level.reject');

        // Query Management Routes
        Route::prefix('queries')->name('queries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\DcAdminQueryController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\DcAdminQueryController::class, 'show'])->name('show');
        });
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:Admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/role-assignments', [AdminRoleAssignmentController::class, 'index'])->name('role-assignments');
        Route::get('/role-assignments/create', [AdminRoleAssignmentController::class, 'create'])->name('role-assignments.create');
        Route::post('/role-assignments', [AdminRoleAssignmentController::class, 'store'])->name('role-assignments.store');
        Route::put('/role-assignments/{userId}/{roleId}', [AdminRoleAssignmentController::class, 'update'])->name('role-assignments.update');
        Route::delete('/role-assignments/{userId}/{roleId}', [AdminRoleAssignmentController::class, 'destroy'])->name('role-assignments.destroy');
    });

    // Accounts Routes
    Route::prefix('accounts')->name('accounts.')->middleware('role:Accounts')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'accounts'])->name('dashboard');
        Route::get('/validated-officers', [EmolumentController::class, 'validated'])->name('validated-officers');
        Route::get('/processed-history', [EmolumentController::class, 'processedHistory'])->name('processed-history');
        Route::get('/processed-history/export', [EmolumentController::class, 'exportProcessedReport'])->name('processed-history.export');
        Route::get('/processed-history/print', [EmolumentController::class, 'printProcessedReport'])->name('processed-history.print');
        Route::get('/emoluments/print', [EmolumentController::class, 'printEmolumentsPage'])->name('emoluments.print');
        Route::get('/emoluments/print/view', [EmolumentController::class, 'printAllEmoluments'])->name('emoluments.print.view');
        Route::get('/emoluments/{id}', [EmolumentController::class, 'show'])->name('emoluments.show');
        Route::post('/emoluments/{id}/process', [EmolumentController::class, 'processPayment'])->name('emoluments.process');
        Route::post('/emoluments/bulk-process', [EmolumentController::class, 'bulkProcess'])->name('emoluments.bulk-process');
        Route::get('/deceased-officers', [DeceasedOfficerController::class, 'index'])->name('deceased-officers');
        Route::get('/deceased-officers/{id}', [DeceasedOfficerController::class, 'show'])->name('deceased-officers.show');
        Route::get('/interdicted-officers', [DashboardController::class, 'interdictedOfficers'])->name('interdicted-officers');
        Route::get('/account-change-requests', [AccountChangeRequestController::class, 'pending'])->name('account-change.pending');
        Route::post('/account-change-requests/{id}/approve', [AccountChangeRequestController::class, 'approve'])->name('account-change.approve');
        Route::post('/account-change-requests/{id}/reject', [AccountChangeRequestController::class, 'reject'])->name('account-change.reject');
    });

    // Account Change Request Show - accessible by both Accounts and Officers (controller handles authorization)
    Route::middleware('role:Accounts|Officer')->group(function () {
        Route::get('/accounts/account-change-requests/{id}', [AccountChangeRequestController::class, 'show'])->name('accounts.account-change.show');
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
        Route::get('/officers', [QuarterController::class, 'officers'])->name('officers');
        Route::get('/requests', [QuarterController::class, 'requests'])->name('requests');
        Route::get('/allocations', [QuarterController::class, 'allocations'])->name('allocations');
        Route::get('/pending-allocations', [QuarterController::class, 'pendingAllocations'])->name('pending-allocations');
        Route::get('/rejected-allocations', [QuarterController::class, 'rejectedAllocations'])->name('rejected-allocations');
    });

    // Establishment Routes
    Route::prefix('establishment')->name('establishment.')->middleware('role:Establishment')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'establishment'])->name('dashboard');
        Route::get('/service-numbers', [DashboardController::class, 'serviceNumbers'])->name('service-numbers');
        Route::get('/new-recruits', [DashboardController::class, 'newRecruits'])->name('new-recruits');
        // Multi-step recruit creation
        Route::get('/new-recruits/create', [EstablishmentController::class, 'createRecruitStep1'])->name('new-recruits.create');
        Route::post('/new-recruits/step1', [EstablishmentController::class, 'saveRecruitStep1'])->name('new-recruits.step1');
        Route::get('/new-recruits/step2', [EstablishmentController::class, 'createRecruitStep2'])->name('new-recruits.step2');
        Route::post('/new-recruits/step2', [EstablishmentController::class, 'saveRecruitStep2'])->name('new-recruits.step2.save');
        Route::get('/new-recruits/step3', [EstablishmentController::class, 'createRecruitStep3'])->name('new-recruits.step3');
        Route::post('/new-recruits/step3', [EstablishmentController::class, 'saveRecruitStep3'])->name('new-recruits.step3.save');
        Route::get('/new-recruits/step4', [EstablishmentController::class, 'createRecruitStep4'])->name('new-recruits.step4');
        Route::post('/new-recruits/step4', [EstablishmentController::class, 'saveRecruitStep4'])->name('new-recruits.step4.save');
        Route::get('/new-recruits/preview', [EstablishmentController::class, 'previewRecruit'])->name('new-recruits.preview');
        Route::post('/new-recruits/final-submit', [EstablishmentController::class, 'finalSubmitRecruit'])->name('new-recruits.final-submit');

        // Legacy route for backward compatibility
        Route::post('/new-recruits', [EstablishmentController::class, 'storeRecruit'])->name('new-recruits.store');
        Route::delete('/new-recruits/{id}', [EstablishmentController::class, 'deleteRecruit'])->name('new-recruits.delete');
        Route::delete('/new-recruits/bulk/delete', [EstablishmentController::class, 'bulkDeleteRecruits'])->name('new-recruits.bulk-delete');
        Route::get('/service-numbers/allocate-batch', [EstablishmentController::class, 'allocateBatch'])->name('service-numbers.allocate-batch');
        Route::post('/service-numbers/allocate-batch', [EstablishmentController::class, 'processBatchAllocation'])->name('service-numbers.process-batch');
        Route::get('/training-results', [EstablishmentController::class, 'trainingResults'])->name('training-results');
        Route::post('/assign-service-numbers', [EstablishmentController::class, 'assignServiceNumbers'])->name('assign-service-numbers');
        Route::post('/assign-appointment-numbers', [EstablishmentController::class, 'assignAppointmentNumbers'])->name('assign-appointment-numbers');

        // Onboarding initiation routes
        Route::post('/onboarding/initiate-create', [EstablishmentController::class, 'initiateCreateOnboarding'])->name('onboarding.initiate-create');
        Route::post('/onboarding/initiate', [EstablishmentController::class, 'initiateOnboarding'])->name('onboarding.initiate');
        Route::post('/onboarding/bulk-initiate', [EstablishmentController::class, 'bulkInitiateOnboarding'])->name('onboarding.bulk-initiate');
        Route::post('/onboarding/csv-upload', [EstablishmentController::class, 'csvUploadOnboarding'])->name('onboarding.csv-upload');
        Route::post('/onboarding/{id}/verify', [EstablishmentController::class, 'verifyRecruit'])->name('onboarding.verify');
        Route::post('/onboarding/{id}/resend-link', [EstablishmentController::class, 'resendOnboardingLink'])->name('onboarding.resend-link');
        Route::get('/new-recruits/{id}/view', [EstablishmentController::class, 'viewRecruit'])->name('new-recruits.view');

        // Officer Deletion Routes
        Route::prefix('officers/delete')->name('officers.delete.')->group(function () {
            Route::get('/', [OfficerDeletionController::class, 'index'])->name('index');
            Route::get('/{id}', [OfficerDeletionController::class, 'show'])->name('show');
            Route::delete('/{id}', [OfficerDeletionController::class, 'destroy'])->name('destroy');
        });
    });

    // TRADOC Routes
    Route::prefix('tradoc')->name('tradoc.')->middleware('role:TRADOC')->group(function () {
        Route::get('/dashboard', [TRADOCController::class, 'index'])->name('dashboard');
        Route::get('/download-template', [TRADOCController::class, 'downloadNewRecruitsTemplate'])->name('download-template');
        Route::get('/upload', [TRADOCController::class, 'create'])->name('upload');
        Route::post('/upload', [TRADOCController::class, 'store'])->name('upload.store');
        Route::get('/sorted-results', [TRADOCController::class, 'sortedResults'])->name('sorted-results');
        Route::get('/export-sorted', [TRADOCController::class, 'exportSortedResults'])->name('export-sorted');
        Route::get('/results/{id}', [TRADOCController::class, 'show'])->name('results.show');
        Route::delete('/results/{id}', [TRADOCController::class, 'destroy'])->name('results.destroy');
    });

    // ICT Routes
    Route::prefix('ict')->name('ict.')->middleware('role:ICT')->group(function () {
        Route::get('/dashboard', [ICTController::class, 'index'])->name('dashboard');
        Route::post('/create-emails', [ICTController::class, 'createEmails'])->name('create-emails');
        Route::post('/delete-personal-emails', [ICTController::class, 'deletePersonalEmails'])->name('delete-personal-emails');
        Route::post('/bulk-create-emails', [ICTController::class, 'bulkCreateEmails'])->name('bulk-create-emails');
        Route::get('/non-submitters', [ICTController::class, 'nonSubmitters'])->name('non-submitters');
        Route::get('/non-submitters/print', [ICTController::class, 'printNonSubmitters'])->name('non-submitters.print');
        Route::get('/emoluments/print', [ICTController::class, 'printEmolumentsPage'])->name('emoluments.print');
        Route::get('/emoluments/print/view', [ICTController::class, 'printAllEmoluments'])->name('emoluments.print.view');
    });

    // Welfare Routes
    Route::prefix('welfare')->name('welfare.')->middleware('role:Welfare')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'welfare'])->name('dashboard');
        Route::get('/next-of-kin-requests', [NextOfKinChangeRequestController::class, 'pending'])->name('next-of-kin.pending');
        Route::post('/next-of-kin-requests/{id}/approve', [NextOfKinChangeRequestController::class, 'approve'])->name('next-of-kin.approve');
        Route::post('/next-of-kin-requests/{id}/reject', [NextOfKinChangeRequestController::class, 'reject'])->name('next-of-kin.reject');
        Route::get('/deceased-officers', [DeceasedOfficerController::class, 'index'])->name('deceased-officers');
        Route::get('/deceased-officers/{id}', [DeceasedOfficerController::class, 'show'])->name('deceased-officers.show');
        Route::post('/deceased-officers/{id}/validate', [DeceasedOfficerController::class, 'validate'])->name('deceased-officers.validate');
        Route::get('/deceased-officers/{id}/report', [DeceasedOfficerController::class, 'generateReport'])->name('deceased-officers.report');
        Route::get('/deceased-officers/{id}/export', [DeceasedOfficerController::class, 'export'])->name('deceased-officers.export');
        Route::post('/deceased-officers/{id}/mark-benefits-processed', [DeceasedOfficerController::class, 'markBenefitsProcessed'])->name('deceased-officers.mark-benefits-processed');
    });

    // Next of Kin Change Request Show - accessible by both Welfare and Officers (controller handles authorization)
    Route::middleware('role:Welfare|Officer')->group(function () {
        Route::get('/welfare/next-of-kin-requests/{id}', [NextOfKinChangeRequestController::class, 'show'])->name('welfare.next-of-kin.show');
    });

    // Investigation Unit Routes
    Route::prefix('investigation')->name('investigation.')->middleware('role:Investigation Unit')->group(function () {
        Route::get('/dashboard', [InvestigationController::class, 'index'])->name('dashboard');
        Route::get('/', [InvestigationController::class, 'index'])->name('index');
        Route::get('/search', [InvestigationController::class, 'search'])->name('search');
        Route::get('/create/{officerId}', [InvestigationController::class, 'create'])->name('create');
        Route::post('/', [InvestigationController::class, 'store'])->name('store');
        Route::get('/{id}', [InvestigationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [InvestigationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InvestigationController::class, 'update'])->name('update');
        Route::post('/{id}/resolve', [InvestigationController::class, 'resolve'])->name('resolve');
    });

    // Session ping route to keep session alive
    Route::post('/api/session/ping', function () {
        // Update last activity in session
        session()->put('last_activity', now()->timestamp);
        return response()->json(['success' => true, 'message' => 'Session kept alive']);
    })->name('session.ping');

    // Form Routes (Public within auth)
    Route::middleware('onboarding.complete')->group(function () {
        Route::get('/emolument/raise', [EmolumentController::class, 'create'])->name('emolument.raise');
        Route::post('/emolument/raise', [EmolumentController::class, 'store'])->name('emolument.store');
        Route::post('/emolument/{id}/resubmit', [EmolumentController::class, 'resubmit'])->name('emolument.resubmit');
        Route::get('/leave/apply', [LeaveApplicationController::class, 'create'])->name('leave.apply');
        Route::post('/leave/apply', [LeaveApplicationController::class, 'store'])->name('leave.store');
        Route::get('/pass/apply', [PassApplicationController::class, 'create'])->name('pass.apply');
        Route::post('/pass/apply', [PassApplicationController::class, 'store'])->name('pass.store');
    });

    // These routes don't require onboarding completion (admin/HRD functions)
    Route::post('/emolument/{id}/assess', [EmolumentController::class, 'processAssessment'])->name('emolument.process-assessment');
    Route::post('/emolument/{id}/validate', [EmolumentController::class, 'processValidation'])->name('emolument.process-validation');
    Route::post('/emolument/{id}/audit', [EmolumentController::class, 'processAudit'])->name('emolument.process-audit');
    Route::post('/emolument/{id}/process-payment', [EmolumentController::class, 'processPayment'])->name('emolument.process-payment');
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
    Route::get('/preview', [DashboardController::class, 'onboardingPreview'])->name('preview');
    Route::post('/final-submit', [DashboardController::class, 'finalSubmitOnboarding'])->name('final-submit');
});

// Print Routes - Available to authenticated users
Route::prefix('print')->name('print.')->middleware('auth')->group(function () {
    // Document Prints (from images)
    Route::get('/internal-staff-order/{id}', [PrintController::class, 'internalStaffOrder'])->name('internal-staff-order');
    Route::get('/staff-order/{id}', [PrintController::class, 'staffOrder'])->name('staff-order');
    Route::get('/deployment', [PrintController::class, 'deployment'])->name('deployment');
    Route::get('/leave-document/{id}', [PrintController::class, 'leaveDocument'])->name('leave-document');
    Route::get('/pass-document/{id}', [PrintController::class, 'passDocument'])->name('pass-document');
    Route::get('/retirement-list', [PrintController::class, 'retirementList'])->name('retirement-list');
    Route::get('/retirement-list/{id}/print', [PrintController::class, 'printRetirementList'])->name('retirement-list.print');
    Route::get('/promotion-eligibility-list/{id}', [PrintController::class, 'promotionEligibilityList'])->name('promotion-eligibility.print');
    Route::get('/movement-order/{id}', [PrintController::class, 'movementOrder'])->name('movement-order.print');

    // Report Prints
    Route::get('/accommodation-report', [PrintController::class, 'accommodationReport'])->name('accommodation-report');
    Route::get('/service-number-report', [PrintController::class, 'serviceNumberReport'])->name('service-number-report');
    Route::get('/validated-officers-report', [PrintController::class, 'validatedOfficersReport'])->name('validated-officers-report');
    Route::get('/interdicted-officers-report', [PrintController::class, 'interdictedOfficersReport'])->name('interdicted-officers-report');
});
