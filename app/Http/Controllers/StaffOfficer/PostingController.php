<?php

namespace App\Http\Controllers\StaffOfficer;

use App\Http\Controllers\Controller;
use App\Models\OfficerPosting;
use App\Models\ReleaseLetter;
use App\Models\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PostingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Staff Officer');
    }

    /**
     * Get Staff Officer's command
     */
    private function getStaffOfficerCommand()
    {
        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        return $staffOfficerRole?->pivot->command_id ? Command::find($staffOfficerRole->pivot->command_id) : null;
    }

    /**
     * List pending release letters (officers being posted OUT of this command)
     */
    public function pendingReleaseLetters()
    {
        $command = $this->getStaffOfficerCommand();
        
        if (!$command) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'No command assigned to your Staff Officer role.');
        }

        // Get officers in this command that have pending postings OUT (awaiting release letter)
        $pendingReleasePostings = OfficerPosting::where('is_current', false)
            ->where('release_letter_printed', false) // Not yet printed
            ->whereHas('officer', function ($q) use ($command) {
                $q->where('present_station', $command->id)->where('is_active', true);
            })
            ->with(['officer.presentStation', 'officer.user', 'command', 'movementOrder', 'staffOrder'])
            ->orderBy('posting_date', 'desc')
            ->get();

        return view('dashboards.staff-officer.pending-release-letters', compact('command', 'pendingReleasePostings'));
    }

    /**
     * Print release letter for a posting
     */
    public function printReleaseLetter($postingId)
    {
        $command = $this->getStaffOfficerCommand();
        
        if (!$command) {
            return redirect()->back()
                ->with('error', 'No command assigned to your Staff Officer role.');
        }

        $posting = OfficerPosting::with([
            'officer.presentStation',
            'command',
            'movementOrder',
            'staffOrder'
        ])->findOrFail($postingId);

        // Verify this posting is for an officer in this command
        if ($posting->officer->present_station !== $command->id) {
            return redirect()->back()
                ->with('error', 'This officer is not in your command.');
        }

        // Verify release letter hasn't been printed yet
        if ($posting->release_letter_printed) {
            return redirect()->back()
                ->with('error', 'Release letter has already been printed for this posting.');
        }

        // Get from command (current command) and to command (destination)
        $fromCommand = $posting->officer->presentStation;
        $toCommand = $posting->command;

        // Determine order type
        $order = $posting->movementOrder ?? $posting->staffOrder;
        $orderType = $posting->movementOrder ? 'Movement Order' : 'Staff Order';
        $orderNumber = $order ? $order->order_number : 'N/A';

        return view('prints.release-letter', compact('posting', 'fromCommand', 'toCommand', 'order', 'orderType', 'orderNumber'));
    }

    /**
     * Mark release letter as printed
     */
    public function markReleaseLetterPrinted($postingId, Request $request)
    {
        $command = $this->getStaffOfficerCommand();
        
        if (!$command) {
            return redirect()->back()
                ->with('error', 'No command assigned to your Staff Officer role.');
        }

        try {
            DB::beginTransaction();

            $posting = OfficerPosting::with(['officer', 'command', 'movementOrder', 'staffOrder'])->findOrFail($postingId);

            // Verify this posting is for an officer in this command
            if ($posting->officer->present_station !== $command->id) {
                return redirect()->back()
                    ->with('error', 'This officer is not in your command.');
            }

            // Verify release letter hasn't been printed yet
            if ($posting->release_letter_printed) {
                return redirect()->back()
                    ->with('error', 'Release letter has already been printed for this posting.');
            }

            // Update posting
            $posting->update([
                'release_letter_printed' => true,
                'release_letter_printed_at' => now(),
                'release_letter_printed_by' => auth()->id(),
            ]);

            // Create release letter record
            $fromCommand = $posting->officer->presentStation;
            $toCommand = $posting->command;
            $order = $posting->movementOrder ?? $posting->staffOrder;

            // Generate release letter number
            $year = date('Y');
            $lastLetter = ReleaseLetter::whereYear('created_at', $year)->orderBy('created_at', 'desc')->first();
            $letterNumber = 'RL-' . $year . '-' . str_pad(($lastLetter ? ((int)substr($lastLetter->letter_number, -4)) + 1 : 1), 4, '0', STR_PAD_LEFT);
            
            ReleaseLetter::create([
                'officer_id' => $posting->officer_id,
                'command_id' => $command->id, // From command
                'letter_number' => $letterNumber,
                'release_date' => now(),
                'reason' => "Posting to {$toCommand->name} via " . ($order ? $order->order_number : 'Order'),
                'prepared_by' => auth()->id(),
            ]);

            // Notify officer about transfer
            $notificationService = app(\App\Services\NotificationService::class);
            try {
                $notificationService->notifyOfficerTransfer($posting->officer, $fromCommand, $toCommand, $order);
            } catch (\Exception $e) {
                Log::warning("Failed to send transfer notification: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('staff-officer.postings.pending-release-letters')
                ->with('success', 'Release letter marked as printed. Officer has been notified of transfer.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark release letter as printed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to mark release letter as printed: ' . $e->getMessage());
        }
    }

    /**
     * List pending arrivals (officers being posted INTO this command)
     */
    public function pendingArrivals()
    {
        $command = $this->getStaffOfficerCommand();
        
        if (!$command) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'No command assigned to your Staff Officer role.');
        }

        // Get pending postings INTO this command where release letter has been printed but not yet accepted
        $pendingArrivals = OfficerPosting::where('command_id', $command->id)
            ->where('is_current', false)
            ->where('release_letter_printed', true) // Release letter must be printed first
            ->where('accepted_by_new_command', false) // Not yet accepted
            ->with([
                'officer.presentStation',
                'officer.user',
                'movementOrder',
                'staffOrder',
                'releaseLetterPrintedBy'
            ])
            ->orderBy('release_letter_printed_at', 'desc')
            ->get();

        return view('dashboards.staff-officer.pending-arrivals', compact('command', 'pendingArrivals'));
    }

    /**
     * Accept officer into new command
     */
    public function acceptOfficer($postingId, Request $request)
    {
        $command = $this->getStaffOfficerCommand();
        
        if (!$command) {
            return redirect()->back()
                ->with('error', 'No command assigned to your Staff Officer role.');
        }

        try {
            DB::beginTransaction();

            $posting = OfficerPosting::with(['officer', 'command', 'movementOrder', 'staffOrder'])->findOrFail($postingId);

            // Verify this posting is TO this command
            if ($posting->command_id !== $command->id) {
                return redirect()->back()
                    ->with('error', 'This posting is not to your command.');
            }

            // Verify release letter has been printed
            if (!$posting->release_letter_printed) {
                return redirect()->back()
                    ->with('error', 'Release letter must be printed before accepting officer.');
            }

            // Verify officer hasn't been accepted yet
            if ($posting->accepted_by_new_command) {
                return redirect()->back()
                    ->with('error', 'Officer has already been accepted.');
            }

            $officer = $posting->officer;
            $fromCommand = $officer->presentStation;
            $toCommand = $posting->command;

            // Update posting - mark as accepted
            $posting->update([
                'accepted_by_new_command' => true,
                'accepted_at' => now(),
                'accepted_by' => auth()->id(),
                'documented_by' => auth()->id(),
                'documented_at' => now(),
            ]);

            // Now complete the transfer - officer moves to new command
            // Set old posting as not current
            OfficerPosting::where('officer_id', $officer->id)
                ->where('id', '!=', $posting->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Set new posting as current
            $posting->update(['is_current' => true]);

            // Update officer's present_station
            $officer->update(['present_station' => $toCommand->id]);

            // Notify officer about acceptance
            $notificationService = app(\App\Services\NotificationService::class);
            try {
                $notificationService->notifyOfficerAccepted($officer, $fromCommand, $toCommand, $posting);
            } catch (\Exception $e) {
                Log::warning("Failed to send acceptance notification: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('staff-officer.postings.pending-arrivals')
                ->with('success', "Officer {$officer->initials} {$officer->surname} has been accepted into {$toCommand->name}. Transfer is now complete.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept officer: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to accept officer: ' . $e->getMessage());
        }
    }
}
