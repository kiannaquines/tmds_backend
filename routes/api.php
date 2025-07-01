<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::middleware(["auth:sanctum"])->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user', [AuthController::class, 'user']);
});

Route::prefix('v1')->group(function () {
    Route::post('/login', action: [AuthController::class, 'register']);
    Route::post('/register', action: [AuthController::class, 'login']);
});
