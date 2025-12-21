<?php

namespace App\Services;

use App\Models\Notification;
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
            // In local environment, send emails synchronously for easier testing
            // In production, use queue for better performance
            if (app()->environment('local')) {
                try {
                    Mail::to($user->email)->send(new NotificationMail($user, $notification));
                    Log::info('Notification email sent synchronously', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'notification_id' => $notification->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification email synchronously', [
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // Queue email notification for production
                SendNotificationEmailJob::dispatch($user, $notification);
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
}
