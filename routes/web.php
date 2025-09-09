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

// Endpoint especial para inicializar la base de datos en producción
Route::get('/init-db', function () {
    try {
        // Ejecutar migraciones
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        
        // Ejecutar seeders
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\Seeders\PersonSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTemplatesTableSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\Seeders\AdminUserSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\Seeders\TestUsersSeeder', '--force' => true]);
        
        // Contar registros
        $peopleCount = \App\Models\Person::count();
        $locationsCount = \App\Models\Location::count();
        $formationsCount = \App\Models\AcademicFormation::count();
        $experiencesCount = \App\Models\Experience::count();
        
        return response()->json([
            'message' => 'Base de datos inicializada exitosamente',
            'counts' => [
                'people' => $peopleCount,
                'locations' => $locationsCount,
                'formations' => $formationsCount,
                'experiences' => $experiencesCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al inicializar base de datos',
            'message' => $e->getMessage()
        ], 500);
    }
});
