<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('late_fee_amount', 10, 2)->default(0)->after('amount_paid');
            $table->decimal('total_amount_due', 10, 2)->default(0)->after('late_fee_amount');
        });

        DB::table('payments')->update([
            'total_amount_due' => DB::raw('amount_paid'),
            'late_fee_amount' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['late_fee_amount', 'total_amount_due']);
        });
    }
};
