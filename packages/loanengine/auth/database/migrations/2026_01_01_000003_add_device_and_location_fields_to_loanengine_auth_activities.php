<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loanengine_auth_activities')) return;
        Schema::table('loanengine_auth_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('loanengine_auth_activities', 'country_code')) $table->string('country_code', 8)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'district')) $table->string('district', 150)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'postal_code')) $table->string('postal_code', 30)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'latitude')) $table->decimal('latitude', 10, 7)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'longitude')) $table->decimal('longitude', 10, 7)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'timezone')) $table->string('timezone', 100)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'device_id')) $table->string('device_id', 120)->nullable()->index();
            if (! Schema::hasColumn('loanengine_auth_activities', 'device_type')) $table->string('device_type', 30)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'operating_system')) $table->string('operating_system', 100)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'browser')) $table->string('browser', 100)->nullable();
            if (! Schema::hasColumn('loanengine_auth_activities', 'session_id')) $table->string('session_id', 120)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('loanengine_auth_activities')) return;
        Schema::table('loanengine_auth_activities', function (Blueprint $table): void {
            foreach (['country_code','district','postal_code','latitude','longitude','timezone','device_id','device_type','operating_system','browser','session_id'] as $column) {
                if (Schema::hasColumn('loanengine_auth_activities', $column)) $table->dropColumn($column);
            }
        });
    }
};
