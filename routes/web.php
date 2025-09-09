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
