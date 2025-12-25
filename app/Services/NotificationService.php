<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\QuarterRequest;
use App\Models\User;
use App\Jobs\SendNotificationEmailJob;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification to a single user
     */
    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        bool $sendEmail = true
    ): Notification {
        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'is_read' => false,
        ]);

        // Send email notification if enabled
        if ($sendEmail && $user->email) {
            try {
                // Try to send synchronously first (works in all environments if mail is configured)
                // This ensures emails are sent immediately without requiring queue worker
                Mail::to($user->email)->send(new NotificationMail($user, $notification));
                Log::info('Notification email sent synchronously', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'notification_id' => $notification->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification email synchronously, attempting to queue', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
                
                // Fallback: Try to queue if synchronous sending fails
                // This helps if there are temporary SMTP issues
                try {
                    SendNotificationEmailJob::dispatch($user, $notification);
                    Log::info('Notification email queued as fallback', [
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                    ]);
                } catch (\Exception $queueException) {
                    Log::error('Failed to queue notification email', [
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                        'error' => $queueException->getMessage(),
                    ]);
                }
            }
        }

        Log::info('Notification created', [
            'user_id' => $user->id,
            'type' => $type,
            'notification_id' => $notification->id,
        ]);

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public function notifyMany(
        array|\Illuminate\Support\Collection $users,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        bool $sendEmail = true
    ): array {
        $notifications = [];

        foreach ($users as $user) {
            if ($user instanceof User) {
                $notifications[] = $this->notify(
                    $user,
                    $type,
                    $title,
                    $message,
                    $entityType,
                    $entityId,
                    $sendEmail
                );
            }
        }

        return $notifications;
    }

    /**
     * Notify users by role
     */
    public function notifyByRole(
        string $roleName,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        bool $sendEmail = true
    ): array {
        $users = User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName)->where('is_active', true);
        })->where('is_active', true)->get();

        return $this->notifyMany($users, $type, $title, $message, $entityType, $entityId, $sendEmail);
    }

    /**
     * Notify specific user about recruit creation
     */
    public function notifyRecruitCreated(User $user, $recruit): Notification
    {
        return $this->notify(
            $user,
            'recruit_created',
            'New Recruit Added',
            "A new recruit {$recruit->initials} {$recruit->surname} has been added to the system.",
            'officer',
            $recruit->id
        );
    }

    /**
     * Notify Establishment about appointment number assignment
     */
    public function notifyAppointmentAssigned(User $user, $recruit): Notification
    {
        return $this->notify(
            $user,
            'appointment_assigned',
            'Appointment Number Assigned',
            "Appointment number {$recruit->appointment_number} has been assigned to {$recruit->initials} {$recruit->surname}.",
            'officer',
            $recruit->id
        );
    }

    /**
     * Notify TRADOC about new recruits ready for training
     */
    public function notifyRecruitsReadyForTraining(array|\Illuminate\Database\Eloquent\Collection $recruits): array
    {
        $count = is_countable($recruits) ? count($recruits) : $recruits->count();
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'TRADOC')->where('is_active', true);
        })->where('is_active', true)->get();
        
        return $this->notifyMany(
            $users,
            'recruits_ready_training',
            'New Recruits Ready for Training',
            "{$count} new recruit(s) with appointment numbers are ready for training results upload.",
            'officer',
            null
        );
    }

    /**
     * Notify Establishment about training results uploaded
     */
    public function notifyTrainingResultsUploaded(User $user, int $count): Notification
    {
        return $this->notify(
            $user,
            'training_results_uploaded',
            'Training Results Uploaded',
            "{$count} training result(s) have been uploaded and are ready for service number assignment.",
            'training_result',
            null
        );
    }

    /**
     * Notify about service number assignment
     */
    public function notifyServiceNumberAssigned(User $user, $officer): Notification
    {
        return $this->notify(
            $user,
            'service_number_assigned',
            'Service Number Assigned',
            "Service number {$officer->service_number} has been assigned to {$officer->initials} {$officer->surname}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify ICT about new service numbers for email creation
     */
    public function notifyServiceNumbersForEmail(array $officers): array
    {
        $count = count($officers);
        return $this->notifyByRole(
            'ICT',
            'service_numbers_ready_email',
            'Service Numbers Ready for Email Creation',
            "{$count} officer(s) have been assigned service numbers and are ready for customs.gov.ng email creation.",
            'officer',
            null
        );
    }

    /**
     * Notify officer about leave application approval
     */
    public function notifyLeaveApplicationApproved($application): ?Notification
    {
        $officer = $application->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $startDate = \Carbon\Carbon::parse($application->start_date)->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($application->end_date)->format('d/m/Y');
        
        return $this->notify(
            $officer->user,
            'leave_application_approved',
            'Leave Application Approved',
            "Your leave application from {$startDate} to {$endDate} ({$application->number_of_days} days) has been approved.",
            'leave_application',
            $application->id
        );
    }

    /**
     * Notify officer about leave application rejection
     */
    public function notifyLeaveApplicationRejected($application, string $rejectionReason): ?Notification
    {
        $officer = $application->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'leave_application_rejected',
            'Leave Application Rejected',
            "Your leave application has been rejected. Reason: {$rejectionReason}",
            'leave_application',
            $application->id
        );
    }

    /**
     * Notify officer about leave application being minuted
     */
    public function notifyLeaveApplicationMinuted($application): ?Notification
    {
        $officer = $application->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $startDate = \Carbon\Carbon::parse($application->start_date)->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($application->end_date)->format('d/m/Y');
        $leaveType = $application->leaveType ? $application->leaveType->name : 'Leave';

        return $this->notify(
            $officer->user,
            'leave_application_minuted',
            'Leave Application Minuted',
            "Your {$leaveType} application from {$startDate} to {$endDate} ({$application->number_of_days} days) has been minuted and forwarded to DC Admin for approval.",
            'leave_application',
            $application->id
        );
    }

    /**
     * Notify DC Admins about minuted leave application ready for approval
     */
    public function notifyLeaveApplicationMinutedToDcAdmin($application): array
    {
        $officer = $application->officer;
        if (!$officer || !$officer->presentStation) {
            return [];
        }

        $command = $officer->presentStation;
        $officerName = "{$officer->initials} {$officer->surname}";
        
        // Get DC Admins for the command
        $dcAdmins = User::whereHas('roles', function($q) {
            $q->where('name', 'DC Admin')
              ->where('user_roles.is_active', true);
        })->whereHas('officer', function($q) use ($command) {
            $q->where('present_station', $command->id);
        })->where('is_active', true)->get();

        if ($dcAdmins->isEmpty()) {
            return [];
        }

        $startDate = \Carbon\Carbon::parse($application->start_date)->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($application->end_date)->format('d/m/Y');
        $leaveType = $application->leaveType ? $application->leaveType->name : 'Leave';

        return $this->notifyMany(
            $dcAdmins,
            'leave_application_minuted',
            'Leave Application Minuted - Requires Approval',
            "Leave application for Officer {$officerName} ({$officer->service_number}) - {$leaveType} from {$startDate} to {$endDate} ({$application->number_of_days} days) has been minuted and requires your approval.",
            'leave_application',
            $application->id
        );
    }

    /**
     * Notify officer about pass application approval
     */
    public function notifyPassApplicationApproved($application): ?Notification
    {
        $officer = $application->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'pass_application_approved',
            'Pass Application Approved',
            "Your pass application has been approved.",
            'pass_application',
            $application->id
        );
    }

    /**
     * Notify officer about pass application rejection
     */
    public function notifyPassApplicationRejected($application, string $rejectionReason): ?Notification
    {
        $officer = $application->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'pass_application_rejected',
            'Pass Application Rejected',
            "Your pass application has been rejected. Reason: {$rejectionReason}",
            'pass_application',
            $application->id
        );
    }

    /**
     * Notify Staff Officer about manning request approval
     */
    public function notifyManningRequestApproved($manningRequest): ?Notification
    {
        $user = $manningRequest->requestedBy;
        if (!$user) {
            return null;
        }

        $command = $manningRequest->command;
        $commandName = $command ? $command->name : 'your command';

        return $this->notify(
            $user,
            'manning_request_approved',
            'Manning Request Approved',
            "Your manning request for {$commandName} has been approved.",
            'manning_request',
            $manningRequest->id
        );
    }

    /**
     * Notify Staff Officer about manning request rejection
     */
    public function notifyManningRequestRejected($manningRequest, string $rejectionReason): ?Notification
    {
        $user = $manningRequest->requestedBy;
        if (!$user) {
            return null;
        }

        return $this->notify(
            $user,
            'manning_request_rejected',
            'Manning Request Rejected',
            "Your manning request has been rejected. Reason: {$rejectionReason}",
            'manning_request',
            $manningRequest->id
        );
    }

    /**
     * Notify Area Controllers about submitted manning request ready for approval
     */
    public function notifyManningRequestSubmitted($manningRequest): array
    {
        $command = $manningRequest->command;
        if (!$command) {
            return [];
        }

        $commandName = $command->name;
        $requestedBy = $manningRequest->requestedBy;
        $requestedByName = $requestedBy ? ($requestedBy->name ?? $requestedBy->email) : 'Staff Officer';
        
        // Get Area Controllers (they can approve any manning request)
        $areaControllers = User::whereHas('roles', function($q) {
            $q->where('name', 'Area Controller')
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($areaControllers->isEmpty()) {
            return [];
        }

        $itemCount = $manningRequest->items->count();
        $totalQuantity = $manningRequest->items->sum('quantity_needed');

        return $this->notifyMany(
            $areaControllers,
            'manning_request_submitted',
            'New Manning Request Submitted',
            "A manning request for {$commandName} has been submitted by {$requestedByName}. It includes {$itemCount} position(s) requiring {$totalQuantity} officer(s) total. Please review and approve.",
            'manning_request',
            $manningRequest->id
        );
    }

    /**
     * Notify HRD team about approved manning request ready for officer matching
     */
    public function notifyManningRequestApprovedToHrd($manningRequest): array
    {
        $command = $manningRequest->command;
        if (!$command) {
            return [];
        }

        $commandName = $command->name;
        
        // Get HRD users
        $hrdUsers = User::whereHas('roles', function($q) {
            $q->where('name', 'HRD')
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($hrdUsers->isEmpty()) {
            return [];
        }

        $itemCount = $manningRequest->items->count();
        $totalQuantity = $manningRequest->items->sum('quantity_needed');

        return $this->notifyMany(
            $hrdUsers,
            'manning_request_approved',
            'Manning Request Approved - Ready for Matching',
            "Manning request for {$commandName} has been approved. It requires {$itemCount} position(s) with {$totalQuantity} officer(s) total. Please proceed with officer matching.",
            'manning_request',
            $manningRequest->id
        );
    }

    /**
     * Notify Staff Officer about manning request fulfillment
     */
    public function notifyManningRequestFulfilled($manningRequest): ?Notification
    {
        $user = $manningRequest->requestedBy;
        if (!$user) {
            return null;
        }

        $command = $manningRequest->command;
        $commandName = $command ? $command->name : 'your command';
        $matchedCount = $manningRequest->items->whereNotNull('matched_officer_id')->count();

        return $this->notify(
            $user,
            'manning_request_fulfilled',
            'Manning Request Fulfilled',
            "Your manning request for {$commandName} has been fulfilled. {$matchedCount} officer(s) have been matched and movement order(s) have been generated.",
            'manning_request',
            $manningRequest->id
        );
    }

    /**
     * Notify about staff order creation
     */
    public function notifyStaffOrderCreated($staffOrder): array
    {
        $notifications = [];
        
        // Notify officer being posted
        $officer = $staffOrder->officer;
        if ($officer && $officer->user) {
            $fromCommand = $staffOrder->fromCommand ? $staffOrder->fromCommand->name : 'Unknown';
            $toCommand = $staffOrder->toCommand ? $staffOrder->toCommand->name : 'Unknown';
            
            $notifications[] = $this->notify(
                $officer->user,
                'staff_order_created',
                'Staff Order Created',
                "A new staff order has been created. You are being posted from {$fromCommand} to {$toCommand}. Order Number: {$staffOrder->order_number}",
                'staff_order',
                $staffOrder->id
            );
        }

        return $notifications;
    }

    /**
     * Notify about role assignment
     */
    public function notifyRoleAssigned(User $user, string $roleName, ?string $commandName = null): Notification
    {
        $message = "You have been assigned the role of {$roleName}";
        if ($commandName) {
            $message .= " for {$commandName}";
        }
        $message .= ".";

        return $this->notify(
            $user,
            'role_assigned',
            'Role Assigned',
            $message,
            'role_assignment',
            null
        );
    }

    /**
     * Notify about onboarding initiation
     */
    public function notifyOnboardingInitiated($officer, string $email): ?Notification
    {
        // Find user by email
        $user = User::where('email', $email)->first();
        if (!$user) {
            // Create a temporary notification entry - user will be created during onboarding
            // For now, we'll skip notification if user doesn't exist yet
            // This will be handled by the email notification
            return null;
        }

        return $this->notify(
            $user,
            'onboarding_initiated',
            'Onboarding Initiated',
            "Your onboarding process has been initiated. Please check your email for the onboarding link.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify Staff Officers of a command about a new pass application
     */
    public function notifyPassApplicationSubmitted($application): array
    {
        $officer = $application->officer;
        if (!$officer || !$officer->presentStation) {
            return [];
        }

        $command = $officer->presentStation;
        
        // Get Staff Officers for the command
        $staffOfficers = User::whereHas('roles', function($q) use ($command) {
            $q->where('name', 'Staff Officer')
              ->where('user_roles.command_id', $command->id)
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($staffOfficers->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $startDate = \Carbon\Carbon::parse($application->start_date)->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($application->end_date)->format('d/m/Y');

        return $this->notifyMany(
            $staffOfficers,
            'pass_application_submitted',
            'New Pass Application',
            "Officer {$officerName} ({$officer->service_number}) has submitted a pass application from {$startDate} to {$endDate} ({$application->number_of_days} days).",
            'pass_application',
            $application->id
        );
    }

    /**
     * Notify Staff Officers of a command about a new leave application
     */
    public function notifyLeaveApplicationSubmitted($application): array
    {
        $officer = $application->officer;
        if (!$officer || !$officer->presentStation) {
            return [];
        }

        $command = $officer->presentStation;
        
        // Get Staff Officers for the command
        $staffOfficers = User::whereHas('roles', function($q) use ($command) {
            $q->where('name', 'Staff Officer')
              ->where('user_roles.command_id', $command->id)
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($staffOfficers->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $startDate = \Carbon\Carbon::parse($application->start_date)->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($application->end_date)->format('d/m/Y');
        $leaveType = $application->leaveType ? $application->leaveType->name : 'Leave';

        return $this->notifyMany(
            $staffOfficers,
            'leave_application_submitted',
            'New Leave Application',
            "Officer {$officerName} ({$officer->service_number}) has submitted a {$leaveType} application from {$startDate} to {$endDate} ({$application->number_of_days} days).",
            'leave_application',
            $application->id
        );
    }

    /**
     * Notify Welfare role users about a new next of kin change request
     */
    public function notifyNextOfKinChangeRequestSubmitted($changeRequest): array
    {
        $officer = $changeRequest->officer;
        if (!$officer) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $actionType = ucfirst($changeRequest->action_type);

        return $this->notifyByRole(
            'Welfare',
            'next_of_kin_change_request_submitted',
            'New Next of Kin Change Request',
            "Officer {$officerName} ({$officer->service_number}) has submitted a {$actionType} request for Next of Kin.",
            'next_of_kin_change_request',
            $changeRequest->id
        );
    }

    /**
     * Notify Assessors of a command about a new emolument raised
     */
    public function notifyEmolumentRaised($emolument): array
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->presentStation) {
            return [];
        }

        $command = $officer->presentStation;
        
        // Get Assessors for the command
        $assessors = User::whereHas('roles', function($q) use ($command) {
            $q->where('name', 'Assessor')
              ->where('user_roles.command_id', $command->id)
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($assessors->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        return $this->notifyMany(
            $assessors,
            'emolument_raised',
            'New Emolument Raised',
            "Officer {$officerName} ({$officer->service_number}) has raised an emolument for year {$emolument->year}.",
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify officer about emolument assessment (approved or rejected)
     */
    public function notifyEmolumentAssessed($emolument, string $status, ?string $comments = null): ?Notification
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        
        if ($status === 'APPROVED') {
            $title = 'Emolument Assessed';
            $message = "Your emolument for year {$emolument->year} has been assessed and approved.";
        } else {
            $title = 'Emolument Assessment Rejected';
            $message = "Your emolument for year {$emolument->year} has been rejected.";
            if ($comments) {
                $message .= " Comments: {$comments}";
            }
        }

        return $this->notify(
            $officer->user,
            'emolument_assessed',
            $title,
            $message,
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify Validators and Area Controllers about assessed emolument ready for validation
     */
    public function notifyEmolumentAssessedReadyForValidation($emolument): array
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->presentStation) {
            return [];
        }

        $command = $officer->presentStation;
        $officerName = "{$officer->initials} {$officer->surname}";
        
        // Get Validators for the command
        $validators = User::whereHas('roles', function($q) use ($command) {
            $q->where('name', 'Validator')
              ->where('user_roles.command_id', $command->id)
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        // Get Area Controllers (they can validate any emolument, no command restriction)
        $areaControllers = User::whereHas('roles', function($q) {
            $q->where('name', 'Area Controller')
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        $allValidators = $validators->merge($areaControllers)->unique('id');

        if ($allValidators->isEmpty()) {
            return [];
        }

        return $this->notifyMany(
            $allValidators,
            'emolument_assessed',
            'Emolument Ready for Validation',
            "Emolument for Officer {$officerName} ({$officer->service_number}) for year {$emolument->year} has been assessed and is ready for validation.",
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify officer about emolument validation (approved or rejected)
     */
    public function notifyEmolumentValidated($emolument, string $status, ?string $comments = null): ?Notification
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        
        if ($status === 'APPROVED') {
            $title = 'Emolument Validated';
            $message = "Your emolument for year {$emolument->year} has been validated and approved. It is now ready for payment processing.";
        } else {
            $title = 'Emolument Validation Rejected';
            $message = "Your emolument for year {$emolument->year} has been rejected during validation.";
            if ($comments) {
                $message .= " Comments: {$comments}";
            }
        }

        return $this->notify(
            $officer->user,
            'emolument_validated',
            $title,
            $message,
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify Accounts team about validated emolument ready for processing
     */
    public function notifyEmolumentValidatedReadyForProcessing($emolument): array
    {
        $officer = $emolument->officer;
        if (!$officer) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        
        // Get Accounts users
        $accountsUsers = User::whereHas('roles', function($q) {
            $q->where('name', 'Accounts')
              ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($accountsUsers->isEmpty()) {
            return [];
        }

        return $this->notifyMany(
            $accountsUsers,
            'emolument_validated',
            'Emolument Ready for Payment Processing',
            "Emolument for Officer {$officerName} ({$officer->service_number}) for year {$emolument->year} has been validated and is ready for payment processing.",
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify officer about emolument payment processed
     */
    public function notifyEmolumentProcessed($emolument): ?Notification
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        return $this->notify(
            $officer->user,
            'emolument_processed',
            'Emolument Payment Processed',
            "Your emolument for year {$emolument->year} has been processed for payment.",
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify officer about quarter allocation
     */
    public function notifyQuarterAllocated($officer, $quarter, $allocationDate): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $date = \Carbon\Carbon::parse($allocationDate)->format('d/m/Y');
        $quarterNumber = $quarter->quarter_number ?? 'N/A';
        $quarterType = $quarter->quarter_type ?? 'N/A';

        return $this->notify(
            $officer->user,
            'quarter_allocated',
            'Quarter Allocated',
            "You have been allocated Quarter {$quarterNumber} ({$quarterType}) effective from {$date}. Your quartered status has been updated.",
            'quarter',
            $quarter->id
        );
    }

    /**
     * Notify officer about quarter deallocation
     */
    public function notifyQuarterDeallocated($officer, $quarter): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $quarterNumber = $quarter->quarter_number ?? 'N/A';

        return $this->notify(
            $officer->user,
            'quarter_deallocated',
            'Quarter Deallocated',
            "Your allocation for Quarter {$quarterNumber} has been deallocated. Your quartered status has been updated.",
            'quarter',
            $quarter->id
        );
    }

    /**
     * Notify officer about quartered status update
     */
    public function notifyQuarteredStatusUpdated($officer, bool $quartered): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $status = $quartered ? 'Yes' : 'No';

        return $this->notify(
            $officer->user,
            'quartered_status_updated',
            'Quartered Status Updated',
            "Your quartered status has been updated to: {$status}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify Building Unit users about new quarter creation
     */
    public function notifyQuarterCreated($quarter, User $createdBy): array
    {
        $command = $quarter->command;
        if (!$command) {
            return [];
        }

        // Get Building Unit users for the command (from role pivot command_id)
        $buildingUnitUsers = User::whereHas('roles', function($q) use ($command) {
            $q->where('name', 'Building Unit')
              ->where('user_roles.is_active', true)
              ->where('user_roles.command_id', $command->id);
        })->where('is_active', true)->where('id', '!=', $createdBy->id)->get();

        if ($buildingUnitUsers->isEmpty()) {
            return [];
        }

        $quarterNumber = $quarter->quarter_number ?? 'N/A';
        $quarterType = $quarter->quarter_type ?? 'N/A';
        $commandName = $command->name ?? 'Unknown';

        return $this->notifyMany(
            $buildingUnitUsers,
            'quarter_created',
            'New Quarter Created',
            "A new quarter {$quarterNumber} ({$quarterType}) has been created for {$commandName}.",
            'quarter',
            $quarter->id
        );
    }

    /**
     * Notify Building Unit users about new quarter request submission
     */
    public function notifyQuarterRequestSubmitted($quarterRequest): array
    {
        $officer = $quarterRequest->officer;
        if (!$officer || !$officer->present_station) {
            return [];
        }

        $commandId = $officer->present_station;

        // Get Building Unit users for the command (from role pivot command_id)
        $buildingUnitUsers = User::whereHas('roles', function($q) use ($commandId) {
            $q->where('name', 'Building Unit')
              ->where('user_roles.is_active', true)
              ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        if ($buildingUnitUsers->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $serviceNumber = $officer->service_number ?? 'N/A';
        $preferredType = $quarterRequest->preferred_quarter_type ?? 'Any';

        return $this->notifyMany(
            $buildingUnitUsers,
            'quarter_request_submitted',
            'New Quarter Request',
            "Officer {$officerName} ({$serviceNumber}) has submitted a quarter request. Preferred type: {$preferredType}.",
            'quarter_request',
            $quarterRequest->id
        );
    }

    /**
     * Notify officer about quarter request approval
     */
    public function notifyQuarterRequestApproved($quarterRequest, $quarter, $allocationDate): ?Notification
    {
        $officer = $quarterRequest->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $date = \Carbon\Carbon::parse($allocationDate)->format('d/m/Y');
        $quarterNumber = $quarter->quarter_number ?? 'N/A';
        $quarterType = $quarter->quarter_type ?? 'N/A';

        return $this->notify(
            $officer->user,
            'quarter_request_approved',
            'Quarter Request Approved',
            "Your quarter request has been approved. You have been allocated Quarter {$quarterNumber} ({$quarterType}) effective from {$date}. Your quartered status has been updated.",
            'quarter_request',
            $quarterRequest->id
        );
    }

    /**
     * Notify officer about quarter request rejection
     */
    public function notifyQuarterRequestRejected($quarterRequest, string $rejectionReason): ?Notification
    {
        $officer = $quarterRequest->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        return $this->notify(
            $officer->user,
            'quarter_request_rejected',
            'Quarter Request Rejected',
            "Your quarter request has been rejected. Reason: {$rejectionReason}",
            'quarter_request',
            $quarterRequest->id
        );
    }

    /**
     * Notify officer about posting/transfer
     */
    public function notifyOfficerPosted($officer, $fromCommand, $toCommand, $postingDate = null): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $fromCommandName = $fromCommand ? $fromCommand->name : 'Previous Command';
        $toCommandName = $toCommand ? $toCommand->name : 'New Command';
        $date = $postingDate ? \Carbon\Carbon::parse($postingDate)->format('d/m/Y') : 'Effective immediately';

        return $this->notify(
            $officer->user,
            'officer_posted',
            'Posting/Transfer Notification',
            "You have been posted/transferred from {$fromCommandName} to {$toCommandName}. Posting date: {$date}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about rank/promotion change
     */
    public function notifyRankChanged($officer, string $oldRank, string $newRank): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'rank_changed',
            'Rank Changed',
            "Your rank has been changed from {$oldRank} to {$newRank}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about interdiction status change
     */
    public function notifyInterdictionStatusChanged($officer, bool $interdicted): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $status = $interdicted ? 'interdicted' : 'removed from interdiction';
        $title = $interdicted ? 'Interdiction Notice' : 'Interdiction Removed';

        return $this->notify(
            $officer->user,
            'interdiction_status_changed',
            $title,
            "You have been {$status}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about suspension status change
     */
    public function notifySuspensionStatusChanged($officer, bool $suspended): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $status = $suspended ? 'suspended' : 'removed from suspension';
        $title = $suspended ? 'Suspension Notice' : 'Suspension Removed';

        return $this->notify(
            $officer->user,
            'suspension_status_changed',
            $title,
            "You have been {$status}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about dismissal
     */
    public function notifyOfficerDismissed($officer): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'officer_dismissed',
            'Dismissal Notice',
            "You have been dismissed from service.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about active/inactive status change
     */
    public function notifyActiveStatusChanged($officer, bool $isActive): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $status = $isActive ? 'activated' : 'deactivated';
        $title = $isActive ? 'Account Activated' : 'Account Deactivated';

        return $this->notify(
            $officer->user,
            'active_status_changed',
            $title,
            "Your account has been {$status}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about command/station change
     */
    public function notifyCommandChanged($officer, $oldCommand, $newCommand): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $oldCommandName = $oldCommand ? $oldCommand->name : 'Previous Command';
        $newCommandName = $newCommand ? $newCommand->name : 'New Command';

        return $this->notify(
            $officer->user,
            'command_changed',
            'Command/Station Changed',
            "Your command/station has been changed from {$oldCommandName} to {$newCommandName}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about date posted to station change
     */
    public function notifyDatePostedChanged($officer, $oldDate, $newDate): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $oldDateFormatted = $oldDate ? \Carbon\Carbon::parse($oldDate)->format('d/m/Y') : 'Not set';
        $newDateFormatted = $newDate ? \Carbon\Carbon::parse($newDate)->format('d/m/Y') : 'Not set';

        return $this->notify(
            $officer->user,
            'date_posted_changed',
            'Date Posted to Station Updated',
            "Your date posted to station has been updated from {$oldDateFormatted} to {$newDateFormatted}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about unit assignment change
     */
    public function notifyUnitChanged($officer, string $oldUnit, string $newUnit): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $oldUnitDisplay = $oldUnit ?: 'Not assigned';
        $newUnitDisplay = $newUnit ?: 'Not assigned';

        return $this->notify(
            $officer->user,
            'unit_changed',
            'Unit Assignment Changed',
            "Your unit assignment has been changed from {$oldUnitDisplay} to {$newUnitDisplay}.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about service number assignment
     */
    public function notifyServiceNumberAssignedToOfficer($officer, string $serviceNumber): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        return $this->notify(
            $officer->user,
            'service_number_assigned',
            'Service Number Assigned',
            "Service number {$serviceNumber} has been assigned to you.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify officer about deceased status (when reported)
     */
    public function notifyOfficerDeceased($officer, $dateOfDeath): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $dateFormatted = \Carbon\Carbon::parse($dateOfDeath)->format('d/m/Y');

        return $this->notify(
            $officer->user,
            'officer_deceased_reported',
            'Deceased Status Reported',
            "You have been reported as deceased with date of death: {$dateFormatted}. This report is pending validation.",
            'officer',
            $officer->id
        );
    }
}
