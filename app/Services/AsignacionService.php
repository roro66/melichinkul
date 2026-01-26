<?php

namespace App\Services;

use App\Models\AsignacionConductor;
use App\Models\Conductor;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Orquesta la asignación conductor–vehículo:
 * - Valida bloqueos (licencia, RT) vía BloqueoAsignacionService.
 * - Cierra la asignación anterior del vehículo.
 * - Crea la nueva asignación y actualiza vehiculo.conductor_actual_id.
 */
class AsignacionService
{
    public function __construct(
        protected BloqueoAsignacionService $bloqueo
    ) {}

    /**
     * Asigna un conductor a un vehículo.
     * Valida bloqueos, cierra asignación previa, crea nueva y actualiza vehiculo.
     *
     * @param  \Carbon\CarbonInterface|string|null  $fechaAsignacion  Por defecto hoy.
     * @throws ValidationException Si licencia vencida o RT del vehículo caducada.
     */
    public function asignar(
        Conductor $conductor,
        Vehiculo $vehiculo,
        ?User $asignadoPor = null,
        $fechaAsignacion = null,
        ?string $observaciones = null
    ): AsignacionConductor {
        $this->bloqueo->validarPuedeAsignar($conductor, $vehiculo);

        $fecha = $fechaAsignacion ? \Carbon\Carbon::parse($fechaAsignacion)->toDateString() : now()->toDateString();

        return DB::transaction(function () use ($conductor, $vehiculo, $asignadoPor, $fecha, $observaciones) {
            $activa = AsignacionConductor::where('vehiculo_id', $vehiculo->id)
                ->whereNull('fecha_fin')
                ->first();

            if ($activa) {
                $activa->update(['fecha_fin' => $fecha, 'motivo_fin' => 'cambio_conductor']);
            }

            $asignacion = AsignacionConductor::create([
                'conductor_id' => $conductor->id,
                'vehiculo_id' => $vehiculo->id,
                'fecha_asignacion' => $fecha,
                'fecha_fin' => null,
                'asignado_por_id' => $asignadoPor?->id,
                'observaciones' => $observaciones,
            ]);

            $vehiculo->update(['conductor_actual_id' => $conductor->id]);

            return $asignacion;
        });
    }
}
