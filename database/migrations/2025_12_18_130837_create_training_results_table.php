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
        Schema::create('training_results', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number', 50);
            $table->foreignId('officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->string('officer_name', 255);
            $table->decimal('training_score', 5, 2)->comment('Training score as percentage (0-100)');
            $table->enum('status', ['PASS', 'FAIL'])->default('PASS');
            $table->integer('rank')->nullable()->comment('Rank based on performance (1 = highest, 2 = second, etc.)');
            $table->string('service_number', 50)->nullable()->comment('Assigned after Establishment processes');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('appointment_number');
            $table->index('training_score');
            $table->index('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_results');
    }
};
