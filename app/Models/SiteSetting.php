<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'store_name', 'tagline', 'logo', 'favicon', 'email', 'phone', 'whatsapp',
        'address', 'instagram', 'facebook', 'tiktok', 'youtube', 'min_order',
        'order_confirmation', 'cod_enabled', 'auto_confirm_prepaid',
        'low_stock_alerts', 'maintenance', 'currency', 'timezone',
        'bank_name', 'bank_account_title', 'bank_account_number',
        'jazzcash_account_title', 'jazzcash_account_number',
        'easypaisa_account_title', 'easypaisa_account_number',
    ];

    protected $casts = [
        'min_order' => 'integer',
        'cod_enabled' => 'boolean',
        'auto_confirm_prepaid' => 'boolean',
        'low_stock_alerts' => 'boolean',
        'maintenance' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'BeastWear.pk',
                'tagline' => 'Streetwear built for beasts.',
                'email' => 'support@beastwear.pk',
                'phone' => '021-34567890',
                'whatsapp' => '0300-1234567',
                'address' => 'Shop 12, Zamzama Commercial Lane 4, DHA Phase 5, Karachi',
                'instagram' => 'https://instagram.com/beastwear.pk',
                'facebook' => 'https://facebook.com/beastwear.pk',
                'tiktok' => 'https://tiktok.com/@beastwear.pk',
                'min_order' => 1000,
                'order_confirmation' => 'whatsapp',
                'cod_enabled' => true,
                'auto_confirm_prepaid' => true,
                'low_stock_alerts' => true,
                'maintenance' => false,
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'bank_name' => null,
                'bank_account_title' => null,
                'bank_account_number' => null,
                'jazzcash_account_title' => null,
                'jazzcash_account_number' => null,
                'easypaisa_account_title' => null,
                'easypaisa_account_number' => null,
            ]
        );
    }

    public function toAdminArray(): array
    {
        return [
            'storeName' => $this->store_name,
            'tagline' => $this->tagline,
            'logo' => $this->publicImageUrl($this->logo),
            'favicon' => $this->publicImageUrl($this->favicon),
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'tiktok' => $this->tiktok,
            'youtube' => $this->youtube,
            'minOrder' => $this->min_order,
            'orderConfirmation' => $this->order_confirmation,
            'codEnabled' => $this->cod_enabled,
            'autoConfirmPrepaid' => $this->auto_confirm_prepaid,
            'lowStockAlerts' => $this->low_stock_alerts,
            'maintenance' => $this->maintenance,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'bankName' => $this->bank_name,
            'bankAccountTitle' => $this->bank_account_title,
            'bankAccountNumber' => $this->bank_account_number,
            'jazzcashAccountTitle' => $this->jazzcash_account_title,
            'jazzcashAccountNumber' => $this->jazzcash_account_number,
            'easypaisaAccountTitle' => $this->easypaisa_account_title,
            'easypaisaAccountNumber' => $this->easypaisa_account_number,
        ];
    }

    public function prepaidPaymentMethods(): array
    {
        $methods = [];
        if ($this->bank_name && $this->bank_account_title && $this->bank_account_number) {
            $methods['Bank Transfer'] = [
                'institution' => $this->bank_name,
                'account_title' => $this->bank_account_title,
                'account_number' => $this->bank_account_number,
            ];
        }
        if ($this->jazzcash_account_title && $this->jazzcash_account_number) {
            $methods['JazzCash'] = [
                'institution' => 'JazzCash',
                'account_title' => $this->jazzcash_account_title,
                'account_number' => $this->jazzcash_account_number,
            ];
        }
        if ($this->easypaisa_account_title && $this->easypaisa_account_number) {
            $methods['EasyPaisa'] = [
                'institution' => 'EasyPaisa',
                'account_title' => $this->easypaisa_account_title,
                'account_number' => $this->easypaisa_account_number,
            ];
        }

        return $methods;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->logo) ?: null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->favicon) ?: null;
    }

    public function getWhatsappUrlAttribute(): string
    {
        $number = preg_replace('/\D+/', '', $this->whatsapp);
        if (str_starts_with($number, '0')) {
            $number = '92'.substr($number, 1);
        } elseif (! str_starts_with($number, '92')) {
            $number = '92'.$number;
        }

        return 'https://wa.me/'.$number;
    }

    public function getOrderConfirmationLabelAttribute(): string
    {
        return match ($this->order_confirmation) {
            'whatsapp' => 'WhatsApp message',
            'call' => 'Phone call',
            'sms' => 'SMS',
            'email' => 'Email',
            default => 'No confirmation required',
        };
    }

    private function publicImageUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = preg_replace('#^/?storage/#', '', ltrim($path, '/'));

        return '/storage/'.$path;
    }

    public static function deleteUploadedAsset(?string $path): void
    {
        if (! $path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $path = preg_replace('#^/?storage/#', '', ltrim($path, '/'));
        if (str_starts_with($path, 'site-settings/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
