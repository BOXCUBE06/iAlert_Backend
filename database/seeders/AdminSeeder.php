<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'login_id' => 'admin@ialert.com', // Log in with this email
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);
    }
}
