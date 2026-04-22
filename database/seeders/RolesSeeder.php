<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@enviorh.local'],
            [
                'name' => 'Administrador (Ing)',
                'password' => Hash::make('admin123'),
                'rol' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@enviorh.local'],
            [
                'name' => 'Cliente RHE',
                'password' => Hash::make('cliente123'),
                'rol' => 'cliente',
                'email_verified_at' => now(),
            ]
        );

        User::where('email', 'admin@example.com')->update(['rol' => 'admin']);
        User::where('email', 'usuario@example.com')->update(['rol' => 'cliente']);
    }
}
