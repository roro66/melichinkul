<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PurchasesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Purchase::with(['supplier', 'user']);

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('purchase_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Proveedor',
            'Fecha',
            'Referencia',
            'Estado',
            'Total',
            'Registrado por',
        ];
    }

    public function map($purchase): array
    {
        $statuses = Purchase::STATUSES;
        $statusLabel = $statuses[$purchase->status] ?? $purchase->status;

        return [
            $purchase->id,
            $purchase->supplier?->name ?? '—',
            $purchase->purchase_date?->format('d/m/Y') ?? '—',
            $purchase->reference ?? '—',
            $statusLabel,
            $purchase->totalAmount(),
            $purchase->user?->name ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 12,
            'D' => 20,
            'E' => 14,
            'F' => 14,
            'G' => 25,
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
