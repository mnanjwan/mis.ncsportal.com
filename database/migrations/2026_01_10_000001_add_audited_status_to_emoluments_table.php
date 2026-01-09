<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the status enum to include AUDITED
        DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'AUDITED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
        
        // Add audited_at timestamp field
        Schema::table('emoluments', function (Blueprint $table) {
            $table->timestamp('audited_at')->nullable()->after('validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('emoluments', function (Blueprint $table) {
            $table->dropColumn('audited_at');
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
    }
};

