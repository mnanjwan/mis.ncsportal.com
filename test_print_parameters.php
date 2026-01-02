<?php

/**
 * Test script to verify all print controller parameters are working
 * Run: php test_print_parameters.php
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
echo "PRINT CONTROLLER PARAMETERS TEST\n";
echo "========================================\n\n";

// Test 1: Leave Document
echo "1. Testing Leave Document Parameters:\n";
echo "--------------------------------------\n";
$leaveApp = LeaveApplication::with([
    'officer.presentStation',
    'leaveType',
    'approval.areaController',
    'approval.staffOfficer.officer'
])->first();

if ($leaveApp) {
    echo "✓ Leave Application ID: {$leaveApp->id}\n";
    echo "  - Officer: " . ($leaveApp->officer->full_name ?? 'N/A') . "\n";
    echo "  - Service No: " . ($leaveApp->officer->service_number ?? 'N/A') . "\n";
    echo "  - Command: " . ($leaveApp->officer->presentStation->name ?? 'N/A') . "\n";
    echo "  - Leave Type: " . ($leaveApp->leaveType->name ?? 'N/A') . "\n";
    echo "  - Status: {$leaveApp->status}\n";
    
    if ($leaveApp->approval) {
        echo "  - Has Approval: Yes\n";
        if ($leaveApp->approval->staffOfficer) {
            echo "  - Staff Officer User: " . ($leaveApp->approval->staffOfficer->email ?? 'N/A') . "\n";
            if ($leaveApp->approval->staffOfficer->officer) {
                echo "  - Staff Officer: " . ($leaveApp->approval->staffOfficer->officer->full_name ?? 'N/A') . "\n";
            }
        }
        if ($leaveApp->approval->areaController) {
            echo "  - Area Controller: " . ($leaveApp->approval->areaController->full_name ?? 'N/A') . "\n";
        }
    } else {
        echo "  - Has Approval: No\n";
    }
    
    // Test authenticated user
    $testUser = User::with('officer')->first();
    if ($testUser) {
        Auth::login($testUser);
        echo "  - Test User: {$testUser->email}\n";
        if ($testUser->officer) {
            echo "  - Test User Officer: " . $testUser->officer->full_name . "\n";
        }
    }
} else {
    echo "✗ No leave applications found\n";
}
echo "\n";

// Test 2: Pass Document
echo "2. Testing Pass Document Parameters:\n";
echo "--------------------------------------\n";
$passApp = PassApplication::with([
    'officer.presentStation',
    'approval.staffOfficer.officer'
])->first();

if ($passApp) {
    echo "✓ Pass Application ID: {$passApp->id}\n";
    echo "  - Officer: " . ($passApp->officer->full_name ?? 'N/A') . "\n";
    echo "  - Service No: " . ($passApp->officer->service_number ?? 'N/A') . "\n";
    echo "  - Command: " . ($passApp->officer->presentStation->name ?? 'N/A') . "\n";
    echo "  - Status: {$passApp->status}\n";
    
    if ($passApp->approval) {
        echo "  - Has Approval: Yes\n";
        if ($passApp->approval->staffOfficer) {
            echo "  - Staff Officer User: " . ($passApp->approval->staffOfficer->email ?? 'N/A') . "\n";
            if ($passApp->approval->staffOfficer->officer) {
                echo "  - Staff Officer: " . ($passApp->approval->staffOfficer->officer->full_name ?? 'N/A') . "\n";
            }
        }
    } else {
        echo "  - Has Approval: No\n";
    }
} else {
    echo "✗ No pass applications found\n";
}
echo "\n";

// Test 3: Staff Order
echo "3. Testing Staff Order Parameters:\n";
echo "-----------------------------------\n";
$staffOrder = StaffOrder::with([
    'officer',
    'fromCommand',
    'toCommand',
    'createdBy.officer'
])->first();

if ($staffOrder) {
    echo "✓ Staff Order ID: {$staffOrder->id}\n";
    echo "  - Order Number: {$staffOrder->order_number}\n";
    echo "  - Officer: " . ($staffOrder->officer->full_name ?? 'N/A') . "\n";
    echo "  - From Command: " . ($staffOrder->fromCommand->name ?? 'N/A') . "\n";
    echo "  - To Command: " . ($staffOrder->toCommand->name ?? 'N/A') . "\n";
    if ($staffOrder->createdBy) {
        echo "  - Created By: " . ($staffOrder->createdBy->email ?? 'N/A') . "\n";
        if ($staffOrder->createdBy->officer) {
            echo "  - Created By Officer: " . ($staffOrder->createdBy->officer->full_name ?? 'N/A') . "\n";
        }
    }
} else {
    echo "✗ No staff orders found\n";
}
echo "\n";

// Test 4: Internal Staff Order
echo "4. Testing Internal Staff Order Parameters:\n";
echo "--------------------------------------------\n";
$internalStaffOrder = InternalStaffOrder::with(['command', 'preparedBy'])->first();

if ($internalStaffOrder) {
    echo "✓ Internal Staff Order ID: {$internalStaffOrder->id}\n";
    echo "  - Order Number: {$internalStaffOrder->order_number}\n";
    echo "  - Command: " . ($internalStaffOrder->command->name ?? 'N/A') . "\n";
    echo "  - Description: " . ($internalStaffOrder->description ?? 'N/A') . "\n";
    if ($internalStaffOrder->preparedBy) {
        echo "  - Prepared By: " . ($internalStaffOrder->preparedBy->email ?? 'N/A') . "\n";
    }
} else {
    echo "✗ No internal staff orders found\n";
}
echo "\n";

// Test 5: Staff Officer Role Check
echo "5. Testing Staff Officer Role Assignment:\n";
echo "-----------------------------------------\n";
$staffOfficerRole = Role::where('name', 'Staff Officer')->first();
if ($staffOfficerRole) {
    echo "✓ Staff Officer Role Found (ID: {$staffOfficerRole->id})\n";
    
    $command = Command::first();
    if ($command) {
        echo "  - Testing Command: {$command->name} (ID: {$command->id})\n";
        
        $staffOfficerUser = User::whereHas('roles', function($query) use ($staffOfficerRole, $command) {
            $query->where('roles.id', $staffOfficerRole->id)
                  ->where('user_roles.command_id', $command->id)
                  ->where('user_roles.is_active', true);
        })->with('officer')->first();
        
        if ($staffOfficerUser) {
            echo "  - Staff Officer User: {$staffOfficerUser->email}\n";
            if ($staffOfficerUser->officer) {
                echo "  - Staff Officer: " . $staffOfficerUser->officer->full_name . "\n";
                echo "  - Service No: " . ($staffOfficerUser->officer->service_number ?? 'N/A') . "\n";
                echo "  - Rank: " . ($staffOfficerUser->officer->substantive_rank ?? 'N/A') . "\n";
            }
        } else {
            echo "  - No Staff Officer assigned to this command\n";
        }
    }
} else {
    echo "✗ Staff Officer role not found\n";
}
echo "\n";

// Test 6: Check Authentication
echo "6. Testing Authentication:\n";
echo "--------------------------\n";
$testUser = User::with('officer')->first();
if ($testUser) {
    Auth::login($testUser);
    $currentUser = Auth::user();
    echo "✓ Authenticated User: {$currentUser->email}\n";
    if ($currentUser->officer) {
        echo "  - Officer: " . $currentUser->officer->full_name . "\n";
        echo "  - Service No: " . ($currentUser->officer->service_number ?? 'N/A') . "\n";
        echo "  - Rank: " . ($currentUser->officer->substantive_rank ?? 'N/A') . "\n";
    } else {
        echo "  - No officer record linked\n";
    }
} else {
    echo "✗ No users found for testing\n";
}
echo "\n";

echo "========================================\n";
echo "TEST COMPLETE\n";
echo "========================================\n";






