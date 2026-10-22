<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_events', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('description', 180);
            $table->decimal('amount', 14, 2);

            $table->enum('category', [
                'income',
                'investment',
                'loan_emi',
                'fixed_bill',
                'living_expense',
            ]);

            $table->date('event_date');

            $table->enum('payment_status', [
                'projected',
                'cleared',
            ])->default('projected');

            $table->text('notes')->nullable();

            // Optional recurring-event configuration
            $table->boolean('is_recurring')->default(false);

            $table->enum('recurrence_frequency', [
                'weekly',
                'monthly',
                'yearly',
            ])->nullable();

            $table->date('recurrence_end_date')->nullable();
            $table->timestamp('last_processed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'event_date']);
            $table->index(['user_id', 'category', 'event_date']);
            $table->index(['user_id', 'payment_status', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_events');
    }
};