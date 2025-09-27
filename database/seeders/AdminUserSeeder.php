<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Person;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Person::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'document_type' => 'CC',
                'document_number' => '12345678',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'full_name' => 'Admin User',
                'gender' => 'Otro',
                'phone_number' => '3001234567',
                'date_of_birth' => '1990-01-01',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'user_status' => 'ACTIVO',
            ]
        );
    }
}