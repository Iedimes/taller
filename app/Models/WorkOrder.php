<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'vehicle_id',
        'entry_date',
        'estimated_delivery_date',
        'actual_delivery_date',
        'status',
        'description',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'total_price',
        'profit',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    // Relación: Una orden pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relación: Una orden tiene muchos servicios
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Relación: Una orden tiene muchos repuestos
    public function parts()
    {
        return $this->belongsToMany(Part::class, 'work_order_parts')
            ->withPivot('quantity', 'unit_cost', 'unit_price', 'subtotal_cost', 'subtotal_price')
            ->withTimestamps();
    }

    // Relación: Detalles de repuestos
    public function workOrderParts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    // Calcular el costo total de repuestos
    public function calculatePartsCost()
    {
        return $this->workOrderParts()->sum('subtotal_cost');
    }

    // Calcular el costo total de servicios
    public function calculateServicesCost()
    {
        return $this->services()->sum('cost');
    }

    // Calcular el precio total de servicios
    public function calculateServicesPrice()
    {
        return $this->services()->sum('price');
    }

    // Calcular el precio total de repuestos
    public function calculatePartsPrice()
    {
        return $this->workOrderParts()->sum('subtotal_price');
    }

    // Calcular y actualizar todos los totales
    public function updateTotals()
    {
        $this->parts_cost = $this->calculatePartsCost();
        $servicesCost = $this->calculateServicesCost();

        $this->total_cost = $this->parts_cost + $this->labor_cost + $servicesCost;

        $partsPrice = $this->calculatePartsPrice();
        $servicesPrice = $this->calculateServicesPrice();

        $this->total_price = $partsPrice + $servicesPrice;
        $this->profit = $this->total_price - $this->total_cost;

        $this->save();
    }

    // Generar número de orden automático
    public static function generateOrderNumber()
    {
        $lastOrder = self::orderBy('id', 'desc')->first();
        $number = $lastOrder ? (int)substr($lastOrder->order_number, 3) + 1 : 1;
        return 'OT-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // Observer para generar número de orden automáticamente
    protected static function booted()
    {
        static::creating(function ($workOrder) {
            if (empty($workOrder->order_number)) {
                $workOrder->order_number = self::generateOrderNumber();
            }
        });
    }
}
