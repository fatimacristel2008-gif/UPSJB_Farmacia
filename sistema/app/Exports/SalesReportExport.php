<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly array $filters) {}

    public function collection(): Collection
    {
        $sales = Sale::query()
            ->with(['customer', 'user'])
            ->whereDate('date', '>=', $this->filters['from'])
            ->whereDate('date', '<=', $this->filters['to'])
            ->when($this->filters['seller'], fn ($query, $seller) => $query->where('user_id', $seller))
            ->when($this->filters['category'], function ($query, $category) {
                $query->whereHas('details.medicine', fn ($medicine) => $medicine->where('category_id', $category));
            })
            ->orderByDesc('date')
            ->get();

        return $sales->map(fn (Sale $sale) => [
            'Fecha' => $sale->date?->format('d/m/Y H:i'),
            'Cliente' => $sale->customer->name ?? 'Sin cliente',
            'Vendedor' => $sale->user->name ?? 'Sin vendedor',
            'IGV' => (float) $sale->igv,
            'Total' => (float) $sale->total,
        ]);
    }

    public function headings(): array
    {
        return ['Fecha', 'Cliente', 'Vendedor', 'IGV', 'Total'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
