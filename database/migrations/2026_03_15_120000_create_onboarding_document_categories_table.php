<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('onboarding_document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('onboarding_document_categories')->insert([
            ['key' => 'birth_certificate', 'name' => 'Birth Certificate', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'educational_qualifications', 'name' => 'Educational Certificates', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'course_completed', 'name' => 'Course Completed', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'appointment_letter', 'name' => 'Appointment Letter', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'promotion_letter', 'name' => 'Promotion Letter', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'posting_order', 'name' => 'Staff Order', 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'movement_order', 'name' => 'Movement Order', 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'training_certificate', 'name' => 'Training Certificate', 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'other', 'name' => 'Other', 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_document_categories');
    }
};
