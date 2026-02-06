<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
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
        
        // Appointment booking routes
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
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
        
        // Veterinarian appointment management
        Route::get('/veterinarian/appointments', [AppointmentController::class, 'index'])->name('veterinarian.appointments.index');
        Route::get('/veterinarian/appointments/{appointment}', [AppointmentController::class, 'show'])->name('veterinarian.appointments.show');
        Route::post('/veterinarian/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('veterinarian.appointments.cancel');
    });
});

