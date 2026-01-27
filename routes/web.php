<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Rutas públicas
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirigir raíz a dashboard si está autenticado, sino a login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Módulo Vehículos
    Route::get('/vehiculos', function () {
        return view('vehiculos.index');
    })->name('vehiculos.index');
    
    Route::get('/vehiculos/create', function () {
        return view('vehiculos.create');
    })->name('vehiculos.create');
    
    Route::get('/vehiculos/{id}/edit', function ($id) {
        return view('vehiculos.edit', ['id' => $id]);
    })->name('vehiculos.edit');
    
    Route::get('/vehiculos/{id}', function ($id) {
        return view('vehiculos.show', ['id' => $id]);
    })->name('vehiculos.show');
    
    // Módulo Mantenimientos
    Route::get('/mantenimientos', function () {
        return view('mantenimientos.index');
    })->name('mantenimientos.index');
    
    Route::get('/mantenimientos/create', function () {
        return view('mantenimientos.create');
    })->name('mantenimientos.create');
    
    Route::get('/mantenimientos/{id}/edit', function ($id) {
        return view('mantenimientos.edit', ['id' => $id]);
    })->name('mantenimientos.edit');
    
    Route::get('/mantenimientos/{id}', function ($id) {
        return view('mantenimientos.show', ['id' => $id]);
    })->name('mantenimientos.show');
});
