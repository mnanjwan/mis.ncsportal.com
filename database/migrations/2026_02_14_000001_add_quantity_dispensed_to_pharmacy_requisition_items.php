<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pharmacy_requisition_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity_dispensed')->default(0)->after('quantity_issued');
        });

        // Backfill: items on DISPENSED requisitions are considered fully dispensed
        $dispensedRequisitionIds = DB::table('pharmacy_requisitions')->where('status', 'DISPENSED')->pluck('id');
        if ($dispensedRequisitionIds->isNotEmpty()) {
            DB::table('pharmacy_requisition_items')
                ->whereIn('pharmacy_requisition_id', $dispensedRequisitionIds)
                ->update(['quantity_dispensed' => DB::raw('quantity_issued')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_requisition_items', function (Blueprint $table) {
            $table->dropColumn('quantity_dispensed');
        });
    }
};
