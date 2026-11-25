<?php

namespace App\Http\Controllers;

use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dates = Validator::make($request->only('from', 'to'), [
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ])->valid();

        $from = isset($dates['from']) ? Carbon::parse($dates['from']) : now()->startOfMonth();
        $to = isset($dates['to']) ? Carbon::parse($dates['to']) : now();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];

        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('date', $range)
            ->latest('date')
            ->get();

        $totalSales = $sales->sum('total');
        $totalIgv = $sales->sum('igv');

        $topMedicines = DetailSale::with('medicine')
            ->select('medicine_id', DB::raw('SUM(amount) as quantity'), DB::raw('SUM(subtotal) as amount'))
            ->whereHas('sale', function ($query) use ($range) {
                $query->whereBetween('date', $range);
            })
            ->groupBy('medicine_id')
            ->orderByDesc('quantity')
            ->take(5)
            ->get();

        $lowStockMedicines = Medicine::with('category')
            ->where('stock', '<=', MedicineController::LOW_STOCK)
            ->orderBy('stock')
            ->get();

        return view('principal.reports.index', compact(
            'from', 'to', 'sales', 'totalSales', 'totalIgv', 'topMedicines', 'lowStockMedicines'
        ));
    }
}
