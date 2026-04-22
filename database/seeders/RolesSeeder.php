<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LoteEmision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'ing@enviorh.com'],
            [
                'name' => 'Ingeniero (Magus)',
                'password' => Hash::make('password'),
                'rol' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $cliente = User::updateOrCreate(
            ['email' => 'cliente@enviorh.com'],
            [
                'name' => 'Cliente RHE',
                'password' => Hash::make('password'),
                'rol' => 'cliente',
                'email_verified_at' => now(),
            ]
        );

        LoteEmision::whereNull('user_id')->update(['user_id' => $cliente->id]);

        User::whereNotIn('email', ['ing@enviorh.com', 'cliente@enviorh.com'])->delete();
    }
}
