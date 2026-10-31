<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_usages', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('calculator_id')
                ->constrained('calculators')
                ->cascadeOnDelete();

            // Every row represents one successful calculator usage.
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['user_id', 'calculator_id', 'created_at'],
                'calculator_usages_user_calculator_created_index'
            );

            $table->index(
                ['calculator_id', 'created_at'],
                'calculator_usages_calculator_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_usages');
    }
};
