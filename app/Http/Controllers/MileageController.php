<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleMileageReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MileageController extends Controller
{
    /**
     * Planilla de ingreso rápido semanal.
     */
    public function index(Request $request)
    {
        $date = $request->filled('fecha')
            ? Carbon::parse($request->fecha)
            : now();

        $vehicles = Vehicle::query()
            ->whereIn('status', ['active', 'maintenance'])
            ->orderBy('license_plate')
            ->get();

        return view('kilometraje.index', [
            'vehicles' => $vehicles,
            'fecha' => $date,
        ]);
    }

    /**
     * Guardar lecturas de kilometraje (batch).
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'readings' => 'required|array',
            'readings.*.vehicle_id' => 'required|exists:vehicles,id',
            'readings.*.mileage' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fecha = Carbon::parse($request->fecha)->startOfDay();
        $userId = auth()->id();

        // Primera pasada: validar todas las lecturas antes de guardar (todo o nada)
        $validationErrors = [];
        foreach ($request->readings as $r) {
            $mileage = (float) ($r['mileage'] ?? 0);
            if ($mileage <= 0) {
                continue;
            }
            $vehicle = Vehicle::find($r['vehicle_id']);
            $lastReading = $vehicle->mileageReadings()->latest('recorded_at')->first();
            $lastKnown = max(
                (float) ($vehicle->current_mileage ?? 0),
                $lastReading ? (float) $lastReading->mileage : 0
            );
            if ($mileage < $lastKnown) {
                $validationErrors[] = [
                    'vehicle_id' => $vehicle->id,
                    'license_plate' => $vehicle->license_plate,
                    'model' => trim($vehicle->brand . ' ' . $vehicle->model),
                    'entered' => (int) $mileage,
                    'current' => (int) $lastKnown,
                ];
            }
        }

        if (! empty($validationErrors)) {
            return redirect()
                ->route('kilometraje.index', ['fecha' => $fecha->format('Y-m-d')])
                ->withInput()
                ->with('mileage_validation_errors', $validationErrors);
        }

        // Todas válidas: guardar en transacción
        $saved = 0;
        DB::transaction(function () use ($request, $fecha, $userId, &$saved) {
            foreach ($request->readings as $r) {
                $mileage = (float) ($r['mileage'] ?? 0);
                if ($mileage <= 0) {
                    continue;
                }
                $vehicle = Vehicle::find($r['vehicle_id']);
                VehicleMileageReading::updateOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'recorded_at' => $fecha,
                    ],
                    [
                        'mileage' => $mileage,
                        'recorded_by' => $userId,
                    ]
                );
                $vehicle->update([
                    'current_mileage' => $mileage,
                    'mileage_updated_at' => $fecha,
                ]);
                $saved++;
            }
        });

        $msg = $saved > 0 ? "Se guardaron {$saved} lecturas correctamente." : "No se guardó ninguna lectura.";
        return redirect()
            ->route('kilometraje.index', ['fecha' => $fecha->format('Y-m-d')])
            ->with('success', $msg);
    }

    /**
     * Formulario de importación masiva.
     */
    public function importForm()
    {
        return view('kilometraje.import');
    }

    /**
     * Procesar archivo CSV/Excel de importación.
     * Columnas esperadas: Patente, Fecha (dd-mm-yyyy o date), Kilometraje
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file = $request->file('archivo');
        $path = $file->getRealPath();

        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'csv') {
            $reader = IOFactory::createReader('Csv');
            $spreadsheet = $reader->load($path);
        } else {
            $spreadsheet = IOFactory::load($path);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_map('trim', array_map('strtolower', (array) ($rows[0] ?? [])));
        $patenteCol = $this->findColumnIndex($header, ['patente', 'placa']);
        $fechaCol = $this->findColumnIndex($header, ['fecha', 'date']);
        $kmCol = $this->findColumnIndex($header, ['kilometraje', 'km', 'mileage', 'odometro']);

        if ($patenteCol === null || $fechaCol === null || $kmCol === null) {
            return redirect()
                ->route('kilometraje.import')
                ->with('error', 'El archivo debe tener columnas: Patente, Fecha, Kilometraje.');
        }

        $imported = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i] ?? [];
            $patente = trim((string) ($row[$patenteCol] ?? ''));
            $fechaVal = $row[$fechaCol] ?? '';
            $kmVal = $row[$kmCol] ?? '';

            if (! $patente || $kmVal === '' || $kmVal === null) {
                continue;
            }

            $mileage = (float) preg_replace('/[^\d.,]/', '', str_replace(',', '.', (string) $kmVal));
            if ($mileage <= 0) {
                continue;
            }

            $recordedAt = $this->parseDate($fechaVal);
            if (! $recordedAt) {
                $errors[] = "Fila " . ($i + 1) . ": fecha inválida '{$fechaVal}'";
                continue;
            }

            $vehicle = Vehicle::where('license_plate', 'ilike', $patente)->first();
            if (! $vehicle) {
                $errors[] = "Fila " . ($i + 1) . ": vehículo con patente '{$patente}' no encontrado";
                continue;
            }

            $lastReading = $vehicle->mileageReadings()->latest('recorded_at')->first();
            $lastKnown = max(
                (float) ($vehicle->current_mileage ?? 0),
                $lastReading ? (float) $lastReading->mileage : 0
            );
            if ($mileage < $lastKnown) {
                $errors[] = "Fila " . ($i + 1) . ": {$patente} - kilometraje {$mileage} menor al último conocido ({$lastKnown})";
                continue;
            }

            VehicleMileageReading::updateOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'recorded_at' => $recordedAt,
                ],
                [
                    'mileage' => $mileage,
                    'recorded_by' => auth()->id(),
                ]
            );

            $vehicle->update([
                'current_mileage' => $mileage,
                'mileage_updated_at' => $recordedAt,
            ]);
            $imported++;
        }

        $msg = "Se importaron {$imported} lecturas.";
        if (! empty($errors)) {
            $msg .= ' Errores: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $msg .= ' (+' . (count($errors) - 5) . ' más)';
            }
        }

        return redirect()
            ->route('kilometraje.import')
            ->with($imported > 0 ? 'success' : 'error', $msg);
    }

    /**
     * Página de gráficos de kilometraje.
     */
    public function charts(Request $request)
    {
        $vehicleId = $request->input('vehicle_id');
        $vehicleIds = $request->input('vehicles', []);

        $vehicles = Vehicle::whereIn('status', ['active', 'maintenance'])
            ->orderBy('license_plate')
            ->get(['id', 'license_plate', 'brand', 'model']);

        return view('kilometraje.charts', [
            'vehicles' => $vehicles,
            'selectedVehicleId' => $vehicleId ? (int) $vehicleId : null,
            'selectedVehicleIds' => array_map('intval', array_filter($vehicleIds)),
        ]);
    }

    /**
     * Datos JSON para gráfico km vs tiempo (un vehículo).
     */
    public function chartDataVehicle(Vehicle $vehicle)
    {
        $readings = $vehicle->mileageReadings()
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'mileage']);

        return response()->json([
            'labels' => $readings->map(fn ($r) => $r->recorded_at->format('d-m-Y'))->values()->all(),
            'data' => $readings->map(fn ($r) => (float) $r->mileage)->values()->all(),
            'vehicle' => $vehicle->license_plate . ' - ' . $vehicle->brand . ' ' . $vehicle->model,
        ]);
    }

    /**
     * Datos JSON para gráfico comparativo (varios vehículos).
     */
    public function chartDataCompare(Request $request)
    {
        $ids = $request->input('vehicle_ids', []);
        if (empty($ids)) {
            return response()->json(['datasets' => [], 'labels' => []]);
        }

        $vehicles = Vehicle::whereIn('id', $ids)->get();
        $allDates = VehicleMileageReading::whereIn('vehicle_id', $ids)
            ->distinct()
            ->orderBy('recorded_at')
            ->pluck('recorded_at')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->values()
            ->all();

        $datasets = [];
        $colors = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'];

        foreach ($vehicles as $i => $v) {
            $byDate = $v->mileageReadings()
                ->orderBy('recorded_at')
                ->get()
                ->keyBy(fn ($r) => $r->recorded_at->format('Y-m-d'));

            $lastKnown = null;
            $values = [];
            foreach ($allDates as $d) {
                $r = $byDate->get($d);
                if ($r) {
                    $lastKnown = (float) $r->mileage;
                }
                $values[] = $lastKnown;
            }

            $datasets[] = [
                'label' => $v->license_plate . ' - ' . $v->brand . ' ' . $v->model,
                'data' => $values,
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => $colors[$i % count($colors)] . '33',
                'fill' => false,
            ];
        }

        return response()->json([
            'labels' => array_map(fn ($d) => \Carbon\Carbon::parse($d)->format('d-m-Y'), $allDates),
            'datasets' => $datasets,
        ]);
    }

    private function findColumnIndex(array $header, array $names): ?int
    {
        foreach ($names as $n) {
            $idx = array_search($n, $header);
            if ($idx !== false) {
                return $idx;
            }
        }
        return null;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Throwable) {
                return null;
            }
        }
        $s = trim((string) $value);
        if (! $s) {
            return null;
        }
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd.m.Y'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $s);
                if ($d) {
                    return $d;
                }
            } catch (\Throwable) {
                continue;
            }
        }
        try {
            return Carbon::parse($s);
        } catch (\Throwable) {
            return null;
        }
    }
}
