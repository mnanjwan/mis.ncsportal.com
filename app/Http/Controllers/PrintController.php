<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\StaffOrder;
use App\Models\InternalStaffOrder;
use App\Models\Officer;
use App\Models\Command;
use App\Models\OfficerPosting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrintController extends Controller
{
    /**
     * Print Internal Staff Order
     */
    public function internalStaffOrder(Request $request, $id)
    {
        $internalStaffOrder = InternalStaffOrder::with([
            'command', 
            'officer',
            'preparedBy.officer'
        ])->findOrFail($id);
        
        $command = $internalStaffOrder->command;
        $officer = $internalStaffOrder->officer;
        $newPosting = $internalStaffOrder->target_unit ?? 'TO BE ASSIGNED';
        
        // Get the authenticated user who is the staff officer for this command
        $currentUser = Auth::user();
        $staffOfficer = null;
        
        // Check if current user is the staff officer for this command
        if ($currentUser && $internalStaffOrder->command_id) {
            $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
            if ($staffOfficerRole) {
                $isStaffOfficer = $currentUser->roles()
                    ->where('roles.id', $staffOfficerRole->id)
                    ->where('user_roles.command_id', $internalStaffOrder->command_id)
                    ->where('user_roles.is_active', true)
                    ->exists();
                
                if ($isStaffOfficer) {
                    if (!$currentUser->relationLoaded('officer')) {
                        $currentUser->load('officer');
                    }
                    $staffOfficer = $currentUser->officer;
                }
            }
        }
        
        // Fallback: get staff officer from command
        if (!$staffOfficer) {
            $staffOfficer = $this->getStaffOfficerForCommand($internalStaffOrder->command_id);
        }
        
        return view('prints.internal-staff-order', compact(
            'internalStaffOrder',
            'officer',
            'newPosting',
            'command',
            'staffOfficer'
        ));
    }

    /**
     * Print Staff Order (HRD Level)
     */
    public function staffOrder($id)
    {
        $staffOrder = StaffOrder::with(['officer', 'fromCommand', 'toCommand', 'createdBy.officer'])->findOrFail($id);
        
        $officer = $staffOrder->officer;
        $fromCommand = $staffOrder->fromCommand;
        $toCommand = $staffOrder->toCommand;
        
        // Get the officer who created the order (HRD officer)
        $createdByOfficer = null;
        if ($staffOrder->createdBy && $staffOrder->createdBy->officer) {
            $createdByOfficer = $staffOrder->createdBy->officer;
        }
        
        return view('prints.staff-order', compact('staffOrder', 'officer', 'fromCommand', 'toCommand', 'createdByOfficer'));
    }

    /**
     * Print Deployment List
     */
    public function deployment(Request $request)
    {
        $commandId = $request->get('command_id');
        $deploymentDate = $request->get('date', now());
        
        $command = null;
        $deployments = [];
        
        if ($commandId) {
            $command = Command::findOrFail($commandId);
            
            // Get recent postings for this command
            $postings = OfficerPosting::with(['officer'])
                ->where('command_id', $commandId)
                ->where('is_current', true)
                ->whereDate('posting_date', '>=', \Carbon\Carbon::parse($deploymentDate)->subDays(30))
                ->get();
            
            foreach ($postings as $posting) {
                $deployments[] = [
                    'service_number' => $posting->officer->service_number ?? 'N/A',
                    'rank' => $posting->officer->substantive_rank ?? 'N/A',
                    'name' => ($posting->officer->initials ?? '') . ' ' . ($posting->officer->surname ?? ''),
                    'new_posting' => $command->name ?? 'N/A',
                ];
            }
        }
        
        $totalPages = ceil(count($deployments) / 20); // Assuming 20 per page
        
        return view('prints.deployment', compact('command', 'deployments', 'deploymentDate', 'totalPages'));
    }

    /**
     * Print Leave Document (Official Format)
     */
    public function leaveDocument($id)
    {
        $leaveApplication = LeaveApplication::with([
            'officer.presentStation',
            'leaveType',
            'approval.areaController',
            'approval.staffOfficer.officer'
        ])->findOrFail($id);
        
        // Ensure officer is loaded
        if (!$leaveApplication->relationLoaded('officer')) {
            $leaveApplication->load('officer.presentStation');
        }
        
        // Get command - use officer's present station
        $command = $leaveApplication->officer->presentStation ?? Command::first();
        
        // Get Area Controller
        $areaController = null;
        if ($leaveApplication->approval && $leaveApplication->approval->areaController) {
            $areaController = $leaveApplication->approval->areaController;
        } else {
            // Try to get from command
            $areaController = $command->areaController ?? null;
        }
        
        // Get the authenticated user who is the staff officer for this command
        $currentUser = Auth::user();
        $staffOfficer = null;
        
        // First: Check if current user is the staff officer for this command
        if ($currentUser && $command) {
            $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
            if ($staffOfficerRole) {
                $isStaffOfficer = $currentUser->roles()
                    ->where('roles.id', $staffOfficerRole->id)
                    ->where('user_roles.command_id', $command->id)
                    ->where('user_roles.is_active', true)
                    ->exists();
                
                if ($isStaffOfficer) {
                    // Load the officer relationship if not already loaded
                    if (!$currentUser->relationLoaded('officer')) {
                        $currentUser->load('officer');
                    }
                    if ($currentUser->officer) {
                        $staffOfficer = $currentUser->officer;
                    }
                }
            }
        }
        
        // Second: Get staff officer from approval record
        if (!$staffOfficer) {
            if ($leaveApplication->approval) {
                $staffOfficerUser = $leaveApplication->approval->staffOfficer;
                if ($staffOfficerUser) {
                    // Load officer relationship if not loaded
                    if (!$staffOfficerUser->relationLoaded('officer')) {
                        $staffOfficerUser->load('officer');
                    }
                    $staffOfficer = $staffOfficerUser->officer ?? null;
                }
            }
        }
        
        // Third: Get staff officer from command using helper method
        if (!$staffOfficer && $command) {
            $staffOfficer = $this->getStaffOfficerForCommand($command->id);
        }
        
        // Fourth: Final fallback - use current user's officer if available (as last resort)
        if (!$staffOfficer && $currentUser) {
            if (!$currentUser->relationLoaded('officer')) {
                $currentUser->load('officer');
            }
            if ($currentUser->officer) {
                $staffOfficer = $currentUser->officer;
            }
        }
        
        // Debug: Log if still not found (remove in production)
        if (!$staffOfficer) {
            Log::warning('Leave Document: Could not find staff officer', [
                'leave_application_id' => $id,
                'command_id' => $command->id ?? null,
                'command_name' => $command->name ?? null,
                'user_id' => $currentUser->id ?? null,
                'has_approval' => $leaveApplication->approval ? 'yes' : 'no',
                'approval_staff_officer_id' => $leaveApplication->approval->staff_officer_id ?? null,
            ]);
        }
        
        return view('prints.leave-document', compact('leaveApplication', 'command', 'areaController', 'staffOfficer'));
    }

    /**
     * Print Pass Document (Official Format)
     */
    public function passDocument($id)
    {
        $passApplication = PassApplication::with([
            'officer.presentStation',
            'approval.staffOfficer.officer'
        ])->findOrFail($id);
        
        // Get command - use officer's present station
        $command = $passApplication->officer->presentStation ?? Command::first();
        
        // Get the authenticated user who is the staff officer for this command
        $currentUser = Auth::user();
        $authorizingOfficer = null;
        
        // First: Check if current user is the staff officer for this command
        if ($currentUser && $command) {
            $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
            if ($staffOfficerRole) {
                $isStaffOfficer = $currentUser->roles()
                    ->where('roles.id', $staffOfficerRole->id)
                    ->where('user_roles.command_id', $command->id)
                    ->where('user_roles.is_active', true)
                    ->exists();
                
                if ($isStaffOfficer) {
                    // Load the officer relationship if not already loaded
                    if (!$currentUser->relationLoaded('officer')) {
                        $currentUser->load('officer');
                    }
                    if ($currentUser->officer) {
                        $authorizingOfficer = $currentUser->officer;
                    }
                }
            }
        }
        
        // Second: Get staff officer from approval record
        if (!$authorizingOfficer) {
            if ($passApplication->approval) {
                $staffOfficerUser = $passApplication->approval->staffOfficer;
                if ($staffOfficerUser) {
                    // Load officer relationship if not loaded
                    if (!$staffOfficerUser->relationLoaded('officer')) {
                        $staffOfficerUser->load('officer');
                    }
                    $authorizingOfficer = $staffOfficerUser->officer ?? null;
                }
            }
        }
        
        // Third: Get staff officer from command using helper method
        if (!$authorizingOfficer && $command) {
            $authorizingOfficer = $this->getStaffOfficerForCommand($command->id);
        }
        
        // Fourth: Final fallback - use current user's officer if available (as last resort)
        if (!$authorizingOfficer && $currentUser) {
            if (!$currentUser->relationLoaded('officer')) {
                $currentUser->load('officer');
            }
            if ($currentUser->officer) {
                $authorizingOfficer = $currentUser->officer;
            }
        }
        
        // Debug: Log if still not found (remove in production)
        if (!$authorizingOfficer) {
            Log::warning('Pass Document: Could not find authorizing officer', [
                'pass_application_id' => $id,
                'command_id' => $command->id ?? null,
                'command_name' => $command->name ?? null,
                'user_id' => $currentUser->id ?? null,
                'has_approval' => $passApplication->approval ? 'yes' : 'no',
                'approval_staff_officer_id' => $passApplication->approval->staff_officer_id ?? null,
            ]);
        }
        
        return view('prints.pass-document', compact('passApplication', 'command', 'authorizingOfficer'));
    }

    /**
     * Print Retirement List
     */
    public function retirementList(Request $request)
    {
        $retirementYear = $request->get('year', now()->addYear()->format('Y'));
        
        // Get officers approaching retirement
        $officers = Officer::where('is_active', true)
            ->whereNotNull('date_of_birth')
            ->whereNotNull('date_of_first_appointment')
            ->get();
        
        $retirements = [];
        foreach ($officers as $officer) {
            $retirementDate = $officer->calculateRetirementDate();
            if ($retirementDate && $retirementDate->format('Y') == $retirementYear) {
                $retirements[] = [
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A',
                    'initials' => $officer->initials ?? '',
                    'surname' => $officer->surname ?? '',
                    'retirement_type' => $officer->getRetirementType() ?? 'N/A',
                    'date_of_birth' => $officer->date_of_birth,
                    'date_of_first_appointment' => $officer->date_of_first_appointment,
                    'date_of_promotion' => $officer->date_of_present_appointment,
                    'retirement_date' => $retirementDate,
                    'remarks' => '',
                ];
            }
        }
        
        // Sort by retirement date
        usort($retirements, function($a, $b) {
            return $a['retirement_date'] <=> $b['retirement_date'];
        });
        
        return view('prints.retirement-list', compact('retirements', 'retirementYear'));
    }

    /**
     * Print Accommodation Report
     */
    public function accommodationReport(Request $request)
    {
        $commandId = $request->get('command_id');
        $filters = [];
        
        $officers = Officer::where('quartered', true)
            ->with(['presentStation', 'currentQuarter']);
        
        if ($commandId) {
            $officers->where('present_station', $commandId);
            $command = Command::find($commandId);
            $filters['Command'] = $command->name ?? 'All';
        } else {
            $filters['Command'] = 'All';
        }
        
        $officers = $officers->get();
        
        $columns = [
            ['key' => 'service_number', 'label' => 'Service No'],
            ['key' => 'rank', 'label' => 'Rank'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'command', 'label' => 'Command'],
            ['key' => 'quarter_status', 'label' => 'Quartered Status'],
        ];
        
        $data = [];
        foreach ($officers as $officer) {
            $data[] = [
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $officer->substantive_rank ?? 'N/A',
                'name' => ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''),
                'command' => $officer->presentStation->name ?? 'N/A',
                'quarter_status' => $officer->quartered ? 'Yes' : 'No',
            ];
        }
        
        $summary = [
            'total_officers' => count($data),
            'total_quartered' => count($data),
        ];
        
        return view('prints.report-template', [
            'reportTitle' => 'ACCOMMODATION REPORT',
            'columns' => $columns,
            'data' => $data,
            'filters' => $filters,
            'summary' => $summary,
            'generatedBy' => Auth::user()->officer->full_name ?? Auth::user()->email,
        ]);
    }

    /**
     * Print Service Number Assignment Report
     */
    public function serviceNumberReport(Request $request)
    {
        $rank = $request->get('rank');
        $filters = [];
        
        $officers = Officer::whereNotNull('service_number')
            ->whereNotNull('appointment_number')
            ->with(['presentStation']);
        
        if ($rank) {
            $officers->where('substantive_rank', $rank);
            $filters['Rank'] = $rank;
        } else {
            $filters['Rank'] = 'All';
        }
        
        $officers = $officers->orderBy('service_number')->get();
        
        $columns = [
            ['key' => 'service_number', 'label' => 'Service No'],
            ['key' => 'appointment_number', 'label' => 'Appointment No'],
            ['key' => 'rank', 'label' => 'Rank'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'date_of_first_appointment', 'label' => 'DOFA'],
        ];
        
        $data = [];
        foreach ($officers as $officer) {
            $data[] = [
                'service_number' => $officer->service_number ?? 'N/A',
                'appointment_number' => $officer->appointment_number ?? 'N/A',
                'rank' => $officer->substantive_rank ?? 'N/A',
                'name' => ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''),
                'date_of_first_appointment' => $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('d/m/Y') : 'N/A',
            ];
        }
        
        $summary = [
            'total_officers' => count($data),
        ];
        
        return view('prints.report-template', [
            'reportTitle' => 'SERVICE NUMBER ASSIGNMENT REPORT',
            'columns' => $columns,
            'data' => $data,
            'filters' => $filters,
            'summary' => $summary,
            'generatedBy' => Auth::user()->officer->full_name ?? Auth::user()->email,
        ]);
    }

    /**
     * Print Validated Officers Report (for Accounts)
     */
    public function validatedOfficersReport(Request $request)
    {
        $officers = Officer::whereHas('emoluments', function($query) {
            $query->where('status', 'VALIDATED');
        })
        ->with(['emoluments' => function($query) {
            $query->where('status', 'VALIDATED')->latest()->first();
        }])
        ->get();
        
        $columns = [
            ['key' => 'service_number', 'label' => 'Service No'],
            ['key' => 'rank', 'label' => 'Rank'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'bank_name', 'label' => 'Bank'],
            ['key' => 'account_number', 'label' => 'Account Number'],
            ['key' => 'pfa_name', 'label' => 'PFA'],
            ['key' => 'rsa_number', 'label' => 'RSA'],
        ];
        
        $data = [];
        foreach ($officers as $officer) {
            $emolument = $officer->emoluments->first();
            $data[] = [
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $officer->substantive_rank ?? 'N/A',
                'name' => ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''),
                'bank_name' => $emolument->bank_name ?? $officer->bank_name ?? 'N/A',
                'account_number' => $emolument->bank_account_number ?? $officer->bank_account_number ?? 'N/A',
                'pfa_name' => $emolument->pfa_name ?? $officer->pfa_name ?? 'N/A',
                'rsa_number' => $emolument->rsa_number ?? $officer->rsa_number ?? 'N/A',
            ];
        }
        
        $summary = [
            'total_officers' => count($data),
        ];
        
        return view('prints.report-template', [
            'reportTitle' => 'VALIDATED OFFICERS REPORT',
            'columns' => $columns,
            'data' => $data,
            'summary' => $summary,
            'generatedBy' => Auth::user()->officer->full_name ?? Auth::user()->email,
        ]);
    }

    /**
     * Print Interdicted Officers Report
     */
    public function interdictedOfficersReport(Request $request)
    {
        $officers = Officer::where('interdicted', true)
            ->with(['presentStation'])
            ->get();
        
        $columns = [
            ['key' => 'service_number', 'label' => 'Service No'],
            ['key' => 'rank', 'label' => 'Rank'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'command', 'label' => 'Command'],
            ['key' => 'interdiction_date', 'label' => 'Interdiction Date'],
        ];
        
        $data = [];
        foreach ($officers as $officer) {
            $data[] = [
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $officer->substantive_rank ?? 'N/A',
                'name' => ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''),
                'command' => $officer->presentStation->name ?? 'N/A',
                'interdiction_date' => 'N/A', // Would need to track this separately
            ];
        }
        
        $summary = [
            'total_interdicted' => count($data),
        ];
        
        return view('prints.report-template', [
            'reportTitle' => 'INTERDICTED OFFICERS REPORT',
            'columns' => $columns,
            'data' => $data,
            'summary' => $summary,
            'generatedBy' => Auth::user()->officer->full_name ?? Auth::user()->email,
        ]);
    }

    /**
     * Get Staff Officer for a command
     */
    private function getStaffOfficerForCommand($commandId)
    {
        if (!$commandId) {
            return null;
        }
        
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        if (!$staffOfficerRole) {
            return null;
        }
        
        $staffOfficerUser = User::whereHas('roles', function($query) use ($staffOfficerRole, $commandId) {
            $query->where('roles.id', $staffOfficerRole->id)
                  ->where('user_roles.command_id', $commandId)
                  ->where('user_roles.is_active', true);
        })->first();
        
        return $staffOfficerUser ? $staffOfficerUser->officer : null;
    }
}

