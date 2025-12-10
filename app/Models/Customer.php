<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'document',
        'address',
    ];

    // Relación: Un cliente tiene muchos vehículos
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
