<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\Api\DocumentSigningController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentManagementController; // Add this line
use App\Http\Controllers\Api\UserController; // Add this line
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
        //CRUD user
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});
