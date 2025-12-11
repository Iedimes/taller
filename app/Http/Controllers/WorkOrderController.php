<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\WorkOrderPart;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workOrders = WorkOrder::with(['vehicle.customer'])->paginate(15);
        return response()->json($workOrders);
    }

    /**
     * Get work orders by status
     */
    public function byStatus($status)
    {
        $workOrders = WorkOrder::with(['vehicle.customer'])
            ->where('status', $status)
            ->paginate(15);
        return response()->json($workOrders);
    }

    /**
     * Get profit report
     */
    /**
     * Get profit report
     */
    public function profitReport(Request $request)
    {
        $query = WorkOrder::query();

        // Filtros: Validar que no estén vacíos
        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $workOrders = $query->with(['vehicle.customer'])->orderBy('entry_date')->get();

        $totalProfit = $workOrders->sum('profit');
        $totalCost = $workOrders->sum('total_cost');
        $totalPrice = $workOrders->sum('total_price');

        // Agrupación por día
        $dailyBreakdown = $workOrders->groupBy(function ($order) {
            return $order->entry_date ? $order->entry_date->format('Y-m-d') : 'Sin Fecha';
        })->map(function ($orders, $date) {
            return [
                'date' => $date,
                'profit' => (float) $orders->sum('profit'),
                'revenue' => (float) $orders->sum('total_price'),
                'count' => $orders->count(),
            ];
        })->values();

        return response()->json([
            'work_orders' => $workOrders,
            'daily_breakdown' => $dailyBreakdown,
            'summary' => [
                'total_profit' => (float) $totalProfit,
                'total_cost' => (float) $totalCost,
                'total_price' => (float) $totalPrice,
                'count' => $workOrders->count(),
                'average_ticket' => $workOrders->count() > 0 ? (float) ($totalPrice / $workOrders->count()) : 0
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date',
            'description' => 'required|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';
        $validated['labor_cost'] = $validated['labor_cost'] ?? 0;

        $workOrder = WorkOrder::create($validated);

        return response()->json([
            'message' => 'Orden de trabajo creada exitosamente',
            'work_order' => $workOrder->load('vehicle.customer')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'vehicle.customer',
            'workOrderParts.part',
            'services'
        ]);
        return new \App\Http\Resources\WorkOrderResource($workOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'entry_date' => 'sometimes|required|date',
            'estimated_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,in_progress,completed,delivered,cancelled',
            'description' => 'sometimes|required|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:50',
        ]);

        $workOrder->update($validated);
        $workOrder->updateTotals();

        return response()->json([
            'message' => 'Orden de trabajo actualizada exitosamente',
            'work_order' => $workOrder->load('vehicle.customer')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return response()->json([
            'message' => 'Orden de trabajo eliminada exitosamente'
        ]);
    }

    /**
     * Add part to work order
     */
    public function addPart(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $validated['work_order_id'] = $workOrder->id;

        $workOrderPart = WorkOrderPart::create($validated);

        return response()->json([
            'message' => 'Repuesto agregado exitosamente',
            'work_order_part' => $workOrderPart->load('part')
        ], 201);
    }

    /**
     * Remove part from work order
     */
    public function removePart(WorkOrder $workOrder, WorkOrderPart $workOrderPart)
    {
        if ($workOrderPart->work_order_id !== $workOrder->id) {
            return response()->json([
                'message' => 'El repuesto no pertenece a esta orden'
            ], 400);
        }

        $workOrderPart->delete();

        return response()->json([
            'message' => 'Repuesto eliminado exitosamente'
        ]);
    }

    /**
     * Add service to work order
     */
    public function addService(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['work_order_id'] = $workOrder->id;

        $service = Service::create($validated);

        return response()->json([
            'message' => 'Servicio agregado exitosamente',
            'service' => $service
        ], 201);
    }

    /**
     * Remove service from work order
     */
    public function removeService(WorkOrder $workOrder, Service $service)
    {
        if ($service->work_order_id !== $workOrder->id) {
            return response()->json([
                'message' => 'El servicio no pertenece a esta orden'
            ], 400);
        }

        $service->delete();

        return response()->json([
            'message' => 'Servicio eliminado exitosamente'
        ]);
    }

    /**
     * Update part in work order
     */
    public function updatePart(Request $request, WorkOrder $workOrder, WorkOrderPart $workOrderPart)
    {
        if ($workOrderPart->work_order_id !== $workOrder->id) {
            return response()->json(['message' => 'El repuesto no pertenece a esta orden'], 400);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            // unit_cost no lo dejamos editar porque es histórico, o sí? Dejémoslo por seguridad.
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['unit_cost'])) {
            $workOrderPart->unit_cost = $validated['unit_cost'];
        }

        $workOrderPart->quantity = $validated['quantity'];
        $workOrderPart->unit_price = $validated['unit_price'];
        // El modelo WorkOrderPart recalcula subtotal y stock automáticamente en su boot/update
        $workOrderPart->save();

        return response()->json([
            'message' => 'Repuesto actualizado exitosamente',
            'work_order_part' => $workOrderPart
        ]);
    }

    /**
     * Update service in work order
     */
    public function updateService(Request $request, WorkOrder $workOrder, Service $service)
    {
        if ($service->work_order_id !== $workOrder->id) {
            return response()->json(['message' => 'El servicio no pertenece a esta orden'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
        ]);

        $service->update($validated);
        // Service model trigger update on work order automatically

        return response()->json([
            'message' => 'Servicio actualizado exitosamente',
            'service' => $service
        ]);
    }

    /**
     * Show the invoice print view.
     */
    public function printInvoice($id)
    {
        $workOrder = WorkOrder::with(['vehicle.customer', 'workOrderParts.part', 'services'])->findOrFail($id);

        // Generar texto del total
        $numberToWords = $this->numberToWords((int)$workOrder->total_price);

        // Mes en texto
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        $entryDate = \Carbon\Carbon::parse($workOrder->entry_date);
        $workOrder->month_text = $months[$entryDate->month] ?? '';

        return view('work-orders.invoice-print', compact('workOrder', 'numberToWords'));
    }

    private function numberToWords($num)
    {
        $num = (int)$num;
        if ($num == 0) return 'CERO';

        $basics = [
            0 => 'CERO',
            1 => 'UN',
            2 => 'DOS',
            3 => 'TRES',
            4 => 'CUATRO',
            5 => 'CINCO',
            6 => 'SEIS',
            7 => 'SIETE',
            8 => 'OCHO',
            9 => 'NUEVE',
            10 => 'DIEZ',
            11 => 'ONCE',
            12 => 'DOCE',
            13 => 'TRECE',
            14 => 'CATORCE',
            15 => 'QUINCE',
            16 => 'DIECISEIS',
            17 => 'DIECISIETE',
            18 => 'DIECIOCHO',
            19 => 'DIECINUEVE',
            20 => 'VEINTE',
            21 => 'VEINTIUN',
            22 => 'VEINTIDOS',
            23 => 'VEINTITRES',
            24 => 'VEINTICUATRO',
            25 => 'VEINTICINCO',
            26 => 'VEINTISEIS',
            27 => 'VEINTISIETE',
            28 => 'VEINTIOCHO',
            29 => 'VEINTINUEVE'
        ];

        if ($num <= 29) return $basics[$num];

        $tens = [3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA'];

        if ($num < 100) {
            $d = floor($num / 10);
            $u = $num % 10;
            return $tens[$d] . ($u > 0 ? ' Y ' . $basics[$u] : '');
        }

        $hundreds = [1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS', 6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS'];

        if ($num < 1000) {
            if ($num == 100) return 'CIEN';
            $c = floor($num / 100);
            $r = $num % 100;
            return $hundreds[$c] . ($r > 0 ? ' ' . $this->numberToWords($r) : '');
        }

        if ($num < 1000000) {
            $m = floor($num / 1000);
            $r = $num % 1000;
            $str = ($m == 1 ? 'MIL' : $this->numberToWords($m) . ' MIL');
            return $str . ($r > 0 ? ' ' . $this->numberToWords($r) : '');
        }

        if ($num < 1000000000) {
            $m = floor($num / 1000000);
            $r = $num % 1000000;
            $str = ($m == 1 ? 'UN MILLON' : $this->numberToWords($m) . ' MILLONES');
            return $str . ($r > 0 ? ' ' . $this->numberToWords($r) : '');
        }

        return number_format($num);
    }
}
