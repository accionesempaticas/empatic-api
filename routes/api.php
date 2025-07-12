<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\Api\DocumentSigningController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentManagementController; // Add this line
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/people/{id}', [PersonController::class, 'show']);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Rutas para usuarios (user y admin)
    Route::middleware('role:user,admin')->group(function () {
        Route::get('/documents/pending', [DocumentSigningController::class, 'getPendingDocuments']);
        Route::get('/documents/signed', [DocumentSigningController::class, 'getSignedDocuments']);
        Route::post('/documents/sign/{template}', [DocumentSigningController::class, 'signDocument']);
        Route::get('/documents/{template}/view', [DocumentSigningController::class, 'serveDocument']);
    });

    // Rutas para administradores (admin)
    Route::middleware('role:admin')->group(function () {
        Route::post('/documents/upload-pdf', [DocumentManagementController::class, 'uploadPdf']);
        Route::post('/documents/create-template', [DocumentManagementController::class, 'createDocumentTemplate']);
        //CRUD person
        Route::get('/people', [PersonController::class, 'index']);
        Route::post('/people', [PersonController::class, 'store']);
        Route::get('/people/{id}', [PersonController::class, 'show']);
        Route::put('/people/{id}', [PersonController::class, 'update']);
        Route::delete('/people/{id}', [PersonController::class, 'destroy']);
    });
});
