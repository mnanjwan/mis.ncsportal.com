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
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER TABLE MODIFY COLUMN for CHECK constraints
            // We need to recreate the table with the new CHECK constraint
            
            // Check if audited_at column already exists (from previous failed migration attempt)
            $hasAuditedAt = Schema::hasColumn('emoluments', 'audited_at');
            
            // Step 1: Create new table with updated CHECK constraint including AUDITED
            DB::statement('CREATE TABLE emoluments_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                timeline_id INTEGER NOT NULL,
                year INTEGER NOT NULL,
                bank_name VARCHAR(255) NOT NULL,
                bank_account_number VARCHAR(50) NOT NULL,
                pfa_name VARCHAR(255) NOT NULL,
                rsa_pin VARCHAR(50) NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT "RAISED" CHECK(status IN ("RAISED", "ASSESSED", "VALIDATED", "AUDITED", "PROCESSED", "REJECTED")),
                submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                assessed_at DATETIME,
                validated_at DATETIME,
                audited_at DATETIME,
                processed_at DATETIME,
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (timeline_id) REFERENCES emolument_timelines(id)
            )');
            
            // Step 2: Copy all data from old table to new table
            if ($hasAuditedAt) {
                // If audited_at already exists, copy it
                DB::statement('INSERT INTO emoluments_new 
                    SELECT id, officer_id, timeline_id, year, bank_name, bank_account_number, 
                           pfa_name, rsa_pin, status, submitted_at, assessed_at, validated_at, 
                           audited_at, processed_at, notes, created_at, updated_at
                    FROM emoluments');
            } else {
                // If audited_at doesn't exist, set it to NULL
                DB::statement('INSERT INTO emoluments_new 
                    SELECT id, officer_id, timeline_id, year, bank_name, bank_account_number, 
                           pfa_name, rsa_pin, status, submitted_at, assessed_at, validated_at, 
                           NULL as audited_at, processed_at, notes, created_at, updated_at
                    FROM emoluments');
            }
            
            // Step 3: Drop old table
            Schema::dropIfExists('emoluments');
            
            // Step 4: Rename new table
            DB::statement('ALTER TABLE emoluments_new RENAME TO emoluments');
            
            // Step 5: Recreate indexes
            DB::statement('CREATE INDEX emoluments_officer_id_year_index ON emoluments (officer_id, year)');
            DB::statement('CREATE INDEX emoluments_status_index ON emoluments (status)');
            DB::statement('CREATE INDEX emoluments_timeline_id_index ON emoluments (timeline_id)');
        } else {
            // For MySQL/MariaDB: Modify the status enum to include AUDITED
            DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'AUDITED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
            
            // Add audited_at timestamp field
            Schema::table('emoluments', function (Blueprint $table) {
                if (Schema::hasColumn('emoluments', 'validated_at')) {
                    $table->timestamp('audited_at')->nullable()->after('validated_at');
                } else {
                    $table->timestamp('audited_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // Revert SQLite table to original CHECK constraint (without AUDITED)
            DB::statement('CREATE TABLE emoluments_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                timeline_id INTEGER NOT NULL,
                year INTEGER NOT NULL,
                bank_name VARCHAR(255) NOT NULL,
                bank_account_number VARCHAR(50) NOT NULL,
                pfa_name VARCHAR(255) NOT NULL,
                rsa_pin VARCHAR(50) NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT "RAISED" CHECK(status IN ("RAISED", "ASSESSED", "VALIDATED", "PROCESSED", "REJECTED")),
                submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                assessed_at DATETIME,
                validated_at DATETIME,
                processed_at DATETIME,
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (timeline_id) REFERENCES emolument_timelines(id)
            )');
            
            // Copy data back (excluding audited_at)
            DB::statement('INSERT INTO emoluments_old 
                SELECT id, officer_id, timeline_id, year, bank_name, bank_account_number, 
                       pfa_name, rsa_pin, status, submitted_at, assessed_at, validated_at, 
                       processed_at, notes, created_at, updated_at
                FROM emoluments');
            
            Schema::dropIfExists('emoluments');
            DB::statement('ALTER TABLE emoluments_old RENAME TO emoluments');
            
            // Recreate indexes
            DB::statement('CREATE INDEX emoluments_officer_id_year_index ON emoluments (officer_id, year)');
            DB::statement('CREATE INDEX emoluments_status_index ON emoluments (status)');
            DB::statement('CREATE INDEX emoluments_timeline_id_index ON emoluments (timeline_id)');
        } else {
            // For MySQL/MariaDB: Drop audited_at and revert enum
            Schema::table('emoluments', function (Blueprint $table) {
                $table->dropColumn('audited_at');
            });
            
            DB::statement("ALTER TABLE emoluments MODIFY COLUMN status ENUM('RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED', 'REJECTED') DEFAULT 'RAISED'");
        }
    }
};

