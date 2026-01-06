<?php

/**
 * Test Script for Find Matches Functionality
 * 
 * This script helps diagnose why Find Matches might not be finding officers
 * 
 * Run: php test-find-matches.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\Officer;
use App\Models\Command;
use App\Models\ManningDeploymentAssignment;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "FIND MATCHES DIAGNOSTIC TEST\n";
echo "========================================\n\n";

try {
    // Test 1: Check total officers in database
    echo "TEST 1: Checking total officers in database...\n";
    $totalOfficers = Officer::count();
    $activeOfficers = Officer::where('is_active', true)->count();
    echo "  Total officers: {$totalOfficers}\n";
    echo "  Active officers: {$activeOfficers}\n\n";
    
    // Test 2: Check officers by status
    echo "TEST 2: Checking officers by status...\n";
    $eligibleOfficers = Officer::where('is_active', true)
        ->where('is_deceased', false)
        ->where('interdicted', false)
        ->where('suspended', false)
        ->where('dismissed', false)
        ->whereNotNull('substantive_rank')
        ->whereNotNull('present_station')
        ->count();
    echo "  Eligible officers (active, not deceased/interdicted/suspended/dismissed, has rank & command): {$eligibleOfficers}\n\n";
    
    // Test 3: Check officers by rank
    echo "TEST 3: Checking officers by rank...\n";
    $ranks = Officer::where('is_active', true)
        ->where('is_deceased', false)
        ->where('interdicted', false)
        ->where('suspended', false)
        ->where('dismissed', false)
        ->whereNotNull('substantive_rank')
        ->whereNotNull('present_station')
        ->select('substantive_rank', DB::raw('count(*) as count'))
        ->groupBy('substantive_rank')
        ->orderBy('count', 'desc')
        ->get();
    
    echo "  Rank distribution:\n";
    foreach ($ranks as $rank) {
        echo "    - {$rank->substantive_rank}: {$rank->count} officer(s)\n";
    }
    echo "\n";
    
    // Test 4: Check approved manning requests
    echo "TEST 4: Checking approved manning requests...\n";
    $approvedRequests = ManningRequest::where('status', 'APPROVED')
        ->with('items')
        ->get();
    
    echo "  Found {$approvedRequests->count()} approved request(s)\n";
    
    if ($approvedRequests->isEmpty()) {
        echo "  ⚠️  No approved requests found.\n\n";
    } else {
        foreach ($approvedRequests as $req) {
            $commandName = $req->command ? $req->command->name : 'N/A';
            echo "  Request ID: {$req->id} - Command: {$commandName}\n";
            echo "    Items: {$req->items->count()}\n";
            foreach ($req->items as $item) {
                echo "      - Item {$item->id}: Rank '{$item->rank}', Quantity: {$item->quantity_needed}\n";
            }
        }
        echo "\n";
    }
    
    // Test 5: Test matching for each request item
    if ($approvedRequests->isNotEmpty()) {
        echo "TEST 5: Testing matching logic for each item...\n";
        
        foreach ($approvedRequests as $request) {
            $reqCommandName = $request->command ? $request->command->name : 'N/A';
            echo "\n  Request ID: {$request->id} ({$reqCommandName})\n";
            
            foreach ($request->items as $item) {
                echo "    Item {$item->id}: Rank '{$item->rank}', Quantity: {$item->quantity_needed}\n";
                
                // Build the same query as hrdMatch
                $query = Officer::where('is_active', true)
                    ->where('is_deceased', false)
                    ->where('interdicted', false)
                    ->where('suspended', false)
                    ->where('dismissed', false)
                    ->whereNotNull('substantive_rank')
                    ->whereNotNull('present_station');
                
                // Exclude requesting command
                if ($request->command_id) {
                    $query->where('present_station', '!=', $request->command_id);
                }
                
                // Rank matching (simplified version)
                if (!empty($item->rank)) {
                    $requestedRank = strtolower(trim($item->rank));
                    $query->where(function($q) use ($requestedRank) {
                        $q->whereRaw('LOWER(TRIM(substantive_rank)) = ?', [$requestedRank])
                          ->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%' . $requestedRank . '%'])
                          ->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%(' . $requestedRank . ')%']);
                    });
                }
                
                // Sex requirement
                if ($item->sex_requirement !== 'ANY') {
                    $query->where('sex', $item->sex_requirement);
                }
                
                // Exclude already matched
                $alreadyMatched = ManningRequestItem::where('manning_request_id', $request->id)
                    ->whereNotNull('matched_officer_id')
                    ->pluck('matched_officer_id');
                
                if ($alreadyMatched->isNotEmpty()) {
                    $query->whereNotIn('id', $alreadyMatched);
                }
                
                // Exclude in draft
                $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) {
                        $q->where('status', 'DRAFT');
                    })
                    ->pluck('officer_id');
                
                if ($officersInDraft->isNotEmpty()) {
                    $query->whereNotIn('id', $officersInDraft);
                }
                
                $count = $query->count();
                $matches = $query->with('presentStation')->take(5)->get();
                
                echo "      Matching officers found: {$count}\n";
                
                if ($count > 0) {
                    echo "      Sample matches:\n";
                    foreach ($matches as $officer) {
                        $station = $officer->presentStation ? $officer->presentStation->name : 'N/A';
                        echo "        - {$officer->service_number} {$officer->substantive_rank} {$officer->initials} {$officer->surname} (From: {$station})\n";
                    }
                } else {
                    echo "      ⚠️  No matches found. Reasons could be:\n";
                    
                    // Check if rank exists at all
                    $rankExists = Officer::where('is_active', true)
                        ->whereNotNull('substantive_rank')
                        ->whereRaw('LOWER(TRIM(substantive_rank)) = ?', [strtolower(trim($item->rank))])
                        ->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%' . strtolower(trim($item->rank)) . '%'])
                        ->count();
                    
                    if ($rankExists == 0) {
                        echo "        - No officers have rank '{$item->rank}' in database\n";
                    } else {
                        echo "        - Officers with rank exist but may be:\n";
                        echo "          * From the requesting command (excluded)\n";
                        echo "          * Already matched\n";
                        echo "          * Already in draft\n";
                        echo "          * Don't match sex requirement\n";
                    }
                }
            }
        }
        echo "\n";
    }
    
    // Test 6: Check commands distribution
    echo "TEST 6: Checking officers by command...\n";
    $commandOfficerCounts = DB::table('officers')
        ->join('commands', 'officers.present_station', '=', 'commands.id')
        ->where('officers.is_active', true)
        ->where('officers.is_deceased', false)
        ->where('officers.interdicted', false)
        ->where('officers.suspended', false)
        ->where('officers.dismissed', false)
        ->whereNotNull('officers.substantive_rank')
        ->select('commands.name', DB::raw('count(*) as count'))
        ->groupBy('commands.id', 'commands.name')
        ->orderBy('count', 'desc')
        ->get();
    
    echo "  Commands with eligible officers:\n";
    foreach ($commandOfficerCounts->take(10) as $cmd) {
        echo "    - {$cmd->name}: {$cmd->count} officer(s)\n";
    }
    echo "\n";
    
    // Test 7: Check specific rank matching
    echo "TEST 7: Testing rank matching for 'CA I'...\n";
    $caIOfficers = Officer::where('is_active', true)
        ->where('is_deceased', false)
        ->where('interdicted', false)
        ->where('suspended', false)
        ->where('dismissed', false)
        ->whereNotNull('substantive_rank')
        ->whereNotNull('present_station')
        ->get()
        ->filter(function($officer) {
            $rank = strtolower(trim($officer->substantive_rank));
            return strpos($rank, 'ca i') !== false || 
                   strpos($rank, 'customs assistant i') !== false ||
                   $rank === 'ca i';
        });
    
    echo "  Officers that should match 'CA I': {$caIOfficers->count()}\n";
    if ($caIOfficers->count() > 0) {
        echo "  Sample officers:\n";
        foreach ($caIOfficers->take(5) as $officer) {
            $station = $officer->presentStation ? $officer->presentStation->name : 'N/A';
            echo "    - {$officer->service_number} | Rank: '{$officer->substantive_rank}' | From: {$station}\n";
        }
    }
    echo "\n";
    
    // Summary
    echo "========================================\n";
    echo "DIAGNOSTIC SUMMARY\n";
    echo "========================================\n";
    echo "✓ Total officers: {$totalOfficers}\n";
    echo "✓ Active officers: {$activeOfficers}\n";
    echo "✓ Eligible officers: {$eligibleOfficers}\n";
    echo "✓ Unique ranks: {$ranks->count()}\n";
    echo "✓ Commands with officers: {$commands->count()}\n";
    echo "✓ Approved requests: {$approvedRequests->count()}\n";
    echo "\n";
    
    if ($eligibleOfficers == 0) {
        echo "⚠️  WARNING: No eligible officers found!\n";
        echo "   You may need to:\n";
        echo "   1. Create officers in the database\n";
        echo "   2. Ensure officers have substantive_rank set\n";
        echo "   3. Ensure officers have present_station set\n";
        echo "   4. Ensure officers are marked as active\n";
    } else {
        echo "✓ System has eligible officers for matching\n";
        echo "\n";
        echo "RECOMMENDATIONS:\n";
        echo "1. Ensure manning requests use ranks that exist in the database\n";
        echo "2. Check that requesting command is different from officer commands\n";
        echo "3. Try 'Find Matches' on a request item\n";
        echo "4. Check logs: storage/logs/laravel.log for detailed matching info\n";
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Diagnostic completed!\n\n";

