<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculators', function (Blueprint $table): void {
            $table->string('formula_type', 100)
                ->nullable()
                ->after('route_name')
                ->index();

            $table->json('input_config')->nullable()->after('formula_type');

            $table->json('result_config')->nullable()->after('input_config');
        });
    }

    public function down(): void
    {
        Schema::table('calculators', function (Blueprint $table): void {
            $table->dropIndex(['formula_type']);

            $table->dropColumn([
                'formula_type',
                'input_config',
                'result_config',
            ]);
        });
    }
};