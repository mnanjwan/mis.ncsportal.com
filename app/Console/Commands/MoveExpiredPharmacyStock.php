<?php

namespace App\Console\Commands;

use App\Models\PharmacyExpiredDrugRecord;
use App\Models\PharmacyStock;
use Illuminate\Console\Command;

class MoveExpiredPharmacyStock extends Command
{
    protected $signature = 'pharmacy:move-expired-stock';

    protected $description = 'Move expired pharmacy stock from pharmacy_stocks into pharmacy_expired_drug_records';

    public function handle(): int
    {
        $expired = PharmacyStock::expired()->withStock()->get();
        $moved = 0;

        foreach ($expired as $stock) {
            if (!$stock->expiry_date || $stock->quantity <= 0) {
                continue;
            }

            PharmacyExpiredDrugRecord::create([
                'pharmacy_drug_id' => $stock->pharmacy_drug_id,
                'location_type' => $stock->location_type,
                'command_id' => $stock->command_id,
                'quantity' => $stock->quantity,
                'expiry_date' => $stock->expiry_date,
                'batch_number' => $stock->batch_number,
                'moved_at' => now(),
            ]);

            $stock->delete();
            $moved++;
        }

        if ($moved > 0) {
            $this->info("Moved {$moved} expired stock record(s) to pharmacy_expired_drug_records.");
        }

        return self::SUCCESS;
    }
}
