<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'verifying', 'verifying_late', 'paid', 'late', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('payments')->where('status', 'verifying_late')->update(['status' => 'verifying']);
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'verifying', 'paid', 'late', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
