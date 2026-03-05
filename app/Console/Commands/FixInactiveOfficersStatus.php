<?php

namespace App\Console\Commands;

use App\Models\Officer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixInactiveOfficersStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'officers:fix-inactive-status
                            {--dry-run : List affected officers without changing anything}
                            {--fix : Set is_active=true for affected officers (use after reviewing --dry-run)}
                            {--with-user-only : Only fix officers who have a linked user account (more conservative)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix officers incorrectly marked inactive by the edit-form bug (no deceased/dismissed). Use --dry-run first, then --fix.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $fix = $this->option('fix');
        $withUserOnly = $this->option('with-user-only');

        if (!$dryRun && !$fix) {
            $this->warn('Use --dry-run to list affected officers, or --fix to apply the change.');
            $this->info('Example: php artisan officers:fix-inactive-status --dry-run');
            $this->info('Then:    php artisan officers:fix-inactive-status --fix');
            return self::FAILURE;
        }

        $query = Officer::where('is_active', false)
            ->where('is_deceased', false)
            ->where('dismissed', false);

        if ($withUserOnly) {
            $query->whereNotNull('user_id');
        }

        $officers = $query->orderBy('id')->get();

        if ($officers->isEmpty()) {
            $this->info('No affected officers found.');
            return self::SUCCESS;
        }

        $this->info('Officers with is_active=false but not deceased/dismissed: ' . $officers->count());
        if ($withUserOnly) {
            $this->comment('(Only those with a linked user account)');
        }
        $this->newLine();

        $rows = $officers->map(fn (Officer $o) => [
            $o->id,
            $o->service_number,
            $o->initials . ' ' . $o->surname,
            $o->email ?? '—',
            $o->user_id ? 'Yes' : 'No',
            $o->updated_at?->format('Y-m-d H:i'),
        ]);
        $this->table(
            ['ID', 'Service No', 'Name', 'Email', 'Has user', 'Updated at'],
            $rows->toArray()
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('To fix these officers, run: php artisan officers:fix-inactive-status --fix' . ($withUserOnly ? ' --with-user-only' : ''));
            return self::SUCCESS;
        }

        if (!$this->confirm('Set is_active=true for these ' . $officers->count() . ' officer(s)?', true)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $ids = $officers->pluck('id')->toArray();
            $updated = Officer::whereIn('id', $ids)->update(['is_active' => true]);
            DB::commit();

            $this->info("Updated {$updated} officer(s) to active.");
            Log::info('FixInactiveOfficersStatus: set is_active=true', [
                'count' => $updated,
                'officer_ids' => $ids,
                'with_user_only' => $withUserOnly,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed: ' . $e->getMessage());
            Log::error('FixInactiveOfficersStatus failed', ['exception' => $e]);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
