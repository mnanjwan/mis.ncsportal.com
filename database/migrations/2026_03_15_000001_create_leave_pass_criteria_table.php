<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_pass_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30); // annual_leave | pass
            $table->string('grade_band', 30); // gl03_below | gl04_06 | gl07_above
            $table->unsignedInteger('max_times_per_year')->nullable();
            $table->string('duration_type', 20)->default('working_days'); // working_days | calendar_days
            $table->unsignedInteger('max_duration_days');
            $table->unsignedInteger('qualification_months')->default(0);
            $table->timestamps();

            $table->unique(['type', 'grade_band']);
            $table->index(['type', 'grade_band']);
        });

        DB::table('leave_pass_criteria')->insert([
            [
                'type' => 'annual_leave',
                'grade_band' => 'gl03_below',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 14,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'annual_leave',
                'grade_band' => 'gl04_06',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 21,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'annual_leave',
                'grade_band' => 'gl07_above',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 30,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pass',
                'grade_band' => 'gl03_below',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 14,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pass',
                'grade_band' => 'gl04_06',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 21,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pass',
                'grade_band' => 'gl07_above',
                'max_times_per_year' => 2,
                'duration_type' => 'working_days',
                'max_duration_days' => 30,
                'qualification_months' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_pass_criteria');
    }
};
