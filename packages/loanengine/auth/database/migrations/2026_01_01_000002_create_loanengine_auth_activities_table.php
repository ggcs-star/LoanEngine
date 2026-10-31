<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loanengine_auth_activities')) {
            return;
        }

        Schema::create('loanengine_auth_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('event', 80)->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('ip_network', 80)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('region', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('isp', 190)->nullable();
            $table->string('asn', 50)->nullable();
            $table->string('network_type', 40)->default('unknown');
            $table->text('user_agent')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('route_name', 190)->nullable();
            $table->string('path', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->char('email_fingerprint', 64)->nullable()->index();
            $table->unsignedTinyInteger('risk_score')->default(0)->index();
            $table->string('risk_level', 20)->default('low')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loanengine_auth_activities');
    }
};
