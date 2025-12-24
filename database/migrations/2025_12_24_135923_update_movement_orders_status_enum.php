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
        // SQLite doesn't support ALTER TABLE MODIFY COLUMN for enums
        // We need to recreate the table with the new enum values
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite: recreate table with new enum values
            DB::statement('CREATE TABLE movement_orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                order_number VARCHAR(100) NOT NULL UNIQUE,
                criteria_months_at_station INTEGER,
                manning_request_id INTEGER,
                created_by INTEGER NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT "DRAFT" CHECK(status IN ("DRAFT", "PUBLISHED", "CANCELLED")),
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (manning_request_id) REFERENCES manning_requests(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )');

            // Copy data with status mapping
            DB::statement('INSERT INTO movement_orders_new 
                SELECT id, order_number, criteria_months_at_station, manning_request_id, created_by,
                    CASE 
                        WHEN status = "ACTIVE" THEN "PUBLISHED"
                        WHEN status = "COMPLETED" THEN "PUBLISHED"
                        ELSE status
                    END as status,
                    created_at, updated_at
                FROM movement_orders');

            // Drop old table and rename new one
            Schema::dropIfExists('movement_orders');
            DB::statement('ALTER TABLE movement_orders_new RENAME TO movement_orders');
        } else {
            // For MySQL/PostgreSQL: update existing data first, then modify enum
            // Map ACTIVE and COMPLETED to PUBLISHED
            DB::table('movement_orders')
                ->whereIn('status', ['ACTIVE', 'COMPLETED'])
                ->update(['status' => 'PUBLISHED']);
            
            // Now modify the enum constraint
            DB::statement("ALTER TABLE movement_orders MODIFY COLUMN status ENUM('DRAFT', 'PUBLISHED', 'CANCELLED') DEFAULT 'DRAFT'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Revert to original enum for SQLite
            DB::statement('CREATE TABLE movement_orders_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                order_number VARCHAR(100) NOT NULL UNIQUE,
                criteria_months_at_station INTEGER,
                manning_request_id INTEGER,
                created_by INTEGER NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT "DRAFT" CHECK(status IN ("DRAFT", "ACTIVE", "COMPLETED")),
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (manning_request_id) REFERENCES manning_requests(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )');

            // Copy data with status mapping back
            DB::statement('INSERT INTO movement_orders_old 
                SELECT id, order_number, criteria_months_at_station, manning_request_id, created_by,
                    CASE 
                        WHEN status = "PUBLISHED" THEN "ACTIVE"
                        ELSE status
                    END as status,
                    created_at, updated_at
                FROM movement_orders');

            Schema::dropIfExists('movement_orders');
            DB::statement('ALTER TABLE movement_orders_old RENAME TO movement_orders');
        } else {
            // Revert to original enum for MySQL/PostgreSQL
            // Map PUBLISHED back to ACTIVE before changing enum
            DB::table('movement_orders')
                ->where('status', 'PUBLISHED')
                ->update(['status' => 'ACTIVE']);
            
            // Now revert the enum constraint
            DB::statement("ALTER TABLE movement_orders MODIFY COLUMN status ENUM('DRAFT', 'ACTIVE', 'COMPLETED') DEFAULT 'DRAFT'");
        }
    }
};
