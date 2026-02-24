<?php

namespace App\Console\Commands;

use App\Http\Controllers\CertificationController;
use App\Models\Certification;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarPlanillaExcel extends Command
{
    protected $signature = 'planilla:importar
                            {archivo : Ruta al archivo Excel (ej. storage/app/planilla.xlsx o ruta absoluta)}
                            {--limpiar : Borrar antes todos los vehículos, categorías, mantenimientos y certificaciones}
                            {--yes : No pedir confirmación al limpiar}';

    protected $description = 'Borra datos de prueba (opcional) e importa vehículos, certificaciones y mantenimientos desde la planilla Excel';

    /** Mapeo nombre documento en Excel -> type en certifications */
    private const DOC_TYPE_MAP = [
        'revision tecnica' => 'technical_review',
        'revisión técnica' => 'technical_review',
        'revison gases' => 'analisis_gases',
        'revision gases' => 'analisis_gases',
        'revisión gases' => 'analisis_gases',
        'permico circulacion' => 'permiso_circulacion',
        'permiso circulacion' => 'permiso_circulacion',
        'permiso de circulación' => 'permiso_circulacion',
        'soap' => 'soap',
        'extintor cabina' => 'extintor_cabina',
        'extintor chasis' => 'extintor_chasis',
    ];

    public function handle(): int
    {
        $path = $this->argument('archivo');
        if (! is_file($path)) {
            $this->error("No se encontró el archivo: {$path}");
            return self::FAILURE;
        }

        if ($this->option('limpiar')) {
            if (! $this->option('yes') && ! $this->confirm('¿Borrar TODOS los vehículos, categorías de vehículo, mantenimientos y certificaciones? (usuarios y conductores no se tocan)')) {
                return self::SUCCESS;
            }
            $this->limpiarDatos();
        }

        $this->info('Leyendo Excel...');
        $spreadsheet = IOFactory::load($path);
        $resumen = $spreadsheet->getSheetByName('Resumen');
        if (! $resumen) {
            $this->error('No se encontró la hoja "Resumen" en el Excel.');
            return self::FAILURE;
        }

        $resumenRows = $resumen->toArray();
        $headers = $resumenRows[2] ?? []; // row 3 = index 2
        $dataRows = array_slice($resumenRows, 3); // from row 4

        // Columnas Resumen (0-based): 0=Registro, 1=IR, 2=Patente, 3=Tipo, 4=Chasis, 5=Rut Tramites, 6=Rut Prop, 7=Tarjeta, 8=GPS, 9=Medida Neum, 16=Km, 17=Fecha KM
        $tiposUnicos = [];
        foreach ($dataRows as $row) {
            $patente = $this->celda($row, 2);
            $tipo = $this->celda($row, 3);
            if (! $patente) {
                continue;
            }
            $tipo = trim((string) $tipo) ?: 'Sin tipo';
            $tiposUnicos[$tipo] = true;
        }
        $tiposUnicos = array_keys($tiposUnicos);

        $this->info('Creando categorías: '.implode(', ', $tiposUnicos));
        $categoryIds = $this->crearCategorias($tiposUnicos);

        $vehiclesCreated = 0;
        $certificationsCreated = 0;
        $maintenancesCreated = 0;
        $fechaIncorporacionDefault = Carbon::parse('2020-01-01');

        foreach ($dataRows as $row) {
            $nombreRegistro = $this->celda($row, 0);
            $patente = $this->celda($row, 2);
            $tipo = trim((string) $this->celda($row, 3));
            if (! $patente) {
                continue;
            }
            $tipo = $tipo ?: 'Sin tipo';
            $categoryId = $categoryIds[$tipo] ?? null;

            $vehicle = Vehicle::firstOrNew(['license_plate' => $this->normalizarPatente($patente)]);
            $vehicle->brand = $tipo;
            $vehicle->model = $tipo;
            $vehicle->year = (int) ($vehicle->year ?: date('Y'));
            $vehicle->chassis_number = $this->celda($row, 4) ?: null;
            $vehicle->rut_tramites = $this->valorONull($row, 5);
            $vehicle->rut_propietario = $this->valorONull($row, 6);
            $vehicle->tarjeta_combustible = $this->siNoABool($this->celda($row, 7));
            $vehicle->gps = $this->siNoABool($this->celda($row, 8));
            $vehicle->tire_size = $this->valorONull($row, 9);
            $vehicle->current_mileage = (float) ($this->parseNumeroChileno($this->celda($row, 16)) ?: 0);
            $vehicle->mileage_updated_at = $this->fechaDesdeCelda($this->celda($row, 17));
            $vehicle->category_id = $categoryId;
            $vehicle->fuel_type = 'diesel';
            $vehicle->status = 'active';
            $vehicle->incorporation_date = $vehicle->incorporation_date ?? $fechaIncorporacionDefault;
            $vehicle->current_driver_id = null;
            if (! $vehicle->exists) {
                $vehicle->incorporation_date = $fechaIncorporacionDefault;
            }
            $vehicle->save();
            $vehiclesCreated++;

            $sheetName = $nombreRegistro ?: $patente;
            $hoja = $spreadsheet->getSheetByName((string) $sheetName);
            if ($hoja) {
                $sheetRows = $hoja->toArray();
                $this->importarDatosDetalleVehículo($vehicle, $sheetRows);
                $c = $this->importarCertificaciones($vehicle, $sheetRows);
                $m = $this->importarMantenimientos($vehicle, $sheetRows);
                $certificationsCreated += $c;
                $maintenancesCreated += $m;
            }
        }

        $this->info("Listo. Vehículos: {$vehiclesCreated}, Certificaciones: {$certificationsCreated}, Mantenimientos: {$maintenancesCreated}.");
        return self::SUCCESS;
    }

    private function limpiarDatos(): void
    {
        $this->warn('Eliminando vehículos (y en cascada: alertas, mantenimientos, certificaciones, asignaciones)...');
        Vehicle::query()->forceDelete();
        $this->warn('Eliminando categorías de vehículo...');
        VehicleCategory::query()->delete();
        $this->info('Datos operativos borrados.');
    }

    private function crearCategorias(array $nombres): array
    {
        $ids = [];
        foreach ($nombres as $nombre) {
            $slug = \Illuminate\Support\Str::slug($nombre);
            $cat = VehicleCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $nombre,
                    'description' => null,
                    'main_counter' => 'mileage',
                    'default_criticality' => 'medium',
                    'requires_certifications' => true,
                    'requires_special_certifications' => false,
                    'active' => true,
                ]
            );
            $ids[$nombre] = $cat->id;
        }
        return $ids;
    }

    private function importarDatosDetalleVehículo(Vehicle $vehicle, array $sheetRows): void
    {
        $val = fn ($rowIdx, $colIdx) => $this->celda($sheetRows[$rowIdx] ?? [], $colIdx);
        if (count($sheetRows) < 12) {
            return;
        }
        $vehicle->chassis_number = $vehicle->chassis_number ?: $val(5, 2);
        $vehicle->rut_propietario = $vehicle->rut_propietario ?: $val(7, 2);
        $vehicle->tarjeta_combustible = $vehicle->tarjeta_combustible ?: $this->siNoABool($val(8, 2));
        $vehicle->gps = $vehicle->gps ?: $this->siNoABool($val(9, 2));
        $vehicle->tire_size = $vehicle->tire_size ?: $val(10, 2);
        $vehicle->safety_gata = $this->valorONullArray($sheetRows, 12, 7);
        $vehicle->safety_llave_rueda = $this->valorONullArray($sheetRows, 13, 7);
        $vehicle->safety_triangulo = $this->valorONullArray($sheetRows, 14, 7);
        $vehicle->safety_botiquin = $this->valorONullArray($sheetRows, 15, 7);
        $vehicle->safety_gancho_arrastre = $this->valorONullArray($sheetRows, 16, 7);
        $vehicle->safety_last_inspection_date = $this->fechaInspeccionSeguridadDesdeFilas($sheetRows);
        $vehicle->save();
    }

    private function importarCertificaciones(Vehicle $vehicle, array $sheetRows): int
    {
        $count = 0;
        $docRows = [12 => 'technical_review', 13 => 'analisis_gases', 14 => 'permiso_circulacion', 15 => 'soap', 16 => 'extintor_cabina', 17 => 'extintor_chasis'];
        foreach ($docRows as $rowIdx => $defaultType) {
            $row = $sheetRows[$rowIdx] ?? [];
            $nombreDoc = $this->celda($row, 1);
            if (! $nombreDoc) {
                continue;
            }
            $type = $this->mapearTipoCertificacion($nombreDoc) ?? $defaultType;
            $emision = $this->fechaDesdeCelda($this->celda($row, 2));
            $vencimiento = $this->fechaDesdeCelda($this->celda($row, 3));
            if (! $vencimiento) {
                continue;
            }
            $name = CertificationController::CERT_TYPES[$type] ?? $nombreDoc;
            Certification::updateOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'type' => $type,
                ],
                [
                    'name' => $name,
                    'issue_date' => $emision,
                    'expiration_date' => $vencimiento,
                    'required' => true,
                    'active' => true,
                ]
            );
            $count++;
        }
        return $count;
    }

    private function mapearTipoCertificacion(string $nombre): ?string
    {
        $key = mb_strtolower(trim($nombre));
        return self::DOC_TYPE_MAP[$key] ?? null;
    }

    private function importarMantenimientos(Vehicle $vehicle, array $sheetRows): int
    {
        $count = 0;
        for ($i = 21; $i < count($sheetRows); $i++) {
            $row = $sheetRows[$i];
            $fecha = $this->fechaDesdeCelda($this->celda($row, 1));
            $servicio = $this->celda($row, 3);
            if (! $fecha || ! $servicio) {
                continue;
            }
            $km = $this->celda($row, 2);
            $mecanico = $this->celda($row, 6);
            $costo = (int) (float) ($this->parseNumeroChileno($this->celda($row, 7)) ?: 0);
            $obs = $this->celda($row, 8);
            if ($km === 'No registra' || $km === null || $km === '') {
                $km = null;
            } else {
                $km = (float) ($this->parseNumeroChileno($km) ?: 0);
            }
            Maintenance::create([
                'vehicle_id' => $vehicle->id,
                'type' => 'corrective',
                'status' => 'completed',
                'scheduled_date' => $fecha,
                'end_date' => $fecha,
                'mileage_at_maintenance' => $km,
                'work_description' => $servicio,
                'workshop_supplier' => $mecanico ?: null,
                'total_cost' => $costo,
                'observations' => $obs ?: null,
            ]);
            $count++;
        }
        return $count;
    }

    private function celda(array $row, int $col): mixed
    {
        $v = $row[$col] ?? null;
        return $v === null || $v === '' ? null : $v;
    }

    private function valorONull(array $row, int $col): ?string
    {
        $v = $this->celda($row, $col);
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    }

    private function valorONullArray(array $rows, int $rowIdx, int $col): ?string
    {
        $row = $rows[$rowIdx] ?? [];
        $v = $this->celda($row, $col);
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }
        $extra = $this->celda($row, $col + 2);
        if ($extra) {
            $s .= ' - '.trim((string) $extra);
        }
        return $s;
    }

    /**
     * Obtiene la fecha de última inspección de elementos de seguridad desde la columna "Revisado el dd-mm-yy".
     * Busca en las filas 12-16 (Gata, Llave rueda, etc.) la primera celda con texto tipo "Revisado el 20-04-23".
     */
    private function fechaInspeccionSeguridadDesdeFilas(array $sheetRows): ?Carbon
    {
        for ($rowIdx = 12; $rowIdx <= 16; $rowIdx++) {
            $row = $sheetRows[$rowIdx] ?? [];
            $texto = $this->celda($row, 8);
            if ($texto === null || $texto === '') {
                continue;
            }
            $date = $this->parseFechaRevisadoEl(trim((string) $texto));
            if ($date) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Parsea una cadena tipo "Revisado el 20-04-23" o "20-04-23" a Carbon (d-m-y).
     */
    private function parseFechaRevisadoEl(string $texto): ?Carbon
    {
        if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{2,4})\b/', $texto, $m)) {
            $d = (int) $m[1];
            $mes = (int) $m[2];
            $y = (int) $m[3];
            if ($y < 100) {
                $y += $y >= 50 ? 1900 : 2000;
            }
            try {
                return Carbon::createFromDate($y, $mes, $d);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function siNoABool(mixed $v): bool
    {
        if ($v === null || $v === '') {
            return false;
        }
        $s = mb_strtolower(trim((string) $v));
        return in_array($s, ['si', 'sí', 'yes', '1', 'true'], true);
    }

    private function fechaDesdeCelda(mixed $v): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        if ($v instanceof Carbon) {
            return $v;
        }
        try {
            if (is_numeric($v)) {
                $dt = ExcelDate::excelToDateTimeObject($v);
                return Carbon::instance($dt);
            }
            return Carbon::parse($v);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizarPatente(string $patente): string
    {
        return strtoupper(trim(preg_replace('/\s+/', '', $patente)));
    }

    /**
     * Parsea un número en formato chileno: punto como separador de miles, coma como decimal.
     * Ej: "330.970" -> 330970, "1.234,56" -> 1234.56
     * Si Excel devuelve float (ej. 330.97 cuando en la celda se ve 330.970), se corrige a 330970.
     */
    private function parseNumeroChileno(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_int($v)) {
            return (float) $v;
        }
        if (is_float($v)) {
            if ($v >= 100 && $v < 1000 && $v != floor($v)) {
                return round($v * 1000);
            }
            return $v;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^\d{1,3},\d{3}$/', $s)) {
            return (float) str_replace(',', '', $s);
        }
        if (preg_match('/^\d{1,3},\d{2}$/', $s)) {
            $parts = explode(',', $s);
            $antes = (int) $parts[0];
            $despues = (int) $parts[1];
            if ($antes >= 100 && $despues < 100) {
                return (float) ($antes * 1000 + $despues * 10);
            }
        }
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return $s === '' ? null : (float) $s;
    }
}
