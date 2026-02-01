<?php

namespace App\Console\Commands;

use App\Models\Officer;
use App\Models\Promotion;
use Illuminate\Console\Command;

class BackfillPromotionProfilePictureRequirement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotion:backfill-picture-requirement 
                            {--officer= : Specific officer service number to update}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill profile_picture_required_after_promotion_at for officers with approved promotions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificOfficer = $this->option('officer');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Get all approved promotions where officer doesn't have the requirement timestamp set
        $query = Promotion::where('approved_by_board', true)
            ->whereHas('officer', function ($q) {
                $q->whereNull('profile_picture_required_after_promotion_at');
            })
            ->with('officer');

        if ($specificOfficer) {
            $query->whereHas('officer', function ($q) use ($specificOfficer) {
                $q->where('service_number', $specificOfficer);
            });
        }

        $promotions = $query->get();

        if ($promotions->isEmpty()) {
            $this->info('No officers found that need backfilling.');
            return Command::SUCCESS;
        }

        $this->info("Found {$promotions->count()} officer(s) to update:");

        $updated = 0;
        foreach ($promotions as $promotion) {
            $officer = $promotion->officer;
            
            if (!$officer) {
                $this->warn("  - Promotion ID {$promotion->id}: Officer not found");
                continue;
            }

            $this->line("  - {$officer->service_number} ({$officer->full_name}): Setting requirement to {$promotion->promotion_date->format('Y-m-d')}");

            if (!$dryRun) {
                $officer->update([
                    'profile_picture_required_after_promotion_at' => $promotion->promotion_date,
                ]);
                $updated++;
            }
        }

        if ($dryRun) {
            $this->info("\nDry run complete. {$promotions->count()} officer(s) would be updated.");
        } else {
            $this->info("\nBackfill complete. Updated {$updated} officer(s).");
        }

        return Command::SUCCESS;
    }
}
