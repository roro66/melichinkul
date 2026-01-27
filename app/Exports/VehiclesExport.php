<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Vehicle::with(['category', 'currentDriver']);

        // Aplicar filtros si existen
        if (isset($this->filters['category_id']) && $this->filters['category_id']) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['search']) && $this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('license_plate');
    }

    public function headings(): array
    {
        return [
            'Patente',
            'Marca',
            'Modelo',
            'Año',
            'Categoría',
            'Estado',
            'Conductor',
            'Kilometraje',
            'Horómetro',
            'Tipo Combustible',
            'Fecha Incorporación',
            'Valor Compra'
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->license_plate,
            $vehicle->brand,
            $vehicle->model,
            $vehicle->year,
            $vehicle->category->name ?? 'Sin categoría',
            ucfirst($vehicle->status),
            $vehicle->currentDriver->full_name ?? 'Sin asignar',
            $vehicle->current_mileage ? number_format($vehicle->current_mileage, 0, ',', '.') : '0',
            $vehicle->current_hours ? number_format($vehicle->current_hours, 0, ',', '.') : '0',
            ucfirst($vehicle->fuel_type),
            $vehicle->incorporation_date?->format('d/m/Y'),
            $vehicle->purchase_value ? '$' . number_format($vehicle->purchase_value, 0, ',', '.') : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Patente
            'B' => 15,  // Marca
            'C' => 20,  // Modelo
            'D' => 10,  // Año
            'E' => 20,  // Categoría
            'F' => 15,  // Estado
            'G' => 25,  // Conductor
            'H' => 15,  // Kilometraje
            'I' => 15,  // Horómetro
            'J' => 18,  // Tipo Combustible
            'K' => 18,  // Fecha Incorporación
            'L' => 18,  // Valor Compra
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
