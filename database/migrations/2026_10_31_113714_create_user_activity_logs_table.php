<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('module', 30);
            $table->string('activity_type', 60);

            // Related record: category, module, lesson, tool, journey or step.
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['user_id', 'module', 'created_at'],
                'ual_user_module_date_idx'
            );

            $table->index(
                ['user_id', 'activity_type', 'created_at'],
                'ual_user_type_date_idx'
            );

            $table->index(
                ['module', 'subject_type', 'subject_id'],
                'ual_subject_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
