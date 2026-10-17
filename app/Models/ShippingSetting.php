<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [
        'nationwide',
        'default_charge',
        'free_threshold',
        'karachi_charge',
        'other_city_charge',
        'remote_area_charge',
        'karachi_days',
        'other_days',
        'courier',
        'cod_enabled',
        'cod_fee',
    ];

    protected $casts = [
        'nationwide' => 'boolean',
        'cod_enabled' => 'boolean',
        'default_charge' => 'integer',
        'free_threshold' => 'integer',
        'karachi_charge' => 'integer',
        'other_city_charge' => 'integer',
        'remote_area_charge' => 'integer',
        'cod_fee' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'nationwide' => true,
                'default_charge' => 250,
                'free_threshold' => 5000,
                'karachi_charge' => 150,
                'other_city_charge' => 250,
                'remote_area_charge' => 350,
                'karachi_days' => '1-2',
                'other_days' => '3-5',
                'courier' => 'Call Courier',
                'cod_enabled' => true,
                'cod_fee' => 100,
            ]
        );
    }

    public function toAdminArray(): array
    {
        return [
            'nationwide' => $this->nationwide,
            'defaultCharge' => $this->default_charge,
            'freeThreshold' => $this->free_threshold,
            'karachiCharge' => $this->karachi_charge,
            'otherCityCharge' => $this->other_city_charge,
            'remoteAreaCharge' => $this->remote_area_charge,
            'karachiDays' => $this->karachi_days,
            'otherDays' => $this->other_days,
            'courier' => $this->courier,
            'codEnabled' => $this->cod_enabled,
            'codFee' => $this->cod_fee,
        ];
    }

    public function deliveryFee(float $subtotal, ?string $city = null): float
    {
        if ($subtotal >= $this->free_threshold) {
            return 0;
        }

        return match (mb_strtolower(trim((string) $city))) {
            'karachi' => $this->karachi_charge,
            'remote', 'remote area' => $this->remote_area_charge,
            default => $city === null || trim($city) === ''
                ? $this->default_charge
                : $this->other_city_charge,
        };
    }
}
