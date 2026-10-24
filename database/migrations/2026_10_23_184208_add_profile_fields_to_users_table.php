
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'employment_type')) {
                $table->string('employment_type', 50)->nullable();
            }

            if (!Schema::hasColumn('users', 'annual_income')) {
                $table->unsignedBigInteger('annual_income')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'city',
                'employment_type',
                'annual_income',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
