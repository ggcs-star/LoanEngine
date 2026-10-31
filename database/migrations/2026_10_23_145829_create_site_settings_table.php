<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            return;
        }

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            $table->string('site_name')->default('LoanEngine');
            $table->string('tagline')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('dark_logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('apple_icon_path')->nullable();
            $table->string('android_icon_path')->nullable();

            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('primary_hover_color', 7)->default('#1d4ed8');
            $table->string('accent_color', 7)->default('#0ea5e9');
            $table->string('sidebar_color', 7)->default('#ffffff');
            $table->string('sidebar_text_color', 7)->default('#1f2937');
            $table->string('page_background_color', 7)->default('#f3f4f6');
            $table->string('card_color', 7)->default('#ffffff');
            $table->string('text_color', 7)->default('#111827');
            $table->string('border_color', 7)->default('#e5e7eb');
            $table->string('button_text_color', 7)->default('#ffffff');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
