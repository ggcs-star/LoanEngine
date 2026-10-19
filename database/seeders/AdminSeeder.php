<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@loanengine.com'],
            [
                'name' => 'LoanEngine Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
            ]
        );
    }
}
