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
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->foreignId('issued_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason'); // Written reason(s) for querying the officer
            $table->text('response')->nullable(); // Officer's response to the query
            $table->enum('status', ['PENDING_RESPONSE', 'PENDING_REVIEW', 'ACCEPTED', 'REJECTED'])->default('PENDING_RESPONSE');
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('officer_id');
            $table->index('issued_by_user_id');
            $table->index('status');
            $table->index(['officer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queries');
    }
};
