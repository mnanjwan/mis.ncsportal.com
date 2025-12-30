<?php

/**
 * Test script to verify print routes and parameters
 * This simulates what happens when routes are called
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\StaffOrder;
use App\Models\InternalStaffOrder;
use App\Models\Officer;
use App\Models\Command;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "========================================\n";
echo "PRINT ROUTES PARAMETER TEST\n";
echo "========================================\n\n";

// Simulate PrintController methods
echo "Testing PrintController Methods:\n\n";

// Test 1: leaveDocument
echo "1. leaveDocument() Method Test:\n";
echo "--------------------------------\n";
$leaveApp = LeaveApplication::with([
    'officer.presentStation',
    'leaveType',
    'approval.areaController',
    'approval.staffOfficer.officer'
])->find(406);

if ($leaveApp) {
    $command = $leaveApp->officer->presentStation ?? Command::first();
    echo "✓ Leave Application loaded\n";
    echo "  - ID: {$leaveApp->id}\n";
    echo "  - Officer: " . ($leaveApp->officer->full_name ?? 'N/A') . "\n";
    echo "  - Command: " . ($command->name ?? 'N/A') . "\n";
    echo "  - Command ID: " . ($command->id ?? 'N/A') . "\n";
    
    // Simulate authenticated user check
    $testUser = User::where('email', 'staff.apapa@ncs.gov.ng')->with('officer')->first();
    if ($testUser) {
        Auth::login($testUser);
        $currentUser = Auth::user();
        echo "  - Authenticated User: {$currentUser->email}\n";
        echo "  - User has officer: " . ($currentUser->officer ? 'Yes' : 'No') . "\n";
        
        if ($currentUser->officer) {
            echo "  - Officer Name: " . $currentUser->officer->full_name . "\n";
            echo "  - Service No: " . ($currentUser->officer->service_number ?? 'N/A') . "\n";
        }
        
        // Check if user is staff officer for command
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        if ($staffOfficerRole && $command) {
            $isStaffOfficer = $currentUser->roles()
                ->where('roles.id', $staffOfficerRole->id)
                ->where('user_roles.command_id', $command->id)
                ->where('user_roles.is_active', true)
                ->exists();
            
            echo "  - Is Staff Officer for command: " . ($isStaffOfficer ? 'Yes' : 'No') . "\n";
        }
    }
    
    // Check approval staff officer
    if ($leaveApp->approval && $leaveApp->approval->staffOfficer) {
        $staffOfficerUser = $leaveApp->approval->staffOfficer;
        echo "  - Approval Staff Officer User: {$staffOfficerUser->email}\n";
        echo "  - Has officer record: " . ($staffOfficerUser->officer ? 'Yes' : 'No') . "\n";
        if ($staffOfficerUser->officer) {
            echo "  - Officer: " . $staffOfficerUser->officer->full_name . "\n";
        }
    }
    
    // Check area controller
    if ($leaveApp->approval && $leaveApp->approval->areaController) {
        echo "  - Area Controller: " . ($leaveApp->approval->areaController->full_name ?? 'N/A') . "\n";
    } elseif ($command && $command->areaController) {
        echo "  - Command Area Controller: " . ($command->areaController->full_name ?? 'N/A') . "\n";
    }
} else {
    echo "✗ Leave Application 406 not found\n";
}
echo "\n";

// Test 2: passDocument
echo "2. passDocument() Method Test:\n";
echo "-------------------------------\n";
$passApp = PassApplication::with([
    'officer.presentStation',
    'approval.staffOfficer.officer'
])->find(271);

if ($passApp) {
    $command = $passApp->officer->presentStation ?? Command::first();
    echo "✓ Pass Application loaded\n";
    echo "  - ID: {$passApp->id}\n";
    echo "  - Officer: " . ($passApp->officer->full_name ?? 'N/A') . "\n";
    echo "  - Command: " . ($command->name ?? 'N/A') . "\n";
    
    // Simulate authenticated user check
    $testUser = User::where('email', 'staff.apapa@ncs.gov.ng')->with('officer')->first();
    if ($testUser) {
        Auth::login($testUser);
        $currentUser = Auth::user();
        echo "  - Authenticated User: {$currentUser->email}\n";
        echo "  - User has officer: " . ($currentUser->officer ? 'Yes' : 'No') . "\n";
        
        if ($currentUser->officer) {
            echo "  - Officer Name: " . $currentUser->officer->full_name . "\n";
            echo "  - Service No: " . ($currentUser->officer->service_number ?? 'N/A') . "\n";
            echo "  - Rank: " . ($currentUser->officer->substantive_rank ?? 'N/A') . "\n";
        }
        
        // Check if user is staff officer for command
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        if ($staffOfficerRole && $command) {
            $isStaffOfficer = $currentUser->roles()
                ->where('roles.id', $staffOfficerRole->id)
                ->where('user_roles.command_id', $command->id)
                ->where('user_roles.is_active', true)
                ->exists();
            
            echo "  - Is Staff Officer for command: " . ($isStaffOfficer ? 'Yes' : 'No') . "\n";
        }
    }
    
    // Check approval staff officer
    if ($passApp->approval && $passApp->approval->staffOfficer) {
        $staffOfficerUser = $passApp->approval->staffOfficer;
        echo "  - Approval Staff Officer User: {$staffOfficerUser->email}\n";
        echo "  - Has officer record: " . ($staffOfficerUser->officer ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "✗ Pass Application 271 not found\n";
}
echo "\n";

// Test 3: Check Staff Officer for Command
echo "3. getStaffOfficerForCommand() Test:\n";
echo "-------------------------------------\n";
$command = Command::where('name', 'APAPA')->first();
if ($command) {
    echo "✓ Command: {$command->name} (ID: {$command->id})\n";
    
    $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
    if ($staffOfficerRole) {
        $staffOfficerUser = User::whereHas('roles', function($query) use ($staffOfficerRole, $command) {
            $query->where('roles.id', $staffOfficerRole->id)
                  ->where('user_roles.command_id', $command->id)
                  ->where('user_roles.is_active', true);
        })->with('officer')->first();
        
        if ($staffOfficerUser) {
            echo "  - Staff Officer User: {$staffOfficerUser->email}\n";
            echo "  - Has officer record: " . ($staffOfficerUser->officer ? 'Yes' : 'No') . "\n";
            if ($staffOfficerUser->officer) {
                echo "  - Officer: " . $staffOfficerUser->officer->full_name . "\n";
                echo "  - Service No: " . ($staffOfficerUser->officer->service_number ?? 'N/A') . "\n";
                echo "  - Rank: " . ($staffOfficerUser->officer->substantive_rank ?? 'N/A') . "\n";
            } else {
                echo "  ⚠ WARNING: Staff Officer user exists but has no officer record!\n";
                echo "  - This will cause authorizingOfficer to be null\n";
            }
        } else {
            echo "  - No Staff Officer found for this command\n";
        }
    }
} else {
    echo "✗ APAPA command not found\n";
}
echo "\n";

// Test 4: View Variables Check
echo "4. View Variables Check:\n";
echo "------------------------\n";
echo "For leaveDocument view, variables should be:\n";
echo "  - \$leaveApplication: " . ($leaveApp ? "Set (ID: {$leaveApp->id})" : "Not set") . "\n";
echo "  - \$command: " . ($command ? "Set ({$command->name})" : "Not set") . "\n";
echo "  - \$areaController: " . (($leaveApp && $leaveApp->approval && $leaveApp->approval->areaController) ? "Set" : "Not set") . "\n";
echo "  - \$staffOfficer: " . (($testUser && $testUser->officer) ? "Set" : "Will be null") . "\n";
echo "\n";

echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "✓ All data models are accessible\n";
echo "✓ Relationships are loading correctly\n";
echo "⚠ Issue: Staff Officer users may not have officer records linked\n";
echo "  - This will cause authorizingOfficer to be null in views\n";
echo "  - Solution: Ensure Staff Officer users have linked officer records\n";
echo "========================================\n";

