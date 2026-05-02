<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'verifying', 'paid', 'late', 'rejected') NOT NULL DEFAULT 'pending'");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('payments_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO payments_new (id, lease_id, amount_paid, due_date, payment_date, payment_method, proof_of_payment, verified_at, status, remarks, created_at, updated_at) SELECT id, lease_id, amount_paid, due_date, payment_date, payment_method, proof_of_payment, verified_at, status, remarks, created_at, updated_at FROM payments');

        Schema::drop('payments');
        DB::statement('ALTER TABLE payments_new RENAME TO payments');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'paid', 'late', 'rejected') NOT NULL DEFAULT 'pending'");

            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        DB::table('payments')->where('status', 'verifying')->update(['status' => 'pending']);

        Schema::disableForeignKeyConstraints();

        Schema::create('payments_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO payments_old (id, lease_id, amount_paid, due_date, payment_date, payment_method, proof_of_payment, verified_at, status, remarks, created_at, updated_at) SELECT id, lease_id, amount_paid, due_date, payment_date, payment_method, proof_of_payment, verified_at, status, remarks, created_at, updated_at FROM payments');

        Schema::drop('payments');
        DB::statement('ALTER TABLE payments_old RENAME TO payments');

        Schema::enableForeignKeyConstraints();
    }
};
