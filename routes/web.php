<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [WebController::class, 'login'])->name('login');

// Protected routes (require authentication)
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [WebController::class, 'customers'])->name('customers');
    Route::get('/vehicles', [WebController::class, 'vehicles'])->name('vehicles');
    Route::get('/parts', [WebController::class, 'parts'])->name('parts');
    Route::get('/work-orders', [WebController::class, 'workOrders'])->name('work-orders');
    Route::get('/work-orders/{id}', [WebController::class, 'workOrderDetails'])->name('work-orders.show');
    Route::get('/work-orders/{id}/invoice', [WebController::class, 'invoice'])->name('work-orders.invoice');
    Route::get('/reports', [WebController::class, 'reports'])->name('reports');
});
