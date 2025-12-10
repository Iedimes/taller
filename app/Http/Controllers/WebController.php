<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function customers()
    {
        return view('customers.index');
    }

    public function vehicles()
    {
        return view('vehicles.index');
    }

    public function parts()
    {
        return view('parts.index');
    }

    public function workOrders()
    {
        return view('work-orders.index');
    }

    public function workOrderDetails($id)
    {
        return view('work-orders.show', ['id' => $id]);
    }

    public function invoice($id)
    {
        return view('work-orders.invoice', ['id' => $id]);
    }

    public function reports()
    {
        return view('reports.index');
    }
}
