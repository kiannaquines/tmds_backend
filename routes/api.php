<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\ReviewController;

Route::middleware(["auth:sanctum"])->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user', [AuthController::class, 'user']);

    // Thesis Routes
    Route::post('/thesis',[ThesisController::class, 'store']);
    Route::get('/thesis/{id}', [ThesisController::class,'show']);
    Route::put('/thesis/{id}',[ThesisController::class, 'update']);
    Route::delete('/thesis/{id}',[ThesisController::class, 'destroy']);


    Route::post('/review',[ReviewController::class, 'store']);
    Route::get('/review/{id}', [ReviewController::class,'show']);
    Route::put('/review/{id}',[ReviewController::class, 'update']);
    Route::delete('/review/{id}',[ReviewController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {
    Route::post('/login', action: [AuthController::class, 'register']);
    Route::post('/register', action: [AuthController::class, 'login']);
});
