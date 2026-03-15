<?php

use App\Models\LeavePassCriterion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_pass_criteria', 'rank')) {
            Schema::table('leave_pass_criteria', function (Blueprint $table) {
                $table->string('rank', 20)->nullable()->after('type');
            });
        }

        // Old uniqueness was per band. Rank-based settings require many rows sharing one band.
        try {
            DB::statement('ALTER TABLE leave_pass_criteria DROP INDEX leave_pass_criteria_type_grade_band_unique');
        } catch (\Throwable $e) {
            // Ignore when index was already dropped.
        }

        try {
            DB::statement('ALTER TABLE leave_pass_criteria ADD INDEX leave_pass_criteria_type_rank_index (type, rank)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }

        try {
            DB::statement('ALTER TABLE leave_pass_criteria ADD UNIQUE INDEX leave_pass_criteria_type_rank_unique (type, rank)');
        } catch (\Throwable $e) {
            // Ignore when unique index already exists.
        }

        $bandRows = DB::table('leave_pass_criteria')
            ->whereNull('rank')
            ->get()
            ->keyBy(fn ($row) => $row->type . ':' . $row->grade_band);

        foreach (LeavePassCriterion::RANKS as $rank) {
            $band = LeavePassCriterion::rankBand($rank);

            foreach ([LeavePassCriterion::TYPE_ANNUAL_LEAVE, LeavePassCriterion::TYPE_PASS] as $type) {
                $source = $bandRows[$type . ':' . $band] ?? null;
                if (!$source) {
                    continue;
                }

                DB::table('leave_pass_criteria')->updateOrInsert(
                    [
                        'type' => $type,
                        'rank' => $rank,
                    ],
                    [
                        'grade_band' => $band,
                        'max_times_per_year' => $source->max_times_per_year,
                        'duration_type' => $source->duration_type,
                        'max_duration_days' => $source->max_duration_days,
                        'qualification_months' => $source->qualification_months,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('leave_pass_criteria', function (Blueprint $table) {
            try {
                $table->dropUnique(['type', 'rank']);
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                $table->dropIndex(['type', 'rank']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        try {
            DB::statement('ALTER TABLE leave_pass_criteria ADD UNIQUE INDEX leave_pass_criteria_type_grade_band_unique (type, grade_band)');
        } catch (\Throwable $e) {
            // Ignore when re-creating the old unique index is not possible.
        }

        if (Schema::hasColumn('leave_pass_criteria', 'rank')) {
            Schema::table('leave_pass_criteria', function (Blueprint $table) {
                $table->dropColumn('rank');
            });
        }
    }
};
