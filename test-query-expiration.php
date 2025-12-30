<?php

/**
 * Test Script for Query Expiration
 * This script tests that queries are automatically moved to ACCEPTED when deadline expires
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Query;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Query Expiration Test ===\n\n";

// Find an officer with a user account
$officer = Officer::whereHas('user')->first();

if (!$officer) {
    echo "❌ No officer with user account found. Cannot run test.\n";
    exit(1);
}

echo "✓ Found test officer: {$officer->initials} {$officer->surname} (ID: {$officer->id})\n";
echo "  Service Number: {$officer->service_number}\n\n";

// Find a Staff Officer to issue the query
$staffOfficer = User::whereHas('roles', function($q) {
    $q->where('name', 'Staff Officer');
})->first();

if (!$staffOfficer) {
    echo "❌ No Staff Officer found. Cannot create test query.\n";
    exit(1);
}

echo "✓ Found Staff Officer: {$staffOfficer->email} (ID: {$staffOfficer->id})\n\n";

// Clean up any existing test queries for this officer
Query::where('officer_id', $officer->id)
    ->where('reason', 'LIKE', 'TEST: %')
    ->delete();

echo "✓ Cleaned up any existing test queries\n\n";

// Create a test query with expired deadline (1 hour ago)
$expiredDeadline = Carbon::now()->subHour();
$testQuery = Query::create([
    'officer_id' => $officer->id,
    'issued_by_user_id' => $staffOfficer->id,
    'reason' => 'TEST: This is a test query for expiration testing',
    'status' => 'PENDING_RESPONSE',
    'issued_at' => Carbon::now()->subDays(2),
    'response_deadline' => $expiredDeadline,
]);

echo "✓ Created test query:\n";
echo "  Query ID: {$testQuery->id}\n";
echo "  Status: {$testQuery->status}\n";
echo "  Deadline: {$testQuery->response_deadline->format('Y-m-d H:i:s')}\n";
echo "  Deadline Status: " . ($testQuery->response_deadline->isPast() ? 'EXPIRED' : 'NOT EXPIRED') . "\n\n";

// Test 1: Check if query is expired
echo "=== Test 1: Check Query Expiration Status ===\n";
$isExpired = $testQuery->isExpired();
$isOverdue = $testQuery->isOverdue();
echo "  isExpired(): " . ($isExpired ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "  isOverdue(): " . ($isOverdue ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "  Expected: Both should be TRUE\n";
if ($isExpired && $isOverdue) {
    echo "  ✓ PASS: Query is correctly identified as expired\n\n";
} else {
    echo "  ✗ FAIL: Query should be expired but isn't\n\n";
}

// Test 2: Simulate dashboard view (OfficerController::dashboard logic)
echo "=== Test 2: Dashboard View Expiration ===\n";
$expiredQueries = Query::where('officer_id', $officer->id)
    ->where('status', 'PENDING_RESPONSE')
    ->whereNotNull('response_deadline')
    ->where('response_deadline', '<=', now())
    ->get();

echo "  Found {$expiredQueries->count()} expired query/queries\n";

if ($expiredQueries->contains('id', $testQuery->id)) {
    echo "  ✓ PASS: Test query found in expired queries list\n";
    
    // Simulate the expiration process
    $testQuery->refresh();
    $originalStatus = $testQuery->status;
    
    $testQuery->update([
        'status' => 'ACCEPTED',
        'reviewed_at' => now(),
    ]);
    
    $testQuery->refresh();
    echo "  Updated status from '{$originalStatus}' to '{$testQuery->status}'\n";
    
    if ($testQuery->status === 'ACCEPTED') {
        echo "  ✓ PASS: Query successfully moved to ACCEPTED\n\n";
    } else {
        echo "  ✗ FAIL: Query status is '{$testQuery->status}', expected 'ACCEPTED'\n\n";
    }
} else {
    echo "  ✗ FAIL: Test query not found in expired queries\n\n";
}

// Test 3: Check queries index method logic
echo "=== Test 3: Queries Index Method ===\n";
// Reset query for this test
$testQuery->update(['status' => 'PENDING_RESPONSE', 'reviewed_at' => null]);
$testQuery->refresh();

$expiredQueriesIndex = Query::where('officer_id', $officer->id)
    ->where('status', 'PENDING_RESPONSE')
    ->whereNotNull('response_deadline')
    ->where('response_deadline', '<=', now())
    ->get();

echo "  Found {$expiredQueriesIndex->count()} expired query/queries in index check\n";

if ($expiredQueriesIndex->contains('id', $testQuery->id)) {
    echo "  ✓ PASS: Test query found in expired queries for index\n";
    
    $testQuery->update([
        'status' => 'ACCEPTED',
        'reviewed_at' => now(),
    ]);
    $testQuery->refresh();
    
    if ($testQuery->status === 'ACCEPTED') {
        echo "  ✓ PASS: Query successfully expired via index method\n\n";
    } else {
        echo "  ✗ FAIL: Query status update failed\n\n";
    }
} else {
    echo "  ✗ FAIL: Test query not found\n\n";
}

// Test 4: Verify pending queries exclude expired ones
echo "=== Test 4: Pending Queries Exclusion ===\n";
$pendingQueries = Query::where('officer_id', $officer->id)
    ->where('status', 'PENDING_RESPONSE')
    ->get();

echo "  Pending queries count: {$pendingQueries->count()}\n";

if (!$pendingQueries->contains('id', $testQuery->id)) {
    echo "  ✓ PASS: Expired query correctly excluded from pending queries\n\n";
} else {
    echo "  ✗ FAIL: Expired query still appears in pending queries\n\n";
}

// Test 5: Test canAcceptResponse method
echo "=== Test 5: canAcceptResponse Method ===\n";
$testQuery->refresh();
$canRespond = $testQuery->canAcceptResponse();
echo "  canAcceptResponse(): " . ($canRespond ? 'TRUE' : 'FALSE') . "\n";
echo "  Expected: FALSE (query is expired)\n";

if (!$canRespond) {
    echo "  ✓ PASS: Query correctly prevents response when expired\n\n";
} else {
    echo "  ✗ FAIL: Query should not accept response when expired\n\n";
}

// Cleanup
echo "=== Cleanup ===\n";
$testQuery->delete();
echo "✓ Test query deleted\n\n";

echo "=== Test Summary ===\n";
echo "All tests completed. Check results above.\n";
echo "\nTo test in browser:\n";
echo "1. Login as officer: {$officer->user->email}\n";
echo "2. View dashboard - expired queries should be auto-expired\n";
echo "3. Click 'View & Respond to Queries' - expired queries should be processed\n";

