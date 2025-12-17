<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Officer;
use App\Models\RetirementList;
use App\Models\RetirementListItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckRetirementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find officers who will retire in the next 6 months
        $retirementDate = now()->addMonths(6);
        $birthYear = $retirementDate->year - 60;

        $officers = Officer::whereYear('date_of_birth', $birthYear)
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->get();

        foreach ($officers as $officer) {
            $retirementDate = $officer->date_of_birth->copy()->addYears(60);

            // Check if already in retirement list
            $inList = RetirementListItem::where('officer_id', $officer->id)
                ->whereHas('retirementList', function ($query) use ($retirementDate) {
                    $query->where('year', $retirementDate->year);
                })
                ->exists();

            if (!$inList && $retirementDate->isFuture() && $retirementDate->diffInMonths(now()) <= 6) {
                // Notify HRD
                $hrdUsers = \App\Models\User::whereHas('roles', function ($query) {
                    $query->where('name', 'HRD');
                })->get();

                foreach ($hrdUsers as $hrdUser) {
                    Notification::create([
                        'user_id' => $hrdUser->id,
                        'notification_type' => 'RETIREMENT_DUE',
                        'title' => 'Officer Approaching Retirement',
                        'message' => "Officer {$officer->service_number} ({$officer->full_name}) will retire on {$retirementDate->format('Y-m-d')}",
                        'data' => [
                            'officer_id' => $officer->id,
                            'retirement_date' => $retirementDate->format('Y-m-d'),
                        ],
                    ]);
                }
            }
        }
    }
}

