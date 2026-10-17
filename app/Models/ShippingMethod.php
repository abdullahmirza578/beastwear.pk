<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'description', 'price', 'estimated_days', 'status'];

    protected $casts = ['price' => 'decimal:2'];
}
