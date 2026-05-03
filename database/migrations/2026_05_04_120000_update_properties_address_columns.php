<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->string('province')->after('name');
            $table->string('city')->after('province');
            $table->string('barangay')->after('city');
            $table->string('address_line1')->after('barangay');
            $table->string('address_line2')->nullable()->after('address_line1');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['province', 'city', 'barangay', 'address_line1', 'address_line2']);
            $table->string('address')->after('name');
        });
    }
};
