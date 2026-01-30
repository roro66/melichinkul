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
    Route::post('/mantenimientos/{id}/aprobar', [\App\Http\Controllers\MaintenanceController::class, 'approve'])->name('mantenimientos.approve');
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

    // Módulo Repuestos (catálogo)
    Route::get('/repuestos', [\App\Http\Controllers\SparePartController::class, 'index'])->name('repuestos.index');
    Route::get('/repuestos/create', [\App\Http\Controllers\SparePartController::class, 'create'])->name('repuestos.create');
    Route::post('/repuestos', [\App\Http\Controllers\SparePartController::class, 'store'])->name('repuestos.store');
    Route::get('/repuestos/{id}/edit', [\App\Http\Controllers\SparePartController::class, 'edit'])->name('repuestos.edit');
    Route::match(['put', 'patch'], '/repuestos/{id}', [\App\Http\Controllers\SparePartController::class, 'update'])->name('repuestos.update');
    Route::delete('/repuestos/{id}', [\App\Http\Controllers\SparePartController::class, 'destroy'])->name('repuestos.destroy');

    // Módulo Proveedores
    Route::get('/proveedores', [\App\Http\Controllers\SupplierController::class, 'index'])->name('proveedores.index');
    Route::get('/proveedores/create', [\App\Http\Controllers\SupplierController::class, 'create'])->name('proveedores.create');
    Route::post('/proveedores', [\App\Http\Controllers\SupplierController::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/{id}/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])->name('proveedores.edit');
    Route::match(['put', 'patch'], '/proveedores/{id}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{id}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('proveedores.destroy');

    // Módulo Compras (inventario)
    Route::get('/compras', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('compras.index');
    Route::get('/compras/create', [\App\Http\Controllers\PurchaseController::class, 'create'])->name('compras.create');
    Route::post('/compras', [\App\Http\Controllers\PurchaseController::class, 'store'])->name('compras.store');
    Route::get('/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('compras.show');
    Route::get('/compras/{id}/edit', [\App\Http\Controllers\PurchaseController::class, 'edit'])->name('compras.edit');
    Route::match(['put', 'patch'], '/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'update'])->name('compras.update');
    Route::delete('/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'destroy'])->name('compras.destroy');
    Route::post('/compras/{id}/recibir', [\App\Http\Controllers\PurchaseController::class, 'receive'])->name('compras.receive');

    // Módulo Certificaciones (por vehículo)
    Route::get('/vehiculos/{vehicleId}/certificaciones/create', [\App\Http\Controllers\CertificationController::class, 'create'])->name('certificaciones.create');
    Route::post('/certificaciones', [\App\Http\Controllers\CertificationController::class, 'store'])->name('certificaciones.store');
    Route::get('/certificaciones/{id}/edit', [\App\Http\Controllers\CertificationController::class, 'edit'])->name('certificaciones.edit');
    Route::match(['put', 'patch'], '/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'update'])->name('certificaciones.update');
    Route::delete('/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'destroy'])->name('certificaciones.destroy');
    Route::get('/certificaciones/{id}/archivo/{slot}', [\App\Http\Controllers\CertificationController::class, 'download'])->name('certificaciones.download')->where('slot', '[12]');
});
