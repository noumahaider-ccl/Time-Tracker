<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Invitation Routes
Route::get('/invitation/{token}', [App\Http\Controllers\InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}/setup', [App\Http\Controllers\InvitationController::class, 'setup'])->name('invitation.setup');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User Management
        Route::resource('users', UserController::class);
        
        // Project Management
        Route::resource('projects', ProjectController::class);
    });
    
    // User Routes (Client Portal)
    Route::middleware(['role:client'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\User\UserController::class, 'dashboard'])->name('dashboard');
        
        // Projects
        Route::get('/projects', [App\Http\Controllers\User\UserController::class, 'projects'])->name('projects');
        Route::get('/projects/{project}', [App\Http\Controllers\User\UserController::class, 'showProject'])->name('projects.show');
        
        // Time Tracking
        Route::get('/time-tracking', [App\Http\Controllers\User\TimeTrackingController::class, 'index'])->name('time-tracking');
        Route::post('/time-tracking/start', [App\Http\Controllers\User\TimeTrackingController::class, 'start'])->name('time-tracking.start');
        Route::post('/time-tracking/stop', [App\Http\Controllers\User\TimeTrackingController::class, 'stop'])->name('time-tracking.stop');
        Route::post('/time-tracking/ping', [App\Http\Controllers\User\TimeTrackingController::class, 'ping'])->name('time-tracking.ping');
        Route::get('/time-tracking/status', [App\Http\Controllers\User\TimeTrackingController::class, 'status'])->name('time-tracking.status');
        Route::post('/time-tracking/manual', [App\Http\Controllers\User\TimeTrackingController::class, 'addManualEntry'])->name('time-tracking.manual');
        Route::put('/time-tracking/{timeEntry}', [App\Http\Controllers\User\TimeTrackingController::class, 'updateEntry'])->name('time-tracking.update');
        Route::delete('/time-tracking/{timeEntry}', [App\Http\Controllers\User\TimeTrackingController::class, 'deleteEntry'])->name('time-tracking.delete');
        Route::get('/time-tracking/reports', [App\Http\Controllers\User\TimeTrackingController::class, 'reports'])->name('time-tracking.reports');
    });
    
    // Basic redirect for authenticated users without specific roles
    Route::get('/home', function() {
        $user = auth()->user();
        
        if ($user->role->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role->name === 'project_manager') {
            // Placeholder for future implementation
            return view('coming-soon', ['type' => 'Project Manager Dashboard']);
        } elseif ($user->role->name === 'client') {
            return redirect()->route('user.dashboard');
        }
        
        return redirect()->route('login');
    })->name('home');
});