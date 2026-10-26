<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculator_usages', function (Blueprint $table): void {
            $table->json('inputs')->nullable()->after('calculator_id');
            $table->json('result')->nullable()->after('inputs');
            $table->string('event_type', 50)->default('calculated')->after('result');
            $table->string('status', 30)->default('completed')->after('event_type');
            $table->string('session_id', 64)->nullable()->after('status');
            $table->timestamp('updated_at')->nullable()->after('created_at');

            $table->index(
                ['event_type', 'created_at'],
                'calculator_usages_event_created_index'
            );

            $table->index(
                ['status', 'created_at'],
                'calculator_usages_status_created_index'
            );

            $table->index(
                ['session_id'],
                'calculator_usages_session_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('calculator_usages', function (Blueprint $table): void {
            $table->dropIndex('calculator_usages_event_created_index');
            $table->dropIndex('calculator_usages_status_created_index');
            $table->dropIndex('calculator_usages_session_index');

            $table->dropColumn([
                'inputs',
                'result',
                'event_type',
                'status',
                'session_id',
                'updated_at',
            ]);
        });
    }
};
