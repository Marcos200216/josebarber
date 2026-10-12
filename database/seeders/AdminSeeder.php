<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'jr417270@gmail.com']);

        $admin->forceFill([
            'name' => 'José Pablo',
            'password' => env('ADMIN_PASSWORD'),
            'role' => 'admin',
            'status' => 'aceptado',
            'is_active' => true,
        ])->save();
    }
}