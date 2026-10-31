<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_lessons', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('education_module_id')
                ->constrained('education_modules')
                ->restrictOnDelete();

            $table->string('title', 180);
            $table->string('slug', 200);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();

            $table->string('content_type', 20)->default('video');
            $table->string('media_path')->nullable();
            $table->string('external_media_url', 2048)->nullable();
            $table->string('thumbnail_path')->nullable();

            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['education_module_id', 'slug']);
            $table->index(['education_module_id', 'status', 'sort_order']);
            $table->index(['content_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_lessons');
    }
};
