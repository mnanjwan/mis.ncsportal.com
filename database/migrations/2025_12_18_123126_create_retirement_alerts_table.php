<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retirement_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->constrained('officers')->cascadeOnDelete();
            $table->date('retirement_date');
            $table->enum('retirement_type', ['AGE', 'SVC'])->comment('AGE = Age-based (60 years), SVC = Service-based (35 years)');
            $table->date('alert_date')->comment('Date when alert was sent (3 months before retirement)');
            $table->boolean('alert_sent')->default(false);
            $table->timestamp('alert_sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('officer_id');
            $table->index('retirement_date');
            $table->index('alert_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retirement_alerts');
    }
};
