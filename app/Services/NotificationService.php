<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\QuarterRequest;
use App\Models\User;
use App\Models\Query;
use App\Jobs\SendNotificationEmailJob;
use App\Jobs\SendRoleAssignedMailJob;
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
        // Use queued jobs for all email sending to avoid blocking requests
        // This is especially important for manning requests which may send many emails
        if ($sendEmail && $user->email) {
            try {
                // Queue email job for asynchronous processing
                // This prevents blocking the request and allows better error handling
                SendNotificationEmailJob::dispatch($user, $notification);
                Log::info('Notification email job dispatched', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'notification_id' => $notification->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch notification email job', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
                
                // Fallback: Try synchronous sending if queue fails (for development/testing)
                // In production, queue should always be available
                try {
                    Mail::to($user->email)->send(new NotificationMail($user, $notification));
                    Log::info('Notification email sent synchronously as fallback', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'notification_id' => $notification->id,
                    ]);
                } catch (\Exception $syncException) {
                    Log::error('Failed to send notification email (both queue and sync failed)', [
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                        'queue_error' => $e->getMessage(),
                        'sync_error' => $syncException->getMessage(),
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
     * Notify TRADOC about new recruit created
     */
    public function notifyNewRecruit($recruit): array
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'TRADOC')->where('is_active', true);
        })->where('is_active', true)->get();

        $recruitName = "{$recruit->initials} {$recruit->surname}";

        return $this->notifyMany(
            $users,
            'new_recruit_created',
            'New Recruit Created',
            "A new recruit {$recruitName} has been created. Appointment number will be assigned by Establishment.",
            'officer',
            $recruit->id
        );
    }

    /**
     * Notify Establishment about recruit onboarding completion
     */
    public function notifyRecruitOnboardingCompleted($recruit): array
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'Establishment')->where('is_active', true);
        })->where('is_active', true)->get();

        $recruitName = trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));

        // Send email to each Establishment user via job
        foreach ($users as $user) {
            if ($user->email) {
                \App\Jobs\SendRecruitOnboardingCompletedMailJob::dispatch($recruit, $user, $recruitName);
            }
        }

        // Also create in-app notifications
        return $this->notifyMany(
            $users,
            'recruit_onboarding_completed',
            'Recruit Onboarding Completed',
            "Recruit {$recruitName} has completed onboarding and is ready for verification.",
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
        $dcAdmins = User::whereHas('roles', function ($q) {
            $q->where('name', 'DC Admin')
                ->where('user_roles.is_active', true);
        })->whereHas('officer', function ($q) use ($command) {
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

        $commandId = $command->id;

        // Get Area Controllers (they can approve any manning request)
        $areaControllers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Area Controller')
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        // Get HRD users (they need to be aware of submitted requests)
        $hrdUsers = User::whereHas('roles', function ($q) {
            $q->where('name', 'HRD')
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        // Get DC Admins for the command (they handle command-level approvals)
        $dcAdmins = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'DC Admin')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        // Combine all users to notify
        $usersToNotify = $areaControllers->merge($hrdUsers)->merge($dcAdmins);

        if ($usersToNotify->isEmpty()) {
            return [];
        }

        $itemCount = $manningRequest->items->count();
        $totalQuantity = $manningRequest->items->sum('quantity_needed');

        return $this->notifyMany(
            $usersToNotify,
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
        $hrdUsers = User::whereHas('roles', function ($q) {
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

            // Create in-app notification (without email via notify method)
            $notification = $this->notify(
                $officer->user,
                'staff_order_created',
                'Staff Order Created',
                "A new staff order has been created. You are being posted from {$fromCommand} to {$toCommand}. Order Number: {$staffOrder->order_number}",
                'staff_order',
                $staffOrder->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($officer->user->email) {
                try {
                    \App\Jobs\SendStaffOrderCreatedMailJob::dispatch(
                        $staffOrder,
                        $officer->user,
                        $fromCommand,
                        $toCommand
                    );
                    Log::info('Staff order created email job dispatched', [
                        'user_id' => $officer->user->id,
                        'staff_order_id' => $staffOrder->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch staff order created email job', [
                        'user_id' => $officer->user->id,
                        'staff_order_id' => $staffOrder->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
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

        // Create in-app notification (without email via notify method)
        $notification = $this->notify(
            $user,
            'role_assigned',
            'Role Assigned',
            $message,
            'role_assignment',
            null,
            false // Don't send email via notify method, we'll use job
        );

        // Send email via job
        if ($user->email) {
            try {
                SendRoleAssignedMailJob::dispatch($user, $notification);
                Log::info('Role assignment email job dispatched', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'role' => $roleName,
                    'command' => $commandName,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch role assignment email job', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
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
        $staffOfficers = User::whereHas('roles', function ($q) use ($command) {
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
        $staffOfficers = User::whereHas('roles', function ($q) use ($command) {
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
        $assessors = User::whereHas('roles', function ($q) use ($command) {
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
        $validators = User::whereHas('roles', function ($q) use ($command) {
            $q->where('name', 'Validator')
                ->where('user_roles.command_id', $command->id)
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        // Get Area Controllers (they can validate any emolument, no command restriction)
        $areaControllers = User::whereHas('roles', function ($q) {
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
            $message = "Your emolument for year {$emolument->year} has been validated and approved. It is now ready for audit.";
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
     * Notify Auditors about validated emolument ready for audit
     */
    public function notifyEmolumentValidatedReadyForAudit($emolument): array
    {
        $officer = $emolument->officer;
        if (!$officer) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        // Get Auditors (system-wide role, no command restriction)
        $auditors = User::whereHas('roles', function ($q) {
            $q->where('name', 'Auditor')
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($auditors->isEmpty()) {
            return [];
        }

        return $this->notifyMany(
            $auditors,
            'emolument_validated',
            'Emolument Ready for Audit',
            "Emolument for Officer {$officerName} ({$officer->service_number}) for year {$emolument->year} has been validated and is ready for audit.",
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify officer about emolument audit (approved or rejected)
     */
    public function notifyEmolumentAudited($emolument, string $status, ?string $comments = null): ?Notification
    {
        $officer = $emolument->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        if ($status === 'APPROVED') {
            $title = 'Emolument Audited';
            $message = "Your emolument for year {$emolument->year} has been audited and approved. It is now ready for payment processing.";
        } else {
            $title = 'Emolument Audit Rejected';
            $message = "Your emolument for year {$emolument->year} has been rejected during audit.";
            if ($comments) {
                $message .= " Comments: {$comments}";
            }
        }

        return $this->notify(
            $officer->user,
            'emolument_audited',
            $title,
            $message,
            'emolument',
            $emolument->id
        );
    }

    /**
     * Notify Accounts team about audited emolument ready for processing
     */
    public function notifyEmolumentAuditedReadyForProcessing($emolument): array
    {
        $officer = $emolument->officer;
        if (!$officer) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";

        // Get Accounts users
        $accountsUsers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Accounts')
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($accountsUsers->isEmpty()) {
            return [];
        }

        return $this->notifyMany(
            $accountsUsers,
            'emolument_audited',
            'Emolument Ready for Payment Processing',
            "Emolument for Officer {$officerName} ({$officer->service_number}) for year {$emolument->year} has been audited and is ready for payment processing.",
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
            'Quarter Allocation Pending',
            "You have been allocated Quarter {$quarterNumber} ({$quarterType}) effective from {$date}. Please accept or reject this allocation on your dashboard.",
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
        $buildingUnitUsers = User::whereHas('roles', function ($q) use ($command) {
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
        $buildingUnitUsers = User::whereHas('roles', function ($q) use ($commandId) {
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
            "Your quarter request has been approved. You have been allocated Quarter {$quarterNumber} ({$quarterType}) effective from {$date}. Please accept or reject this allocation on your dashboard.",
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
     * Notify Building Unit about quarter allocation acceptance
     */
    public function notifyQuarterAllocationAccepted($allocation): array
    {
        $officer = $allocation->officer;
        $quarter = $allocation->quarter;

        if (!$officer || !$quarter || !$quarter->command_id) {
            return [];
        }

        $commandId = $quarter->command_id;

        // Get Building Unit users for the command
        $buildingUnitUsers = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'Building Unit')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        if ($buildingUnitUsers->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $serviceNumber = $officer->service_number ?? 'N/A';
        $quarterNumber = $quarter->quarter_number ?? 'N/A';
        $quarterType = $quarter->quarter_type ?? 'N/A';

        return $this->notifyMany(
            $buildingUnitUsers,
            'quarter_allocation_accepted',
            'Quarter Allocation Accepted',
            "Officer {$officerName} ({$serviceNumber}) has accepted the allocation of Quarter {$quarterNumber} ({$quarterType}).",
            'quarter',
            $quarter->id
        );
    }

    /**
     * Notify Building Unit about quarter allocation rejection
     */
    public function notifyQuarterAllocationRejected($allocation, ?string $rejectionReason = null): array
    {
        $officer = $allocation->officer;
        $quarter = $allocation->quarter;

        if (!$officer || !$quarter || !$quarter->command_id) {
            return [];
        }

        $commandId = $quarter->command_id;

        // Get Building Unit users for the command
        $buildingUnitUsers = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'Building Unit')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        if ($buildingUnitUsers->isEmpty()) {
            return [];
        }

        $officerName = "{$officer->initials} {$officer->surname}";
        $serviceNumber = $officer->service_number ?? 'N/A';
        $quarterNumber = $quarter->quarter_number ?? 'N/A';
        $quarterType = $quarter->quarter_type ?? 'N/A';
        $reason = $rejectionReason ? " Reason: {$rejectionReason}" : '';

        return $this->notifyMany(
            $buildingUnitUsers,
            'quarter_allocation_rejected',
            'Quarter Allocation Rejected',
            "Officer {$officerName} ({$serviceNumber}) has rejected the allocation of Quarter {$quarterNumber} ({$quarterType}).{$reason}",
            'quarter',
            $quarter->id
        );
    }

    /**
     * Notify officer about posting/transfer
     */
    /**
     * Notify command about officer release (Release Letter)
     * This is sent BEFORE documentation - the command is notified that officer is being released
     * Authorized by Area Comptroller or DC Admin through Staff Officer
     */
    public function notifyCommandOfficerRelease($officer, $fromCommand, $toCommand, $movementOrder): array
    {
        $notifications = [];
        
        if (!$fromCommand) {
            return $notifications;
        }

        // Get Staff Officers, Area Controllers, and DC Admins for the FROM command
        // These are the authorized personnel who receive release letters
        $authorizedUsers = User::whereHas('roles', function($q) use ($fromCommand) {
                $q->whereIn('name', ['Staff Officer', 'Area Controller', 'DC Admin'])
                  ->where('role_user.command_id', $fromCommand->id)
                  ->where('role_user.is_active', true);
            })
            ->get();

        $officerName = ($officer->initials ?? '') . ' ' . ($officer->surname ?? '');
        $officerServiceNumber = $officer->service_number ?? 'N/A';
        $officerRank = $officer->substantive_rank ?? 'N/A';
        $toCommandName = $toCommand ? $toCommand->name : 'New Command';
        $orderNumber = $movementOrder ? $movementOrder->order_number : 'N/A';
        $orderDate = $movementOrder && $movementOrder->created_at 
            ? $movementOrder->created_at->format('d M Y') 
            : now()->format('d M Y');

        foreach ($authorizedUsers as $user) {
            $message = "RELEASE LETTER: Officer {$officerServiceNumber} {$officerRank} {$officerName} has been officially released from {$fromCommand->name} for posting to {$toCommandName}. Movement Order: {$orderNumber} dated {$orderDate}. The officer is to report accordingly.";
            
            $notification = $this->notify(
                $user,
                'officer_release',
                'Officer Release Letter',
                $message,
                'officer',
                $officer->id,
                true
            );
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

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
     * Notify officer about transfer (after release letter is printed)
     * This is sent when the old command prints the release letter
     */
    public function notifyOfficerTransfer($officer, $fromCommand, $toCommand, $order = null): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $fromCommandName = $fromCommand ? $fromCommand->name : 'Current Command';
        $toCommandName = $toCommand ? $toCommand->name : 'New Command';
        $orderNumber = $order ? $order->order_number : 'N/A';
        $orderType = $order instanceof \App\Models\MovementOrder ? 'Movement Order' : 'Staff Order';

        return $this->notify(
            $officer->user,
            'officer_transfer',
            'Transfer Notification',
            "You have been released from {$fromCommandName} and are to report to {$toCommandName}. {$orderType}: {$orderNumber}. Please report to your new command for acceptance.",
            'officer',
            $officer->id
        );
    }

    /**
     * Notify Staff Officers of TO command about pending officer arrival
     * This is sent when a movement order is published and officers are posted to their command
     */
    public function notifyStaffOfficerPendingArrival($officer, $fromCommand, $toCommand, $movementOrder): array
    {
        $notifications = [];
        
        if (!$toCommand) {
            return $notifications;
        }

        // Get Staff Officers, Area Controllers, and DC Admins for the TO command
        // These are the authorized personnel who need to accept incoming officers
        $authorizedUsers = User::whereHas('roles', function($q) use ($toCommand) {
                $q->whereIn('name', ['Staff Officer', 'Area Controller', 'DC Admin'])
                  ->where('role_user.command_id', $toCommand->id)
                  ->where('role_user.is_active', true);
            })
            ->get();

        $officerName = ($officer->initials ?? '') . ' ' . ($officer->surname ?? '');
        $officerServiceNumber = $officer->service_number ?? 'N/A';
        $officerRank = $officer->substantive_rank ?? 'N/A';
        $fromCommandName = $fromCommand ? $fromCommand->name : 'Previous Command';
        $orderNumber = $movementOrder ? $movementOrder->order_number : 'N/A';
        $orderDate = $movementOrder && $movementOrder->created_at 
            ? $movementOrder->created_at->format('d M Y') 
            : now()->format('d M Y');

        foreach ($authorizedUsers as $user) {
            $message = "PENDING ARRIVAL: Officer {$officerServiceNumber} {$officerRank} {$officerName} from {$fromCommandName} is being posted to {$toCommand->name}. Movement Order: {$orderNumber} dated {$orderDate}. Please wait for the release letter to be printed by the previous command before accepting the officer.";
            
            $notification = $this->notify(
                $user,
                'officer_pending_arrival',
                'Pending Officer Arrival',
                $message,
                'officer',
                $officer->id,
                true
            );
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Notify officer about acceptance into new command
     * This is sent when the new command accepts the officer
     */
    public function notifyOfficerAccepted($officer, $fromCommand, $toCommand, $posting): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $fromCommandName = $fromCommand ? $fromCommand->name : 'Previous Command';
        $toCommandName = $toCommand ? $toCommand->name : 'New Command';
        $postingDate = $posting->posting_date ? $posting->posting_date->format('d/m/Y') : now()->format('d/m/Y');

        return $this->notify(
            $officer->user,
            'officer_accepted',
            'Transfer Complete',
            "You have been accepted into {$toCommandName}. Your transfer from {$fromCommandName} is now complete. Posting date: {$postingDate}. You are now officially posted to {$toCommandName}.",
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

        // Create in-app notification
        $notification = $this->notify(
            $officer->user,
            'officer_deceased_reported',
            'Deceased Status Reported',
            "You have been reported as deceased with date of death: {$dateFormatted}. This report is pending validation.",
            'officer',
            $officer->id,
            false // Don't send email via notify method, we'll send via job
        );

        // Send email via job
        if ($officer->user->email) {
            try {
                \App\Jobs\SendDeceasedOfficerNotificationJob::dispatch($officer, $notification);
                Log::info('Deceased officer notification email job dispatched', [
                    'user_id' => $officer->user->id,
                    'officer_id' => $officer->id,
                    'notification_id' => $notification->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch deceased officer notification email job', [
                    'user_id' => $officer->user->id,
                    'officer_id' => $officer->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify officer about course nomination
     */
    public function notifyCourseNominationCreated($course): ?Notification
    {
        $officer = $course->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $startDate = \Carbon\Carbon::parse($course->start_date)->format('d/m/Y');
        $endDate = $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('d/m/Y') : 'TBD';
        $courseType = $course->course_type ? " ({$course->course_type})" : '';

        // Create notification first
        $notification = Notification::create([
            'user_id' => $officer->user->id,
            'notification_type' => 'course_nomination_created',
            'title' => 'Course Nomination',
            'message' => "You have been nominated for the course: {$course->course_name}{$courseType}. Start date: {$startDate}, End date: {$endDate}.",
            'entity_type' => 'officer_course',
            'entity_id' => $course->id,
            'is_read' => false,
        ]);

        // Dispatch job to send email asynchronously for better performance
        if ($officer->user->email) {
            \App\Jobs\SendNotificationEmailJob::dispatch($officer->user, $notification);
        }

        Log::info('Course nomination notification created and email job dispatched', [
            'user_id' => $officer->user->id,
            'notification_id' => $notification->id,
            'course_id' => $course->id,
        ]);

        return $notification;
    }

    /**
     * Notify officer about course completion
     */
    public function notifyCourseCompleted($course): ?Notification
    {
        $officer = $course->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $completionDate = $course->completion_date ? \Carbon\Carbon::parse($course->completion_date)->format('d/m/Y') : 'N/A';

        return $this->notify(
            $officer->user,
            'course_completed',
            'Course Completed',
            "Your course nomination for '{$course->course_name}' has been marked as completed. Completion date: {$completionDate}. This has been recorded in your service record.",
            'officer_course',
            $course->id
        );
    }

    /**
     * Notify officer about query issued
     */
    public function notifyQueryIssued($query, $officer): ?Notification
    {
        if (!$officer || !$officer->user) {
            return null;
        }

        $issuedBy = $query->issuedBy;
        $issuedByName = $issuedBy ? ($issuedBy->name ?? $issuedBy->email) : 'Staff Officer';
        $issuedDate = $query->issued_at ? \Carbon\Carbon::parse($query->issued_at)->format('d/m/Y') : now()->format('d/m/Y');
        $deadlineText = $query->response_deadline ? \Carbon\Carbon::parse($query->response_deadline)->format('d/m/Y H:i') : 'Not specified';

        // Create in-app notification
        $notification = $this->notify(
            $officer->user,
            'query_issued',
            'Query Issued',
            "A query has been issued to you by {$issuedByName} on {$issuedDate}. Response deadline: {$deadlineText}. Please respond to the query through your dashboard before the deadline.",
            'query',
            $query->id,
            false // Don't send email via notify method, we'll send custom email
        );

        // Send custom email via job
        if ($officer->user->email) {
            try {
                \App\Jobs\SendQueryIssuedMailJob::dispatch($query);
                Log::info('Query issued email job dispatched', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch query issued email job', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify Staff Officer about query response submitted
     */
    public function notifyQueryResponseSubmitted($query): ?Notification
    {
        $issuedBy = $query->issuedBy;
        if (!$issuedBy) {
            return null;
        }

        $officer = $query->officer;
        $officerName = $officer ? "{$officer->initials} {$officer->surname}" : 'Officer';
        $serviceNumber = $officer->service_number ?? 'N/A';

        // Create in-app notification
        $notification = $this->notify(
            $issuedBy,
            'query_response_submitted',
            'Query Response Submitted',
            "Officer {$officerName} ({$serviceNumber}) has submitted a response to your query. Please review and accept or reject the response.",
            'query',
            $query->id,
            false // Don't send email via notify method, we'll send custom email
        );

        // Send custom email via job
        if ($issuedBy->email) {
            try {
                \App\Jobs\SendQueryResponseSubmittedMailJob::dispatch($query);
                Log::info('Query response submitted email job dispatched', [
                    'user_id' => $issuedBy->id,
                    'query_id' => $query->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch query response submitted email job', [
                    'user_id' => $issuedBy->id,
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify officer about query accepted
     */
    public function notifyQueryAccepted($query): ?Notification
    {
        $officer = $query->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $issuedDate = $query->issued_at ? \Carbon\Carbon::parse($query->issued_at)->format('d/m/Y') : 'N/A';

        // Create in-app notification for officer
        $notification = $this->notify(
            $officer->user,
            'query_accepted',
            'Query Accepted',
            "Your response to the query issued on {$issuedDate} has been reviewed and accepted. This query has been added to your disciplinary record.",
            'query',
            $query->id,
            false // Don't send email via notify method, we'll send custom email
        );

        // Send custom email via job to officer
        if ($officer->user->email) {
            try {
                \App\Jobs\SendQueryAcceptedMailJob::dispatch($query);
                Log::info('Query accepted email job dispatched', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch query accepted email job', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify Area Controller, DC Admin, and HRD about accepted query
        $this->notifyAuthoritiesQueryAccepted($query);

        return $notification;
    }

    /**
     * Notify Area Controller, DC Admin, and HRD when a query is accepted
     */
    private function notifyAuthoritiesQueryAccepted($query): void
    {
        $officer = $query->officer;
        if (!$officer || !$officer->present_station) {
            return;
        }

        $commandId = $officer->present_station;
        $officerName = ($officer->initials ?? '') . ' ' . ($officer->surname ?? '');
        $serviceNumber = $officer->service_number ?? 'N/A';

        // Get Area Controller for this command
        $areaControllers = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'Area Controller')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->get();

        // Get DC Admin for this command
        $dcAdmins = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'DC Admin')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->get();

        // Get all HRD users
        $hrdUsers = User::whereHas('roles', function ($q) {
            $q->where('name', 'HRD')
                ->where('user_roles.is_active', true);
        })->get();

        // Notify Area Controllers (email + in-app)
        foreach ($areaControllers as $user) {
            $this->notify(
                $user,
                'query_accepted_authority',
                'Query Accepted - Disciplinary Record Updated',
                "A query for Officer {$officerName} ({$serviceNumber}) has been accepted and added to their disciplinary record.",
                'query',
                $query->id,
                true // Send email
            );
        }

        // Notify DC Admins (email + in-app)
        foreach ($dcAdmins as $user) {
            $this->notify(
                $user,
                'query_accepted_authority',
                'Query Accepted - Disciplinary Record Updated',
                "A query for Officer {$officerName} ({$serviceNumber}) has been accepted and added to their disciplinary record.",
                'query',
                $query->id,
                true // Send email
            );
        }

        // Notify HRD users (in-app only)
        foreach ($hrdUsers as $user) {
            $this->notify(
                $user,
                'query_accepted_authority',
                'Query Accepted - Disciplinary Record Updated',
                "A query for Officer {$officerName} ({$serviceNumber}) has been accepted and added to their disciplinary record.",
                'query',
                $query->id,
                false // In-app only for HRD
            );
        }
    }

    /**
     * Notify officer about query rejected
     */
    public function notifyQueryRejected($query): ?Notification
    {
        $officer = $query->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $issuedDate = $query->issued_at ? \Carbon\Carbon::parse($query->issued_at)->format('d/m/Y') : 'N/A';

        // Create in-app notification
        $notification = $this->notify(
            $officer->user,
            'query_rejected',
            'Query Rejected',
            "Your response to the query issued on {$issuedDate} has been reviewed and rejected. This query will not be added to your disciplinary record.",
            'query',
            $query->id,
            false // Don't send email via notify method, we'll send custom email
        );

        // Send custom email via job
        if ($officer->user->email) {
            try {
                \App\Jobs\SendQueryRejectedMailJob::dispatch($query);
                Log::info('Query rejected email job dispatched', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch query rejected email job', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify officer about query expired (automatically accepted)
     */
    public function notifyQueryExpired($query): ?Notification
    {
        $officer = $query->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $issuedDate = $query->issued_at ? \Carbon\Carbon::parse($query->issued_at)->format('d/m/Y') : 'N/A';
        $deadlineDate = $query->response_deadline ? \Carbon\Carbon::parse($query->response_deadline)->format('d/m/Y H:i') : 'N/A';

        // Create in-app notification
        $notification = $this->notify(
            $officer->user,
            'query_expired',
            'Query Expired - Added to Disciplinary Record',
            "The query issued to you on {$issuedDate} has expired. The response deadline ({$deadlineDate}) has passed without a response. This query has been automatically added to your disciplinary record.",
            'query',
            $query->id,
            false // Don't send email via notify method, we'll send custom email
        );

        // Send custom email via job
        if ($officer->user->email) {
            try {
                \App\Jobs\SendQueryExpiredMailJob::dispatch($query);
                Log::info('Query expired email job dispatched', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch query expired email job', [
                    'user_id' => $officer->user->id,
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also notify authorities (same as when query is accepted)
        $this->notifyAuthoritiesQueryAccepted($query);

        return $notification;
    }

    /**
     * Notify officer about query deadline approaching (24 hours before)
     */
    public function notifyQueryDeadlineReminder($query, $hoursRemaining): ?Notification
    {
        $officer = $query->officer;
        if (!$officer || !$officer->user) {
            return null;
        }

        $issuedDate = $query->issued_at ? \Carbon\Carbon::parse($query->issued_at)->format('d/m/Y') : 'N/A';
        $deadlineDate = $query->response_deadline ? \Carbon\Carbon::parse($query->response_deadline)->format('d/m/Y H:i') : 'N/A';
        $issuedBy = $query->issuedBy;
        $issuedByName = $issuedBy ? ($issuedBy->name ?? $issuedBy->email) : 'Staff Officer';

        // Create in-app notification
        $notification = $this->notify(
            $officer->user,
            'query_deadline_reminder',
            'Query Response Deadline Approaching',
            "REMINDER: You have approximately {$hoursRemaining} hours remaining to respond to the query issued by {$issuedByName} on {$issuedDate}. Deadline: {$deadlineDate}. If you do not respond before the deadline, this query will be automatically accepted and added to your disciplinary record.",
            'query',
            $query->id,
            true // Send email for reminders
        );

        Log::info('Query deadline reminder notification sent', [
            'query_id' => $query->id,
            'officer_id' => $query->officer_id,
            'user_id' => $officer->user->id,
            'hours_remaining' => $hoursRemaining,
        ]);

        return $notification;
    }


    /**
     * Notify officers assigned to duty roster
     */
    public function notifyDutyRosterAssigned($roster, array $assignedOfficerIds): array
    {
        $notifications = [];
        $command = $roster->command;
        $commandName = $command ? $command->name : 'your command';
        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';

        // Get OIC and 2IC names if set
        $oicName = $roster->oicOfficer ? "{$roster->oicOfficer->initials} {$roster->oicOfficer->surname}" : null;
        $secondInCommandName = $roster->secondInCommandOfficer ? "{$roster->secondInCommandOfficer->initials} {$roster->secondInCommandOfficer->surname}" : null;

        // Ensure OIC and 2IC are included in notifications
        $allOfficerIds = $assignedOfficerIds;
        if ($roster->oic_officer_id && !in_array($roster->oic_officer_id, $allOfficerIds)) {
            $allOfficerIds[] = $roster->oic_officer_id;
        }
        if ($roster->second_in_command_officer_id && !in_array($roster->second_in_command_officer_id, $allOfficerIds)) {
            $allOfficerIds[] = $roster->second_in_command_officer_id;
        }

        foreach ($allOfficerIds as $officerId) {
            $officer = \App\Models\Officer::find($officerId);
            if (!$officer || !$officer->user) {
                continue;
            }

            // Determine role
            $role = 'Regular Officer';
            if ($roster->oic_officer_id == $officerId) {
                $role = 'Officer in Charge (OIC)';
            } elseif ($roster->second_in_command_officer_id == $officerId) {
                $role = 'Second In Command (2IC)';
            }

            $message = "You have been assigned to the duty roster for {$commandName} as {$role}. ";
            $message .= "Period: {$periodStart} to {$periodEnd}. ";

            if ($role === 'Officer in Charge (OIC)') {
                $message .= "You are the Officer in Charge for this roster period.";
            } elseif ($role === 'Second In Command (2IC)') {
                $message .= "You are the Second In Command for this roster period.";
                if ($oicName) {
                    $message .= " OIC: {$oicName}.";
                }
            } else {
                if ($oicName) {
                    $message .= "OIC: {$oicName}.";
                }
                if ($secondInCommandName) {
                    $message .= " 2IC: {$secondInCommandName}.";
                }
            }

            // Create in-app notification
            $notification = $this->notify(
                $officer->user,
                'duty_roster_assigned',
                'Duty Roster Assignment',
                $message,
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($officer->user->email) {
                try {
                    \App\Jobs\SendDutyRosterAssignmentMailJob::dispatch(
                        $roster,
                        $officer,
                        $role,
                        $commandName,
                        $periodStart,
                        $periodEnd,
                        $oicName,
                        $secondInCommandName
                    );
                    Log::info('Duty roster assignment email job dispatched', [
                        'user_id' => $officer->user->id,
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'role' => $role,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster assignment email job', [
                        'user_id' => $officer->user->id,
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify DC Admins about submitted duty roster ready for approval
     */
    public function notifyDutyRosterSubmitted($roster): array
    {
        $command = $roster->command;
        if (!$command) {
            return [];
        }

        $commandId = $command->id;
        $preparedBy = $roster->preparedBy;
        $preparedByName = $preparedBy ? ($preparedBy->name ?? $preparedBy->email) : 'Staff Officer';
        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';
        $assignmentsCount = $roster->assignments ? $roster->assignments->count() : 0;

        // Get OIC and 2IC names if set
        $oicName = $roster->oicOfficer ? "{$roster->oicOfficer->initials} {$roster->oicOfficer->surname}" : null;
        $secondInCommandName = $roster->secondInCommandOfficer ? "{$roster->secondInCommandOfficer->initials} {$roster->secondInCommandOfficer->surname}" : null;

        // Get DC Admins for the command
        $dcAdmins = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'DC Admin')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        if ($dcAdmins->isEmpty()) {
            return [];
        }

        $notifications = [];
        foreach ($dcAdmins as $dcAdmin) {
            // Create in-app notification
            $notification = $this->notify(
                $dcAdmin,
                'duty_roster_submitted',
                'Duty Roster Submitted - Requires Approval',
                "A duty roster for {$command->name} has been submitted by {$preparedByName}. Period: {$periodStart} to {$periodEnd}. Total assignments: {$assignmentsCount}. Please review and approve.",
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($dcAdmin->email) {
                try {
                    \App\Jobs\SendDutyRosterSubmittedMailJob::dispatch(
                        $roster,
                        $dcAdmin,
                        $command->name,
                        $periodStart,
                        $periodEnd,
                        $preparedByName,
                        $assignmentsCount,
                        $oicName,
                        $secondInCommandName,
                        'dc-admin/roster'
                    );
                    Log::info('Duty roster submitted email job dispatched to DC Admin', [
                        'user_id' => $dcAdmin->id,
                        'roster_id' => $roster->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster submitted email job to DC Admin', [
                        'user_id' => $dcAdmin->id,
                        'roster_id' => $roster->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify Area Controllers about submitted duty roster ready for approval
     */
    public function notifyDutyRosterSubmittedToAreaController($roster): array
    {
        $command = $roster->command;
        if (!$command) {
            return [];
        }

        $commandId = $command->id;
        $preparedBy = $roster->preparedBy;
        $preparedByName = $preparedBy ? ($preparedBy->name ?? $preparedBy->email) : 'Staff Officer';
        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';
        $assignmentsCount = $roster->assignments ? $roster->assignments->count() : 0;

        // Get OIC and 2IC names if set
        $oicName = $roster->oicOfficer ? "{$roster->oicOfficer->initials} {$roster->oicOfficer->surname}" : null;
        $secondInCommandName = $roster->secondInCommandOfficer ? "{$roster->secondInCommandOfficer->initials} {$roster->secondInCommandOfficer->surname}" : null;

        // Get Area Controllers (they can approve any roster)
        $areaControllers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Area Controller')
                ->where('user_roles.is_active', true);
        })->where('is_active', true)->get();

        if ($areaControllers->isEmpty()) {
            return [];
        }

        $notifications = [];
        foreach ($areaControllers as $areaController) {
            // Create in-app notification
            $notification = $this->notify(
                $areaController,
                'duty_roster_submitted',
                'Duty Roster Submitted - Requires Approval',
                "A duty roster for {$command->name} has been submitted by {$preparedByName}. Period: {$periodStart} to {$periodEnd}. Total assignments: {$assignmentsCount}. Please review and approve.",
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($areaController->email) {
                try {
                    \App\Jobs\SendDutyRosterSubmittedMailJob::dispatch(
                        $roster,
                        $areaController,
                        $command->name,
                        $periodStart,
                        $periodEnd,
                        $preparedByName,
                        $assignmentsCount,
                        $oicName,
                        $secondInCommandName,
                        'area-controller/roster'
                    );
                    Log::info('Duty roster submitted email job dispatched to Area Controller', [
                        'user_id' => $areaController->id,
                        'roster_id' => $roster->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster submitted email job to Area Controller', [
                        'user_id' => $areaController->id,
                        'roster_id' => $roster->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify Staff Officer when duty roster is approved
     * Also sends assignment emails to all assigned officers
     */
    public function notifyDutyRosterApproved($roster, $approvedBy): array
    {
        // Ensure roster has all necessary relationships loaded
        if (!$roster->relationLoaded('command')) {
            $roster->load('command');
        }
        if (!$roster->relationLoaded('preparedBy')) {
            $roster->load('preparedBy');
        }
        if (!$roster->relationLoaded('assignments')) {
            $roster->load('assignments.officer');
        }
        if (!$roster->relationLoaded('oicOfficer')) {
            $roster->load('oicOfficer');
        }
        if (!$roster->relationLoaded('secondInCommandOfficer')) {
            $roster->load('secondInCommandOfficer');
        }

        $command = $roster->command;
        $commandName = $command ? $command->name : 'Unknown Command';
        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';

        $approvedByName = $approvedBy ? ($approvedBy->name ?? $approvedBy->email) : 'Approver';
        $staffOfficer = $roster->preparedBy;

        $notifications = [];

        // Notify Staff Officer about approval
        if ($staffOfficer) {
            $message = "Your duty roster for {$commandName} has been approved by {$approvedByName}. Period: {$periodStart} to {$periodEnd}. All assigned officers have been notified.";

            $notification = $this->notify(
                $staffOfficer,
                'duty_roster_approved',
                'Duty Roster Approved',
                $message,
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($staffOfficer->email) {
                try {
                    \App\Jobs\SendDutyRosterApprovedMailJob::dispatch(
                        $roster,
                        $approvedByName,
                        $commandName,
                        $periodStart,
                        $periodEnd
                    );
                    Log::info('Duty roster approved email job dispatched to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'roster_id' => $roster->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster approved email job to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'roster_id' => $roster->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        // Send assignment emails to all assigned officers (OIC, 2IC, and regular officers)
        $allOfficerIds = [];

        // Add OIC if set
        if ($roster->oic_officer_id) {
            $allOfficerIds[] = $roster->oic_officer_id;
        }

        // Add 2IC if set
        if ($roster->second_in_command_officer_id) {
            $allOfficerIds[] = $roster->second_in_command_officer_id;
        }

        // Add officers from assignments
        if ($roster->assignments) {
            foreach ($roster->assignments as $assignment) {
                if ($assignment->officer_id && !in_array($assignment->officer_id, $allOfficerIds)) {
                    $allOfficerIds[] = $assignment->officer_id;
                }
            }
        }

        // Get OIC and 2IC names
        $oicName = $roster->oicOfficer ? "{$roster->oicOfficer->initials} {$roster->oicOfficer->surname}" : null;
        $secondInCommandName = $roster->secondInCommandOfficer ? "{$roster->secondInCommandOfficer->initials} {$roster->secondInCommandOfficer->surname}" : null;

        // Send assignment emails to all officers
        foreach ($allOfficerIds as $officerId) {
            $officer = \App\Models\Officer::find($officerId);
            if (!$officer || !$officer->user) {
                continue;
            }

            // Determine role
            $role = 'Regular Officer';
            if ($roster->oic_officer_id == $officerId) {
                $role = 'Officer in Charge (OIC)';
            } elseif ($roster->second_in_command_officer_id == $officerId) {
                $role = 'Second In Command (2IC)';
            }

            $message = "You have been assigned to the duty roster for {$commandName} as {$role}. ";
            $message .= "Period: {$periodStart} to {$periodEnd}. ";

            if ($role === 'Officer in Charge (OIC)') {
                $message .= "You are the Officer in Charge for this roster period.";
            } elseif ($role === 'Second In Command (2IC)') {
                $message .= "You are the Second In Command for this roster period.";
                if ($oicName) {
                    $message .= " OIC: {$oicName}.";
                }
            } else {
                if ($oicName) {
                    $message .= "OIC: {$oicName}.";
                }
                if ($secondInCommandName) {
                    $message .= " 2IC: {$secondInCommandName}.";
                }
            }

            // Create in-app notification
            $notification = $this->notify(
                $officer->user,
                'duty_roster_assigned',
                'Duty Roster Assignment',
                $message,
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($officer->user->email) {
                try {
                    \App\Jobs\SendDutyRosterAssignmentMailJob::dispatch(
                        $roster,
                        $officer,
                        $role,
                        $commandName,
                        $periodStart,
                        $periodEnd,
                        $oicName,
                        $secondInCommandName
                    );
                    Log::info('Duty roster assignment email job dispatched after approval', [
                        'user_id' => $officer->user->id,
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'role' => $role,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster assignment email job after approval', [
                        'user_id' => $officer->user->id,
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify Staff Officer when duty roster is rejected
     */
    public function notifyDutyRosterRejected($roster, $rejectedBy, $rejectionReason): array
    {
        // Ensure roster has relationships loaded
        if (!$roster->relationLoaded('command')) {
            $roster->load('command');
        }
        if (!$roster->relationLoaded('preparedBy')) {
            $roster->load('preparedBy');
        }

        $command = $roster->command;
        $commandName = $command ? $command->name : 'Unknown Command';
        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';

        $rejectedByName = $rejectedBy ? ($rejectedBy->name ?? $rejectedBy->email) : 'Approver';
        $staffOfficer = $roster->preparedBy;

        $notifications = [];

        if ($staffOfficer) {
            $message = "Your duty roster for {$commandName} has been rejected by {$rejectedByName}. Period: {$periodStart} to {$periodEnd}. Reason: {$rejectionReason}";

            $notification = $this->notify(
                $staffOfficer,
                'duty_roster_rejected',
                'Duty Roster Rejected',
                $message,
                'duty_roster',
                $roster->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($staffOfficer->email) {
                try {
                    \App\Jobs\SendDutyRosterRejectedMailJob::dispatch(
                        $roster,
                        $rejectedByName,
                        $rejectionReason,
                        $commandName,
                        $periodStart,
                        $periodEnd
                    );
                    Log::info('Duty roster rejected email job dispatched to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'roster_id' => $roster->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch duty roster rejected email job to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'roster_id' => $roster->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify DC Admins about submitted internal staff order ready for approval
     */
    public function notifyInternalStaffOrderSubmitted($order): array
    {
        $command = $order->command;
        if (!$command) {
            return [];
        }

        $commandId = $command->id;
        $preparedBy = $order->preparedBy;
        $preparedByName = $preparedBy ? ($preparedBy->name ?? $preparedBy->email) : 'Staff Officer';
        $officer = $order->officer;
        $officerName = $officer ? "{$officer->initials} {$officer->surname}" : 'N/A';
        $serviceNumber = $officer ? $officer->service_number : 'N/A';
        $targetUnit = $order->target_unit ?? 'N/A';
        $targetRole = $order->target_role ?? 'N/A';

        // Get DC Admins for the command
        $dcAdmins = User::whereHas('roles', function ($q) use ($commandId) {
            $q->where('name', 'DC Admin')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $commandId);
        })->where('is_active', true)->get();

        if ($dcAdmins->isEmpty()) {
            return [];
        }

        $notifications = [];
        foreach ($dcAdmins as $dcAdmin) {
            // Create in-app notification
            $notification = $this->notify(
                $dcAdmin,
                'internal_staff_order_submitted',
                'Internal Staff Order Submitted - Requires Approval',
                "An internal staff order has been submitted by {$preparedByName}. Officer: {$officerName} ({$serviceNumber}). Target: {$targetUnit} - {$targetRole}. Please review and approve.",
                'internal_staff_order',
                $order->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($dcAdmin->email) {
                try {
                    \App\Jobs\SendInternalStaffOrderSubmittedMailJob::dispatch(
                        $order,
                        $dcAdmin,
                        $command->name,
                        $preparedByName,
                        $officerName,
                        $serviceNumber,
                        $targetUnit,
                        $targetRole
                    );
                    Log::info('Internal staff order submitted email job dispatched to DC Admin', [
                        'user_id' => $dcAdmin->id,
                        'order_id' => $order->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch internal staff order submitted email job to DC Admin', [
                        'user_id' => $dcAdmin->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify Staff Officer and affected officers when internal staff order is approved
     */
    public function notifyInternalStaffOrderApproved($order, $outgoingOfficer = null): array
    {
        // Ensure order has all necessary relationships loaded
        if (!$order->relationLoaded('command')) {
            $order->load('command');
        }
        if (!$order->relationLoaded('preparedBy')) {
            $order->load('preparedBy');
        }
        if (!$order->relationLoaded('officer')) {
            $order->load('officer');
        }

        $command = $order->command;
        $commandName = $command ? $command->name : 'Unknown Command';
        $staffOfficer = $order->preparedBy;
        $officer = $order->officer;
        $targetUnit = $order->target_unit ?? 'N/A';
        $targetRole = $order->target_role ?? 'N/A';
        $officerName = $officer ? "{$officer->initials} {$officer->surname}" : 'N/A';
        $serviceNumber = $officer ? $officer->service_number : 'N/A';

        $notifications = [];

        // Notify Staff Officer about approval
        if ($staffOfficer) {
            $message = "Your internal staff order (Order #{$order->order_number}) has been approved. Officer {$officerName} ({$serviceNumber}) has been reassigned to {$targetUnit} as {$targetRole}.";

            $notification = $this->notify(
                $staffOfficer,
                'internal_staff_order_approved',
                'Internal Staff Order Approved',
                $message,
                'internal_staff_order',
                $order->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($staffOfficer->email) {
                try {
                    \App\Jobs\SendInternalStaffOrderApprovedMailJob::dispatch(
                        $order,
                        $staffOfficer,
                        $commandName,
                        $officerName,
                        $serviceNumber,
                        $targetUnit,
                        $targetRole,
                        $outgoingOfficer
                    );
                    Log::info('Internal staff order approved email job dispatched to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'order_id' => $order->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch internal staff order approved email job to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        // Notify the officer being reassigned
        if ($officer && $officer->user) {
            $message = "You have been reassigned to {$targetUnit} as {$targetRole} per Internal Staff Order #{$order->order_number}.";
            if ($order->current_unit) {
                $message .= " Previous assignment: {$order->current_unit}.";
            }

            $notification = $this->notify(
                $officer->user,
                'internal_staff_order_approved',
                'Internal Staff Order - Reassignment',
                $message,
                'internal_staff_order',
                $order->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($officer->user->email) {
                try {
                    \App\Jobs\SendInternalStaffOrderApprovedMailJob::dispatch(
                        $order,
                        $officer->user,
                        $commandName,
                        $officerName,
                        $serviceNumber,
                        $targetUnit,
                        $targetRole,
                        $outgoingOfficer
                    );
                    Log::info('Internal staff order approved email job dispatched to Officer', [
                        'user_id' => $officer->user->id,
                        'order_id' => $order->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch internal staff order approved email job to Officer', [
                        'user_id' => $officer->user->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        // Notify outgoing officer if applicable (OIC/2IC being replaced)
        if ($outgoingOfficer && $outgoingOfficer->user) {
            $message = "You have been replaced as {$targetRole} of {$targetUnit} per Internal Staff Order #{$order->order_number}. You have been reassigned as a regular member of the unit.";
            $outgoingOfficerName = "{$outgoingOfficer->initials} {$outgoingOfficer->surname}";

            $notification = $this->notify(
                $outgoingOfficer->user,
                'internal_staff_order_approved',
                'Internal Staff Order - Role Change',
                $message,
                'internal_staff_order',
                $order->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($outgoingOfficer->user->email) {
                try {
                    \App\Jobs\SendInternalStaffOrderApprovedMailJob::dispatch(
                        $order,
                        $outgoingOfficer->user,
                        $commandName,
                        $outgoingOfficerName,
                        $outgoingOfficer->service_number ?? 'N/A',
                        $targetUnit,
                        'Regular Member',
                        null
                    );
                    Log::info('Internal staff order approved email job dispatched to Outgoing Officer', [
                        'user_id' => $outgoingOfficer->user->id,
                        'order_id' => $order->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch internal staff order approved email job to Outgoing Officer', [
                        'user_id' => $outgoingOfficer->user->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Notify Staff Officer when internal staff order is rejected
     */
    public function notifyInternalStaffOrderRejected($order, $rejectedBy, $rejectionReason): array
    {
        // Ensure order has relationships loaded
        if (!$order->relationLoaded('command')) {
            $order->load('command');
        }
        if (!$order->relationLoaded('preparedBy')) {
            $order->load('preparedBy');
        }

        $command = $order->command;
        $commandName = $command ? $command->name : 'Unknown Command';
        $rejectedByName = $rejectedBy ? ($rejectedBy->name ?? $rejectedBy->email) : 'DC Admin';
        $staffOfficer = $order->preparedBy;

        $notifications = [];

        if ($staffOfficer) {
            $message = "Your internal staff order (Order #{$order->order_number}) has been rejected by {$rejectedByName}. Reason: {$rejectionReason}";

            $notification = $this->notify(
                $staffOfficer,
                'internal_staff_order_rejected',
                'Internal Staff Order Rejected',
                $message,
                'internal_staff_order',
                $order->id,
                false // Don't send email via notify method, we'll use job
            );

            // Send email via job
            if ($staffOfficer->email) {
                try {
                    \App\Jobs\SendInternalStaffOrderRejectedMailJob::dispatch(
                        $order,
                        $staffOfficer,
                        $rejectedByName,
                        $rejectionReason,
                        $commandName
                    );
                    Log::info('Internal staff order rejected email job dispatched to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'order_id' => $order->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch internal staff order rejected email job to Staff Officer', [
                        'user_id' => $staffOfficer->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }
}
