<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    const LOW_STOCK = 10;

    public function index(Request $request)
    {
        $medicines = Medicine::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->boolean('low_stock'), function ($query) {
                $query->where('stock', '<=', self::LOW_STOCK);
            })
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();
        $lowStock = self::LOW_STOCK;

        return view('management.medicines.index', compact('medicines', 'categories', 'lowStock'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('management.medicines.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:120',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('medicines', 'public');
        }

        Medicine::create($validated);

        return to_route('medicines.index')->with('ok', 'Medicamento registrado correctamente.');
    }

    public function edit(Medicine $medicine)
    {
        $categories = Category::orderBy('name')->get();

        return view('management.medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:120',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($medicine->image) {
                Storage::disk('public')->delete($medicine->image);
            }
            $validated['image'] = $request->file('image')->store('medicines', 'public');
        } else {
            unset($validated['image']);
        }

        $medicine->update($validated);

        return to_route('medicines.index')->with('ok', 'Medicamento actualizado correctamente.');
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->detailSales()->exists()) {
            return to_route('medicines.index')->with('error', 'No se puede eliminar el medicamento porque tiene ventas registradas.');
        }

        if ($medicine->image) {
            Storage::disk('public')->delete($medicine->image);
        }

        $medicine->delete();

        return to_route('medicines.index')->with('ok', 'Medicamento eliminado correctamente.');
    }
}
