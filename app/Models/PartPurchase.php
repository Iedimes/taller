<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'part_id',
        'supplier',
        'quantity',
        'unit_price',
        'total',
        'purchase_date',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    // Relación: Una compra pertenece a un repuesto
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    // Observer para actualizar el stock automáticamente
    protected static function booted()
    {
        static::created(function ($purchase) {
            $part = $purchase->part;
            $part->stock += $purchase->quantity;
            $part->save();
        });
    }
}
