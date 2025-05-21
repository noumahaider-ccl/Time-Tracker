<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'apiLogin']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Users
        Route::apiResource('users', UserController::class);
        
        // Projects
        Route::apiResource('projects', ProjectController::class);
    });
    
    // Project Manager Routes
    Route::middleware(['role:project_manager'])->prefix('manager')->group(function () {
        // Project Manager specific routes...
    });
    
    // Client Routes
    Route::middleware(['role:client'])->prefix('client')->group(function () {
        // Client specific routes...
    });
});