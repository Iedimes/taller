<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Part;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard general con estadísticas
     */
    public function dashboard()
    {
        $today = now();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'summary' => [
                'total_customers' => Customer::count(),
                'total_vehicles' => Vehicle::count(),
                'total_parts' => Part::count(),
                'low_stock_parts' => Part::whereColumn('stock', '<=', 'min_stock')->count(),
            ],
            'work_orders' => [
                'total' => WorkOrder::count(),
                'pending' => WorkOrder::where('status', 'pending')->count(),
                'in_progress' => WorkOrder::where('status', 'in_progress')->count(),
                'completed_this_month' => WorkOrder::where('status', 'completed')
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
            ],
            'financial' => [
                'total_profit_all_time' => (float) WorkOrder::sum('profit'),
                'total_profit_this_month' => (float) WorkOrder::where('created_at', '>=', $thisMonth)
                    ->sum('profit'),
                'average_profit_per_order' => (float) WorkOrder::avg('profit'),
            ],
            'inventory_value' => (float) Part::selectRaw('SUM(stock * purchase_price) as total')
                ->value('total'),
        ]);
    }

    /**
     * Reporte de ventas por período
     */
    public function salesReport(Request $request)
    {
        // Validar base
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = WorkOrder::with(['vehicle.customer'])
            ->whereBetween('entry_date', [$request->start_date, $request->end_date]);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $workOrders = $query->orderBy('entry_date')->get();

        // Calcular resumen
        $totalProfit = $workOrders->sum('profit');
        $totalCost = $workOrders->sum('total_cost');
        $totalPrice = $workOrders->sum('total_price');
        $count = $workOrders->count();

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

        // Estructura compatible con el frontend modificado
        return response()->json([
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
            'work_orders' => $workOrders,
            'daily_breakdown' => $dailyBreakdown,
            'summary' => [
                'count' => $count,
                'total_orders' => $count, // Legacy support
                'total_cost' => (float) $totalCost,
                'total_price' => (float) $totalPrice,
                'total_profit' => (float) $totalProfit,
                'average_profit' => $count > 0 ? (float) ($totalProfit / $count) : 0,
                'average_ticket' => $count > 0 ? (float) ($totalPrice / $count) : 0,
            ],
        ]);
    }

    /**
     * Top clientes por utilidad
     */
    public function topCustomers(Request $request)
    {
        $limit = $request->input('limit', 10);

        $customers = Customer::select('customers.*')
            ->join('vehicles', 'customers.id', '=', 'vehicles.customer_id')
            ->join('work_orders', 'vehicles.id', '=', 'work_orders.vehicle_id')
            ->selectRaw('customers.*,
                COUNT(DISTINCT vehicles.id) as vehicles_count,
                COUNT(work_orders.id) as orders_count,
                SUM(work_orders.total_price) as total_spent,
                SUM(work_orders.profit) as total_profit')
            ->groupBy('customers.id')
            ->orderByDesc('total_profit')
            ->limit($limit)
            ->get();

        return response()->json([
            'top_customers' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'vehicles_count' => $customer->vehicles_count,
                    'orders_count' => $customer->orders_count,
                    'total_spent' => (float) $customer->total_spent,
                    'total_profit' => (float) $customer->total_profit,
                    'average_order_value' => $customer->orders_count > 0
                        ? (float) ($customer->total_spent / $customer->orders_count)
                        : 0,
                ];
            }),
        ]);
    }

    /**
     * Repuestos más vendidos
     */
    public function topParts(Request $request)
    {
        $limit = $request->input('limit', 10);

        $parts = Part::select('parts.*')
            ->join('work_order_parts', 'parts.id', '=', 'work_order_parts.part_id')
            ->selectRaw('parts.*,
                SUM(work_order_parts.quantity) as total_sold,
                SUM(work_order_parts.subtotal_cost) as total_cost,
                SUM(work_order_parts.subtotal_price) as total_revenue,
                SUM(work_order_parts.subtotal_price - work_order_parts.subtotal_cost) as total_profit')
            ->groupBy('parts.id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'top_parts' => $parts->map(function ($part) {
                return [
                    'id' => $part->id,
                    'code' => $part->code,
                    'name' => $part->name,
                    'current_stock' => $part->stock,
                    'total_sold' => $part->total_sold,
                    'total_cost' => (float) $part->total_cost,
                    'total_revenue' => (float) $part->total_revenue,
                    'total_profit' => (float) $part->total_profit,
                    'profit_margin' => $part->total_cost > 0
                        ? round(($part->total_profit / $part->total_cost) * 100, 2)
                        : 0,
                ];
            }),
        ]);
    }

    /**
     * Análisis de inventario
     */
    public function inventoryAnalysis()
    {
        $parts = Part::all();

        $lowStock = $parts->filter(function ($part) {
            return $part->stock <= $part->min_stock;
        });

        $mediumStock = $parts->filter(function ($part) {
            return $part->stock > $part->min_stock && $part->stock <= ($part->min_stock * 2);
        });

        $goodStock = $parts->filter(function ($part) {
            return $part->stock > ($part->min_stock * 2);
        });

        return response()->json([
            'summary' => [
                'total_parts' => $parts->count(),
                'total_value' => (float) $parts->sum(function ($part) {
                    return $part->stock * $part->purchase_price;
                }),
                'potential_revenue' => (float) $parts->sum(function ($part) {
                    return $part->stock * $part->sale_price;
                }),
            ],
            'stock_levels' => [
                'low' => [
                    'count' => $lowStock->count(),
                    'parts' => $lowStock->map(function ($part) {
                        return [
                            'id' => $part->id,
                            'code' => $part->code,
                            'name' => $part->name,
                            'stock' => $part->stock,
                            'min_stock' => $part->min_stock,
                            'suggested_order' => max(0, ($part->min_stock * 2) - $part->stock),
                        ];
                    })->values(),
                ],
                'medium' => [
                    'count' => $mediumStock->count(),
                ],
                'good' => [
                    'count' => $goodStock->count(),
                ],
            ],
        ]);
    }

    /**
     * Servicios más rentables
     */
    public function topServices(Request $request)
    {
        $limit = $request->input('limit', 10);

        $services = Service::select('name')
            ->selectRaw('COUNT(*) as times_performed,
                SUM(cost) as total_cost,
                SUM(price) as total_revenue,
                SUM(price - cost) as total_profit,
                AVG(price - cost) as avg_profit')
            ->groupBy('name')
            ->orderByDesc('total_profit')
            ->limit($limit)
            ->get();

        return response()->json([
            'top_services' => $services->map(function ($service) {
                return [
                    'name' => $service->name,
                    'times_performed' => $service->times_performed,
                    'total_cost' => (float) $service->total_cost,
                    'total_revenue' => (float) $service->total_revenue,
                    'total_profit' => (float) $service->total_profit,
                    'average_profit' => (float) $service->avg_profit,
                ];
            }),
        ]);
    }

    /**
     * Análisis de eficiencia (tiempo de servicio)
     */
    public function efficiencyAnalysis()
    {
        $completedOrders = WorkOrder::whereNotNull('actual_delivery_date')
            ->get();

        $onTimeOrders = $completedOrders->filter(function ($order) {
            return $order->actual_delivery_date <= $order->estimated_delivery_date;
        });

        $delayedOrders = $completedOrders->filter(function ($order) {
            return $order->actual_delivery_date > $order->estimated_delivery_date;
        });

        return response()->json([
            'summary' => [
                'total_completed' => $completedOrders->count(),
                'on_time' => $onTimeOrders->count(),
                'delayed' => $delayedOrders->count(),
                'on_time_percentage' => $completedOrders->count() > 0
                    ? round(($onTimeOrders->count() / $completedOrders->count()) * 100, 2)
                    : 0,
            ],
            'average_days' => [
                'estimated' => $completedOrders->avg(function ($order) {
                    return $order->entry_date->diffInDays($order->estimated_delivery_date);
                }),
                'actual' => $completedOrders->avg(function ($order) {
                    return $order->entry_date->diffInDays($order->actual_delivery_date);
                }),
            ],
        ]);
    }
}
