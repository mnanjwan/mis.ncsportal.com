<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        // Modify the status enum to include AUDITED (only for MySQL/MariaDB)
        // SQLite stores ENUMs as TEXT, so 'AUDITED' can be used without modifying the constraint
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'AUDITED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
        }
        
        // Add audited_at timestamp field (works for all database types)
        Schema::table('emoluments', function (Blueprint $table) use ($driver) {
            if (in_array($driver, ['mysql', 'mariadb']) && Schema::hasColumn('emoluments', 'validated_at')) {
                // MySQL/MariaDB: can use after() clause
                $table->timestamp('audited_at')->nullable()->after('validated_at');
            } else {
                // SQLite/PostgreSQL: just add the column (after() not supported)
                $table->timestamp('audited_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('emoluments', function (Blueprint $table) {
            $table->dropColumn('audited_at');
        });
        
        // Revert status enum (only for MySQL/MariaDB)
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
        }
    }
};

