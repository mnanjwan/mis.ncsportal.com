<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Admin role if it doesn't exist
        Role::firstOrCreate(
            ['code' => 'ADMIN'],
            [
                'name' => 'Admin',
                'code' => 'ADMIN',
                'description' => 'Admin - Command Role Assignment Manager',
                'access_level' => 'command_level',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Admin role if it exists
        Role::where('code', 'ADMIN')->delete();
    }
};
