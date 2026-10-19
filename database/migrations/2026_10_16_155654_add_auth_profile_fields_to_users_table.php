<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('customer');
            }

            if (!Schema::hasColumn('users', 'channel_name')) {
                $table->string('channel_name', 120)->nullable();
            }

            if (!Schema::hasColumn('users', 'channel_url')) {
                $table->string('channel_url', 2048)->nullable();
            }

            if (!Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 16)->nullable();
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['role', 'channel_name', 'channel_url', 'mobile', 'address'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};