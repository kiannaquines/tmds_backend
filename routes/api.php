<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;

Route::middleware(["auth:sanctum"])->prefix('v1')->group(function () {
    // User Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user', [AuthController::class, 'user']);

    // Thesis Routes
    Route::post('/thesis', [ThesisController::class, 'store']);
    Route::get('/thesis/{id}', [ThesisController::class, 'show']);
    Route::put('/thesis/{id}', [ThesisController::class, 'update']);
    Route::delete('/thesis/{id}', [ThesisController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/login', action: [AuthController::class, 'login']);
    Route::post('/register', action: [AuthController::class, 'register']);
    Route::post('/register/faculty', action: [AuthController::class, 'registerFaculty']);


    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
});
