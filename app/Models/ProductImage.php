<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'image', 'sort_order', 'is_primary',
        'is_selectable_design', 'design_name',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_selectable_design' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        return self::urlFor($this->image);
    }

    public static function urlFor(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = Str::after($path, 'storage/');
        $path = ltrim($path, '/');

        return Str::startsWith($path, ['products/', 'categories/'])
            ? asset('storage/'.$path)
            : asset($path);
    }
}
