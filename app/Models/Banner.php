<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'button_text', 'button_url',
        'position', 'status', 'starts_at', 'ends_at', 'mobile_image',
    ];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->image);
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->mobile_image);
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return '/storage/'.$path;
    }
}
