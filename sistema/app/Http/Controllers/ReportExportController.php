<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function pdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $sales = $this->filteredSales($filters);

        $pdf = Pdf::loadView('principal.reports.pdf', [
            'sales' => $sales,
            'from' => $filters['from'],
            'to' => $filters['to'],
            'totalSales' => $sales->sum('total'),
            'salesCount' => $sales->count(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('reporte-ventas-'.$filters['from'].'-'.$filters['to'].'.pdf');
    }

    public function excel(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request);

        return Excel::download(
            new SalesReportExport($filters),
            'reporte-ventas-'.$filters['from'].'-'.$filters['to'].'.xlsx'
        );
    }

    private function validatedFilters(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'seller' => 'nullable|exists:users,id',
            'category' => 'nullable|exists:categories,id',
        ]);

        return [
            'from' => $request->input('from') ?: now()->startOfMonth()->toDateString(),
            'to' => $request->input('to') ?: now()->toDateString(),
            'seller' => $request->input('seller'),
            'category' => $request->input('category'),
        ];
    }

    private function filteredSales(array $filters)
    {
        return Sale::query()
            ->with(['customer', 'user'])
            ->whereDate('date', '>=', $filters['from'])
            ->whereDate('date', '<=', $filters['to'])
            ->when($filters['seller'], fn ($query, $seller) => $query->where('user_id', $seller))
            ->when($filters['category'], function ($query, $category) {
                $query->whereHas('details.medicine', fn ($medicine) => $medicine->where('category_id', $category));
            })
            ->orderByDesc('date')
            ->get();
    }
}
