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
    Route::get('/vehiculos', [\App\Http\Controllers\VehicleController::class, 'index'])->name('vehiculos.index');
    
    Route::get('/vehiculos/create', function () {
        return view('vehiculos.create');
    })->name('vehiculos.create');
    
    Route::get('/vehiculos/{id}/edit', function ($id) {
        return view('vehiculos.edit', ['id' => $id]);
    })->name('vehiculos.edit');
    
    Route::get('/vehiculos/{id}', [\App\Http\Controllers\VehicleController::class, 'show'])->name('vehiculos.show');
    
    Route::delete('/vehiculos/{id}', [\App\Http\Controllers\VehicleController::class, 'destroy'])->name('vehiculos.destroy');
    Route::post('/vehiculos/export/{format}', [\App\Http\Controllers\VehicleController::class, 'export'])->name('vehiculos.export');
    
    // Módulo Mantenimientos
    Route::get('/mantenimientos', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('mantenimientos.index');
    Route::delete('/mantenimientos/{id}', [\App\Http\Controllers\MaintenanceController::class, 'destroy'])->name('mantenimientos.destroy');
    Route::post('/mantenimientos/export/{format}', [\App\Http\Controllers\MaintenanceController::class, 'export'])->name('mantenimientos.export');
    
    Route::get('/mantenimientos/create', function () {
        return view('mantenimientos.create');
    })->name('mantenimientos.create');
    
    Route::get('/mantenimientos/{id}/edit', function ($id) {
        return view('mantenimientos.edit', ['id' => $id]);
    })->name('mantenimientos.edit');
    
    Route::get('/mantenimientos/{id}', function ($id) {
        return view('mantenimientos.show', ['id' => $id]);
    })->name('mantenimientos.show');

    // Módulo Conductores
    Route::get('/conductores', [\App\Http\Controllers\DriverController::class, 'index'])->name('conductores.index');
    Route::get('/conductores/create', [\App\Http\Controllers\DriverController::class, 'create'])->name('conductores.create');
    Route::get('/conductores/{id}/edit', [\App\Http\Controllers\DriverController::class, 'edit'])->name('conductores.edit');
    Route::get('/conductores/{id}', [\App\Http\Controllers\DriverController::class, 'show'])->name('conductores.show');
    Route::delete('/conductores/{id}', [\App\Http\Controllers\DriverController::class, 'destroy'])->name('conductores.destroy');

    // Módulo Alertas
    Route::get('/alertas', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alertas/{id}/cerrar', [\App\Http\Controllers\AlertController::class, 'close'])->name('alerts.close');
    Route::post('/alertas/{id}/posponer', [\App\Http\Controllers\AlertController::class, 'snooze'])->name('alerts.snooze');

    // Módulo Certificaciones (por vehículo)
    Route::get('/vehiculos/{vehicleId}/certificaciones/create', [\App\Http\Controllers\CertificationController::class, 'create'])->name('certificaciones.create');
    Route::post('/certificaciones', [\App\Http\Controllers\CertificationController::class, 'store'])->name('certificaciones.store');
    Route::get('/certificaciones/{id}/edit', [\App\Http\Controllers\CertificationController::class, 'edit'])->name('certificaciones.edit');
    Route::match(['put', 'patch'], '/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'update'])->name('certificaciones.update');
    Route::delete('/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'destroy'])->name('certificaciones.destroy');
    Route::get('/certificaciones/{id}/archivo/{slot}', [\App\Http\Controllers\CertificationController::class, 'download'])->name('certificaciones.download')->where('slot', '[12]');
});
