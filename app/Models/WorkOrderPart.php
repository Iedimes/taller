<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'part_id',
        'quantity',
        'unit_cost',
        'unit_price',
        'subtotal_cost',
        'subtotal_price',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal_cost' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
    ];

    // Relación: Pertenece a una orden de trabajo
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    // Relación: Pertenece a un repuesto
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    // Observer para calcular subtotales y actualizar stock
    protected static function booted()
    {
        // Calcular subtotales antes de crear O actualizar
        static::saving(function ($workOrderPart) {
            $workOrderPart->subtotal_cost = $workOrderPart->quantity * $workOrderPart->unit_cost;
            $workOrderPart->subtotal_price = $workOrderPart->quantity * $workOrderPart->unit_price;
        });

        static::created(function ($workOrderPart) {
            // Reducir stock del repuesto al crear
            $part = $workOrderPart->part;
            if ($part) {
                $part->stock -= $workOrderPart->quantity;
                $part->save();
            }
            $workOrderPart->workOrder->updateTotals();
        });

        static::updating(function ($workOrderPart) {
            // Ajustar stock si cambia la cantidad
            if ($workOrderPart->isDirty('quantity')) {
                $originalQty = $workOrderPart->getOriginal('quantity');
                $newQty = $workOrderPart->quantity;
                $diff = $newQty - $originalQty;

                $part = $workOrderPart->part;
                if ($part) {
                    $part->stock -= $diff; // Si aumentó (diff > 0), resta stock. Si disminuyó (diff < 0), suma stock.
                    $part->save();
                }
            }
        });

        static::updated(function ($workOrderPart) {
            $workOrderPart->workOrder->updateTotals();
        });

        static::deleted(function ($workOrderPart) {
            // Devolver stock al eliminar
            $part = $workOrderPart->part;
            if ($part) {
                $part->stock += $workOrderPart->quantity;
                $part->save();
            }
            $workOrderPart->workOrder->updateTotals();
        });
    }
}
