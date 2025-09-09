<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage/privates/{userId}/{filename}', function ($userId, $filename) {
    $filePath = "privates/{$userId}/{$filename}";
    
    if (!Storage::exists($filePath)) {
        abort(404);
    }
    
    $file = Storage::get($filePath);
    $mimeType = Storage::mimeType($filePath);
    
    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', 'http://localhost:3001')
        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
})->name('private.files');

// Endpoint para verificar estado de la base de datos
Route::get('/db-status', function () {
    try {
        $peopleCount = \App\Models\Person::count();
        $locationsCount = \App\Models\Location::count();
        $formationsCount = \App\Models\AcademicFormation::count();
        $experiencesCount = \App\Models\Experience::count();
        
        return response()->json([
            'message' => 'Base de datos funcionando',
            'counts' => [
                'people' => $peopleCount,
                'locations' => $locationsCount,
                'formations' => $formationsCount,
                'experiences' => $experiencesCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al verificar base de datos',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Endpoint para poblar la base de datos manualmente
Route::get('/populate-db', function () {
    try {
        // Crear locations (usar la estructura real: region, province, address)
        $locations = [
            ['region' => 'Cundinamarca', 'province' => 'Bogotá', 'address' => 'Calle 26 #68-10, Bogotá'],
            ['region' => 'Antioquia', 'province' => 'Medellín', 'address' => 'Carrera 43 #19-125, Medellín'],
            ['region' => 'Valle del Cauca', 'province' => 'Cali', 'address' => 'Avenida 6N #23-45, Cali'],
            ['region' => 'Atlántico', 'province' => 'Barranquilla', 'address' => 'Carrera 53 #75-185, Barranquilla'],
            ['region' => 'Bolívar', 'province' => 'Cartagena', 'address' => 'Centro Histórico, Cartagena'],
        ];
        
        foreach ($locations as $location) {
            \App\Models\Location::firstOrCreate($location);
        }
        
        // Crear formaciones académicas (usar la estructura real: academic_degree, career, formation_center)
        $formations = [
            ['academic_degree' => 'Pregrado', 'career' => 'Ingeniería de Sistemas', 'formation_center' => 'Universidad Nacional'],
            ['academic_degree' => 'Pregrado', 'career' => 'Administración de Empresas', 'formation_center' => 'Universidad de los Andes'],
            ['academic_degree' => 'Pregrado', 'career' => 'Psicología', 'formation_center' => 'Universidad Javeriana'],
            ['academic_degree' => 'Pregrado', 'career' => 'Derecho', 'formation_center' => 'Universidad Externado'],
            ['academic_degree' => 'Pregrado', 'career' => 'Medicina', 'formation_center' => 'Universidad del Rosario'],
        ];
        
        foreach ($formations as $formation) {
            \App\Models\AcademicFormation::firstOrCreate($formation);
        }
        
        // Crear experiencias (usar la estructura real: experience_time, other_volunteer_work)
        $experiences = [
            ['experience_time' => '2-3 años', 'other_volunteer_work' => 1],
            ['experience_time' => '1-2 años', 'other_volunteer_work' => 1],
            ['experience_time' => '3-5 años', 'other_volunteer_work' => 0],
            ['experience_time' => 'Menos de 1 año', 'other_volunteer_work' => 1],
            ['experience_time' => 'Más de 5 años', 'other_volunteer_work' => 0],
        ];
        
        foreach ($experiences as $experience) {
            \App\Models\Experience::firstOrCreate($experience);
        }
        
        // Crear usuario admin
        $admin = \App\Models\Person::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'document_type' => 'CC',
                'document_number' => '12345678',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'full_name' => 'Admin User',
                'gender' => 'Masculino',
                'phone_number' => '3001234567',
                'date_of_birth' => '1990-01-01',
                'age' => 34,
                'nationality' => 'Colombiano',
                'family_phone_number' => '3009876543',
                'linkedin' => 'https://linkedin.com/in/admin',
                'location_id' => \App\Models\Location::first()->id,
                'formation_id' => \App\Models\AcademicFormation::first()->id,
                'experience_id' => \App\Models\Experience::first()->id,
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
                'area' => 'Administración',
                'group' => 'Admin',
                'user_status' => 'ACTIVO',
            ]
        );
        
        // Crear 25 usuarios de prueba
        $testUsers = [
            ['María', 'García López', 'maria.garcia@test.com', '3001234567', 'Colombiana'],
            ['Carlos', 'Rodríguez Silva', 'carlos.rodriguez@test.com', '3009876543', 'Colombiano'],
            ['Ana', 'Martínez Pérez', 'ana.martinez@test.com', '3005551234', 'Venezolana'],
            ['Luis', 'Hernández Castro', 'luis.hernandez@test.com', '3007778888', 'Peruano'],
            ['Sofia', 'Ramírez Torres', 'sofia.ramirez@test.com', '3002223333', 'Ecuatoriana'],
            ['Diego', 'González Morales', 'diego.gonzalez@test.com', '3004445555', 'Mexicano'],
            ['Valentina', 'López Vargas', 'valentina.lopez@test.com', '3006667777', 'Chilena'],
            ['Andrés', 'Díaz Ruiz', 'andres.diaz@test.com', '3008889999', 'Argentino'],
            ['Isabella', 'Jiménez Flores', 'isabella.jimenez@test.com', '3001112222', 'Boliviana'],
            ['Santiago', 'Moreno Gómez', 'santiago.moreno@test.com', '3003334444', 'Uruguayo'],
            ['Camila', 'Herrera Santos', 'camila.herrera@test.com', '3005556666', 'Paraguaya'],
            ['Mateo', 'Castro Medina', 'mateo.castro@test.com', '3007778899', 'Colombiano'],
            ['Lucía', 'Vega Ortiz', 'lucia.vega@test.com', '3009990000', 'Venezolana'],
            ['Sebastián', 'Rojas Mendoza', 'sebastian.rojas@test.com', '3001119999', 'Peruano'],
            ['Gabriela', 'Sandoval Cruz', 'gabriela.sandoval@test.com', '3003338888', 'Ecuatoriana'],
            ['Felipe', 'Aguilar Rivera', 'felipe.aguilar@test.com', '3005557777', 'Mexicano'],
            ['Martina', 'Paredes Luna', 'martina.paredes@test.com', '3007776666', 'Chilena'],
            ['Nicolás', 'Guerrero Soto', 'nicolas.guerrero@test.com', '3009995555', 'Argentino'],
            ['Valeria', 'Navarro Ponce', 'valeria.navarro@test.com', '3001114444', 'Boliviana'],
            ['Emilio', 'Campos Ibarra', 'emilio.campos@test.com', '3003332222', 'Uruguayo'],
            ['Elena', 'Morales Cruz', 'elena.morales@test.com', '3005554444', 'Colombiana'],
            ['Rafael', 'Silva Mendez', 'rafael.silva@test.com', '3007776666', 'Venezolano'],
            ['Paola', 'Torres Vega', 'paola.torres@test.com', '3009998888', 'Peruana'],
            ['Joaquín', 'Ramos Díaz', 'joaquin.ramos@test.com', '3001113333', 'Ecuatoriano'],
            ['Amanda', 'Flores Ruiz', 'amanda.flores@test.com', '3003335555', 'Mexicana']
        ];
        
        $locations = \App\Models\Location::all();
        $formations = \App\Models\AcademicFormation::all();
        $experiences = \App\Models\Experience::all();
        
        $documentTypes = ['CC', 'TI', 'CE', 'PP'];
        $genders = ['Masculino', 'Femenino', 'Otro'];
        $roles = ['applicant', 'participant', 'admin'];
        $areas = ['Tecnología', 'Educación', 'Salud', 'Comercio', 'Ingeniería', 'Arte', 'Comunicación'];
        $groups = ['Grupo A', 'Grupo B', 'Grupo C', 'Grupo D'];
        $statuses = ['ACTIVO', 'INACTIVO', 'PENDIENTE', 'RETIRADO', 'BL'];
        
        foreach ($testUsers as $index => $userData) {
            $birthYear = rand(1990, 2005);
            $birthMonth = rand(1, 12);
            $birthDay = rand(1, 28);
            $dateOfBirth = sprintf('%d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
            $age = date('Y') - $birthYear;
            $documentNumber = rand(10000000, 99999999);
            
            \App\Models\Person::firstOrCreate(
                ['email' => $userData[2]],
                [
                    'document_type' => $documentTypes[array_rand($documentTypes)],
                    'document_number' => (string)$documentNumber,
                    'first_name' => $userData[0],
                    'last_name' => $userData[1],
                    'full_name' => $userData[0] . ' ' . $userData[1],
                    'gender' => $genders[array_rand($genders)],
                    'phone_number' => $userData[3],
                    'date_of_birth' => $dateOfBirth,
                    'age' => $age,
                    'nationality' => $userData[4],
                    'family_phone_number' => '300' . rand(1000000, 9999999),
                    'linkedin' => 'https://linkedin.com/in/' . strtolower(str_replace(' ', '', $userData[0] . $userData[1])),
                    'location_id' => $locations->random()->id,
                    'formation_id' => $formations->random()->id,
                    'experience_id' => $experiences->random()->id,
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => $roles[array_rand($roles)],
                    'area' => $areas[array_rand($areas)],
                    'group' => $groups[array_rand($groups)],
                    'user_status' => $statuses[array_rand($statuses)],
                ]
            );
        }
        
        // Contar registros finales
        $peopleCount = \App\Models\Person::count();
        $locationsCount = \App\Models\Location::count();
        $formationsCount = \App\Models\AcademicFormation::count();
        $experiencesCount = \App\Models\Experience::count();
        
        return response()->json([
            'message' => 'Base de datos poblada exitosamente',
            'counts' => [
                'people' => $peopleCount,
                'locations' => $locationsCount,
                'formations' => $formationsCount,
                'experiences' => $experiencesCount
            ],
            'admin_credentials' => [
                'email' => 'admin@example.com',
                'password' => 'admin123'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al poblar base de datos',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Endpoint simple para crear usuarios básicos sin user_status
Route::get('/populate-basic', function () {
    try {
        // Crear admin básico
        $admin = \App\Models\Person::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'document_type' => 'CC',
                'document_number' => '12345678',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'full_name' => 'Admin User',
                'gender' => 'Masculino',
                'phone_number' => '3001234567',
                'date_of_birth' => '1990-01-01',
                'age' => 34,
                'nationality' => 'Colombiano',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
        
        // Crear 20 usuarios de prueba básicos
        $testUsers = [
            ['María García', 'maria.garcia@test.com'],
            ['Carlos Rodríguez', 'carlos.rodriguez@test.com'],
            ['Ana Martínez', 'ana.martinez@test.com'],
            ['Luis Hernández', 'luis.hernandez@test.com'],
            ['Sofia Ramírez', 'sofia.ramirez@test.com'],
            ['Diego González', 'diego.gonzalez@test.com'],
            ['Valentina López', 'valentina.lopez@test.com'],
            ['Andrés Díaz', 'andres.diaz@test.com'],
            ['Isabella Jiménez', 'isabella.jimenez@test.com'],
            ['Santiago Moreno', 'santiago.moreno@test.com'],
            ['Camila Herrera', 'camila.herrera@test.com'],
            ['Mateo Castro', 'mateo.castro@test.com'],
            ['Lucía Vega', 'lucia.vega@test.com'],
            ['Sebastián Rojas', 'sebastian.rojas@test.com'],
            ['Gabriela Sandoval', 'gabriela.sandoval@test.com'],
            ['Felipe Aguilar', 'felipe.aguilar@test.com'],
            ['Martina Paredes', 'martina.paredes@test.com'],
            ['Nicolás Guerrero', 'nicolas.guerrero@test.com'],
            ['Valeria Navarro', 'valeria.navarro@test.com'],
            ['Emilio Campos', 'emilio.campos@test.com']
        ];
        
        foreach ($testUsers as $userData) {
            $names = explode(' ', $userData[0]);
            $documentNumber = rand(10000000, 99999999);
            
            \App\Models\Person::firstOrCreate(
                ['email' => $userData[1]],
                [
                    'document_type' => 'CC',
                    'document_number' => (string)$documentNumber,
                    'first_name' => $names[0],
                    'last_name' => $names[1] ?? 'Apellido',
                    'full_name' => $userData[0],
                    'gender' => 'Otro',
                    'phone_number' => '300' . rand(1000000, 9999999),
                    'date_of_birth' => '1995-01-01',
                    'age' => 29,
                    'nationality' => 'Colombiano',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => 'applicant',
                ]
            );
        }
        
        $peopleCount = \App\Models\Person::count();
        
        return response()->json([
            'message' => 'Usuarios básicos creados exitosamente',
            'count' => $peopleCount,
            'admin_credentials' => [
                'email' => 'admin@example.com',
                'password' => 'admin123'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al crear usuarios básicos',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Endpoint para agregar columna user_status manualmente
Route::get('/fix-user-status', function () {
    try {
        // Verificar si la columna ya existe
        $hasColumn = \Illuminate\Support\Facades\Schema::hasColumn('people', 'user_status');
        
        if ($hasColumn) {
            return response()->json([
                'message' => 'Columna user_status ya existe',
                'status' => 'ok'
            ]);
        }
        
        // Agregar la columna user_status
        \Illuminate\Support\Facades\Schema::table('people', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->enum('user_status', ['ACTIVO', 'INACTIVO', 'PENDIENTE', 'RETIRADO', 'BL'])
                  ->default('PENDIENTE')
                  ->after('role');
        });
        
        return response()->json([
            'message' => 'Columna user_status agregada exitosamente',
            'status' => 'fixed'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al agregar columna user_status',
            'message' => $e->getMessage()
        ], 500);
    }
});