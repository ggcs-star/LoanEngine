<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_events', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_events', 'reminder_time')) {
                $table->time('reminder_time')->nullable();
            }
            if (!Schema::hasColumn('financial_events', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_events', function (Blueprint $table) {
            if (Schema::hasColumn('financial_events', 'reminder_time')) $table->dropColumn('reminder_time');
            if (Schema::hasColumn('financial_events', 'reminder_sent_at')) $table->dropColumn('reminder_sent_at');
        });
    }
};
