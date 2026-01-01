<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Officer;
use App\Models\DutyRoster;
use App\Services\DutyRosterService;

$serviceNumber = 'NCS33246';

echo "Checking officer: {$serviceNumber}\n";
echo str_repeat("=", 60) . "\n\n";

// Find the officer
$officer = Officer::where('service_number', $serviceNumber)->first();

if (!$officer) {
    echo "❌ Officer with service number {$serviceNumber} not found.\n";
    exit(1);
}

echo "✅ Officer found:\n";
echo "   ID: {$officer->id}\n";
echo "   Name: {$officer->initials} {$officer->surname}\n";
$rank = $officer->substantive_rank ? $officer->substantive_rank : 'N/A';
echo "   Rank: {$rank}\n";
$presentStationId = $officer->present_station ? $officer->present_station : 'N/A';
echo "   Present Station ID: {$presentStationId}\n";
$presentStationName = $officer->presentStation ? $officer->presentStation->name : 'N/A';
echo "   Present Station: {$presentStationName}\n";
echo "\n";

$commandId = $officer->present_station;
if (!$commandId) {
    echo "❌ Officer is not assigned to a command.\n";
    exit(1);
}

$year = date('Y');
echo "Checking for year: {$year}\n";
echo str_repeat("-", 60) . "\n\n";

// Check using DutyRosterService
$dutyRosterService = app(DutyRosterService::class);
$rosterRole = $dutyRosterService->getOfficerRoleInRoster($officer->id, $commandId, $year);
$isOICOr2IC = $dutyRosterService->isOfficerOICOr2IC($officer->id, $commandId, $year);

echo "📊 Results from DutyRosterService:\n";
$roleDisplay = $rosterRole ? $rosterRole : 'None';
echo "   Role: {$roleDisplay}\n";
echo "   Is OIC or 2IC: " . ($isOICOr2IC ? 'Yes' : 'No') . "\n";
echo "\n";

// Check directly in database
echo "📋 Direct Database Check:\n";
echo str_repeat("-", 60) . "\n\n";

$startDate = "{$year}-01-01";
$endDate = "{$year}-12-31";

// Check as OIC
$oicRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', 'APPROVED')
    ->where('oic_officer_id', $officer->id)
    ->where(function($query) use ($startDate, $endDate) {
        $query->where(function($nullQuery) {
                $nullQuery->whereNull('roster_period_start')
                         ->whereNull('roster_period_end');
            })
            ->orWhere(function($dateQuery) use ($startDate, $endDate) {
                $dateQuery->whereNotNull('roster_period_start')
                         ->whereNotNull('roster_period_end')
                         ->where(function($overlapQuery) use ($startDate, $endDate) {
                             $overlapQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function($spanQuery) use ($startDate, $endDate) {
                                            $spanQuery->where('roster_period_start', '<=', $startDate)
                                                     ->where('roster_period_end', '>=', $endDate);
                                        });
                         });
            });
    })
    ->get();

// Check as 2IC
$secondInCommandRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', 'APPROVED')
    ->where('second_in_command_officer_id', $officer->id)
    ->where(function($query) use ($startDate, $endDate) {
        $query->where(function($nullQuery) {
                $nullQuery->whereNull('roster_period_start')
                         ->whereNull('roster_period_end');
            })
            ->orWhere(function($dateQuery) use ($startDate, $endDate) {
                $dateQuery->whereNotNull('roster_period_start')
                         ->whereNotNull('roster_period_end')
                         ->where(function($overlapQuery) use ($startDate, $endDate) {
                             $overlapQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function($spanQuery) use ($startDate, $endDate) {
                                            $spanQuery->where('roster_period_start', '<=', $startDate)
                                                     ->where('roster_period_end', '>=', $endDate);
                                        });
                         });
            });
    })
    ->get();

// Check ALL rosters (without date filtering) for reference
$allOicRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', 'APPROVED')
    ->where('oic_officer_id', $officer->id)
    ->get();

$all2IcRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', 'APPROVED')
    ->where('second_in_command_officer_id', $officer->id)
    ->get();

// Also check UNAPPROVED rosters
$unapprovedOicRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', '!=', 'APPROVED')
    ->where('oic_officer_id', $officer->id)
    ->get();

$unapproved2IcRosters = DutyRoster::where('command_id', $commandId)
    ->where('status', '!=', 'APPROVED')
    ->where('second_in_command_officer_id', $officer->id)
    ->get();

// Check ALL commands (not just current command)
$allCommandsOicRosters = DutyRoster::where('oic_officer_id', $officer->id)
    ->where('status', 'APPROVED')
    ->get();

$allCommands2IcRosters = DutyRoster::where('second_in_command_officer_id', $officer->id)
    ->where('status', 'APPROVED')
    ->get();

echo "🔍 OIC Rosters (with date filtering): {$oicRosters->count()}\n";
foreach ($oicRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}
echo "\n";

echo "🔍 2IC Rosters (with date filtering): {$secondInCommandRosters->count()}\n";
foreach ($secondInCommandRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}
echo "\n";

echo "📝 ALL Approved Rosters (without date filtering):\n";
echo "   OIC Rosters: {$allOicRosters->count()}\n";
foreach ($allOicRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "   2IC Rosters: {$all2IcRosters->count()}\n";
foreach ($all2IcRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "\n";
echo "⚠️  UNAPPROVED Rosters in Command {$commandId}:\n";
echo "   OIC Rosters: {$unapprovedOicRosters->count()}\n";
foreach ($unapprovedOicRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}, Status: {$roster->status}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "   2IC Rosters: {$unapproved2IcRosters->count()}\n";
foreach ($unapproved2IcRosters as $roster) {
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}, Status: {$roster->status}\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "\n";
echo "🌐 ALL Approved Rosters (ALL Commands):\n";
echo "   OIC Rosters: {$allCommandsOicRosters->count()}\n";
foreach ($allCommandsOicRosters as $roster) {
    $commandName = $roster->command ? $roster->command->name : 'N/A';
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}, Command: {$commandName} (ID: {$roster->command_id})\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "   2IC Rosters: {$allCommands2IcRosters->count()}\n";
foreach ($allCommands2IcRosters as $roster) {
    $commandName = $roster->command ? $roster->command->name : 'N/A';
    echo "   - Roster ID: {$roster->id}, Unit: {$roster->unit}, Command: {$commandName} (ID: {$roster->command_id})\n";
    echo "     Period: " . ($roster->roster_period_start ? $roster->roster_period_start->format('Y-m-d') : 'NULL') . 
         " to " . ($roster->roster_period_end ? $roster->roster_period_end->format('Y-m-d') : 'NULL') . "\n";
}

echo "\n";
echo str_repeat("=", 60) . "\n";
$finalStatus = $rosterRole ? $rosterRole : "Not OIC or 2IC";
echo "✅ Final Status: {$finalStatus}\n";

