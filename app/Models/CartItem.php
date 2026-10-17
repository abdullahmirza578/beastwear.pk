<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'product_variant_id', 'selected_design_image_id',
        'selection_key', 'quantity', 'price',
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function selectedDesign(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'selected_design_image_id');
    }
}
