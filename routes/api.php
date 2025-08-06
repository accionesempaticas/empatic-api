<?php

use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentManagementController; // Add this line
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/people/{id}', [PersonController::class, 'show']);

Route::post('/postulant', [PersonController::class, 'registered']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Rutas para usuarios (user)
    Route::middleware('role:user')->group(function () {
        Route::post('/postulant/docsPending/{id}', [PersonController::class, 'docsPending']);
    });

    // Rutas para administradores (admin)
    Route::middleware('role:admin')->group(function () {

        //CRUD person
        Route::post('/people', [PersonController::class, 'store']);
        Route::get('/people', [PersonController::class, 'index']);
        Route::get('/people/{id}', [PersonController::class, 'show']);
        Route::put('/people/{id}', [PersonController::class, 'update']);
        Route::delete('/people/{id}', [PersonController::class, 'destroy']);

        //CRUD postulant
        Route::post('/postulant/interviewing/{id}', [PersonController::class, 'interviewing']);
        Route::post('/postulant/generatePassword/{id}', [PersonController::class, 'generatePassword']);
        Route::post('/postulant/accepted/{id}', [PersonController::class, 'accepted']);
        Route::post('/postulant/reject/{id}', [PersonController::class, 'reject']);


        // CRUD Programs
        Route::get('/programs', [ProgramController::class, 'index']);
        Route::post('/programs', [ProgramController::class, 'store']);
        Route::get('/programs/{program}', [ProgramController::class, 'show']);
        Route::put('/programs/{program}', [ProgramController::class, 'update']);
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Download file
        Route::get('/documents/download', [DocumentManagementController::class, 'downloadFile']);
    });
});
