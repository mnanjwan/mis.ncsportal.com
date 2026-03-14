<?php

namespace App\Console\Commands\Pharmacy;

use Illuminate\Console\Command;

class QuarantineExpiredStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pharmacy:quarantine-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting pharmacy stock quarantine process...');

        $expiredStocks = \App\Models\PharmacyStock::where('expiry_date', '<', now()->toDateString())
            ->where('quantity', '>', 0)
            ->get();

        if ($expiredStocks->isEmpty()) {
            $this->info('No expired stock found.');
            return 0;
        }

        $count = 0;
        \Illuminate\Support\Facades\DB::transaction(function () use ($expiredStocks, &$count) {
            foreach ($expiredStocks as $stock) {
                // Move to expired drug records
                \App\Models\PharmacyExpiredDrugRecord::create([
                    'pharmacy_drug_id' => $stock->pharmacy_drug_id,
                    'location_type' => $stock->location_type,
                    'command_id' => $stock->command_id,
                    'quantity' => $stock->quantity,
                    'expiry_date' => $stock->expiry_date,
                    'batch_number' => $stock->batch_number,
                    'moved_at' => now(),
                    'status' => 'QUARANTINED',
                ]);

                // Record stock movement
                \App\Models\PharmacyStockMovement::create([
                    'pharmacy_drug_id' => $stock->pharmacy_drug_id,
                    'movement_type' => 'QUARANTINE_OUT',
                    'reference_id' => null,
                    'reference_type' => null,
                    'location_type' => $stock->location_type,
                    'command_id' => $stock->command_id,
                    'quantity' => -$stock->quantity,
                    'expiry_date' => $stock->expiry_date,
                    'batch_number' => $stock->batch_number,
                    'notes' => 'Automatic quarantine of expired stock',
                    'created_by' => 1, // System user or admin if available, using 1 as fallback for migration-seeded admin
                ]);

                // Reset stock quantity
                $stock->update(['quantity' => 0]);
                $count++;
            }
        });

        $this->info("Successfully quarantined {$count} expired stock items.");
        return 0;
    }
}
