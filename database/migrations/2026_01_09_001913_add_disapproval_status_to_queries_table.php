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
        // For MySQL: Add DISAPPROVAL to the enum
        if (DB::getDriverName() !== 'sqlite') {
            // Step 1: Change column to VARCHAR temporarily (removes enum constraint)
            DB::statement("ALTER TABLE queries MODIFY COLUMN status VARCHAR(20) DEFAULT 'PENDING_RESPONSE'");
            
            // Step 2: Change back to ENUM with new values including DISAPPROVAL
            DB::statement("ALTER TABLE queries MODIFY COLUMN status ENUM('PENDING_RESPONSE', 'PENDING_REVIEW', 'ACCEPTED', 'REJECTED', 'DISAPPROVAL') DEFAULT 'PENDING_RESPONSE'");
        } else {
            // For SQLite: recreate table with new enum values
            DB::statement('CREATE TABLE queries_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                issued_by_user_id INTEGER NOT NULL,
                reason TEXT NOT NULL,
                response TEXT,
                status VARCHAR(255) NOT NULL DEFAULT "PENDING_RESPONSE" CHECK(status IN ("PENDING_RESPONSE", "PENDING_REVIEW", "ACCEPTED", "REJECTED", "DISAPPROVAL")),
                issued_at DATETIME,
                response_deadline DATETIME,
                responded_at DATETIME,
                reviewed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (issued_by_user_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy data
            DB::statement('INSERT INTO queries_new 
                SELECT id, officer_id, issued_by_user_id, reason, response, status, 
                       issued_at, response_deadline, responded_at, reviewed_at, 
                       created_at, updated_at
                FROM queries');

            // Drop old table and rename new one
            Schema::dropIfExists('queries');
            DB::statement('ALTER TABLE queries_new RENAME TO queries');
            
            // Recreate indexes
            DB::statement('CREATE INDEX queries_officer_id_index ON queries(officer_id)');
            DB::statement('CREATE INDEX queries_issued_by_user_id_index ON queries(issued_by_user_id)');
            DB::statement('CREATE INDEX queries_status_index ON queries(status)');
            DB::statement('CREATE INDEX queries_officer_id_status_index ON queries(officer_id, status)');
            if (Schema::hasColumn('queries', 'response_deadline')) {
                DB::statement('CREATE INDEX queries_response_deadline_index ON queries(response_deadline)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Step 1: Change column to VARCHAR temporarily
            DB::statement("ALTER TABLE queries MODIFY COLUMN status VARCHAR(20) DEFAULT 'PENDING_RESPONSE'");
            
            // Step 2: Map DISAPPROVAL back to ACCEPTED (for rollback purposes)
            DB::table('queries')
                ->where('status', 'DISAPPROVAL')
                ->update(['status' => 'ACCEPTED']);
            
            // Step 3: Change back to original ENUM
            DB::statement("ALTER TABLE queries MODIFY COLUMN status ENUM('PENDING_RESPONSE', 'PENDING_REVIEW', 'ACCEPTED', 'REJECTED') DEFAULT 'PENDING_RESPONSE'");
        } else {
            // Revert to original enum for SQLite
            DB::statement('CREATE TABLE queries_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                officer_id INTEGER NOT NULL,
                issued_by_user_id INTEGER NOT NULL,
                reason TEXT NOT NULL,
                response TEXT,
                status VARCHAR(255) NOT NULL DEFAULT "PENDING_RESPONSE" CHECK(status IN ("PENDING_RESPONSE", "PENDING_REVIEW", "ACCEPTED", "REJECTED")),
                issued_at DATETIME,
                response_deadline DATETIME,
                responded_at DATETIME,
                reviewed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
                FOREIGN KEY (issued_by_user_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy data, mapping DISAPPROVAL to ACCEPTED
            DB::statement('INSERT INTO queries_old 
                SELECT id, officer_id, issued_by_user_id, reason, response, 
                       CASE WHEN status = "DISAPPROVAL" THEN "ACCEPTED" ELSE status END as status,
                       issued_at, response_deadline, responded_at, reviewed_at, 
                       created_at, updated_at
                FROM queries');

            Schema::dropIfExists('queries');
            DB::statement('ALTER TABLE queries_old RENAME TO queries');
            
            // Recreate indexes
            DB::statement('CREATE INDEX queries_officer_id_index ON queries(officer_id)');
            DB::statement('CREATE INDEX queries_issued_by_user_id_index ON queries(issued_by_user_id)');
            DB::statement('CREATE INDEX queries_status_index ON queries(status)');
            DB::statement('CREATE INDEX queries_officer_id_status_index ON queries(officer_id, status)');
            if (Schema::hasColumn('queries', 'response_deadline')) {
                DB::statement('CREATE INDEX queries_response_deadline_index ON queries(response_deadline)');
            }
        }
    }
};
