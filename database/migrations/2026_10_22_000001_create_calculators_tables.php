<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calculators', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('short_description', 200);
            $table->string('icon', 60)->default('calculator');
            $table->string('status', 16)->default('active')->index();
            $table->unsignedInteger('display_order')->default(1)->index();
            $table->string('route_name', 180)->nullable();
            $table->timestamps();
        });

        Schema::create('calculator_journey', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->foreignId('journey_id')->constrained('tool_journeys')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['calculator_id', 'journey_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_journey');
        Schema::dropIfExists('calculators');
    }
};
