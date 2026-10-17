<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->public_tracking_id) {
                return;
            }

            do {
                $trackingId = 'BW-TRK-'.Str::upper(Str::random(12));
            } while (self::where('public_tracking_id', $trackingId)->exists());

            $order->public_tracking_id = $trackingId;
        });
    }

    protected $fillable = [
        'user_id', 'coupon_id', 'order_number', 'customer_name', 'customer_phone',
        'customer_email', 'shipping_address', 'city', 'postal_code', 'subtotal',
        'discount', 'shipping_fee', 'total_amount', 'payment_method',
        'payment_status', 'order_status', 'shipping_status', 'customer_note', 'admin_note',
        'shipping_courier', 'tracking_number', 'public_tracking_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
