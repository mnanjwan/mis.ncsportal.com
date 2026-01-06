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
use App\Models\PromotionEligibilityList;
use App\Models\RetirementList;
use App\Models\DutyRoster;
use App\Models\RosterAssignment;
use App\Models\MovementOrder;
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
        
        // Get HRD officer (authorizing officer) - prioritize authenticated user
        $hrdOfficer = null;
        
        // First: Use authenticated user's officer details if available
        $currentUser = Auth::user();
        if ($currentUser) {
            if (!$currentUser->relationLoaded('officer')) {
                $currentUser->load('officer');
            }
            if ($currentUser->officer) {
                $hrdOfficer = $currentUser->officer;
            }
        }
        
        // Second: Get the officer who created the order (HRD officer)
        if (!$hrdOfficer && $staffOrder->createdBy && $staffOrder->createdBy->officer) {
            $hrdOfficer = $staffOrder->createdBy->officer;
        }
        
        // Third: Get HRD user from role
        if (!$hrdOfficer) {
            $hrdOfficer = $this->getHrdOfficer();
        }
        
        return view('prints.staff-order', compact('staffOrder', 'officer', 'fromCommand', 'toCommand', 'hrdOfficer'));
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
     * Print Promotion Eligibility List
     */
    public function promotionEligibilityList($id)
    {
        $list = PromotionEligibilityList::with(['items.officer', 'generatedBy'])
            ->findOrFail($id);
        
        // Filter out officers who are now ineligible (indicted/interdicted, suspended, dismissed, or under investigation)
        // This ensures that even if a list was created before status changes, ineligible officers won't appear
        $filteredItems = $list->items->filter(function($item) {
            if (!$item->officer) {
                return false; // Remove items with no officer
            }
            
            $officer = $item->officer;
            
            // Exclude officers who are indicted/interdicted, suspended, dismissed, or under investigation
            if ($officer->interdicted || 
                $officer->suspended || 
                $officer->ongoing_investigation || 
                $officer->dismissed ||
                $officer->is_deceased) {
                return false;
            }
            
            return true;
        });
        
        // Get all items with their officers
        $items = $filteredItems->map(function($item) {
            $officer = $item->officer;
            
            // Get unit from current active roster (as OIC/2IC or from assignment)
            $unit = null;
            
            // Check if officer is OIC or 2IC of an active roster
            $activeRosterAsOIC = DutyRoster::where('oic_officer_id', $officer->id)
                ->where('status', 'APPROVED')
                ->where('roster_period_start', '<=', now())
                ->where('roster_period_end', '>=', now())
                ->first();
            
            if ($activeRosterAsOIC && $activeRosterAsOIC->unit) {
                $unit = $activeRosterAsOIC->unit;
            } else {
                // Check if officer is 2IC of an active roster
                $activeRosterAs2IC = DutyRoster::where('second_in_command_officer_id', $officer->id)
                    ->where('status', 'APPROVED')
                    ->where('roster_period_start', '<=', now())
                    ->where('roster_period_end', '>=', now())
                    ->first();
                
                if ($activeRosterAs2IC && $activeRosterAs2IC->unit) {
                    $unit = $activeRosterAs2IC->unit;
                } else {
                    // Check if officer has a roster assignment with an active roster
                    $currentRosterAssignment = RosterAssignment::where('officer_id', $officer->id)
                        ->whereHas('roster', function ($query) {
                            $query->where('status', 'APPROVED')
                                  ->where('roster_period_start', '<=', now())
                                  ->where('roster_period_end', '>=', now());
                        })
                        ->with(['roster:id,unit'])
                        ->latest('duty_date')
                        ->first();
                    
                    if ($currentRosterAssignment && $currentRosterAssignment->roster && $currentRosterAssignment->roster->unit) {
                        $unit = $currentRosterAssignment->roster->unit;
                    }
                }
            }
            
            return [
                'serial_number' => $item->serial_number,
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $item->current_rank ?? ($officer->substantive_rank ?? 'N/A'),
                'initials' => $officer->initials ?? '',
                'name' => $officer->surname ?? '',
                'unit' => $unit,
                'state' => $item->state ?? ($officer->state_of_origin ?? 'N/A'),
                'date_of_birth' => $item->date_of_birth ?? ($officer->date_of_birth ?? null),
                'date_of_first_appointment' => $item->date_of_first_appointment ?? ($officer->date_of_first_appointment ?? null),
            ];
        })->toArray();
        
        // Sort by rank in descending order: CGC, DCG, ACG, CC, DC, AC, CSC, SC, DSC, ASC I, ASC II, IC, AIC, CA I, CA II, CA III
        $rankOrder = [
            'CGC' => 1,
            'DCG' => 2,
            'ACG' => 3,
            'CC' => 4,
            'DC' => 5,
            'AC' => 6,
            'CSC' => 7,
            'SC' => 8,
            'DSC' => 9,
            'ASC I' => 10,
            'ASC II' => 11,
            'IC' => 12,
            'AIC' => 13,
            'CA I' => 14,
            'CA II' => 15,
            'CA III' => 16,
        ];
        
        usort($items, function($a, $b) use ($rankOrder) {
            $rankA = $this->normalizeRankForSorting($a['rank'], $rankOrder);
            $rankB = $this->normalizeRankForSorting($b['rank'], $rankOrder);
            
            // If ranks are equal, maintain original order
            if ($rankA === $rankB) {
                return 0;
            }
            
            // Sort in descending order (lower number = higher rank)
            return $rankA <=> $rankB;
        });
        
        // Reassign serial numbers after sorting
        foreach ($items as $index => &$item) {
            $item['serial_number'] = $index + 1;
        }
        unset($item);
        
        return view('prints.promotion-eligibility-list', compact('list', 'items'));
    }

    /**
     * Print Retirement List
     */
    public function printRetirementList($id)
    {
        $list = RetirementList::with(['items.officer', 'generatedBy'])
            ->findOrFail($id);
        
        // Filter out officers who are now ineligible (indicted/interdicted, suspended, dismissed, or under investigation)
        $filteredItems = $list->items->filter(function($item) {
            if (!$item->officer) {
                return false;
            }
            
            $officer = $item->officer;
            
            if ($officer->interdicted || 
                $officer->suspended || 
                $officer->ongoing_investigation || 
                $officer->dismissed ||
                $officer->is_deceased) {
                return false;
            }
            
            return true;
        });
        
        // Get all items with their data
        $items = $filteredItems->map(function($item) {
            $officer = $item->officer;
            
            return [
                'serial_number' => $item->serial_number,
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $item->rank ?? ($officer->substantive_rank ?? 'N/A'),
                'initials' => $item->initials ?? ($officer->initials ?? ''),
                'name' => $item->name ?? ($officer->surname ?? ''),
                'retirement_condition' => $item->retirement_condition ?? 'N/A',
                'date_of_birth' => $item->date_of_birth ?? ($officer->date_of_birth ?? null),
                'date_of_first_appointment' => $item->date_of_first_appointment ?? ($officer->date_of_first_appointment ?? null),
                'date_of_pre_retirement_leave' => $item->date_of_pre_retirement_leave ?? null,
                'retirement_date' => $item->retirement_date ?? null,
            ];
        })->toArray();
        
        // Sort by rank in descending order: CGC, DCG, ACG, CC, DC, AC, CSC, SC, DSC, ASC I, ASC II, IC, AIC, CA I, CA II, CA III
        $rankOrder = [
            'CGC' => 1,
            'DCG' => 2,
            'ACG' => 3,
            'CC' => 4,
            'DC' => 5,
            'AC' => 6,
            'CSC' => 7,
            'SC' => 8,
            'DSC' => 9,
            'ASC I' => 10,
            'ASC II' => 11,
            'IC' => 12,
            'AIC' => 13,
            'CA I' => 14,
            'CA II' => 15,
            'CA III' => 16,
        ];
        
        usort($items, function($a, $b) use ($rankOrder) {
            $rankA = $this->normalizeRankForSorting($a['rank'], $rankOrder);
            $rankB = $this->normalizeRankForSorting($b['rank'], $rankOrder);
            
            // If ranks are equal, maintain original order
            if ($rankA === $rankB) {
                return 0;
            }
            
            // Sort in descending order (lower number = higher rank)
            return $rankA <=> $rankB;
        });
        
        // Reassign serial numbers after sorting
        foreach ($items as $index => &$item) {
            $item['serial_number'] = $index + 1;
        }
        unset($item);
        
        return view('prints.retirement-list-print', compact('list', 'items'));
    }

    /**
     * Normalize rank for sorting (extract abbreviation from full rank names)
     */
    private function normalizeRankForSorting($rank, $rankOrder)
    {
        if (empty($rank)) {
            return 999; // Put empty ranks at the end
        }
        
        // Standard rank abbreviations
        $standardRanks = array_keys($rankOrder);
        
        // If already an abbreviation, return its order
        if (isset($rankOrder[$rank])) {
            return $rankOrder[$rank];
        }
        
        // Try to extract abbreviation from parentheses
        if (preg_match('/\(([A-Z\s]+)\)/', $rank, $matches)) {
            $abbr = trim($matches[1]);
            if (isset($rankOrder[$abbr])) {
                return $rankOrder[$abbr];
            }
        }
        
        // Try partial matching
        foreach ($rankOrder as $abbr => $order) {
            if (stripos($rank, $abbr) !== false) {
                return $order;
            }
        }
        
        // If no match found, put at end
        return 999;
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

    /**
     * Get HRD Officer (for authorizing staff orders)
     */
    private function getHrdOfficer()
    {
        // Try to find a user with HRD role
        $hrdRole = Role::where('name', 'HRD')->first();
        if ($hrdRole) {
            $hrdUser = User::whereHas('roles', function($query) use ($hrdRole) {
                $query->where('roles.id', $hrdRole->id)
                      ->where('user_roles.is_active', true);
            })
            ->where('is_active', true)
            ->with('officer')
            ->first();
            
            if ($hrdUser && $hrdUser->officer) {
                return $hrdUser->officer;
            }
        }
        
        // Fallback: Try DC Admin role
        $dcAdminRole = Role::where('name', 'DC Admin')->first();
        if ($dcAdminRole) {
            $dcAdminUser = User::whereHas('roles', function($query) use ($dcAdminRole) {
                $query->where('roles.id', $dcAdminRole->id)
                      ->where('user_roles.is_active', true);
            })
            ->where('is_active', true)
            ->with('officer')
            ->first();
            
            if ($dcAdminUser && $dcAdminUser->officer) {
                return $dcAdminUser->officer;
            }
        }
        
        // Final fallback: Get any active user with officer record (prefer admin users)
        $adminUser = User::where('is_active', true)
            ->with('officer')
            ->whereHas('officer')
            ->first();
        
        return $adminUser ? $adminUser->officer : null;
    }

    /**
     * Print Single Duty Roster
     */
    public function printRoster($id)
    {
        $roster = DutyRoster::with([
            'command',
            'preparedBy.officer',
            'approvedBy.user.roles',
            'oicOfficer',
            'secondInCommandOfficer',
            'assignments.officer'
        ])->findOrFail($id);

        // Build deployment list (OIC, 2IC, and assigned officers)
        $deployments = collect();

        // Add OIC if exists
        if ($roster->oicOfficer) {
            $deployments->push([
                'service_number' => $roster->oicOfficer->service_number ?? 'N/A',
                'rank' => $roster->oicOfficer->substantive_rank ?? 'N/A',
                'name' => trim(($roster->oicOfficer->initials ?? '') . ' ' . ($roster->oicOfficer->surname ?? '')),
                'role' => 'O/C',
                'unit' => $roster->unit ?? 'N/A',
            ]);
        }

        // Add 2IC if exists
        if ($roster->secondInCommandOfficer) {
            $deployments->push([
                'service_number' => $roster->secondInCommandOfficer->service_number ?? 'N/A',
                'rank' => $roster->secondInCommandOfficer->substantive_rank ?? 'N/A',
                'name' => trim(($roster->secondInCommandOfficer->initials ?? '') . ' ' . ($roster->secondInCommandOfficer->surname ?? '')),
                'role' => '2iC',
                'unit' => $roster->unit ?? 'N/A',
            ]);
        }

        // Add assigned officers (excluding OIC and 2IC)
        foreach ($roster->assignments as $assignment) {
            $officer = $assignment->officer;
            if ($officer && 
                $officer->id !== $roster->oic_officer_id && 
                $officer->id !== $roster->second_in_command_officer_id) {
                $deployments->push([
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A',
                    'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')),
                    'role' => '',
                    'unit' => $roster->unit ?? 'N/A',
                ]);
            }
        }

        // Get prepared by Staff Officer
        $staffOfficer = null;
        if ($roster->preparedBy && $roster->preparedBy->officer) {
            $staffOfficer = $roster->preparedBy->officer;
        }

        // Get approver
        $approver = null;
        $approverRole = null;
        if ($roster->approvedBy) {
            $approver = $roster->approvedBy;
            // Check if approver's user has DC Admin role
            if ($approver->user && $approver->user->hasRole('DC Admin')) {
                $approverRole = 'DC Admin';
            } else {
                $approverRole = 'Comptroller';
            }
        }

        $deploymentDate = $roster->roster_period_start ? $roster->roster_period_start->format('d M Y') : now()->format('d M Y');

        return view('prints.duty-roster', compact(
            'roster',
            'deployments',
            'staffOfficer',
            'approver',
            'approverRole',
            'deploymentDate'
        ));
    }

    /**
     * Print All Active Rosters for a Month
     */
    public function printAllRosters(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? Command::find($commandId) : null;

        // Get month from request or use current month
        $month = $request->get('month', date('Y-m'));

        // Get all APPROVED rosters for this command and month
        $rosters = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->whereYear('roster_period_start', date('Y', strtotime($month . '-01')))
            ->whereMonth('roster_period_start', date('m', strtotime($month . '-01')))
            ->with([
                'command',
                'preparedBy.officer',
                'approvedBy.user.roles',
                'oicOfficer',
                'secondInCommandOfficer',
                'assignments.officer'
            ])
            ->orderBy('unit')
            ->orderBy('roster_period_start')
            ->get();

        if ($rosters->isEmpty()) {
            return redirect()->route('staff-officer.roster', ['month' => $month])
                ->with('error', 'No approved rosters found for the selected month.');
        }

        // Process each roster into deployments
        $allDeployments = [];
        foreach ($rosters as $roster) {
            $rosterDeployments = collect();

            // Add OIC
            if ($roster->oicOfficer) {
                $rosterDeployments->push([
                    'service_number' => $roster->oicOfficer->service_number ?? 'N/A',
                    'rank' => $roster->oicOfficer->substantive_rank ?? 'N/A',
                    'name' => trim(($roster->oicOfficer->initials ?? '') . ' ' . ($roster->oicOfficer->surname ?? '')),
                    'role' => 'O/C',
                    'unit' => $roster->unit ?? 'N/A',
                ]);
            }

            // Add 2IC
            if ($roster->secondInCommandOfficer) {
                $rosterDeployments->push([
                    'service_number' => $roster->secondInCommandOfficer->service_number ?? 'N/A',
                    'rank' => $roster->secondInCommandOfficer->substantive_rank ?? 'N/A',
                    'name' => trim(($roster->secondInCommandOfficer->initials ?? '') . ' ' . ($roster->secondInCommandOfficer->surname ?? '')),
                    'role' => '2iC',
                    'unit' => $roster->unit ?? 'N/A',
                ]);
            }

            // Add assigned officers
            foreach ($roster->assignments as $assignment) {
                $officer = $assignment->officer;
                if ($officer && 
                    $officer->id !== $roster->oic_officer_id && 
                    $officer->id !== $roster->second_in_command_officer_id) {
                    $rosterDeployments->push([
                        'service_number' => $officer->service_number ?? 'N/A',
                        'rank' => $officer->substantive_rank ?? 'N/A',
                        'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')),
                        'role' => '',
                        'unit' => $roster->unit ?? 'N/A',
                    ]);
                }
            }

            $allDeployments[] = [
                'roster' => $roster,
                'deployments' => $rosterDeployments->toArray(),
            ];
        }

        // Get Staff Officer (from first roster's preparedBy)
        $staffOfficer = null;
        if ($rosters->first() && $rosters->first()->preparedBy && $rosters->first()->preparedBy->officer) {
            $staffOfficer = $rosters->first()->preparedBy->officer;
        }

        // Get approver from first roster (assuming all approved by same person)
        $approver = null;
        $approverRole = null;
        if ($rosters->first() && $rosters->first()->approvedBy) {
            $approver = $rosters->first()->approvedBy;
            if ($approver->user && $approver->user->hasRole('DC Admin')) {
                $approverRole = 'DC Admin';
            } else {
                $approverRole = 'Comptroller';
            }
        }

        $deploymentDate = date('d M Y', strtotime($month . '-01'));

        return view('prints.duty-roster-all', compact(
            'rosters',
            'allDeployments',
            'command',
            'staffOfficer',
            'approver',
            'approverRole',
            'deploymentDate',
            'month'
        ));
    }

    /**
     * Print Movement Order
     */
    public function movementOrder($id)
    {
        $order = MovementOrder::with([
            'postings.officer.presentStation',
            'postings.command',
            'createdBy'
        ])->findOrFail($id);

        // Build items list with from/to commands
        $items = collect();
        
        foreach ($order->postings as $posting) {
            if (!$posting->officer) {
                continue;
            }

            $officer = $posting->officer;
            $fromCommand = null;
            $toCommand = $posting->command;

            // Get from command (present posting)
            if ($posting->officer->presentStation) {
                $fromCommand = $posting->officer->presentStation;
            } else {
                // Try to find previous posting
                $previousPosting = \App\Models\OfficerPosting::where('officer_id', $officer->id)
                    ->where('id', '<', $posting->id)
                    ->with('command')
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($previousPosting && $previousPosting->command) {
                    $fromCommand = $previousPosting->command;
                }
            }

            // Combine initials and surname for full name
            $fullName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
            
            $items->push([
                'serial_number' => 0, // Will be reassigned after sorting
                'rank' => $officer->substantive_rank ?? 'N/A',
                'name' => $fullName ?: 'N/A',
                'previous_posting' => $fromCommand ? $fromCommand->name : 'N/A',
                'new_posting' => $toCommand ? $toCommand->name : 'N/A',
            ]);
        }

        // Group items by destination command (new_posting)
        $groupedByCommand = $items->groupBy('new_posting');
        
        // Sort by rank (descending: CGC to CA III)
        $rankOrder = [
            'CGC' => 1, 'DCG' => 2, 'ACG' => 3, 'CC' => 4, 'DC' => 5, 'AC' => 6,
            'CSC' => 7, 'SC' => 8, 'DSC' => 9, 'ASC I' => 10, 'ASC II' => 11,
            'IC' => 12, 'AIC' => 13, 'CA I' => 14, 'CA II' => 15, 'CA III' => 16,
        ];

        // Process each command group
        $commandGroups = [];
        foreach ($groupedByCommand as $commandName => $commandItems) {
            $itemsArray = $commandItems->toArray();
            
            // Sort by rank within each command group
            usort($itemsArray, function($a, $b) use ($rankOrder) {
                $rankA = $this->normalizeRankForSorting($a['rank'], $rankOrder);
                $rankB = $this->normalizeRankForSorting($b['rank'], $rankOrder);
                return $rankA <=> $rankB;
            });

            // Reassign serial numbers after sorting (starting from 1 for each command)
            foreach ($itemsArray as $index => &$item) {
                $item['serial_number'] = $index + 1;
            }
            unset($item);

            $commandGroups[] = [
                'command_name' => $commandName,
                'items' => $itemsArray
            ];
        }

        return view('prints.movement-order', compact('order', 'commandGroups'));
    }
}

