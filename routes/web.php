<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Doctor Discovery Routes (Public)
Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
Route::get('/doctors/search', [DoctorController::class, 'search'])->name('doctors.search');
Route::get('/doctors/{veterinarian}', [DoctorController::class, 'show'])->name('doctors.show');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:3,5'); // 3 attempts per 5 minutes
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'user' => auth()->user(),
        ]);
    })->name('dashboard');
    
    // Pet/Animal management routes (for pet owners)
    Route::middleware('role:pet_owner')->group(function () {
        Route::resource('pets', PetController::class);
    });
    
    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });
    
    // Veterinarian routes
    Route::middleware('role:veterinarian')->group(function () {
        Route::get('/veterinarian/schedule', function () {
            return Inertia::render('Veterinarian/Schedule');
        })->name('veterinarian.schedule');
    });
});

