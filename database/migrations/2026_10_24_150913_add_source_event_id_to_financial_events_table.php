<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_events', function (Blueprint $table) {
            $table->unsignedBigInteger('source_event_id')
                ->nullable()
                ->after('user_id');

            $table->unique(
                ['source_event_id', 'event_date'],
                'financial_events_source_event_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('financial_events', function (Blueprint $table) {
            $table->dropUnique(
                'financial_events_source_event_date_unique'
            );

            $table->dropColumn('source_event_id');
        });
    }
};