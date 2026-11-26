<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        // 1. VENTAS DE HOY
        $ventasHoy = Sale::whereDate('date', today())
            ->sum('total');

        // 2. VENTAS DEL MES
        $ventasMes = Sale::whereBetween('date', [
            $inicioMes,
            $finMes,
        ])->sum('total');

        // 3. CLIENTES REGISTRADOS
        $totalClientes = Customer::count();

        // 4. MEDICAMENTOS CON STOCK BAJO
        $stockBajo = Medicine::where('stock', '<=', MedicineController::LOW_STOCK)->count();

        // 5. VENTAS DE LOS ÚLTIMOS 7 DÍAS
        $ventasLabels = [];
        $ventasDatos = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);

            $ventasLabels[] = $fecha->format('d/m');

            $ventasDatos[] = (float) Sale::whereDate(
                'date',
                $fecha
            )->sum('total');
        }

        // 6. VENTAS DEL MES POR CATEGORÍA
        $ventasCategorias = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->whereBetween('sales.date', [$inicioMes, $finMes])
            ->select(
                'categories.name',
                DB::raw('SUM(detail_sales.subtotal) as total')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        $categoriasLabels = $ventasCategorias->pluck('name');
        $categoriasDatos = $ventasCategorias->pluck('total');

        // 7. CINCO MEDICAMENTOS MÁS VENDIDOS
        $medicamentosVendidos = DB::table('detail_sales')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->whereBetween('sales.date', [$inicioMes, $finMes])
            ->select(
                'medicines.name',
                DB::raw('SUM(detail_sales.amount) as cantidad')
            )
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        $medicamentosLabels = $medicamentosVendidos->pluck('name');
        $medicamentosDatos = $medicamentosVendidos->pluck('cantidad');

        return view('principal.dashboard.index', compact(
            'ventasHoy',
            'ventasMes',
            'totalClientes',
            'stockBajo',
            'ventasLabels',
            'ventasDatos',
            'categoriasLabels',
            'categoriasDatos',
            'medicamentosLabels',
            'medicamentosDatos'
        ));
    }
}
