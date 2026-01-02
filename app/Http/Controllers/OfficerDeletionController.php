<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\User;
use App\Models\Zone;
use App\Models\Command;
use App\Models\OfficerDeletionAuditLog;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\StaffOrder;
use App\Models\InternalStaffOrder;
use App\Models\OfficerPosting;
use App\Models\RosterAssignment;
use App\Models\DutyRoster;
use App\Models\APERForm;
use App\Models\OfficerCourse;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OfficerDeletionController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Check if user has HRD or Establishment role
     */
    private function authorizeAccess()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HRD', 'Establishment'])) {
            abort(403, 'Unauthorized. Only HRD and Establishment roles can access this feature.');
        }
    }

    /**
     * Entry page - Filter officers for deletion
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        $selectedZoneId = $request->get('zone_id');
        $selectedCommandId = $request->get('command_id');
        $search = $request->get('search');

        $commands = collect();
        if ($selectedZoneId) {
            $commands = Command::where('zone_id', $selectedZoneId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $query = Officer::with(['presentStation.zone', 'currentPosting']);

        // Filter by zone
        if ($selectedZoneId) {
            $zoneCommandIds = Command::where('zone_id', $selectedZoneId)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $query->whereIn('present_station', $zoneCommandIds);
        }

        // Filter by command
        if ($selectedCommandId) {
            $query->where('present_station', $selectedCommandId);
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('appointment_number', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'surname');
        $sortOrder = $request->get('sort_order', 'asc');

        $sortableColumns = [
            'service_number' => 'service_number',
            'name' => 'surname',
            'rank' => 'substantive_rank',
            'command' => 'present_station',
            'status' => 'is_active',
        ];

        $column = $sortableColumns[$sortBy] ?? 'surname';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

        if ($sortBy === 'command') {
            $query->leftJoin('commands', 'officers.present_station', '=', 'commands.id')
                ->select('officers.*')
                ->orderBy('commands.name', $order);
        } else {
            $query->orderBy($column, $order);
        }

        // If sorting by name, add initials as secondary sort
        if ($sortBy === 'name' || !$request->has('sort_by')) {
            $query->orderBy('initials', $order);
        }

        $officers = $query->paginate(20)->withQueryString();

        return view('dashboards.officer-deletion.index', compact(
            'zones',
            'commands',
            'officers',
            'selectedZoneId',
            'selectedCommandId',
            'search'
        ));
    }

    /**
     * Show officer detail view with delete option
     */
    public function show($id)
    {
        $this->authorizeAccess();

        $officer = Officer::with([
            'presentStation.zone',
            'user',
            'nextOfKin',
            'documents',
            'postings',
            'currentPosting',
            'leaveApplications',
            'passApplications',
            'courses',
            'dutyRosterAssignments.roster',
            'dutyRosterAsOIC',
            'dutyRosterAs2IC',
            'queries',
            'investigations',
            'emoluments',
            'promotions',
            'quarters',
            'accountChangeRequests',
            'trainingResult',
        ])->findOrFail($id);

        // Check for pending approvals
        $pendingLeaveApplications = LeaveApplication::where('officer_id', $officer->id)
            ->whereHas('approval', function ($q) {
                $q->whereNull('approved_at')->whereNull('rejected_at');
            })
            ->exists();

        $pendingPassApplications = PassApplication::where('officer_id', $officer->id)
            ->whereHas('approval', function ($q) {
                $q->whereNull('approved_at')->whereNull('rejected_at');
            })
            ->exists();

        // Check if officer is active OIC or 2IC
        $isActiveOIC = DutyRoster::where('oic_officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->where('roster_period_start', '<=', now())
            ->where('roster_period_end', '>=', now())
            ->exists();

        $isActive2IC = DutyRoster::where('second_in_command_officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->where('roster_period_start', '<=', now())
            ->where('roster_period_end', '>=', now())
            ->exists();

        $canDelete = !$pendingLeaveApplications && !$pendingPassApplications && !$isActiveOIC && !$isActive2IC;
        $deletionBlockers = [];

        if ($pendingLeaveApplications) {
            $deletionBlockers[] = 'Officer has pending leave applications';
        }
        if ($pendingPassApplications) {
            $deletionBlockers[] = 'Officer has pending pass applications';
        }
        if ($isActiveOIC) {
            $deletionBlockers[] = 'Officer is currently an active OIC (Officer in Charge)';
        }
        if ($isActive2IC) {
            $deletionBlockers[] = 'Officer is currently an active 2IC (Second in Command)';
        }

        // Get current unit from most recent approved InternalStaffOrder
        $currentUnit = null;
        $latestInternalStaffOrder = InternalStaffOrder::where('officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->orderBy('approved_at', 'desc')
            ->first();
        
        if ($latestInternalStaffOrder && $latestInternalStaffOrder->target_unit) {
            $currentUnit = $latestInternalStaffOrder->target_unit;
        } else {
            // Fallback to officer's unit field if no approved internal staff order
            $currentUnit = $officer->unit;
        }

        return view('dashboards.officer-deletion.show', compact(
            'officer',
            'canDelete',
            'deletionBlockers',
            'currentUnit'
        ));
    }

    /**
     * Delete officer and all associated records
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAccess();

        $request->validate([
            'confirmation_text' => 'required|in:DELETE',
            'reason' => 'nullable|string|max:1000',
        ]);

        $officer = Officer::with(['user', 'presentStation'])->findOrFail($id);

        // Re-check safeguards
        $pendingLeaveApplications = LeaveApplication::where('officer_id', $officer->id)
            ->whereHas('approval', function ($q) {
                $q->whereNull('approved_at')->whereNull('rejected_at');
            })
            ->exists();

        $pendingPassApplications = PassApplication::where('officer_id', $officer->id)
            ->whereHas('approval', function ($q) {
                $q->whereNull('approved_at')->whereNull('rejected_at');
            })
            ->exists();

        $isActiveOIC = DutyRoster::where('oic_officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->where('roster_period_start', '<=', now())
            ->where('roster_period_end', '>=', now())
            ->exists();

        $isActive2IC = DutyRoster::where('second_in_command_officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->where('roster_period_start', '<=', now())
            ->where('roster_period_end', '>=', now())
            ->exists();

        if ($pendingLeaveApplications || $pendingPassApplications || $isActiveOIC || $isActive2IC) {
            return redirect()->back()
                ->with('error', 'Cannot delete officer. Please resolve pending approvals or active OIC/2IC assignments first.');
        }

        $deletedBy = Auth::user();
        $appointmentNumber = $officer->appointment_number;
        $officerName = "{$officer->initials} {$officer->surname}";
        $reason = $request->input('reason');

        DB::beginTransaction();
        try {
            // Store audit information before deletion
            $auditData = [
                'appointment_number' => $appointmentNumber,
                'officer_name' => $officerName,
                'service_number' => $officer->service_number,
                'rank' => $officer->substantive_rank,
                'command' => $officer->presentStation ? $officer->presentStation->name : null,
                'deleted_by_user_id' => $deletedBy->id,
                'deleted_by_name' => $deletedBy->officer ? "{$deletedBy->officer->initials} {$deletedBy->officer->surname}" : $deletedBy->email,
                'deleted_by_role' => $deletedBy->hasRole('HRD') ? 'HRD' : 'Establishment',
                'reason' => $reason,
                'deleted_at' => now(),
            ];

            // Delete all associated records
            // 1. Roster assignments
            RosterAssignment::where('officer_id', $officer->id)->delete();

            // 2. Duty rosters (remove OIC/2IC references)
            DutyRoster::where('oic_officer_id', $officer->id)
                ->update(['oic_officer_id' => null]);
            DutyRoster::where('second_in_command_officer_id', $officer->id)
                ->update(['second_in_command_officer_id' => null]);

            // 3. APER forms
            APERForm::where('officer_id', $officer->id)->delete();

            // 4. Leave applications and approvals
            $leaveApplicationIds = LeaveApplication::where('officer_id', $officer->id)->pluck('id');
            \App\Models\LeaveApproval::whereIn('leave_application_id', $leaveApplicationIds)->delete();
            LeaveApplication::where('officer_id', $officer->id)->delete();

            // 5. Pass applications and approvals
            $passApplicationIds = PassApplication::where('officer_id', $officer->id)->pluck('id');
            \App\Models\PassApproval::whereIn('pass_application_id', $passApplicationIds)->delete();
            PassApplication::where('officer_id', $officer->id)->delete();

            // 6. Staff orders
            StaffOrder::where('officer_id', $officer->id)->delete();

            // 7. Internal staff orders
            InternalStaffOrder::where('officer_id', $officer->id)->delete();

            // 8. Officer postings
            OfficerPosting::where('officer_id', $officer->id)->delete();

            // 9. Course nominations
            OfficerCourse::where('officer_id', $officer->id)->delete();

            // 10. Queries
            \App\Models\Query::where('officer_id', $officer->id)->delete();

            // 11. Investigations
            \App\Models\Investigation::where('officer_id', $officer->id)->delete();

            // 12. Emoluments
            \App\Models\Emolument::where('officer_id', $officer->id)->delete();

            // 13. Promotions
            \App\Models\Promotion::where('officer_id', $officer->id)->delete();

            // 14. Quarters
            \App\Models\OfficerQuarter::where('officer_id', $officer->id)->delete();
            \App\Models\QuarterRequest::where('officer_id', $officer->id)->delete();

            // 15. Account change requests
            \App\Models\AccountChangeRequest::where('officer_id', $officer->id)->delete();

            // 16. Next of kin change requests
            \App\Models\NextOfKinChangeRequest::where('officer_id', $officer->id)->delete();

            // 17. Next of kin
            \App\Models\NextOfKin::where('officer_id', $officer->id)->delete();

            // 18. Documents
            \App\Models\OfficerDocument::where('officer_id', $officer->id)->delete();

            // 19. Training results
            \App\Models\TrainingResult::where('officer_id', $officer->id)->delete();

            // 20. Notifications (for the user)
            if ($officer->user) {
                Notification::where('user_id', $officer->user->id)->delete();
            }

            // 21. User roles (remove all role assignments)
            if ($officer->user) {
                DB::table('user_roles')->where('user_id', $officer->user->id)->delete();
            }

            // 22. User account
            if ($officer->user) {
                $userId = $officer->user->id;
                $officer->user->delete();
            }

            // 23. Finally, delete the officer record
            $officer->delete();

            // Create audit log
            OfficerDeletionAuditLog::create($auditData);

            DB::commit();

            // Send notifications
            $this->sendDeletionNotifications($deletedBy, $appointmentNumber, $officerName, $reason);

            Log::warning('Officer permanently deleted', [
                'officer_id' => $id,
                'appointment_number' => $appointmentNumber,
                'officer_name' => $officerName,
                'deleted_by' => $deletedBy->id,
                'reason' => $reason,
            ]);

            // Determine the correct route based on user role
            $redirectRoute = $deletedBy->hasRole('HRD') 
                ? 'hrd.officers.delete.index' 
                : 'establishment.officers.delete.index';
            
            return redirect()->route($redirectRoute)
                ->with('success', "Officer {$appointmentNumber} ({$officerName}) has been permanently deleted from the system.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting officer', [
                'officer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while deleting the officer. Please try again or contact support.');
        }
    }

    /**
     * Send notifications about officer deletion
     */
    private function sendDeletionNotifications($deletedBy, $appointmentNumber, $officerName, $reason)
    {
        try {
            // Notify the user who performed the deletion
            $this->notificationService->notify(
                $deletedBy,
                'officer_deleted',
                'Officer Deletion Confirmed',
                "Officer {$appointmentNumber} ({$officerName}) has been permanently deleted from the system." . ($reason ? " Reason: {$reason}" : ''),
                'officer_deletion',
                null,
                true
            );

            // Notify all HRD users
            $hrdUsers = User::whereHas('roles', function ($q) {
                $q->where('name', 'HRD')->where('user_roles.is_active', true);
            })->where('is_active', true)->where('id', '!=', $deletedBy->id)->get();

            foreach ($hrdUsers as $user) {
                $this->notificationService->notify(
                    $user,
                    'officer_deleted',
                    'Officer Deletion Notification',
                    "Officer {$appointmentNumber} ({$officerName}) has been permanently deleted by {$deletedBy->email}." . ($reason ? " Reason: {$reason}" : ''),
                    'officer_deletion',
                    null,
                    true
                );
            }

            // Notify all Establishment users
            $establishmentUsers = User::whereHas('roles', function ($q) {
                $q->where('name', 'Establishment')->where('user_roles.is_active', true);
            })->where('is_active', true)->where('id', '!=', $deletedBy->id)->get();

            foreach ($establishmentUsers as $user) {
                $this->notificationService->notify(
                    $user,
                    'officer_deleted',
                    'Officer Deletion Notification',
                    "Officer {$appointmentNumber} ({$officerName}) has been permanently deleted by {$deletedBy->email}." . ($reason ? " Reason: {$reason}" : ''),
                    'officer_deletion',
                    null,
                    true
                );
            }

        } catch (\Exception $e) {
            Log::error('Error sending deletion notifications', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

