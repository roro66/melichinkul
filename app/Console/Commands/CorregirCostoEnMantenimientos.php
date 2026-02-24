<?php

namespace App\Console\Commands;

use App\Models\Maintenance;
use Illuminate\Console\Command;

class CorregirCostoEnMantenimientos extends Command
{
    protected $signature = 'mantenimientos:corregir-costo
                            {--dry-run : Solo mostrar qué se corregiría, sin guardar}';

    protected $description = 'Corrige mantenimientos donde el costo se guardó en workshop_supplier (importación con columnas intercambiadas) y total_cost está en 0';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Modo dry-run: no se guardarán cambios.');
        }

        $candidatos = Maintenance::query()
            ->where(function ($q) {
                $q->whereNull('total_cost')->orWhere('total_cost', 0);
            })
            ->whereNotNull('workshop_supplier')
            ->where('workshop_supplier', '!=', '')
            ->get();

        $corregidos = 0;
        foreach ($candidatos as $m) {
            $valor = $this->parsearNumero($m->workshop_supplier);
            if ($valor === null || $valor <= 0) {
                continue;
            }
            $this->line(sprintf(
                'Mantenimiento #%d (vehículo %s): workshop_supplier="%s" → total_cost=%s',
                $m->id,
                $m->vehicle_id,
                $m->workshop_supplier,
                number_format($valor, 0, ',', '.')
            ));
            if (! $dryRun) {
                $m->total_cost = (int) round($valor);
                $m->workshop_supplier = null;
                $m->save();
            }
            $corregidos++;
        }

        if ($corregidos === 0) {
            $this->info('No se encontraron registros para corregir.');
            return self::SUCCESS;
        }

        $this->info($dryRun
            ? "Se habrían corregido {$corregidos} registro(s). Ejecuta sin --dry-run para aplicar."
            : "Corregidos {$corregidos} registro(s)."
        );
        return self::SUCCESS;
    }

    /**
     * Parsea un número desde workshop_supplier (ej: "75000", "100.000", " $ 519,100 ", etc.).
     */
    private function parsearNumero(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        $s = preg_replace('/[\s\$]+/', '', $s);
        if ($s === '') {
            return null;
        }
        if (is_numeric($s)) {
            return (float) $s;
        }
        if (preg_match('/^[\d.,]+$/', $s)) {
            $soloDigitos = str_replace(['.', ','], '', $s);
            if (is_numeric($soloDigitos) && (int) $soloDigitos > 0) {
                return (float) $soloDigitos;
            }
        }
        return null;
    }
}
