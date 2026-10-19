<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('short_description', 200);
            $table->string('icon', 60)->default('calculator');
            $table->string('category', 40)->default('Calculators');
            $table->string('type', 30)->default('Calculator');
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('display_order')->default(1);
            $table->string('page_route', 255);
            $table->boolean('show_on_toolkit_home')->default(true);
            $table->timestamps();
            $table->index(['category', 'status']);
            $table->index('display_order');
        });

        Schema::create('journey_tool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('tool_journeys')->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['journey_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_tool');
        Schema::dropIfExists('tools');
    }
};
