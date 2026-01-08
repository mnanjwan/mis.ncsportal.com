<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Query;
use Carbon\Carbon;

echo "=== Checking for Expired Queries ===\n\n";

$expiredQueries = Query::where('status', 'PENDING_RESPONSE')
    ->whereNotNull('response_deadline')
    ->where('response_deadline', '<=', now())
    ->with(['officer', 'issuedBy'])
    ->orderBy('response_deadline', 'asc')
    ->get();

if ($expiredQueries->isEmpty()) {
    echo "✓ No expired queries found. All queries are up to date.\n\n";
    
    // Also show total pending queries
    $totalPending = Query::where('status', 'PENDING_RESPONSE')->count();
    echo "Total pending queries: {$totalPending}\n";
    
    if ($totalPending > 0) {
        echo "\nPending queries (not expired):\n";
        $pendingQueries = Query::where('status', 'PENDING_RESPONSE')
            ->where(function($q) {
                $q->whereNull('response_deadline')
                  ->orWhere('response_deadline', '>', now());
            })
            ->with(['officer', 'issuedBy'])
            ->orderBy('response_deadline', 'asc')
            ->get();
            
        foreach ($pendingQueries as $query) {
            $deadline = $query->response_deadline 
                ? $query->response_deadline->format('Y-m-d H:i:s') 
                : 'No deadline';
            $officerName = $query->officer 
                ? "{$query->officer->initials} {$query->officer->surname} ({$query->officer->service_number})"
                : 'N/A';
            echo "  - Query #{$query->id}: {$officerName} | Deadline: {$deadline}\n";
        }
    }
} else {
    echo "⚠ Found {$expiredQueries->count()} expired query/queries:\n\n";
    
    foreach ($expiredQueries as $query) {
        $officerName = $query->officer 
            ? "{$query->officer->initials} {$query->officer->surname} ({$query->officer->service_number})"
            : 'N/A';
        $issuedBy = $query->issuedBy 
            ? $query->issuedBy->email 
            : 'N/A';
        $hoursOverdue = now()->diffInHours($query->response_deadline);
        $daysOverdue = now()->diffInDays($query->response_deadline);
        
        echo "Query ID: {$query->id}\n";
        echo "  Officer: {$officerName}\n";
        echo "  Issued By: {$issuedBy}\n";
        echo "  Status: {$query->status}\n";
        echo "  Deadline: {$query->response_deadline->format('Y-m-d H:i:s')}\n";
        echo "  Issued At: " . ($query->issued_at ? $query->issued_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "  Overdue: {$daysOverdue} day(s), {$hoursOverdue} hour(s)\n";
        echo "  Reason: " . substr($query->reason, 0, 60) . (strlen($query->reason) > 60 ? '...' : '') . "\n";
        echo "\n";
    }
    
    echo "These queries should be automatically expired by:\n";
    echo "  1. Dashboard view (when officer views dashboard)\n";
    echo "  2. Queries index view (when officer clicks 'View & Respond to Queries')\n";
    echo "  3. Scheduled command (runs every 3 minutes)\n";
    echo "\n";
    
    // Ask if user wants to expire them now
    echo "Would you like to expire them now? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) === 'y' || strtolower($line) === 'yes') {
        echo "\nExpiring queries...\n";
        
        $notificationService = app(\App\Services\NotificationService::class);
        $expiredCount = 0;
        
        foreach ($expiredQueries as $query) {
            try {
                DB::beginTransaction();
                
                $query->update([
                    'status' => 'DISAPPROVAL',
                    'reviewed_at' => now(),
                ]);
                
                if ($query->officer && $query->officer->user) {
                    $notificationService->notifyQueryExpired($query);
                }
                
                DB::commit();
                $expiredCount++;
                
                echo "  ✓ Expired query #{$query->id}\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "  ✗ Failed to expire query #{$query->id}: {$e->getMessage()}\n";
            }
        }
        
        echo "\n✓ Successfully expired {$expiredCount} query/queries.\n";
    } else {
        echo "\nQueries will be expired automatically by the scheduled task or when officers view them.\n";
    }
}

echo "\n=== Check Complete ===\n";

