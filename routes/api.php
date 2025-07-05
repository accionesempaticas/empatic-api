<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/people/{id}', [PersonController::class, 'show']);
