
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $columns = [
            'primary_color' => '#2563eb',
            'primary_hover_color' => '#1d4ed8',
            'accent_color' => '#0ea5e9',
            'sidebar_color' => '#ffffff',
            'sidebar_text_color' => '#1f2937',
            'page_background_color' => '#f3f4f6',
            'card_color' => '#ffffff',
            'text_color' => '#111827',
            'border_color' => '#e5e7eb',
            'button_text_color' => '#ffffff',
        ];

        foreach ($columns as $column => $default) {
            if (! Schema::hasColumn('site_settings', $column)) {
                Schema::table('site_settings', function (Blueprint $table) use ($column, $default) {
                    $table->string($column, 7)->default($default);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $columns = [
            'primary_color',
            'primary_hover_color',
            'accent_color',
            'sidebar_color',
            'sidebar_text_color',
            'page_background_color',
            'card_color',
            'text_color',
            'border_color',
            'button_text_color',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('site_settings', $column)) {
                Schema::table('site_settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
    