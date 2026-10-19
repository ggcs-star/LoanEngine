<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journey_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('tool_journeys')->cascadeOnDelete();
            $table->unsignedInteger('step_number')->default(1);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->text('what_you_need')->nullable();
            $table->text('watch_out_for')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['journey_id', 'display_order']);
        });

        Schema::create('journey_step_tool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('journey_steps')->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['step_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_step_tool');
        Schema::dropIfExists('journey_steps');
    }
};
