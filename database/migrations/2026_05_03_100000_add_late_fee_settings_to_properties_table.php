<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('late_fee_type', ['fixed', 'percentage'])->default('percentage')->after('description');
            $table->decimal('late_fee_value', 10, 2)->default(0)->after('late_fee_type');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['late_fee_type', 'late_fee_value']);
        });
    }
};
