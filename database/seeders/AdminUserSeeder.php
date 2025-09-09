<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        Person::create([
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'first_name' => 'Admin',
            'last_name' => 'Usuario',
            'full_name' => 'Admin Usuario',
            'email' => 'admin@empathicactions.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'gender' => 'Otro',
            'phone_number' => '999999999',
            'age' => 30,
            'nationality' => 'Peruana',
            'status' => 'ACCEPTED'
        ]);

        // Crear usuario regular de ejemplo
        Person::create([
            'document_type' => 'DNI', 
            'document_number' => '87654321',
            'first_name' => 'Usuario',
            'last_name' => 'Prueba',
            'full_name' => 'Usuario Prueba',
            'email' => 'usuario@empathicactions.com',
            'password' => Hash::make('usuario123'),
            'role' => 'user',
            'gender' => 'Femenino',
            'phone_number' => '888888888',
            'age' => 25,
            'nationality' => 'Peruana',
            'status' => 'COMPLETED'
        ]);

        $this->command->info('Usuarios de ejemplo creados:');
        $this->command->info('Admin: admin@empathicactions.com / admin123');
        $this->command->info('Usuario: usuario@empathicactions.com / usuario123');
    }
}
