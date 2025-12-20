<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->enum('sex', ['M', 'F'])->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->date('date_of_first_appointment')->nullable()->change();
            $table->date('date_of_present_appointment')->nullable()->change();
            $table->string('salary_grade_level', 10)->nullable()->change();
            $table->string('state_of_origin', 100)->nullable()->change();
            $table->string('lga', 100)->nullable()->change();
            $table->string('geopolitical_zone', 50)->nullable()->change();
            $table->string('entry_qualification', 255)->nullable()->change();
            $table->text('permanent_home_address')->nullable()->change();
            $table->string('phone_number', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->enum('sex', ['M', 'F'])->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->date('date_of_first_appointment')->nullable(false)->change();
            $table->date('date_of_present_appointment')->nullable(false)->change();
            $table->string('salary_grade_level', 10)->nullable(false)->change();
            $table->string('state_of_origin', 100)->nullable(false)->change();
            $table->string('lga', 100)->nullable(false)->change();
            $table->string('geopolitical_zone', 50)->nullable(false)->change();
            $table->string('entry_qualification', 255)->nullable(false)->change();
            $table->text('permanent_home_address')->nullable(false)->change();
            $table->string('phone_number', 20)->nullable(false)->change();
        });
    }
};
