<?php

namespace App\Console\Commands;

use App\Models\Officer;
use App\Models\Promotion;
use Illuminate\Console\Command;

class DiagnosePromotionPictureGating extends Command
{
    protected $signature = 'promotion:diagnose {service_number? : Officer service number to check}';
    protected $description = 'Diagnose profile picture gating for promoted officers';

    public function handle(): int
    {
        $this->info('=== Profile Picture Promotion Gating Diagnostic ===');
        $this->newLine();

        // Check 1: Database columns exist
        $this->info('1. Checking database columns...');
        $columns = \Schema::getColumnListing('officers');
        $hasRequiredAt = in_array('profile_picture_required_after_promotion_at', $columns);
        $hasUpdatedAt = in_array('profile_picture_updated_at', $columns);
        
        if ($hasRequiredAt && $hasUpdatedAt) {
            $this->line('   ✓ Both columns exist in officers table');
        } else {
            $this->error('   ✗ Missing columns! Run: php artisan migrate');
            if (!$hasRequiredAt) $this->error('     - profile_picture_required_after_promotion_at');
            if (!$hasUpdatedAt) $this->error('     - profile_picture_updated_at');
            return Command::FAILURE;
        }

        // Check 2: Middleware registered
        $this->info('2. Checking middleware registration...');
        $middlewareAliases = app('router')->getMiddleware();
        if (isset($middlewareAliases['profile_picture.post_promotion'])) {
            $this->line('   ✓ Middleware alias registered');
        } else {
            $this->error('   ✗ Middleware alias NOT registered!');
            $this->error('     Check bootstrap/app.php');
        }

        // Check 3: Routes using middleware
        $this->info('3. Checking officer routes middleware...');
        $officerDashboardRoute = app('router')->getRoutes()->getByName('officer.dashboard');
        if ($officerDashboardRoute) {
            $routeMiddleware = $officerDashboardRoute->middleware();
            if (in_array('profile_picture.post_promotion', $routeMiddleware)) {
                $this->line('   ✓ Middleware applied to officer routes');
            } else {
                $this->error('   ✗ Middleware NOT applied to officer routes!');
                $this->line('     Applied middleware: ' . implode(', ', $routeMiddleware));
            }
        }

        // Check specific officer if provided
        $serviceNumber = $this->argument('service_number');
        if ($serviceNumber) {
            $this->newLine();
            $this->info("4. Checking officer: {$serviceNumber}");
            
            $officer = Officer::where('service_number', $serviceNumber)->first();
            if (!$officer) {
                $this->error("   ✗ Officer not found!");
                return Command::FAILURE;
            }

            $this->line("   Officer: {$officer->full_name}");
            $this->line("   Rank: {$officer->substantive_rank}");
            $this->line("   Grade Level: {$officer->salary_grade_level}");
            $this->newLine();

            // Check promotion record
            $promotion = Promotion::where('officer_id', $officer->id)
                ->where('approved_by_board', true)
                ->latest()
                ->first();

            if ($promotion) {
                $this->line("   Has approved promotion: YES");
                $this->line("   - From: {$promotion->from_rank} → To: {$promotion->to_rank}");
                $this->line("   - Promotion Date: {$promotion->promotion_date->format('Y-m-d')}");
                $this->line("   - Approved: " . ($promotion->approved_by_board ? 'Yes' : 'No'));
            } else {
                $this->line("   Has approved promotion: NO");
            }

            $this->newLine();
            $this->line("   profile_picture_required_after_promotion_at: " . 
                ($officer->profile_picture_required_after_promotion_at 
                    ? $officer->profile_picture_required_after_promotion_at->format('Y-m-d H:i:s') 
                    : 'NULL (NOT SET!)'));
            
            $this->line("   profile_picture_updated_at: " . 
                ($officer->profile_picture_updated_at 
                    ? $officer->profile_picture_updated_at->format('Y-m-d H:i:s') 
                    : 'NULL'));

            $this->newLine();
            $needsUpdate = $officer->needsProfilePictureUpdateAfterPromotion();
            $this->line("   needsProfilePictureUpdateAfterPromotion(): " . ($needsUpdate ? 'TRUE (SHOULD BE BLOCKED)' : 'FALSE (can navigate freely)'));

            if ($promotion && !$officer->profile_picture_required_after_promotion_at) {
                $this->newLine();
                $this->warn('   ⚠ ISSUE FOUND: Officer has approved promotion but profile_picture_required_after_promotion_at is NULL!');
                $this->warn('   FIX: Run: php artisan promotion:backfill-picture-requirement --officer=' . $serviceNumber);
            }

            if ($officer->profile_picture_updated_at && $officer->profile_picture_required_after_promotion_at) {
                if ($officer->profile_picture_updated_at->gte($officer->profile_picture_required_after_promotion_at)) {
                    $this->line('   → Profile picture was updated AFTER promotion requirement, so officer is NOT blocked.');
                }
            }
        } else {
            // Show summary of all promoted officers
            $this->newLine();
            $this->info('4. Summary of promoted officers:');
            
            $promotedOfficers = Promotion::where('approved_by_board', true)
                ->with('officer')
                ->get()
                ->groupBy(function ($p) {
                    $officer = $p->officer;
                    if (!$officer) return 'missing_officer';
                    if (!$officer->profile_picture_required_after_promotion_at) return 'missing_requirement';
                    if ($officer->needsProfilePictureUpdateAfterPromotion()) return 'blocked';
                    return 'compliant';
                });

            $this->line("   Total approved promotions: " . Promotion::where('approved_by_board', true)->count());
            $this->line("   - Missing requirement timestamp: " . ($promotedOfficers->get('missing_requirement')?->count() ?? 0));
            $this->line("   - Should be blocked: " . ($promotedOfficers->get('blocked')?->count() ?? 0));
            $this->line("   - Compliant (updated picture): " . ($promotedOfficers->get('compliant')?->count() ?? 0));

            if ($promotedOfficers->has('missing_requirement') && $promotedOfficers->get('missing_requirement')->count() > 0) {
                $this->newLine();
                $this->warn('   ⚠ Some officers are missing the requirement timestamp!');
                $this->warn('   FIX: Run: php artisan promotion:backfill-picture-requirement');
            }
        }

        $this->newLine();
        $this->info('=== Diagnostic Complete ===');
        
        return Command::SUCCESS;
    }
}
