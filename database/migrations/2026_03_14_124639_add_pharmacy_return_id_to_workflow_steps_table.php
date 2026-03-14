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
        Schema::table('pharmacy_workflow_steps', function (Blueprint $table) {
            $table->foreignId('pharmacy_return_id')->nullable()->after('pharmacy_requisition_id')->constrained('pharmacy_returns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_workflow_steps', function (Blueprint $table) {
            //
        });
    }
};
