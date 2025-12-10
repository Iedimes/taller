<?php

namespace App\Http\Controllers;

use App\Models\PartPurchase;
use App\Models\Part;
use Illuminate\Http\Request;

class PartPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = PartPurchase::with('part')->paginate(15);
        return response()->json($purchases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'supplier' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Calcular el total
        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $purchase = PartPurchase::create($validated);

        return response()->json([
            'message' => 'Compra registrada exitosamente',
            'purchase' => $purchase->load('part')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PartPurchase $partPurchase)
    {
        $partPurchase->load('part');
        return response()->json($partPurchase);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PartPurchase $partPurchase)
    {
        $validated = $request->validate([
            'part_id' => 'sometimes|required|exists:parts,id',
            'supplier' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'purchase_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        // Recalcular el total si cambió cantidad o precio
        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $quantity = $validated['quantity'] ?? $partPurchase->quantity;
            $unitPrice = $validated['unit_price'] ?? $partPurchase->unit_price;
            $validated['total'] = $quantity * $unitPrice;
        }

        $partPurchase->update($validated);

        return response()->json([
            'message' => 'Compra actualizada exitosamente',
            'purchase' => $partPurchase->load('part')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartPurchase $partPurchase)
    {
        // Restar el stock antes de eliminar
        $part = $partPurchase->part;
        $part->stock -= $partPurchase->quantity;
        $part->save();

        $partPurchase->delete();

        return response()->json([
            'message' => 'Compra eliminada exitosamente'
        ]);
    }
}
