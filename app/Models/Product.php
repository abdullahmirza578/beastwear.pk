<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description',
        'short_description', 'price', 'sale_price', 'cost_price',
        'stock_quantity', 'low_stock_threshold', 'status', 'featured', 'design_selection_enabled',
        'size_selection_enabled', 'color_selection_enabled',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'featured' => 'boolean',
        'design_selection_enabled' => 'boolean',
        'size_selection_enabled' => 'boolean',
        'color_selection_enabled' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function selectableDesigns(): HasMany
    {
        return $this->images()->where('is_selectable_design', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function getCurrentPriceAttribute(): string
    {
        return $this->sale_price ?? $this->price;
    }
}
