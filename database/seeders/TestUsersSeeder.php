<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Person;
use App\Models\Location;
use App\Models\AcademicFormation;
use App\Models\Experience;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $locations = Location::all();
        $formations = AcademicFormation::all();
        $experiences = Experience::all();
        
        $documentTypes = ['CC', 'TI', 'CE', 'PP'];
        $genders = ['Masculino', 'Femenino', 'Otro'];
        $roles = ['applicant', 'participant', 'admin'];
        $areas = ['Tecnología', 'Educación', 'Salud', 'Comercio', 'Ingeniería', 'Arte', 'Comunicación'];
        $groups = ['Grupo A', 'Grupo B', 'Grupo C', 'Grupo D'];
        $statuses = ['ACTIVO', 'INACTIVO', 'PENDIENTE', 'RETIRADO', 'BL'];
        
        $testUsers = [
            [
                'first_name' => 'María',
                'last_name' => 'García López',
                'email' => 'maria.garcia@test.com',
                'phone_number' => '3001234567',
                'nationality' => 'Colombiana'
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez Silva',
                'email' => 'carlos.rodriguez@test.com',
                'phone_number' => '3009876543',
                'nationality' => 'Colombiano'
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martínez Pérez',
                'email' => 'ana.martinez@test.com',
                'phone_number' => '3005551234',
                'nationality' => 'Venezolana'
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Hernández Castro',
                'email' => 'luis.hernandez@test.com',
                'phone_number' => '3007778888',
                'nationality' => 'Peruano'
            ],
            [
                'first_name' => 'Sofia',
                'last_name' => 'Ramírez Torres',
                'email' => 'sofia.ramirez@test.com',
                'phone_number' => '3002223333',
                'nationality' => 'Ecuatoriana'
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'González Morales',
                'email' => 'diego.gonzalez@test.com',
                'phone_number' => '3004445555',
                'nationality' => 'Mexicano'
            ],
            [
                'first_name' => 'Valentina',
                'last_name' => 'López Vargas',
                'email' => 'valentina.lopez@test.com',
                'phone_number' => '3006667777',
                'nationality' => 'Chilena'
            ],
            [
                'first_name' => 'Andrés',
                'last_name' => 'Díaz Ruiz',
                'email' => 'andres.diaz@test.com',
                'phone_number' => '3008889999',
                'nationality' => 'Argentino'
            ],
            [
                'first_name' => 'Isabella',
                'last_name' => 'Jiménez Flores',
                'email' => 'isabella.jimenez@test.com',
                'phone_number' => '3001112222',
                'nationality' => 'Boliviana'
            ],
            [
                'first_name' => 'Santiago',
                'last_name' => 'Moreno Gómez',
                'email' => 'santiago.moreno@test.com',
                'phone_number' => '3003334444',
                'nationality' => 'Uruguayo'
            ],
            [
                'first_name' => 'Camila',
                'last_name' => 'Herrera Santos',
                'email' => 'camila.herrera@test.com',
                'phone_number' => '3005556666',
                'nationality' => 'Paraguaya'
            ],
            [
                'first_name' => 'Mateo',
                'last_name' => 'Castro Medina',
                'email' => 'mateo.castro@test.com',
                'phone_number' => '3007778899',
                'nationality' => 'Colombiano'
            ],
            [
                'first_name' => 'Lucía',
                'last_name' => 'Vega Ortiz',
                'email' => 'lucia.vega@test.com',
                'phone_number' => '3009990000',
                'nationality' => 'Venezolana'
            ],
            [
                'first_name' => 'Sebastián',
                'last_name' => 'Rojas Mendoza',
                'email' => 'sebastian.rojas@test.com',
                'phone_number' => '3001119999',
                'nationality' => 'Peruano'
            ],
            [
                'first_name' => 'Gabriela',
                'last_name' => 'Sandoval Cruz',
                'email' => 'gabriela.sandoval@test.com',
                'phone_number' => '3003338888',
                'nationality' => 'Ecuatoriana'
            ],
            [
                'first_name' => 'Felipe',
                'last_name' => 'Aguilar Rivera',
                'email' => 'felipe.aguilar@test.com',
                'phone_number' => '3005557777',
                'nationality' => 'Mexicano'
            ],
            [
                'first_name' => 'Martina',
                'last_name' => 'Paredes Luna',
                'email' => 'martina.paredes@test.com',
                'phone_number' => '3007776666',
                'nationality' => 'Chilena'
            ],
            [
                'first_name' => 'Nicolás',
                'last_name' => 'Guerrero Soto',
                'email' => 'nicolas.guerrero@test.com',
                'phone_number' => '3009995555',
                'nationality' => 'Argentino'
            ],
            [
                'first_name' => 'Valeria',
                'last_name' => 'Navarro Ponce',
                'email' => 'valeria.navarro@test.com',
                'phone_number' => '3001114444',
                'nationality' => 'Boliviana'
            ],
            [
                'first_name' => 'Emilio',
                'last_name' => 'Campos Ibarra',
                'email' => 'emilio.campos@test.com',
                'phone_number' => '3003332222',
                'nationality' => 'Uruguayo'
            ]
        ];

        foreach ($testUsers as $index => $userData) {
            $birthYear = rand(1990, 2005);
            $birthMonth = rand(1, 12);
            $birthDay = rand(1, 28);
            $dateOfBirth = sprintf('%d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
            $age = date('Y') - $birthYear;
            
            $documentNumber = rand(10000000, 99999999);
            
            Person::create([
                'document_type' => $documentTypes[array_rand($documentTypes)],
                'document_number' => (string)$documentNumber,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'full_name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'gender' => $genders[array_rand($genders)],
                'phone_number' => $userData['phone_number'],
                'email' => $userData['email'],
                'date_of_birth' => $dateOfBirth,
                'age' => $age,
                'nationality' => $userData['nationality'],
                'family_phone_number' => '300' . rand(1000000, 9999999),
                'linkedin' => 'https://linkedin.com/in/' . strtolower(str_replace(' ', '', $userData['first_name'] . $userData['last_name'])),
                'location_id' => $locations->random()->id,
                'formation_id' => $formations->random()->id,
                'experience_id' => $experiences->random()->id,
                'password' => Hash::make('password123'),
                'role' => $roles[array_rand($roles)],
                'area' => $areas[array_rand($areas)],
                'group' => $groups[array_rand($groups)],
                'user_status' => $statuses[array_rand($statuses)],
            ]);
        }
        
        $this->command->info('Created 20 test users successfully!');
    }
}