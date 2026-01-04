<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: Recreate table with new CHECK constraint that includes DISMISSED
            DB::statement('CREATE TABLE investigations_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                investigation_officer_id INTEGER NOT NULL,
                invitation_message TEXT,
                status VARCHAR(255) NOT NULL DEFAULT "INVITED" CHECK(status IN ("INVITED", "ONGOING_INVESTIGATION", "INTERDICTED", "SUSPENDED", "DISMISSED", "RESOLVED")),
                notes TEXT,
                invited_at DATETIME,
                status_changed_at DATETIME,
                resolved_at DATETIME,
                resolution_notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (investigation_officer_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy all data from old table
            DB::statement('INSERT INTO investigations_new 
                SELECT id, officer_id, investigation_officer_id, invitation_message, status, notes,
                    invited_at, status_changed_at, resolved_at, resolution_notes, created_at, updated_at
                FROM investigations');

            // Drop old table and rename new one
            Schema::dropIfExists('investigations');
            DB::statement('ALTER TABLE investigations_new RENAME TO investigations');

            // Recreate indexes
            DB::statement('CREATE INDEX investigations_officer_id_index ON investigations(officer_id)');
            DB::statement('CREATE INDEX investigations_status_index ON investigations(status)');
            DB::statement('CREATE INDEX investigations_investigation_officer_id_index ON investigations(investigation_officer_id)');
        } else {
            // For MySQL/PostgreSQL: Add DISMISSED to the status enum
            DB::statement("ALTER TABLE investigations MODIFY COLUMN status ENUM('INVITED', 'ONGOING_INVESTIGATION', 'INTERDICTED', 'SUSPENDED', 'DISMISSED', 'RESOLVED') DEFAULT 'INVITED'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: Recreate table with original CHECK constraint (without DISMISSED)
            DB::statement('CREATE TABLE investigations_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                investigation_officer_id INTEGER NOT NULL,
                invitation_message TEXT,
                status VARCHAR(255) NOT NULL DEFAULT "INVITED" CHECK(status IN ("INVITED", "ONGOING_INVESTIGATION", "INTERDICTED", "SUSPENDED", "RESOLVED")),
                notes TEXT,
                invited_at DATETIME,
                status_changed_at DATETIME,
                resolved_at DATETIME,
                resolution_notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (investigation_officer_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy data back, mapping DISMISSED to RESOLVED
            DB::statement('INSERT INTO investigations_old 
                SELECT id, officer_id, investigation_officer_id, invitation_message,
                    CASE WHEN status = "DISMISSED" THEN "RESOLVED" ELSE status END as status,
                    notes, invited_at, status_changed_at, resolved_at, resolution_notes, created_at, updated_at
                FROM investigations');

            Schema::dropIfExists('investigations');
            DB::statement('ALTER TABLE investigations_old RENAME TO investigations');

            // Recreate indexes
            DB::statement('CREATE INDEX investigations_officer_id_index ON investigations(officer_id)');
            DB::statement('CREATE INDEX investigations_status_index ON investigations(status)');
            DB::statement('CREATE INDEX investigations_investigation_officer_id_index ON investigations(investigation_officer_id)');
        } else {
            // Remove DISMISSED from the status enum (revert to original) for MySQL/PostgreSQL
            DB::statement("ALTER TABLE investigations MODIFY COLUMN status ENUM('INVITED', 'ONGOING_INVESTIGATION', 'INTERDICTED', 'SUSPENDED', 'RESOLVED') DEFAULT 'INVITED'");
        }
    }
};
