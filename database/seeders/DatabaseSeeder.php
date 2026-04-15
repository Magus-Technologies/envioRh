<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ConfiguracionEmpresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador (modelo User de Breeze)
        User::create([
            'name' => 'Administrador RHE',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Crear usuario de prueba
        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Crear configuración de empresa por defecto
        ConfiguracionEmpresa::create([
            'ruc_emisor' => '20123456789',
            'razon_social' => 'Empresa Ejemplo SAC',
            'direccion' => 'Av. Principal 123, Lima, Perú',
            'email' => 'admin@empresa-ejemplo.com',
            'estado' => 'activa',
        ]);
    }
}
