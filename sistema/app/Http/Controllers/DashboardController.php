<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMedicines = Medicine::count();
        $totalCustomers = Customer::count();
        $totalSales = Sale::count();
        $lowStock = Medicine::where('stock', '<=', MedicineController::LOW_STOCK)->count();

        return view('principal.dashboard.index', compact('totalMedicines', 'totalCustomers', 'totalSales', 'lowStock'));
    }
}
