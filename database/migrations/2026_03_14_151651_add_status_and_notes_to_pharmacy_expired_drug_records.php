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
        Schema::table('pharmacy_expired_drug_records', function (Blueprint $table) {
            $table->string('status')->default('QUARANTINED'); // QUARANTINED, DESTROYED, NAFDAC
            $table->text('action_notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_expired_drug_records', function (Blueprint $table) {
            $table->dropForeign(['acted_by']);
            $table->dropColumn(['status', 'action_notes', 'acted_by', 'acted_at']);
        });
    }
};
