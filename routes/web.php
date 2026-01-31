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

// Rutas protegidas (auth + permisos por ruta)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo Vehículos
    Route::get('/vehiculos-buscar', [\App\Http\Controllers\VehicleController::class, 'search'])->name('vehiculos.search')->middleware('permission:vehicles.view');
    Route::get('/vehiculos', [\App\Http\Controllers\VehicleController::class, 'index'])->name('vehiculos.index')->middleware('permission:vehicles.view');
    Route::get('/vehiculos/create', function ($id = null) {
        return view('vehiculos.create');
    })->name('vehiculos.create')->middleware('permission:vehicles.create');
    Route::get('/vehiculos/{id}/edit', function ($id) {
        return view('vehiculos.edit', ['id' => $id]);
    })->name('vehiculos.edit')->middleware('permission:vehicles.edit');
    Route::get('/vehiculos/{id}', [\App\Http\Controllers\VehicleController::class, 'show'])->name('vehiculos.show')->middleware('permission:vehicles.view');
    Route::delete('/vehiculos/{id}', [\App\Http\Controllers\VehicleController::class, 'destroy'])->name('vehiculos.destroy')->middleware('permission:vehicles.delete');
    Route::post('/vehiculos/export/{format}', [\App\Http\Controllers\VehicleController::class, 'export'])->name('vehiculos.export')->middleware('permission:vehicles.export');

    // Módulo Mantenimientos
    Route::get('/mantenimientos', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('mantenimientos.index')->middleware('permission:maintenances.view');
    Route::post('/mantenimientos/{id}/aprobar', [\App\Http\Controllers\MaintenanceController::class, 'approve'])->name('mantenimientos.approve')->middleware('permission:maintenances.approve');
    Route::post('/mantenimientos/{id}/repuestos', [\App\Http\Controllers\MaintenanceController::class, 'addSparePart'])->name('mantenimientos.repuestos.add')->middleware('permission:maintenances.edit');
    Route::delete('/mantenimientos/{id}/repuestos/{pivotId}', [\App\Http\Controllers\MaintenanceController::class, 'removeSparePart'])->name('mantenimientos.repuestos.remove')->middleware('permission:maintenances.edit');
    Route::post('/mantenimientos/{id}/checklist/{itemId}/toggle', [\App\Http\Controllers\MaintenanceController::class, 'toggleChecklistItem'])->name('mantenimientos.checklist.toggle')->middleware('permission:maintenances.edit');
    Route::delete('/mantenimientos/{id}', [\App\Http\Controllers\MaintenanceController::class, 'destroy'])->name('mantenimientos.destroy')->middleware('permission:maintenances.delete');
    Route::post('/mantenimientos/export/{format}', [\App\Http\Controllers\MaintenanceController::class, 'export'])->name('mantenimientos.export')->middleware('permission:maintenances.export');
    Route::get('/mantenimientos/create', function () {
        return view('mantenimientos.create');
    })->name('mantenimientos.create')->middleware('permission:maintenances.create');
    Route::get('/mantenimientos/{id}/edit', function ($id) {
        return view('mantenimientos.edit', ['id' => $id]);
    })->name('mantenimientos.edit')->middleware('permission:maintenances.edit');
    Route::get('/mantenimientos/{id}', function ($id) {
        return view('mantenimientos.show', ['id' => $id]);
    })->name('mantenimientos.show')->middleware('permission:maintenances.view');

    // Plantillas de mantenimiento
    Route::get('/plantillas', [\App\Http\Controllers\MaintenanceTemplateController::class, 'index'])->name('plantillas.index')->middleware('permission:maintenances.view');
    Route::get('/plantillas/create', [\App\Http\Controllers\MaintenanceTemplateController::class, 'create'])->name('plantillas.create')->middleware('permission:maintenances.create');
    Route::post('/plantillas', [\App\Http\Controllers\MaintenanceTemplateController::class, 'store'])->name('plantillas.store')->middleware('permission:maintenances.create');
    Route::get('/plantillas/{id}/edit', [\App\Http\Controllers\MaintenanceTemplateController::class, 'edit'])->name('plantillas.edit')->middleware('permission:maintenances.create');
    Route::match(['put', 'patch'], '/plantillas/{id}', [\App\Http\Controllers\MaintenanceTemplateController::class, 'update'])->name('plantillas.update')->middleware('permission:maintenances.create');
    Route::delete('/plantillas/{id}', [\App\Http\Controllers\MaintenanceTemplateController::class, 'destroy'])->name('plantillas.destroy')->middleware('permission:maintenances.create');

    // Ítems de checklist de mantenimiento
    Route::get('/checklist', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'index'])->name('checklist.index')->middleware('permission:maintenances.view');
    Route::get('/checklist/create', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'create'])->name('checklist.create')->middleware('permission:maintenances.create');
    Route::post('/checklist', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'store'])->name('checklist.store')->middleware('permission:maintenances.create');
    Route::get('/checklist/{id}/edit', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'edit'])->name('checklist.edit')->middleware('permission:maintenances.create');
    Route::match(['put', 'patch'], '/checklist/{id}', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'update'])->name('checklist.update')->middleware('permission:maintenances.create');
    Route::delete('/checklist/{id}', [\App\Http\Controllers\MaintenanceChecklistItemController::class, 'destroy'])->name('checklist.destroy')->middleware('permission:maintenances.create');

    // Módulo Conductores
    Route::get('/conductores', [\App\Http\Controllers\DriverController::class, 'index'])->name('conductores.index')->middleware('permission:drivers.view');
    Route::get('/conductores/create', [\App\Http\Controllers\DriverController::class, 'create'])->name('conductores.create')->middleware('permission:drivers.create');
    Route::get('/conductores/{id}/edit', [\App\Http\Controllers\DriverController::class, 'edit'])->name('conductores.edit')->middleware('permission:drivers.edit');
    Route::get('/conductores/{id}', [\App\Http\Controllers\DriverController::class, 'show'])->name('conductores.show')->middleware('permission:drivers.view');
    Route::delete('/conductores/{id}', [\App\Http\Controllers\DriverController::class, 'destroy'])->name('conductores.destroy')->middleware('permission:drivers.delete');

    // Módulo Alertas
    Route::get('/alertas', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index')->middleware('permission:alerts.view');
    Route::post('/alertas/{id}/cerrar', [\App\Http\Controllers\AlertController::class, 'close'])->name('alerts.close')->middleware('permission:alerts.close');
    Route::post('/alertas/{id}/posponer', [\App\Http\Controllers\AlertController::class, 'snooze'])->name('alerts.snooze')->middleware('permission:alerts.snooze');

    // Módulo Repuestos
    Route::get('/repuestos', [\App\Http\Controllers\SparePartController::class, 'index'])->name('repuestos.index')->middleware('permission:spare_parts.view');
    Route::get('/repuestos/create', [\App\Http\Controllers\SparePartController::class, 'create'])->name('repuestos.create')->middleware('permission:spare_parts.create');
    Route::post('/repuestos', [\App\Http\Controllers\SparePartController::class, 'store'])->name('repuestos.store')->middleware('permission:spare_parts.create');
    Route::get('/repuestos/{id}/edit', [\App\Http\Controllers\SparePartController::class, 'edit'])->name('repuestos.edit')->middleware('permission:spare_parts.edit');
    Route::match(['put', 'patch'], '/repuestos/{id}', [\App\Http\Controllers\SparePartController::class, 'update'])->name('repuestos.update')->middleware('permission:spare_parts.edit');
    Route::delete('/repuestos/{id}', [\App\Http\Controllers\SparePartController::class, 'destroy'])->name('repuestos.destroy')->middleware('permission:spare_parts.delete');
    Route::get('/repuestos/{id}/ajustar', [\App\Http\Controllers\StockController::class, 'showAdjustForm'])->name('repuestos.ajustar')->middleware('permission:spare_parts.adjust_stock');
    Route::post('/repuestos/{id}/ajustar', [\App\Http\Controllers\StockController::class, 'storeAdjustment'])->name('repuestos.ajustar.store')->middleware('permission:spare_parts.adjust_stock');
    Route::post('/repuestos/{id}/stock', [\App\Http\Controllers\StockController::class, 'updateSettings'])->name('repuestos.stock.update')->middleware('permission:spare_parts.edit');
    Route::get('/repuestos/{id}', [\App\Http\Controllers\SparePartController::class, 'show'])->name('repuestos.show')->middleware('permission:spare_parts.view');

    // Movimientos de inventario
    Route::get('/inventario/movimientos', [\App\Http\Controllers\InventoryMovementController::class, 'index'])->name('inventario.movimientos.index')->middleware('permission:inventory.view_movements');

    // Módulo Proveedores
    Route::get('/proveedores', [\App\Http\Controllers\SupplierController::class, 'index'])->name('proveedores.index')->middleware('permission:suppliers.view');
    Route::get('/proveedores/create', [\App\Http\Controllers\SupplierController::class, 'create'])->name('proveedores.create')->middleware('permission:suppliers.create');
    Route::post('/proveedores', [\App\Http\Controllers\SupplierController::class, 'store'])->name('proveedores.store')->middleware('permission:suppliers.create');
    Route::get('/proveedores/{id}/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])->name('proveedores.edit')->middleware('permission:suppliers.edit');
    Route::match(['put', 'patch'], '/proveedores/{id}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('proveedores.update')->middleware('permission:suppliers.edit');
    Route::delete('/proveedores/{id}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('proveedores.destroy')->middleware('permission:suppliers.delete');

    // Módulo Compras
    Route::get('/compras', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('compras.index')->middleware('permission:purchases.view');
    Route::get('/compras/create', [\App\Http\Controllers\PurchaseController::class, 'create'])->name('compras.create')->middleware('permission:purchases.create');
    Route::post('/compras', [\App\Http\Controllers\PurchaseController::class, 'store'])->name('compras.store')->middleware('permission:purchases.create');
    Route::get('/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('compras.show')->middleware('permission:purchases.view');
    Route::get('/compras/{id}/edit', [\App\Http\Controllers\PurchaseController::class, 'edit'])->name('compras.edit')->middleware('permission:purchases.edit');
    Route::match(['put', 'patch'], '/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'update'])->name('compras.update')->middleware('permission:purchases.edit');
    Route::delete('/compras/{id}', [\App\Http\Controllers\PurchaseController::class, 'destroy'])->name('compras.destroy')->middleware('permission:purchases.delete');
    Route::post('/compras/{id}/recibir', [\App\Http\Controllers\PurchaseController::class, 'receive'])->name('compras.receive')->middleware('permission:purchases.receive');
    Route::post('/compras/export/{format}', [\App\Http\Controllers\PurchaseController::class, 'export'])->name('compras.export')->middleware('permission:purchases.export');

    // Módulo Certificaciones
    Route::get('/vehiculos/{vehicleId}/certificaciones/create', [\App\Http\Controllers\CertificationController::class, 'create'])->name('certificaciones.create')->middleware('permission:certifications.create');
    Route::post('/certificaciones', [\App\Http\Controllers\CertificationController::class, 'store'])->name('certificaciones.store')->middleware('permission:certifications.create');
    Route::get('/certificaciones/{id}/edit', [\App\Http\Controllers\CertificationController::class, 'edit'])->name('certificaciones.edit')->middleware('permission:certifications.edit');
    Route::match(['put', 'patch'], '/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'update'])->name('certificaciones.update')->middleware('permission:certifications.edit');
    Route::delete('/certificaciones/{id}', [\App\Http\Controllers\CertificationController::class, 'destroy'])->name('certificaciones.destroy')->middleware('permission:certifications.delete');
    Route::get('/certificaciones/{id}/archivo/{slot}', [\App\Http\Controllers\CertificationController::class, 'download'])->name('certificaciones.download')->where('slot', '[12]')->middleware('permission:certifications.view');

    // Auditoría (solo administrator y supervisor)
    Route::get('/auditoria', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index')->middleware('permission:audit.view');
});