<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['cash', 'gcash', 'bank_transfer', 'maya'])->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'late', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
