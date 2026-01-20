<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            // Store JSON array of education entries; can exceed 255 chars easily.
            $table->longText('additional_qualification')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->string('additional_qualification', 255)->nullable()->change();
        });
    }
};

