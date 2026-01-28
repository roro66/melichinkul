<?php

namespace App\Exports;

use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MaintenancesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Maintenance::with(['vehicle', 'responsibleTechnician', 'assignedDriver']);

        if (isset($this->filters['vehicle_id'])) {
            $query->where('vehicle_id', $this->filters['vehicle_id']);
        }

        if (isset($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('vehicle', function ($vehicleQuery) use ($search) {
                    $vehicleQuery->where('license_plate', 'like', "%{$search}%")
                                 ->orWhere('brand', 'like', "%{$search}%")
                                 ->orWhere('model', 'like', "%{$search}%");
                })
                ->orWhere('work_description', 'like', "%{$search}%")
                ->orWhere('entry_reason', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('scheduled_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Vehículo',
            'Patente',
            'Tipo',
            'Estado',
            'Fecha Programada',
            'Fecha Inicio',
            'Fecha Fin',
            'Descripción',
            'Costo Repuestos',
            'Costo Mano de Obra',
            'Costo Total',
            'Técnico Responsable',
            'Conductor Asignado',
            'Taller/Proveedor',
        ];
    }

    public function map($maintenance): array
    {
        return [
            $maintenance->id,
            $maintenance->vehicle ? $maintenance->vehicle->brand . ' ' . $maintenance->vehicle->model : 'Vehículo eliminado',
            $maintenance->vehicle ? $maintenance->vehicle->license_plate : '-',
            ucfirst($maintenance->type),
            ucfirst(str_replace('_', ' ', $maintenance->status)),
            $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d/m/Y') : '',
            $maintenance->start_date ? $maintenance->start_date->format('d/m/Y') : '',
            $maintenance->end_date ? $maintenance->end_date->format('d/m/Y') : '',
            $maintenance->work_description,
            $maintenance->parts_cost ?? 0,
            $maintenance->labor_cost ?? 0,
            $maintenance->total_cost ?? 0,
            $maintenance->responsibleTechnician ? $maintenance->responsibleTechnician->name : '-',
            $maintenance->assignedDriver ? $maintenance->assignedDriver->full_name : '-',
            $maintenance->workshop_supplier ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 12,
            'D' => 12,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 40,
            'J' => 15,
            'K' => 18,
            'L' => 15,
            'M' => 25,
            'N' => 25,
            'O' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
