<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_modules', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('education_category_id')
                ->constrained('education_categories')
                ->restrictOnDelete();

            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['education_category_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_modules');
    }
};
