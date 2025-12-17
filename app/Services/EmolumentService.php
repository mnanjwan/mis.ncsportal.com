<?php

namespace App\Services;

use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use App\Models\Notification;
use App\Models\User;

class EmolumentService
{
    /**
     * Check if officer can raise emolument
     */
    public function canRaiseEmolument(int $officerId, int $year): array
    {
        $timeline = EmolumentTimeline::where('is_active', true)
            ->where('year', $year)
            ->first();

        if (!$timeline) {
            return ['can_raise' => false, 'reason' => 'No active timeline for this year'];
        }

        if (!$timeline->can_submit) {
            return ['can_raise' => false, 'reason' => 'Timeline has expired'];
        }

        $existing = Emolument::where('officer_id', $officerId)
            ->where('year', $year)
            ->first();

        if ($existing) {
            return ['can_raise' => false, 'reason' => 'Emolument already raised for this year'];
        }

        return ['can_raise' => true];
    }

    /**
     * Raise emolument
     */
    public function raiseEmolument(int $officerId, array $data): Emolument
    {
        $timeline = EmolumentTimeline::where('is_active', true)
            ->where('year', now()->year)
            ->firstOrFail();

        $emolument = Emolument::create([
            'officer_id' => $officerId,
            'timeline_id' => $timeline->id,
            'year' => now()->year,
            'bank_name' => $data['bank_name'],
            'bank_account_number' => $data['bank_account_number'],
            'pfa_name' => $data['pfa_name'],
            'rsa_pin' => $data['rsa_pin'],
            'status' => 'RAISED',
        ]);

        // Notify assessors
        $this->notifyAssessors($emolument);

        return $emolument;
    }

    /**
     * Assess emolument
     */
    public function assessEmolument(Emolument $emolument, int $assessorId, string $status, ?string $comments = null): void
    {
        $emolument->assessment()->create([
            'assessor_id' => $assessorId,
            'assessment_status' => $status,
            'comments' => $comments,
        ]);

        $emolument->update([
            'status' => 'ASSESSED',
            'assessed_at' => now(),
        ]);

        if ($status === 'APPROVED') {
            // Notify validators
            $this->notifyValidators($emolument);
        } else {
            // Notify officer
            $this->notifyOfficer($emolument, 'Emolument assessment rejected');
        }
    }

    /**
     * Validate emolument
     */
    public function validateEmolument(Emolument $emolument, int $validatorId, string $status, ?string $comments = null): void
    {
        $assessment = $emolument->assessment;

        $emolument->validation()->create([
            'assessment_id' => $assessment->id,
            'validator_id' => $validatorId,
            'validation_status' => $status,
            'comments' => $comments,
        ]);

        $emolument->update([
            'status' => 'VALIDATED',
            'validated_at' => now(),
        ]);

        if ($status === 'APPROVED') {
            // Notify accounts
            $this->notifyAccounts($emolument);
        } else {
            // Notify officer
            $this->notifyOfficer($emolument, 'Emolument validation rejected');
        }
    }

    /**
     * Notify assessors
     */
    private function notifyAssessors(Emolument $emolument): void
    {
        // Get assessors for the officer's command
        $assessors = User::whereHas('roles', function ($query) {
            $query->where('name', 'Assessor');
        })->whereHas('officer', function ($query) use ($emolument) {
            $query->where('present_station', $emolument->officer->present_station);
        })->get();

        foreach ($assessors as $assessor) {
            Notification::create([
                'user_id' => $assessor->id,
                'notification_type' => 'EMOLUMENT_RAISED',
                'title' => 'New Emolument to Assess',
                'message' => "Officer {$emolument->officer->service_number} has raised an emolument for {$emolument->year}",
                'data' => ['emolument_id' => $emolument->id],
            ]);
        }
    }

    /**
     * Notify validators
     */
    private function notifyValidators(Emolument $emolument): void
    {
        $validators = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Validator', 'Area Controller']);
        })->whereHas('officer', function ($query) use ($emolument) {
            $query->where('present_station', $emolument->officer->present_station);
        })->get();

        foreach ($validators as $validator) {
            Notification::create([
                'user_id' => $validator->id,
                'notification_type' => 'EMOLUMENT_ASSESSED',
                'title' => 'Emolument Ready for Validation',
                'message' => "Emolument for {$emolument->officer->service_number} has been assessed and requires validation",
                'data' => ['emolument_id' => $emolument->id],
            ]);
        }
    }

    /**
     * Notify accounts
     */
    private function notifyAccounts(Emolument $emolument): void
    {
        $accounts = User::whereHas('roles', function ($query) {
            $query->where('name', 'Accounts');
        })->get();

        foreach ($accounts as $account) {
            Notification::create([
                'user_id' => $account->id,
                'notification_type' => 'EMOLUMENT_VALIDATED',
                'title' => 'Emolument Ready for Payment',
                'message' => "Emolument for {$emolument->officer->service_number} has been validated and is ready for payment",
                'data' => ['emolument_id' => $emolument->id],
            ]);
        }
    }

    /**
     * Notify officer
     */
    private function notifyOfficer(Emolument $emolument, string $message): void
    {
        if ($emolument->officer->user_id) {
            Notification::create([
                'user_id' => $emolument->officer->user_id,
                'notification_type' => 'EMOLUMENT_STATUS',
                'title' => 'Emolument Status Update',
                'message' => $message,
                'data' => ['emolument_id' => $emolument->id],
            ]);
        }
    }
}

