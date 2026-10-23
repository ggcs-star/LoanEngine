<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('short_description', 200)->nullable()->change();
        });

        Schema::table('calculators', function (Blueprint $table) {
            $table->text('short_description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('short_description', 200)->nullable(false)->change();
        });

        Schema::table('calculators', function (Blueprint $table) {
            $table->text('short_description')->nullable(false)->change();
        });
    }
};