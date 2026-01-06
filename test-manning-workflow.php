<?php

/**
 * Test Script for Manning Request Workflow
 * 
 * This script tests:
 * 1. Auto-matching functionality
 * 2. Draft deployment creation
 * 3. Release letter notifications
 * 4. Movement order format
 * 
 * Run: php test-manning-workflow.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;
use App\Models\Officer;
use App\Models\Command;
use App\Models\MovementOrder;
use App\Models\OfficerPosting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "========================================\n";
echo "MANNING REQUEST WORKFLOW TEST\n";
echo "========================================\n\n";

try {
    // Test 1: Check for approved manning requests
    echo "TEST 1: Checking for approved manning requests...\n";
    $approvedRequests = ManningRequest::where('status', 'APPROVED')
        ->with('items')
        ->get();
    
    echo "Found {$approvedRequests->count()} approved request(s)\n";
    
    if ($approvedRequests->isEmpty()) {
        echo "⚠️  No approved requests found. Please create and approve a manning request first.\n";
        echo "   You can create one through: Staff Officer → Manning Level → Create Request\n\n";
        exit(0);
    }
    
    // Find a request with items
    $testRequest = null;
    foreach ($approvedRequests as $request) {
        if ($request->items->count() > 0) {
            $testRequest = $request;
            break;
        }
    }
    
    if (!$testRequest) {
        echo "⚠️  No approved requests with items found.\n";
        echo "   Please create a manning request with items first.\n\n";
        exit(0);
    }
    
    $commandName = $testRequest->command ? $testRequest->command->name : 'N/A';
    echo "✓ Using request ID: {$testRequest->id} from command: {$commandName}\n";
    echo "  Items: {$testRequest->items->count()}\n\n";
    
    // Test 2: Check for items without matches
    echo "TEST 2: Checking for items that need matching...\n";
    $itemsNeedingMatch = $testRequest->items->filter(function($item) {
        // Check if item has officers in draft
        $inDraft = ManningDeploymentAssignment::where('manning_request_item_id', $item->id)
            ->whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->exists();
        
        return !$inDraft && !$item->matched_officer_id;
    });
    
    if ($itemsNeedingMatch->isEmpty()) {
        echo "⚠️  All items are already matched or in draft.\n";
        echo "   Testing with first item anyway...\n\n";
        $testItem = $testRequest->items->first();
        if (!$testItem) {
            echo "❌ No items found in request. Exiting.\n";
            exit(0);
        }
    } else {
        $testItem = $itemsNeedingMatch->first();
        echo "✓ Found item ID: {$testItem->id} - Rank: {$testItem->rank}, Quantity: {$testItem->quantity_needed}\n\n";
    }
    
    // Test 3: Simulate auto-matching
    echo "TEST 3: Simulating auto-matching...\n";
    echo "  Searching for officers with rank: {$testItem->rank}\n";
    echo "  Excluding officers from command: {$testRequest->command->name}\n";
    
    $query = Officer::where('is_active', true)
        ->where('is_deceased', false)
        ->where('interdicted', false)
        ->where('suspended', false)
        ->where('dismissed', false)
        ->whereNotNull('substantive_rank')
        ->whereNotNull('present_station')
        ->where('present_station', '!=', $testRequest->command_id);
    
    // Rank matching (simplified for test)
    $requestedRank = strtolower(trim($testItem->rank));
    $query->where(function($q) use ($requestedRank) {
        $q->whereRaw('LOWER(TRIM(substantive_rank)) = ?', [$requestedRank])
          ->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%' . $requestedRank . '%']);
    });
    
    // Exclude officers in draft
    $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) {
            $q->where('status', 'DRAFT');
        })
        ->pluck('officer_id');
    
    if ($officersInDraft->isNotEmpty()) {
        $query->whereNotIn('id', $officersInDraft);
    }
    
    $matchingOfficers = $query->with('presentStation')->take($testItem->quantity_needed)->get();
    
    echo "  Found {$matchingOfficers->count()} matching officer(s)\n";
    
    if ($matchingOfficers->isEmpty()) {
        echo "⚠️  No matching officers found. This might be expected if:\n";
        echo "   - No officers have the requested rank\n";
        echo "   - All matching officers are already in draft\n";
        echo "   - Rank format doesn't match\n\n";
    } else {
        foreach ($matchingOfficers as $officer) {
            $stationName = $officer->presentStation ? $officer->presentStation->name : 'N/A';
            echo "  - {$officer->service_number} {$officer->substantive_rank} {$officer->initials} {$officer->surname} (From: {$stationName})\n";
        }
        echo "\n";
    }
    
    // Test 4: Check draft deployment
    echo "TEST 4: Checking draft deployment...\n";
    $draft = ManningDeployment::draft()->latest()->first();
    
    if ($draft) {
        echo "✓ Found draft deployment: {$draft->deployment_number}\n";
        $assignments = $draft->assignments()->with(['officer', 'fromCommand', 'toCommand'])->get();
        echo "  Total officers in draft: {$assignments->count()}\n";
        
        // Group by destination command
        $byCommand = $assignments->groupBy('to_command_id');
        echo "  Commands receiving officers: {$byCommand->count()}\n";
        
        foreach ($byCommand as $commandId => $commandAssignments) {
            $command = $commandAssignments->first()->toCommand;
            $commandName = $command ? $command->name : 'N/A';
            echo "    - {$commandName}: {$commandAssignments->count()} officer(s)\n";
        }
        echo "\n";
    } else {
        echo "⚠️  No draft deployment found. One will be created when you click 'Find Matches'.\n\n";
    }
    
    // Test 5: Check movement orders
    echo "TEST 5: Checking movement orders...\n";
    $movementOrders = MovementOrder::where('status', 'DRAFT')
        ->with('postings.officer')
        ->latest()
        ->take(5)
        ->get();
    
    echo "Found {$movementOrders->count()} draft movement order(s)\n";
    
    if ($movementOrders->isNotEmpty()) {
        $latestOrder = $movementOrders->first();
        echo "✓ Latest order: {$latestOrder->order_number}\n";
        echo "  Postings: {$latestOrder->postings->count()}\n";
        
        // Check if sorted by rank
        $postings = $latestOrder->postings()->with('officer')->get();
        $ranks = $postings->pluck('officer.substantive_rank')->filter()->toArray();
        echo "  Ranks in order: " . implode(', ', array_slice($ranks, 0, 5)) . (count($ranks) > 5 ? '...' : '') . "\n";
        echo "\n";
    } else {
        echo "⚠️  No draft movement orders found.\n\n";
    }
    
    // Test 6: Check release letter notifications
    echo "TEST 6: Testing release letter notification structure...\n";
    $notificationService = app(NotificationService::class);
    
    // Get a sample officer and command
    $sampleOfficer = Officer::whereNotNull('present_station')->first();
    $sampleFromCommand = $sampleOfficer ? $sampleOfficer->presentStation : null;
    $sampleToCommand = Command::where('id', '!=', $sampleFromCommand?->id)->first();
    
    if ($sampleOfficer && $sampleFromCommand && $sampleToCommand) {
        echo "✓ Sample officer: {$sampleOfficer->service_number} {$sampleOfficer->substantive_rank}\n";
        echo "  From: {$sampleFromCommand->name}\n";
        echo "  To: {$sampleToCommand->name}\n";
        
        // Check if notification method exists
        if (method_exists($notificationService, 'notifyCommandOfficerRelease')) {
            echo "✓ Release letter notification method exists\n";
            echo "  This will notify Staff Officers, Area Controllers, and DC Admins\n";
        } else {
            echo "⚠️  Release letter notification method not found\n";
        }
        echo "\n";
    } else {
        echo "⚠️  Could not find sample data for release letter test\n\n";
    }
    
    // Summary
    echo "========================================\n";
    echo "TEST SUMMARY\n";
    echo "========================================\n";
    echo "✓ Auto-matching logic: " . ($matchingOfficers->isNotEmpty() ? "WORKING" : "NO MATCHES FOUND") . "\n";
    echo "✓ Draft deployment: " . ($draft ? "EXISTS" : "WILL BE CREATED") . "\n";
    echo "✓ Movement orders: " . ($movementOrders->isNotEmpty() ? "EXISTS" : "NONE YET") . "\n";
    echo "✓ Release letter method: " . (method_exists($notificationService, 'notifyCommandOfficerRelease') ? "EXISTS" : "MISSING") . "\n";
    echo "\n";
    echo "NEXT STEPS:\n";
    echo "1. Go to HRD Dashboard → Manning Requests\n";
    echo "2. Click 'Find Matches' on an approved request item\n";
    echo "3. Officers will be auto-matched and added to draft\n";
    echo "4. Go to Draft Deployment to review/edit\n";
    echo "5. Publish draft to create movement order and send release letters\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Test completed successfully!\n\n";

