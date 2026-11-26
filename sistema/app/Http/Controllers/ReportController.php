<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'seller' => 'nullable|exists:users,id',
            'category' => 'nullable|exists:categories,id',
        ]);

        $from = $request->input('from')
            ?: now()->startOfMonth()->toDateString();

        $to = $request->input('to')
            ?: now()->toDateString();

        $seller = $request->input('seller');
        $category = $request->input('category');

        $vendors = User::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $query = Sale::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->when($seller, function ($q) use ($seller) {
                $q->where('user_id', $seller);
            })
            ->when($category, function ($q) use ($category) {
                $q->whereHas('details.medicine', function ($m) use ($category) {
                    $m->where('category_id', $category);
                });
            });

        $totalSales = (clone $query)->sum('total');
        $salesCount = (clone $query)->count();
        $averageSale = $salesCount ? $totalSales / $salesCount : 0;

        $recentSales = (clone $query)
            ->with(['customer', 'user'])
            ->orderByDesc('date')
            ->get();

        $daily = (clone $query)
            ->selectRaw('DATE(date) as day, SUM(total) as revenue')
            ->groupByRaw('DATE(date)')
            ->pluck('revenue', 'day');

        $labels = [];
        $values = [];

        for (
            $day = Carbon::parse($from);
            $day->lte(Carbon::parse($to));
            $day->addDay()
        ) {
            $labels[] = $day->format('d/m/Y');
            $values[] = (float) ($daily[$day->toDateString()] ?? 0);
        }

        return view('principal.reports.index', compact(
            'from',
            'to',
            'seller',
            'category',
            'vendors',
            'categories',
            'totalSales',
            'salesCount',
            'averageSale',
            'recentSales',
            'labels',
            'values'
        ));
    }
}
