<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartPurchaseController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ============================================
// RUTAS DE AUTENTICACIÓN (Públicas)
// ============================================
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas de autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Rutas de Clientes
Route::apiResource('customers', CustomerController::class);

// Rutas de Vehículos
Route::apiResource('vehicles', VehicleController::class);

// Rutas de Repuestos
Route::apiResource('parts', PartController::class);
Route::get('parts-low-stock', [PartController::class, 'lowStock']);

// Rutas de Compras de Repuestos
Route::apiResource('part-purchases', PartPurchaseController::class);

// Rutas de Órdenes de Trabajo
Route::apiResource('work-orders', WorkOrderController::class);
Route::get('work-orders-status/{status}', [WorkOrderController::class, 'byStatus']);
Route::get('work-orders-profit-report', [WorkOrderController::class, 'profitReport']);

// Rutas para agregar/quitar/editar repuestos y servicios a órdenes
Route::post('work-orders/{workOrder}/parts', [WorkOrderController::class, 'addPart']);
Route::put('work-orders/{workOrder}/parts/{workOrderPart}', [WorkOrderController::class, 'updatePart']);
Route::delete('work-orders/{workOrder}/parts/{workOrderPart}', [WorkOrderController::class, 'removePart']);

Route::post('work-orders/{workOrder}/services', [WorkOrderController::class, 'addService']);
Route::put('work-orders/{workOrder}/services/{service}', [WorkOrderController::class, 'updateService']);
Route::delete('work-orders/{workOrder}/services/{service}', [WorkOrderController::class, 'removeService']);

// Rutas de Reportes y Análisis
Route::prefix('reports')->group(function () {
    Route::get('dashboard', [ReportController::class, 'dashboard']);
    Route::get('sales', [ReportController::class, 'salesReport']);
    Route::get('top-customers', [ReportController::class, 'topCustomers']);
    Route::get('top-parts', [ReportController::class, 'topParts']);
    Route::get('top-services', [ReportController::class, 'topServices']);
    Route::get('inventory-analysis', [ReportController::class, 'inventoryAnalysis']);
    Route::get('efficiency', [ReportController::class, 'efficiencyAnalysis']);
});
