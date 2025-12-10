<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parts = Part::paginate(15);
        return response()->json($parts);
    }

    /**
     * Get low stock parts
     */
    public function lowStock()
    {
        $parts = Part::whereColumn('stock', '<=', 'min_stock')->get();
        return response()->json($parts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:parts,code|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $part = Part::create($validated);

        return response()->json([
            'message' => 'Repuesto creado exitosamente',
            'part' => $part
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Part $part)
    {
        $part->load('purchases');
        return response()->json($part);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|unique:parts,code,' . $part->id . '|max:255',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'sale_price' => 'sometimes|required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $part->update($validated);

        return response()->json([
            'message' => 'Repuesto actualizado exitosamente',
            'part' => $part
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Part $part)
    {
        $part->delete();

        return response()->json([
            'message' => 'Repuesto eliminado exitosamente'
        ]);
    }
}
