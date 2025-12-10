<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with('customer')->paginate(15);
        return response()->json($vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'plate' => 'required|string|unique:vehicles,plate|max:255',
            'vin' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'mileage' => 'nullable|integer|min:0',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'message' => 'Vehículo creado exitosamente',
            'vehicle' => $vehicle->load('customer')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['customer', 'workOrders']);
        return response()->json($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'brand' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|required|string|max:255',
            'plate' => 'sometimes|required|string|unique:vehicles,plate,' . $vehicle->id . '|max:255',
            'vin' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'mileage' => 'nullable|integer|min:0',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'message' => 'Vehículo actualizado exitosamente',
            'vehicle' => $vehicle->load('customer')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehículo eliminado exitosamente'
        ]);
    }
}
