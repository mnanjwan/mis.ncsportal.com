<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('service_number', 50)->unique();
            $table->string('initials', 50);
            $table->string('surname', 255);
            $table->enum('sex', ['M', 'F']);
            $table->date('date_of_birth');
            $table->date('date_of_first_appointment');
            $table->date('date_of_present_appointment');
            $table->string('substantive_rank', 100);
            $table->string('salary_grade_level', 10);
            $table->string('state_of_origin', 100);
            $table->string('lga', 100);
            $table->string('geopolitical_zone', 50);
            $table->string('marital_status', 50)->nullable();
            $table->string('entry_qualification', 255);
            $table->string('discipline', 255)->nullable();
            $table->string('additional_qualification', 255)->nullable();
            $table->foreignId('present_station')->nullable()->constrained('commands')->nullOnDelete();
            $table->date('date_posted_to_station')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('permanent_home_address');
            $table->string('phone_number', 20);
            $table->string('email', 255)->unique();
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('sort_code', 20)->nullable();
            $table->string('pfa_name', 255)->nullable();
            $table->string('rsa_number', 50)->nullable();
            $table->string('unit', 255)->nullable();
            $table->boolean('interdicted')->default(false);
            $table->boolean('suspended')->default(false);
            $table->boolean('dismissed')->default(false);
            $table->boolean('quartered')->default(false);
            $table->boolean('is_deceased')->default(false);
            $table->date('deceased_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('profile_picture_url', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('service_number');
            $table->index('present_station');
            $table->index('substantive_rank');
            $table->index('is_active');
            $table->index('is_deceased');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};

