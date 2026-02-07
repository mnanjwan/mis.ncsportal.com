<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            ['name' => 'Staff Officer T&L', 'code' => 'SO_TL', 'access_level' => 'command_level'],
            ['name' => 'OC Workshop', 'code' => 'OC_WORKSHOP', 'access_level' => 'command_level'],
            ['name' => 'T&L Officer', 'code' => 'TL_OFFICER', 'access_level' => 'command_level'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Role::whereIn('name', [
            'Staff Officer T&L',
            'OC Workshop',
            'T&L Officer',
        ])->delete();
    }
};
