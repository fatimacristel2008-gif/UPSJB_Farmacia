<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return view('settings.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|max:50|unique:categories',
            'descripcion' => 'nullable|max:255',
        ])->validate();

        Category::query()->create($validated);

        return to_route('categories.index')->with('ok', 'Categoría registrada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('settings.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|max:50|unique:categories,nombre,' . $category->id,
            'descripcion' => 'nullable|max:255',
        ])->validate();

        $category->update($validated);

        return to_route('categories.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
   {
        if ($category->medicines()->exists()) {
            return to_route('categories.index')->with('error', 'No se puede eliminar: la categoría tiene medicamentos asociados.');
        }

        $category->delete();

        return to_route('categories.index')->with('ok', 'Categoría eliminada correctamente.');
    }
    
}