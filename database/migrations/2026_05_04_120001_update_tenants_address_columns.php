<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->string('province')->nullable()->after('emergency_contact_number');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->string('address_line1')->nullable()->after('barangay');
            $table->string('address_line2')->nullable()->after('address_line1');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['province', 'city', 'barangay', 'address_line1', 'address_line2']);
            $table->string('address')->nullable()->after('emergency_contact_number');
        });
    }
};
