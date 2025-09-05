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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['expense','income']);
            $table->unsignedBigInteger('amount_cents');
            $table->date('date');
            $table->string('vendor', 191);
            $table->text('memo')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_type', ['org_card','personal_card'])->default('org_card');
            $table->boolean('lost_receipt')->default(false);
            $table->string('reference_code', 20)->unique();
            $table->timestamps();

            $table->index(['budget_id','category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
