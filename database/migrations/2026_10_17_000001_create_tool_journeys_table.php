<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tool_journeys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('short_label', 80)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('tool_journeys'); }
};
