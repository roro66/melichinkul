<?php

/**
 * Pruebas tipo Tinker para funcionalidad reciente:
 * - maintenance_purchase_items: line_total = unit_price × quantity (boot del modelo)
 * - Alta proveedor (equivalente a quickStoreSupplier)
 * - Alta repuesto (equivalente a quickStoreSparePart)
 *
 * Ejecutar (con Docker): docker exec melichinkul_app php /var/www/html/scripts/tinker-recent-features-smoke.php
 * Sin local: php scripts/tinker-recent-features-smoke.php
 */

declare(strict_types=1);

use App\Models\Maintenance;
use App\Models\MaintenancePurchaseItem;
use App\Models\SparePart;
use App\Models\Supplier;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fail = 0;
$pass = 0;
$skip = 0;

function ok(string $msg): void
{
    global $pass;
    $pass++;
    fwrite(STDOUT, "[OK] {$msg}\n");
}

function bad(string $msg): void
{
    global $fail;
    $fail++;
    fwrite(STDOUT, "[FAIL] {$msg}\n");
}

function skp(string $msg): void
{
    global $skip;
    $skip++;
    fwrite(STDOUT, "[SKIP] {$msg}\n");
}

fwrite(STDOUT, "\n=== Smoke tests (estilo Tinker) — características recientes ===\n\n");

// 1) Tabla dinámica de líneas de compra en mantenimiento
if (! Schema::hasTable('maintenance_purchase_items')) {
    bad('No existe tabla maintenance_purchase_items');
} else {
    ok('Existe tabla maintenance_purchase_items');
}

// 2) Modelo MaintenancePurchaseItem fuerza line_total al guardar
$maintenance = Maintenance::query()->first();
if (! $maintenance || ! Schema::hasTable('maintenance_purchase_items')) {
    skp('line_total automático: sin mantenimiento o sin tabla');
} else {
    $tag = (string) time();
    $item = MaintenancePurchaseItem::create([
        'maintenance_id' => $maintenance->id,
        'spare_part_id' => null,
        'product_name' => 'Tinker test '.$tag,
        'supplier_name' => 'Proveedor tinker',
        'document_number' => 'TEST-'.$tag,
        'unit_price' => 2500,
        'quantity' => 3,
        'line_total' => 1,
        'document_image_path' => null,
    ]);
    $item->refresh();
    if ((int) $item->line_total === 7500) {
        ok('line_total recalculado: 2500 × 3 = 7500 (ignora valor erróneo previo)');
    } else {
        bad('line_total esperado 7500, obtuvo '.$item->line_total);
    }
    $item->delete();
}

// 3) Proveedor (misma idea que POST /compras/quick-supplier)
try {
    $name = 'Tinker proveedor '.time();
    $supplier = Supplier::create([
        'name' => $name,
        'rut' => null,
        'contact_name' => null,
        'phone' => null,
        'email' => null,
        'address' => null,
        'active' => true,
    ]);
    if ($supplier->id && $supplier->name === $name) {
        ok('Supplier::create (flujo quick proveedor)');
    } else {
        bad('Supplier creado pero datos inesperados');
    }
    $supplier->delete();
} catch (\Throwable $e) {
    bad('Supplier::create: '.$e->getMessage());
}

// 4) Repuesto (misma idea que POST /compras/quick-spare-part)
try {
    $code = 'TINKER-'.time();
    $spare = SparePart::create([
        'code' => $code,
        'description' => 'Repuesto creado desde smoke test',
        'brand' => null,
        'category' => 'spare_part',
        'reference_price' => 12345,
        'has_expiration' => false,
        'active' => true,
    ]);
    if ($spare->id && $spare->code === $code && (int) $spare->reference_price === 12345) {
        ok('SparePart::create (flujo quick repuesto)');
    } else {
        bad('SparePart creado pero datos inesperados');
    }
    $spare->delete();
} catch (\Throwable $e) {
    bad('SparePart::create: '.$e->getMessage());
}

// 5) Unicidad código repuesto (validación que usa quick-spare)
try {
    $code2 = 'TINKER-DUP-'.time();
    SparePart::create([
        'code' => $code2,
        'description' => 'Uno',
        'category' => 'spare_part',
        'reference_price' => null,
        'has_expiration' => false,
        'active' => true,
    ]);
    $dupOk = false;
    try {
        SparePart::create([
            'code' => $code2,
            'description' => 'Duplicado',
            'category' => 'spare_part',
            'reference_price' => null,
            'has_expiration' => false,
            'active' => true,
        ]);
    } catch (\Illuminate\Database\QueryException $e) {
        $dupOk = true;
    }
    SparePart::where('code', $code2)->delete();
    if ($dupOk) {
        ok('Código repuesto único a nivel BD (falla duplicado como en producción)');
    } else {
        bad('Se esperaba error por código duplicado');
    }
} catch (\Throwable $e) {
    bad('Prueba unicidad código: '.$e->getMessage());
}

fwrite(STDOUT, "\n--- Resumen: {$pass} OK, {$fail} fallos, {$skip} omitidos ---\n\n");

exit($fail > 0 ? 1 : 0);
