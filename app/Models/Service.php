<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'name',
        'description',
        'cost',
        'price',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Relación: Un servicio pertenece a una orden de trabajo
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    // Observer para actualizar totales de la orden
    protected static function booted()
    {
        static::created(function ($service) {
            $service->workOrder->updateTotals();
        });

        static::updated(function ($service) {
            $service->workOrder->updateTotals();
        });

        static::deleted(function ($service) {
            $service->workOrder->updateTotals();
        });
    }
}
