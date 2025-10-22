<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Beyazıt Kölemen',
            'email' => 'beyazit@artf4.com',
            'password' => Hash::make('121234'),
            'email_verified_at' => now(),
        ]);
    }
}

