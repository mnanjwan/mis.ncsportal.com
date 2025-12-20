<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->string('appointment_number', 50)->nullable()->unique()->after('service_number');
            $table->string('personal_email', 255)->nullable()->after('email')->comment('Personal email used during onboarding');
            $table->string('customs_email', 255)->nullable()->after('personal_email')->comment('Email on customs.gov.ng domain');
            $table->enum('email_status', ['personal', 'customs', 'migrated'])->default('personal')->after('customs_email');
        });
    }

    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn(['appointment_number', 'personal_email', 'customs_email', 'email_status']);
        });
    }
};
