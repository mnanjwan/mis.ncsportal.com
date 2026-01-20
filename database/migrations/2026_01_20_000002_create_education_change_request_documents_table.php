<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_change_request_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('education_change_request_id');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Keep index names short for MySQL
            $table->index('education_change_request_id', 'ecr_docs_ecr_id_idx');
            $table->index('uploaded_by', 'ecr_docs_uploaded_by_idx');
            $table->index('created_at', 'ecr_docs_created_at_idx');

            // Keep constraint name short for MySQL
            $table->foreign('education_change_request_id', 'ecr_docs_ecr_id_fk')
                ->references('id')
                ->on('education_change_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_change_request_documents');
    }
};

