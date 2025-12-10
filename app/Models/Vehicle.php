<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'brand',
        'model',
        'year',
        'plate',
        'vin',
        'color',
        'mileage',
    ];

    // Relación: Un vehículo pertenece a un cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación: Un vehículo tiene muchas órdenes de trabajo
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
