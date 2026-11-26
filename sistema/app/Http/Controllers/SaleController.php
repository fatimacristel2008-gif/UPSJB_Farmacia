<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest('date')->get();

        return view('management.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $medicines = Medicine::where('stock', '>', 0)->orderBy('name')->get();

        return view('management.sales.create', compact('customers', 'users', 'medicines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|distinct|exists:medicines,id',
            'items.*.amount' => 'required|integer|min:1',
        ], [
            'items.required' => 'Debe agregar al menos un medicamento.',
            'items.*.medicine_id.distinct' => 'No repita el mismo medicamento, aumente la cantidad.',
        ]);

        $sale = DB::transaction(function () use ($request) {
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'date' => now(),
                'total' => 0,
                'igv' => 0,
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $medicine = Medicine::lockForUpdate()->find($item['medicine_id']);

                if ($medicine->stock < $item['amount']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$medicine->stock}).",
                    ]);
                }

                $subtotal = $medicine->price * $item['amount'];

                $sale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $item['amount'],
                    'price_unit' => $medicine->price,
                    'subtotal' => $subtotal,
                ]);

                $medicine->decrement('stock', $item['amount']);

                $total += $subtotal;
            }

            $sale->update([
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);

            return $sale;
        });

        return to_route('sales.show', $sale)->with('ok', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.medicine']);

        return view('management.sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                Medicine::where('id', $detail->medicine_id)->increment('stock', $detail->amount);
            }

            $sale->delete();
        });

        return to_route('sales.index')->with('ok', 'Venta anulada y stock devuelto.');
    }

    public function edit(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.medicine']);
        $customers = Customer::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $medicines = Medicine::where(function ($query) use ($sale) {
            $query->where('stock', '>', 0)
                ->orWhereIn('id', $sale->details->pluck('medicine_id'));
        })->orderBy('name')->get();

        return view('management.sales.edit', compact('sale', 'customers', 'users', 'medicines'));
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|distinct|exists:medicines,id',
            'items.*.amount' => 'required|integer|min:1',
        ], [
            'items.required' => 'Debe agregar al menos un medicamento.',
            'items.*.medicine_id.distinct' => 'No repita el mismo medicamento, aumente la cantidad.',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $oldDetails = $sale->details()->lockForUpdate()->get()->keyBy('medicine_id');

            foreach ($oldDetails as $medicineId => $detail) {
                Medicine::whereKey($medicineId)->lockForUpdate()->increment('stock', $detail->amount);
            }

            $sale->details()->delete();
            $total = 0;

            foreach ($request->items as $item) {
                $medicine = Medicine::lockForUpdate()->findOrFail($item['medicine_id']);

                if ($medicine->stock < $item['amount']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$medicine->stock}).",
                    ]);
                }

                $historicalDetail = $oldDetails->get($medicine->id);
                $priceUnit = $historicalDetail
                    ? (float) $historicalDetail->price_unit
                    : (float) $medicine->price;

                $subtotal = round($priceUnit * $item['amount'], 2);

                $sale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $item['amount'],
                    'price_unit' => $priceUnit,
                    'subtotal' => $subtotal,
                ]);

                $medicine->decrement('stock', $item['amount']);
                $total += $subtotal;
            }

            $sale->update([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'total' => round($total, 2),
                'igv' => round($total - $total / 1.18, 2),
            ]);
        });

        return to_route('sales.show', $sale)->with('ok', 'Venta actualizada correctamente.');
    }
}
