<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'purchase_price',
        'sale_price',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    // Relación: Un repuesto tiene muchas compras
    public function purchases()
    {
        return $this->hasMany(PartPurchase::class);
    }

    // Relación: Un repuesto puede estar en muchas órdenes de trabajo
    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_parts')
            ->withPivot('quantity', 'unit_cost', 'unit_price', 'subtotal_cost', 'subtotal_price')
            ->withTimestamps();
    }

    // Verificar si el stock está bajo
    public function isLowStock()
    {
        return $this->stock <= $this->min_stock;
    }
}
